<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * UpdateBookingRequest
 * ============================================================
 *
 * Validates the payload when a user updates an existing booking.
 *
 * WHAT CAN BE UPDATED:
 *   A booking in 'pending' or 'confirmed' status can have:
 *   - notes updated (always allowed)
 *   - booking times extended (if slot is still available for new window)
 *
 * WHAT CANNOT BE UPDATED:
 *   - parking_slot_id: changing the slot = cancel + rebook
 *   - vehicle_id: changing the vehicle = cancel + rebook
 *   - booking_status: changed via dedicated cancel endpoint
 *   - payment_status: changed by the payment system
 *
 * FUTURE:
 *   - Add `extend_hours` integer for booking extension flow
 *   - Add `new_end_time` for explicit time extension
 */
class UpdateBookingRequest extends FormRequest
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
             * Allow updating the end time to extend a booking.
             * Must remain after the original start time.
             * Slot re-availability check done in controller.
             */
            'booking_end_time' => [
                'sometimes',
                'required',
                'date',
                'after:now',
            ],

            /**
             * Notes can be updated at any time before check-in.
             */
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_end_time.after' => 'The new end time must be in the future.',
            'notes.max'              => 'Notes cannot exceed 500 characters.',
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