<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreCheckInRequest
 * ============================================================
 *
 * Validates the payload when a booking is being checked in.
 *
 * CHECK-IN CAN BE TRIGGERED BY:
 *   1. Parking staff scanning the user's QR code at the gate
 *   2. The parking owner manually processing check-in from their app
 *   3. An automated gate system (in future) via API
 *
 * For cases 1 and 2: checked_in_by = auth()->id() (set in controller)
 * For case 3 (automated): checked_in_by = null (system action)
 *
 * FUTURE:
 *   - Add `gate_id` for multi-gate facilities
 *   - Add `checkin_method` enum('qr_scan','manual','anpr')
 *   - Add `vehicle_photo` for camera-captured entry evidence
 */
class StoreCheckInRequest extends FormRequest
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
             * checkin_time defaults to NOW() in the controller if not provided.
             * Allowing it here lets staff retroactively record a check-in
             * if the system was briefly offline.
             *
             * Must not be in the future — you can't check in before arriving!
             */
            'checkin_time' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:now',
            ],

            /**
             * Optional staff notes about this check-in.
             * e.g. "Scratch on rear bumper noted on arrival."
             */
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'checkin_time.before_or_equal' => 'Check-in time cannot be in the future.',
            'notes.max'                    => 'Notes cannot exceed 1000 characters.',
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