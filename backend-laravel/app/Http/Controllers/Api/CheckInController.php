<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreCheckInRequest;
use App\Http\Resources\CheckInResource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\CheckIn;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * CheckInController
 * ============================================================
 *
 * Handles vehicle check-in events at a parking facility.
 *
 * ENDPOINTS:
 *   POST /api/v1/bookings/{booking}/checkin  → Process check-in
 *   GET  /api/v1/bookings/{booking}/checkin  → Get check-in details
 *
 * WHO CAN CHECK IN:
 *   - Parking owner (for their own parking locations)
 *   - Admin (any booking)
 *
 *   Regular users cannot process check-ins — they present their QR code.
 *   The owner/staff scans it and calls this endpoint.
 *
 *   FUTURE: Add a dedicated QR scan endpoint for automated gates.
 *
 * CHECK-IN FLOW:
 *   1. Verify booking is in 'confirmed' status
 *   2. Verify no existing check-in (prevents duplicate)
 *   3. Create CheckIn record
 *   4. Update Booking: status → 'checked_in', actual_checkin_time = NOW()
 *   5. Log status history
 *   6. Return CheckIn details
 */
class CheckInController extends Controller
{
    use ApiResponse;

    // =========================================================
    // STORE — Process a Check-In
    // =========================================================

    /**
     * Record a vehicle check-in for a confirmed booking.
     *
     * @param  \App\Http\Requests\Booking\StoreCheckInRequest $request
     * @param  \App\Models\Booking                            $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCheckInRequest $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization: only owner of the parking or admin ──────────
            if (! $this->canProcessCheckIn($request->user(), $booking)) {
                return $this->forbiddenResponse(
                    'You are not authorized to process check-in for this booking.'
                );
            }

            // ── Booking must be confirmed to check in ──────────────────────
            if ($booking->booking_status !== Booking::STATUS_CONFIRMED) {
                return $this->errorResponse(
                    'Check-in is only allowed for confirmed bookings. Current status: ' . $booking->booking_status,
                    409
                );
            }

            // ── Prevent duplicate check-in ─────────────────────────────────
            if ($booking->checkIn()->exists()) {
                return $this->errorResponse(
                    'This booking has already been checked in.',
                    409
                );
            }

            $checkinTime = $request->filled('checkin_time')
                ? $request->checkin_time
                : now();

            $checkIn = DB::transaction(function () use ($request, $booking, $checkinTime) {

                // ── Create the CheckIn record ──────────────────────────────
                $checkIn = CheckIn::create([
                    'booking_id'    => $booking->id,
                    'checked_in_by' => $request->user()->id, // Staff/owner who processed it
                    'checkin_time'  => $checkinTime,
                    'notes'         => $request->notes,
                ]);

                // ── Update booking status and actual check-in time ─────────
                $booking->update([
                    'booking_status'      => Booking::STATUS_CHECKED_IN,
                    'actual_checkin_time' => $checkinTime,
                ]);

                // ── Log status history ─────────────────────────────────────
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => Booking::STATUS_CONFIRMED,
                    'new_status' => Booking::STATUS_CHECKED_IN,
                    'remarks'    => 'Vehicle checked in at ' . now()->format('d M Y H:i') . '.',
                    'changed_by' => $request->user()->id,
                ]);

                return $checkIn;
            });

            $checkIn->load('checkedInBy');

            return $this->createdResponse(
                new CheckInResource($checkIn),
                'Vehicle checked in successfully. Welcome to ' . ($booking->parking->name ?? 'the parking') . '!'
            );

        } catch (Throwable $e) {
            Log::error('CheckInController@store failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to process check-in. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Check-In Details
    // =========================================================

    /**
     * Return check-in details for a booking.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        try {
            // Any authorized user who can see the booking can see its check-in
            if (! $this->canViewBooking($request->user(), $booking)) {
                return $this->forbiddenResponse('You do not have access to this booking.');
            }

            $checkIn = $booking->checkIn()->with('checkedInBy')->first();

            if (! $checkIn) {
                return $this->notFoundResponse('No check-in record found for this booking.');
            }

            return $this->successResponse(
                new CheckInResource($checkIn),
                'Check-in details retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CheckInController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve check-in details.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Only parking owners (for their own parking) and admins can process check-ins.
     */
    private function canProcessCheckIn($user, Booking $booking): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('owner')) {
            $booking->loadMissing('parking');
            return $booking->parking?->owner_id === $user->id;
        }

        return false; // Regular users cannot process check-ins
    }

    /**
     * Users can view their own booking's check-in details.
     * Owners and admins can view any.
     */
    private function canViewBooking($user, Booking $booking): bool
    {
        if ($user->hasRole('admin')) return true;

        if ($user->hasRole('owner')) {
            $booking->loadMissing('parking');
            return $booking->parking?->owner_id === $user->id;
        }

        return $booking->user_id === $user->id;
    }
}