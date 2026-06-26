<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreQRBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\QRBookingResource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\CheckIn;
use App\Models\QRBooking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * ============================================================
 * QRBookingController
 * ============================================================
 *
 * Manages QR codes for booking entry/exit verification.
 *
 * ENDPOINTS:
 *   GET    /api/v1/bookings/{booking}/qr          → Get current QR code
 *   POST   /api/v1/bookings/{booking}/qr          → Regenerate QR code
 *   POST   /api/v1/bookings/verify-qr             → Scan & verify a QR token
 *
 * HOW QR WORKS IN THE SYSTEM:
 *
 *   GENERATION:
 *     QR is auto-generated when a booking is created (status: pending).
 *     The Flutter app displays the QR image by encoding qr_code token
 *     using a QR library (e.g. qr_flutter package).
 *
 *   SCANNING AT ENTRY (Check-In):
 *     Gate scanner reads token → calls POST /bookings/verify-qr
 *     → server finds booking → triggers check-in flow
 *     → returns booking details to gate system
 *
 *   SCANNING AT EXIT (Check-Out):
 *     Same token is scanned again at exit.
 *     Server checks booking.booking_status = 'checked_in'
 *     → triggers check-out flow
 *
 *   REGENERATION:
 *     User can request a new QR if:
 *       - Original QR expired
 *       - QR was accidentally shared
 *     Old QR is revoked, new one is issued.
 *
 * SECURITY:
 *   - qr_code token is 64 random characters (cryptographically secure)
 *   - Tokens are single-use per direction (entry OR exit)
 *   - Expired tokens are rejected at the gate
 *   - Revoked tokens (on cancellation) are also rejected
 *
 * FUTURE SCALABILITY:
 *   - Add Redis cache for high-speed QR token lookups at gate
 *   - Add `scan_count` column for multi-scan analytics
 *   - Add gate_id for multi-gate facilities
 *   - Add ANPR (number plate recognition) as fallback to QR
 */
class QRBookingController extends Controller
{
    use ApiResponse;

    // =========================================================
    // SHOW — Get Current QR Code for a Booking
    // =========================================================

    /**
     * Return the current QR code for a booking.
     * The user displays this in the Flutter app at the gate.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization: only the booking owner can see their QR ──────
            if ($booking->user_id !== $request->user()->id && ! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('You do not have access to this QR code.');
            }

            $qrBooking = $booking->qrBooking;

            if (! $qrBooking) {
                return $this->notFoundResponse(
                    'No QR code found for this booking. Please contact support.'
                );
            }

            // ── If the QR is expired, suggest regeneration ─────────────────
            if ($qrBooking->isExpired() || $qrBooking->status === QRBooking::STATUS_EXPIRED) {
                return $this->errorResponse(
                    'Your QR code has expired. Please regenerate a new QR code.',
                    410 // 410 Gone — resource no longer available
                );
            }

            if ($qrBooking->status === QRBooking::STATUS_REVOKED) {
                return $this->errorResponse(
                    'This QR code has been revoked (booking was cancelled).',
                    410
                );
            }

            return $this->successResponse(
                new QRBookingResource($qrBooking),
                'QR code retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('QRBookingController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve QR code.');
        }
    }

    // =========================================================
    // STORE — Regenerate QR Code
    // =========================================================

    /**
     * Revoke the existing QR and generate a fresh one.
     *
     * USE CASES:
     *   - Old QR expired (user arrives but QR is past expiry)
     *   - User accidentally shared QR with someone else
     *   - Technical issue with QR display
     *
     * NEW QR EXPIRY:
     *   If the booking hasn't started yet → expiry = booking_end_time + 30 min
     *   If currently checked in → expiry = NOW + 2 hours (overstay buffer)
     *   If booking is past end time but still active → expiry = NOW + 30 min
     *
     * @param  \App\Http\Requests\Booking\StoreQRBookingRequest $request
     * @param  \App\Models\Booking                              $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreQRBookingRequest $request, Booking $booking): JsonResponse
    {
        try {
            // ── Only the booking owner can regenerate their QR ─────────────
            if ($booking->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only regenerate QR codes for your own bookings.');
            }

            // ── QR regeneration only makes sense for active bookings ────────
            if (! in_array($booking->booking_status, [
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
            ])) {
                return $this->errorResponse(
                    'QR code cannot be regenerated for bookings with status: ' . $booking->booking_status,
                    409
                );
            }

            $newQR = DB::transaction(function () use ($booking) {

                // ── Revoke the existing QR (if any) ───────────────────────
                $existing = $booking->qrBooking;
                if ($existing && ! in_array($existing->status, [
                    QRBooking::STATUS_USED,
                    QRBooking::STATUS_REVOKED,
                ])) {
                    $existing->update(['status' => QRBooking::STATUS_REVOKED]);
                }

                // ── Calculate new expiry time ──────────────────────────────
                /**
                 * Grace period logic:
                 *   - Booking not yet started → use booking_end_time + 30min
                 *   - Checked in (overstay risk) → NOW + 2 hours
                 *   - Past end time but confirmed → NOW + 30 min
                 */
                if ($booking->booking_status === Booking::STATUS_CHECKED_IN) {
                    $newExpiry = now()->addHours(2);
                } elseif (now()->lt($booking->booking_end_time)) {
                    $newExpiry = $booking->booking_end_time->addMinutes(30);
                } else {
                    $newExpiry = now()->addMinutes(30);
                }

