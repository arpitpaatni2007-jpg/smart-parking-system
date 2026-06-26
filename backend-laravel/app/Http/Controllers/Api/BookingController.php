<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\ParkingSlot;
use App\Models\PricingRule;
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
 * BookingController
 * ============================================================
 *
 * Manages the full booking lifecycle for the Smart Parking system.
 *
 * ENDPOINTS:
 *   GET    /api/v1/bookings               → My bookings (user) or all (owner/admin)
 *   POST   /api/v1/bookings               → Create new booking
 *   GET    /api/v1/bookings/{booking}     → Single booking detail
 *   PUT    /api/v1/bookings/{booking}     → Update notes / extend end time
 *   DELETE /api/v1/bookings/{booking}     → Soft-delete (admin only)
 *   POST   /api/v1/bookings/{booking}/cancel → Cancel a booking
 *
 * BOOKING CREATION FLOW:
 *   1. Validate request (StoreBookingRequest)
 *   2. Verify vehicle belongs to the requesting user
 *   3. Verify slot is available (not double-booked) using DB-level lock
 *   4. Verify slot type matches vehicle type
 *   5. Calculate amount using PricingRule
 *   6. Create Booking record (status: pending)
 *   7. Mark slot as 'booked'
 *   8. Generate QR code for the booking
 *   9. Log initial status history entry
 *  10. Return booking with QR details
 *
 * DOUBLE-BOOKING PREVENTION:
 *   We use a SELECT FOR UPDATE (lockForUpdate()) inside a transaction
 *   to prevent two simultaneous requests from booking the same slot.
 *   This is the standard pessimistic locking approach.
 *
 * PRICING CALCULATION:
 *   Uses PricingRule for the slot's vehicle type.
 *   Formula: base_price for first hour + extra_hour_price for remaining.
 *   If no pricing rule exists for the vehicle type, a default is applied.
 *
 * FUTURE SCALABILITY:
 *   - Extract booking creation logic into a BookingService class
 *   - Add queueable job for QR generation (for high concurrency)
 *   - Add event: BookingCreated for notification dispatch
 *   - Add support for recurring bookings (daily/monthly subscriptions)
 */
