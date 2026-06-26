<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreCheckOutRequest;
use App\Http\Resources\CheckOutResource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\CheckOut;
use App\Models\PricingRule;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * CheckOutController
 * ============================================================
 *
 * Handles vehicle check-out events at a parking facility.
 *
 * ENDPOINTS:
 *   POST /api/v1/bookings/{booking}/checkout  → Process check-out
 *   GET  /api/v1/bookings/{booking}/checkout  → Get check-out details
 *
 * CHECK-OUT FLOW:
 *   1. Verify booking is in 'checked_in' status
 *   2. Verify no existing check-out (prevents duplicate)
 *   3. Calculate actual duration from check-in time to now
 *   4. Calculate overstay hours and extra charges
 *   5. Create CheckOut record
 *   6. Update Booking: status → 'checked_out', actual_checkout_time
 *   7. Release the parking slot → 'available'
 *   8. Mark QR as used
 *   9. Log status history
 *
 * OVERSTAY CALCULATION:
 *   extra_hours  = max(0, actual_hours − booked_hours)
 *   extra_amount = ceil(extra_hours) × PricingRule.extra_hour_price
 *
 *   Example:
 *     Booked: 10:00–12:00 (2 hours, base ₹60)
 *     Actual checkout: 13:30 (3.5 hours)
 *     extra_hours  = 3.5 − 2 = 1.5 → ceil = 2 extra hours billed
 *     extra_amount = 2 × ₹20 = ₹40
 *     Total bill   = ₹60 + ₹40 = ₹100
 *
 * WHO CAN CHECK OUT:
 *   - Parking owner (for their own parking locations)
 *   - Admin (any booking)
 *   Users present their QR — staff processes the checkout.
 *
 * FUTURE SCALABILITY:
 *   - Dispatch a BookingCompleted event for payment processing
 *   - Dispatch notification to user with final bill
 *   - Add `waive_overstay` flag for staff to waive extra charges
 *   - Move calculation into a dedicated BillingService
 */
class CheckOutController extends Controller
{
    use ApiResponse;

    // =========================================================
    // STORE — Process a Check-Out
    // =========================================================

