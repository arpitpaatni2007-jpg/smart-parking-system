<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreBookingRequest
 * ============================================================
 *
 * Validates the payload when a user creates a new parking booking.
 *
 * WHAT THIS VALIDATES:
 *   1. The slot exists and is selectable
 *   2. The vehicle belongs to the requesting user
 *   3. The time window is valid (start < end, not in the past)
 *   4. Duration is within allowed limits
 *
 * WHAT THIS DOES NOT VALIDATE (done in controller):
 *   - Whether the slot is actually available (not double-booked)
 *   - Whether the slot type matches the vehicle type
 *   - Pricing calculation (done in BookingService)
 *
 * FUTURE:
 *   - Add `coupon_code` string for discount integration
 *   - Add `payment_method_id` to pre-select payment channel
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * The parking slot the user wants to reserve.
             * Must exist in parking_slots table.
             * Availability (not double-booked) checked in controller.
             */
            'parking_slot_id' => ['required', 'integer', 'exists:parking_slots,id'],

            /**
             * The user's saved vehicle for this booking.
             * Must exist in vehicles table.
             * Ownership check (vehicle belongs to this user) done in controller.
             */
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],

            /**
             * Booking start time.
             * Must be a valid datetime and not in the past.
             * 'date' rule accepts: "2025-01-15 10:00:00", "2025-01-15T10:00:00"
             */
            'booking_start_time' => ['required', 'date', 'after_or_equal:now'],

            /**
             * Booking end time.
             * Must be after the start time.
             */
            'booking_end_time' => ['required', 'date', 'after:booking_start_time'],

            /**
             * Optional notes from the user.
             * e.g. "Please keep near the lift if possible."
             */
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'parking_slot_id.required'        => 'Please select a parking slot.',
            'parking_slot_id.exists'          => 'The selected parking slot does not exist.',
            'vehicle_id.required'             => 'Please select your vehicle.',
            'vehicle_id.exists'               => 'The selected vehicle does not exist.',
            'booking_start_time.required'     => 'Please select a booking start time.',
            'booking_start_time.after_or_equal' => 'Booking start time cannot be in the past.',
            'booking_end_time.required'       => 'Please select a booking end time.',
            'booking_end_time.after'          => 'Booking end time must be after the start time.',
            'notes.max'                       => 'Notes cannot exceed 500 characters.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}