class BookingController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List Bookings
    // =========================================================

    /**
     * Return paginated list of bookings.
     *
     * ACCESS RULES:
     *   Regular user  → sees only their own bookings
     *   Parking owner → sees bookings for their parking locations
     *   Admin         → sees all bookings
     *
     * QUERY PARAMETERS:
     *   ?status=confirmed          → filter by booking_status
     *   ?payment_status=unpaid     → filter by payment_status
     *   ?parking_id=3              → filter by parking (owner/admin)
     *   ?date_from=2025-01-01      → filter by booking start date
     *   ?date_to=2025-01-31        → filter by booking end date
     *   ?search=BK-20250115        → search by booking_number
     *   ?per_page=15               → pagination size (max 50)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user  = $request->user();
            $query = Booking::query()->with(['user', 'parking', 'parkingSlot', 'vehicle', 'qrBooking']);

            // ── Scope by role ──────────────────────────────────────────────
            if ($user->hasRole('user')) {
                // Regular users see only their own bookings
                $query->where('user_id', $user->id);

            } elseif ($user->hasRole('owner')) {
                // Owners see bookings for their parking locations
                $query->whereHas('parking', fn ($q) => $q->where('owner_id', $user->id));

            }
            // Admin: no scope — sees all bookings

            // ── Filters ────────────────────────────────────────────────────
            if ($request->filled('status')) {
                $query->where('booking_status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('parking_id')) {
                $query->where('parking_id', $request->parking_id);
            }

            if ($request->filled('date_from')) {
                $query->where('booking_start_time', '>=', $request->date_from . ' 00:00:00');
            }

            if ($request->filled('date_to')) {
                $query->where('booking_start_time', '<=', $request->date_to . ' 23:59:59');
            }

            // ── Search by booking number ───────────────────────────────────
            if ($request->filled('search')) {
                $query->where('booking_number', 'LIKE', '%' . $request->search . '%');
            }

            // ── Sort and paginate ──────────────────────────────────────────
            $query->latest('booking_start_time');

            $perPage  = min((int) ($request->per_page ?? 15), 50);
            $bookings = $query->paginate($perPage);

            return $this->successResponse(
                BookingResource::collection($bookings)->response()->getData(true),
                'Bookings retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve bookings.');
        }
    }

    // =========================================================
    // STORE — Create a New Booking
    // =========================================================

    /**
     * Create a new parking booking.
     *
     * @param  \App\Http\Requests\Booking\StoreBookingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // ── 1. Verify vehicle belongs to the requesting user ───────────
            $vehicle = $user->vehicles()->active()->find($request->vehicle_id);
            if (! $vehicle) {
                return $this->errorResponse(
                    'The selected vehicle does not belong to you or is inactive.',
                    422
                );
            }

            $booking = DB::transaction(function () use ($request, $user, $vehicle) {

                // ── 2. Lock the slot row to prevent concurrent bookings ─────
                /**
                 * lockForUpdate() issues SELECT ... FOR UPDATE in MySQL.
                 * This prevents any other transaction from reading or modifying
                 * this row until our transaction commits or rolls back.
                 * Essential for preventing double-booking race conditions.
                 */
                $slot = ParkingSlot::lockForUpdate()->findOrFail($request->parking_slot_id);

                // ── 3. Verify slot is available ────────────────────────────
                if (! $slot->isAvailable()) {
                    throw new \RuntimeException(
                        'This parking slot is not available. Please choose a different slot.'
                    );
                }

                // ── 4. Verify vehicle type matches slot type (where applicable)
                /**
                 * EV slots should only be booked by EVs.
                 * Standard slots accept any vehicle type.
                 * This is a soft rule — adjust based on business requirements.
                 */
                if ($slot->slot_type === 'ev' && $vehicle->vehicleType->name !== 'EV') {
                    throw new \RuntimeException(
                        'This EV slot is reserved for electric vehicles only.'
                    );
                }

                // ── 5. Check for overlapping bookings on this slot ─────────
                $hasConflict = Booking::overlapping(
                    $slot->id,
                    $request->booking_start_time,
                    $request->booking_end_time
                )->whereIn('booking_status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CHECKED_IN,
                    Booking::STATUS_PENDING,
                ])->exists();

                if ($hasConflict) {
                    throw new \RuntimeException(
                        'This slot is already booked for the selected time window. Please choose a different time or slot.'
                    );
                }

                // ── 6. Calculate duration and amount ───────────────────────
                $startTime     = now()->parse($request->booking_start_time);
                $endTime       = now()->parse($request->booking_end_time);
                $durationHours = round($startTime->diffInMinutes($endTime) / 60, 2);

                /**
                 * Look up the pricing rule for this vehicle type.
                 * Falls back to 0 if no rule found (admin should configure pricing).
                 *
                 * FUTURE: Add a default PricingRule fallback or throw an error
                 * if no pricing rule exists for the vehicle type.
                 */
                $pricingRule = PricingRule::where('vehicle_type_id', $vehicle->vehicle_type_id)
                    ->where('status', 'active')
                    ->where('pricing_type', 'hourly')
                    ->first();

                $amount = $pricingRule
                    ? $pricingRule->calculateCharge($durationHours)
                    : 0.00;

                // ── 7. Generate a unique booking number ────────────────────
                /**
                 * Format: BK-YYYYMMDD-XXXX where XXXX is 4 random uppercase chars.
                 * e.g. "BK-20250115-A3F2"
                 * Loop ensures uniqueness (collision extremely unlikely).
                 */
                do {
                    $bookingNumber = 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
                } while (Booking::where('booking_number', $bookingNumber)->exists());

                // ── 8. Create the Booking record ───────────────────────────
                $booking = Booking::create([
                    'booking_number'     => $bookingNumber,
                    'user_id'            => $user->id,
                    'parking_id'         => $slot->parking_id,
                    'parking_slot_id'    => $slot->id,
                    'vehicle_id'         => $vehicle->id,
                    'booking_start_time' => $request->booking_start_time,
                    'booking_end_time'   => $request->booking_end_time,
                    'duration_hours'     => $durationHours,
                    'amount'             => $amount,
                    'booking_status'     => Booking::STATUS_PENDING,
                    'payment_status'     => Booking::PAYMENT_UNPAID,
                    'notes'              => $request->notes,
                ]);

                // ── 9. Mark the slot as booked ─────────────────────────────
                $slot->markAsBooked();

                // ── 10. Generate QR code for entry/exit ───────────────────
                /**
                 * QR expiry = booking end time + 30 minute grace period.
                 * This gives the user a buffer to exit after their booking ends.
                 */
                QRBooking::create([
                    'booking_id' => $booking->id,
                    'qr_code'    => Str::random(64), // Cryptographically random token
                    'qr_expiry'  => $endTime->addMinutes(30),
                    'status'     => QRBooking::STATUS_ACTIVE,
                ]);

                // ── 11. Log initial status history entry ───────────────────
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => null,
                    'new_status' => Booking::STATUS_PENDING,
                    'remarks'    => 'Booking created. Awaiting payment confirmation.',
                    'changed_by' => $user->id,
                ]);

                return $booking;
            });

            // ── Load all relationships for the response ────────────────────
            $booking->load([
                'user', 'parking', 'parkingSlot.vehicleType', 'vehicle', 'qrBooking',
            ]);

            return $this->createdResponse(
                new BookingResource($booking),
                'Booking created successfully. Please complete payment to confirm your slot.'
            );

        } catch (\RuntimeException $e) {
            // Business logic errors (slot unavailable, conflict, type mismatch)
            return $this->errorResponse($e->getMessage(), 409);

        } catch (Throwable $e) {
            Log::error('BookingController@store failed', [
                'user_id' => $request->user()->id,
                'data'    => $request->validated(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to create booking. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Single Booking Detail
    // =========================================================

    /**
     * Return full detail for a single booking including all sub-records.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessBooking($request->user(), $booking)) {
                return $this->forbiddenResponse('You do not have access to this booking.');
            }

            $booking->load([
                'user',
                'parking',
                'parkingSlot.vehicleType',
                'vehicle.vehicleType',
                'checkIn.checkedInBy',
                'checkOut.checkedOutBy',
                'qrBooking',
                'statusHistory.changedBy',
            ]);

            return $this->successResponse(
                new BookingResource($booking),
                'Booking retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve booking.');
        }
    }

    // =========================================================
    // UPDATE — Update Notes / Extend End Time
    // =========================================================

    /**
     * Update a booking's notes or extend its end time.
     *
     * Only allowed for pending/confirmed bookings.
     * Changing the slot or vehicle requires cancel + rebook.
     *
     * @param  \App\Http\Requests\Booking\UpdateBookingRequest $request
     * @param  \App\Models\Booking                             $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if ($booking->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only update your own bookings.');
            }

            // ── Only updatable when pending or confirmed ───────────────────
            if (! in_array($booking->booking_status, [
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
            ])) {
                return $this->errorResponse(
                    'This booking cannot be updated in its current status: ' . $booking->booking_status,
                    409
                );
            }

            $updateData = [];

            // ── Update notes ───────────────────────────────────────────────
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            // ── Extend end time ────────────────────────────────────────────
            if ($request->filled('booking_end_time')) {
                // Verify new end time is after the start time
                if (now()->parse($request->booking_end_time)->lte($booking->booking_start_time)) {
                    return $this->errorResponse(
                        'The new end time must be after the booking start time.',
                        422
                    );
                }

                // Check for conflicts with the extended window
                $hasConflict = Booking::overlapping(
                    $booking->parking_slot_id,
                    $booking->booking_start_time,
                    $request->booking_end_time
                )->where('id', '!=', $booking->id)
                 ->whereIn('booking_status', [
                     Booking::STATUS_CONFIRMED,
                     Booking::STATUS_CHECKED_IN,
                     Booking::STATUS_PENDING,
                 ])->exists();

                if ($hasConflict) {
                    return $this->errorResponse(
                        'Cannot extend booking — another booking exists in the new time window.',
                        409
                    );
                }

                // Recalculate duration and amount for the new end time
                $newEndTime    = now()->parse($request->booking_end_time);
                $durationHours = round($booking->booking_start_time->diffInMinutes($newEndTime) / 60, 2);

                $pricingRule = PricingRule::where('vehicle_type_id', $booking->vehicle->vehicle_type_id)
                    ->where('status', 'active')
                    ->where('pricing_type', 'hourly')
                    ->first();

                $updateData['booking_end_time'] = $request->booking_end_time;
                $updateData['duration_hours']   = $durationHours;
                $updateData['amount']           = $pricingRule
                    ? $pricingRule->calculateCharge($durationHours)
                    : $booking->amount;

                // Update QR expiry to new end time + grace period
                $booking->qrBooking?->update([
                    'qr_expiry' => $newEndTime->addMinutes(30),
                ]);
            }

            if (empty($updateData)) {
                return $this->errorResponse('No update data provided.', 400);
            }

            $booking->update($updateData);
            $booking->load(['user', 'parking', 'parkingSlot', 'vehicle', 'qrBooking']);

            return $this->successResponse(
                new BookingResource($booking),
                'Booking updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingController@update failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update booking.');
        }
    }

    // =========================================================
    // DESTROY — Soft Delete (Admin Only)
    // =========================================================

    /**
     * Soft-delete a booking record. Admin only.
     * Users cancel via the /cancel endpoint — not delete.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only admins can delete booking records.');
            }

            // Cannot delete an active booking
            if (in_array($booking->booking_status, [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
            ])) {
                return $this->errorResponse(
                    'Cannot delete an active booking. Cancel it first.',
                    409
                );
            }

            $booking->delete();

            return $this->successResponse(null, 'Booking record deleted successfully.');

        } catch (Throwable $e) {
            Log::error('BookingController@destroy failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete booking.');
        }
    }

    // =========================================================
    // CANCEL — Cancel a Booking
    // =========================================================

    /**
     * Cancel a booking.
     *
     * FLOW:
     *   1. Verify the booking is cancellable (pending or confirmed)
     *   2. Authorization: user owns it, or owner's parking, or admin
     *   3. Update booking status → cancelled
     *   4. Release the parking slot → available
     *   5. Revoke the QR code → revoked
     *   6. Log status change in history
     *   7. Return updated booking
     *
     * REFUND:
     *   This endpoint does NOT process refunds.
     *   Refund eligibility is determined by the payment module
     *   based on cancellation policy. A separate RefundController
     *   handles the actual refund flow.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        try {
            // ── Check cancellability ───────────────────────────────────────
            if (! $booking->isCancellable()) {
                return $this->errorResponse(
                    'This booking cannot be cancelled. It is currently: ' . $booking->booking_status,
                    409
                );
            }

            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessBooking($request->user(), $booking)) {
                return $this->forbiddenResponse('You do not have permission to cancel this booking.');
            }

            $cancelReason = $request->input('reason', 'Cancelled by user.');

            DB::transaction(function () use ($booking, $request, $cancelReason) {
                $oldStatus = $booking->booking_status;

                // ── Update booking status ──────────────────────────────────
                $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

                // ── Release the parking slot ───────────────────────────────
                $booking->parkingSlot?->markAsAvailable();

                // ── Revoke the QR code ─────────────────────────────────────
                $booking->qrBooking?->revoke();

                // ── Log the status change ──────────────────────────────────
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => $oldStatus,
                    'new_status' => Booking::STATUS_CANCELLED,
                    'remarks'    => $cancelReason,
                    'changed_by' => $request->user()->id,
                ]);
            });

            $booking->load(['user', 'parking', 'parkingSlot', 'vehicle', 'qrBooking', 'statusHistory']);

            return $this->successResponse(
                new BookingResource($booking),
                'Booking cancelled successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingController@cancel failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to cancel booking. Please try again.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Check if the authenticated user can access a given booking.
     *
     * Rules:
     *   - Admin: always yes
     *   - Owner: yes if the booking is for their parking
     *   - User:  yes if they own the booking
     *
     * @param  \App\Models\User    $user
     * @param  \App\Models\Booking $booking
     * @return bool
     */
    private function canAccessBooking($user, Booking $booking): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('owner')) {
            // Load parking relationship if not loaded
            $booking->loadMissing('parking');
            return $booking->parking?->owner_id === $user->id;
        }

        // Regular user: must own the booking
        return $booking->user_id === $user->id;
    }
}