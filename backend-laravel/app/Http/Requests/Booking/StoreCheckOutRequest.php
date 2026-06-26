<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreCheckOutRequest
 * ============================================================
 *
 * Validates the payload when a booking is being checked out.
 *
 * OVERSTAY CALCULATION:
 *   extra_hours and extra_amount are COMPUTED in the controller
 *   from (actual checkout time − booking_end_time).
 *   They are NOT submitted by the client — the server calculates them.
 *   This prevents clients from submitting false overstay values.
 *
 * CHECKOUT CAN BE TRIGGERED BY:
 *   1. Parking staff scanning QR at exit gate
 *   2. Parking owner manually processing checkout
 *   3. System auto-checkout (scheduled job for overdue bookings)
 *
 * FUTURE:
 *   - Add `waive_overstay` boolean for staff to waive extra charges
 *   - Add `exit_photo` for camera evidence
 */
class StoreCheckOutRequest extends FormRequest
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
             * Checkout time defaults to NOW() in the controller if not provided.
             * Allowing it here lets staff retroactively record a checkout.
             * Must not be in the future.
             */
            'checkout_time' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:now',
            ],

            /**
             * Optional staff notes about this checkout.
             * e.g. "Vehicle left without paying extra — chasing up."
             */
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'checkout_time.before_or_equal' => 'Checkout time cannot be in the future.',
            'notes.max'                     => 'Notes cannot exceed 1000 characters.',
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