                // ── Create the new QR record ───────────────────────────────
                return QRBooking::create([
                    'booking_id' => $booking->id,
                    'qr_code'    => Str::random(64), // New cryptographically random token
                    'qr_expiry'  => $newExpiry,
                    'status'     => QRBooking::STATUS_ACTIVE,
                ]);
            });

            return $this->createdResponse(
                new QRBookingResource($newQR),
                'New QR code generated successfully. The previous QR code is no longer valid.'
            );

        } catch (Throwable $e) {
            Log::error('QRBookingController@store failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to regenerate QR code.');
        }
    }

    // =========================================================
    // VERIFY QR — Gate Scanner Endpoint
    // =========================================================

    /**
     * Verify a QR token scanned at the entry or exit gate.
     *
     * This endpoint is called by:
     *   - Gate scanner hardware (via API)
     *   - Owner/staff app when manually scanning a QR
     *
     * RESPONSE:
     *   Returns the full booking details so the gate system
     *   can display: vehicle number, slot, user name, and
     *   whether to allow entry (check-in) or exit (check-out).
     *
     * GATE LOGIC:
     *   Booking status = 'confirmed'   → Gate opens for ENTRY → trigger check-in
     *   Booking status = 'checked_in'  → Gate opens for EXIT  → trigger check-out
     *   Anything else                  → Gate stays closed     → show error
     *
     * NOTE: This endpoint does NOT automatically process check-in/out.
     * The gate system receives the booking info and then calls the
     * appropriate CheckInController or CheckOutController endpoint.
     *
     * FUTURE:
     *   - Combine verify + check-in into one atomic endpoint for
     *     fully automated unmanned gates (single API call per scan)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyQR(Request $request): JsonResponse
    {
        try {
            // ── Validate that a token was provided ─────────────────────────
            $request->validate([
                'token' => ['required', 'string'],
            ]);

            // ── Look up the QR token ───────────────────────────────────────
            $qrBooking = QRBooking::where('qr_code', $request->token)->first();

            if (! $qrBooking) {
                return $this->errorResponse('Invalid QR code. No booking found for this code.', 404);
            }

            // ── Check QR status ────────────────────────────────────────────
            if ($qrBooking->status === QRBooking::STATUS_REVOKED) {
                return $this->errorResponse(
                    'This QR code has been revoked. The booking may have been cancelled.',
                    403
                );
            }

            if ($qrBooking->status === QRBooking::STATUS_EXPIRED || $qrBooking->isExpired()) {
                // Auto-update status if expired but not yet marked
                if ($qrBooking->status !== QRBooking::STATUS_EXPIRED) {
                    $qrBooking->markAsExpired();
                }
                return $this->errorResponse(
                    'This QR code has expired. Ask the user to generate a new QR code.',
                    403
                );
            }

            // ── Load the booking with all needed relationships ─────────────
            $booking = $qrBooking->booking()->with([
                'user',
                'parking',
                'parkingSlot.vehicleType',
                'vehicle',
                'checkIn',
            ])->first();

            if (! $booking) {
                return $this->errorResponse('Booking not found for this QR code.', 404);
            }

            // ── Determine gate action based on booking status ──────────────
            $gateAction = match($booking->booking_status) {
                Booking::STATUS_CONFIRMED  => 'allow_entry',   // Scan at entry → check-in
                Booking::STATUS_CHECKED_IN => 'allow_exit',    // Scan at exit  → check-out
                Booking::STATUS_PENDING    => 'payment_pending', // Payment not confirmed yet
                default                   => 'deny',
            };

            $actionMessages = [
                'allow_entry'     => 'Valid QR. Allow vehicle entry and process check-in.',
                'allow_exit'      => 'Valid QR. Allow vehicle exit and process check-out.',
                'payment_pending' => 'Booking is pending payment. Gate access not allowed yet.',
                'deny'            => 'Booking status does not permit gate access: ' . $booking->booking_status,
            ];

            return $this->successResponse(
                [
                    'gate_action'  => $gateAction,
                    'can_proceed'  => in_array($gateAction, ['allow_entry', 'allow_exit']),
                    'message'      => $actionMessages[$gateAction],
                    'booking'      => new BookingResource($booking),
                    'qr'           => new QRBookingResource($qrBooking),
                ],
                $actionMessages[$gateAction]
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'QR token is required.');
        } catch (Throwable $e) {
            Log::error('QRBookingController@verifyQR failed', [
                'token' => substr($request->token ?? '', 0, 8) . '...', // Log only first 8 chars
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to verify QR code.');
        }
    }
}