    /**
     * Record a vehicle check-out and compute any overstay charges.
     *
     * @param  \App\Http\Requests\Booking\StoreCheckOutRequest $request
     * @param  \App\Models\Booking                             $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCheckOutRequest $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canProcessCheckOut($request->user(), $booking)) {
                return $this->forbiddenResponse(
                    'You are not authorized to process check-out for this booking.'
                );
            }

            // ── Booking must be in 'checked_in' status ─────────────────────
            if ($booking->booking_status !== Booking::STATUS_CHECKED_IN) {
                return $this->errorResponse(
                    'Check-out is only allowed for checked-in bookings. Current status: ' . $booking->booking_status,
                    409
                );
            }

            // ── Prevent duplicate check-out ────────────────────────────────
            if ($booking->checkOut()->exists()) {
                return $this->errorResponse(
                    'This booking has already been checked out.',
                    409
                );
            }

            // ── Check-in record must exist ─────────────────────────────────
            $checkIn = $booking->checkIn;
            if (! $checkIn) {
                return $this->errorResponse(
                    'Cannot checkout — no check-in record found for this booking.',
                    422
                );
            }

            // ── Resolve checkout time ──────────────────────────────────────
            $checkoutTime = $request->filled('checkout_time')
                ? now()->parse($request->checkout_time)
                : now();

            // Checkout time must be after check-in time
            if ($checkoutTime->lte($checkIn->checkin_time)) {
                return $this->errorResponse(
                    'Checkout time must be after check-in time.',
                    422
                );
            }

            // ── Calculate overstay ─────────────────────────────────────────
            [$extraHours, $extraAmount] = $this->calculateOverstay($booking, $checkoutTime);

            $checkOut = DB::transaction(function () use (
                $request, $booking, $checkoutTime, $extraHours, $extraAmount
            ) {
                // ── Create CheckOut record ─────────────────────────────────
                $checkOut = CheckOut::create([
                    'booking_id'     => $booking->id,
                    'checked_out_by' => $request->user()->id,
                    'checkout_time'  => $checkoutTime,
                    'extra_hours'    => $extraHours,
                    'extra_amount'   => $extraAmount,
                    'notes'          => $request->notes,
                ]);

                // ── Update booking ─────────────────────────────────────────
                $booking->update([
                    'booking_status'      => Booking::STATUS_CHECKED_OUT,
                    'actual_checkout_time'=> $checkoutTime,
                ]);

                // ── Free up the parking slot ───────────────────────────────
                $booking->parkingSlot?->markAsAvailable();

                // ── Mark QR as used ────────────────────────────────────────
                $booking->qrBooking?->markAsUsed();

                // ── Log status history ─────────────────────────────────────
                $remarks = 'Vehicle checked out at ' . $checkoutTime->format('d M Y H:i') . '.';
                if ($extraHours > 0) {
                    $remarks .= " Overstay: {$extraHours} hours, extra charge: ₹{$extraAmount}.";
                }

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => Booking::STATUS_CHECKED_IN,
                    'new_status' => Booking::STATUS_CHECKED_OUT,
                    'remarks'    => $remarks,
                    'changed_by' => $request->user()->id,
                ]);

                return $checkOut;
            });

            $checkOut->load('checkedOutBy');

            $message = $extraHours > 0
                ? "Check-out successful. Overstay charge of ₹{$extraAmount} applies. Total bill: ₹" . $checkOut->totalBill()
                : 'Check-out successful. Thank you for using Smart Parking!';

            return $this->createdResponse(
                new CheckOutResource($checkOut),
                $message
            );

        } catch (Throwable $e) {
            Log::error('CheckOutController@store failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to process check-out. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Check-Out Details
    // =========================================================

    /**
     * Return check-out details for a booking.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        try {
            if (! $this->canViewBooking($request->user(), $booking)) {
                return $this->forbiddenResponse('You do not have access to this booking.');
            }

            $checkOut = $booking->checkOut()->with('checkedOutBy')->first();

            if (! $checkOut) {
                return $this->notFoundResponse('No check-out record found for this booking.');
            }

            return $this->successResponse(
                new CheckOutResource($checkOut),
                'Check-out details retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CheckOutController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve check-out details.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Calculate overstay hours and extra charge.
     *
     * LOGIC:
     *   1. Actual duration = checkout_time − checkin_time (in hours)
     *   2. Booked duration = booking.duration_hours
     *   3. Extra hours     = max(0, actual − booked) → rounded UP (ceil)
     *      We round up because parking lots typically bill full hours.
     *   4. Extra amount    = extra_hours × extra_hour_price from PricingRule
     *
     * @param  \App\Models\Booking            $booking
     * @param  \Illuminate\Support\Carbon     $checkoutTime
     * @return array{float, float}  [$extraHours, $extraAmount]
     */
    private function calculateOverstay(Booking $booking, $checkoutTime): array
    {
        // Load check-in relationship if needed
        $booking->loadMissing('checkIn');
        $checkIn = $booking->checkIn;

        if (! $checkIn) {
            return [0.0, 0.0];
        }

        // Actual duration in minutes → hours
        $actualMinutes  = $checkIn->checkin_time->diffInMinutes($checkoutTime);
        $actualHours    = $actualMinutes / 60;

        // Booked duration (stored when booking was created)
        $bookedHours    = (float) ($booking->duration_hours ?? 0);

        // Extra hours = how long they stayed beyond what they paid for
        $rawExtraHours  = max(0, $actualHours - $bookedHours);
        $extraHours     = (float) ceil($rawExtraHours * 2) / 2; // Round to nearest 0.5 hour
        // ALTERNATIVE: use ceil($rawExtraHours) to round to full hours

        if ($extraHours <= 0) {
            return [0.0, 0.0];
        }

        // Look up extra hour price from PricingRule
        $booking->loadMissing(['vehicle.vehicleType']);
        $vehicleTypeId = $booking->vehicle?->vehicle_type_id;

        $pricingRule = $vehicleTypeId
            ? PricingRule::where('vehicle_type_id', $vehicleTypeId)
                ->where('status', 'active')
                ->where('pricing_type', 'hourly')
                ->first()
            : null;

        $extraHourPrice = $pricingRule
            ? (float) $pricingRule->extra_hour_price
            : 0.0;

        $extraAmount = round($extraHours * $extraHourPrice, 2);

        return [$extraHours, $extraAmount];
    }

    /**
     * Only parking owners (their own parking) and admins can process checkouts.
     */
    private function canProcessCheckOut($user, Booking $booking): bool
    {
        if ($user->hasRole('admin')) return true;

        if ($user->hasRole('owner')) {
            $booking->loadMissing('parking');
            return $booking->parking?->owner_id === $user->id;
        }

        return false;
    }

    /**
     * Users can view their own booking's checkout. Owners and admins can view any.
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