<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateParkingSlotRequest
 * ============================================================
 *
 * Validates partial updates to an existing parking slot.
 * All fields are optional — only sent fields are validated.
 *
 * SLOT NUMBER UNIQUENESS ON UPDATE:
 *   When updating slot_number, we must ignore the CURRENT slot's
 *   own row in the unique check. We also still scope uniqueness
 *   to the same parking_id.
 */
class UpdateParkingSlotRequest extends FormRequest
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
        // Get the current slot's ID from the route parameter
        // e.g. /api/v1/parkings/{parking}/slots/{slot}
        $slotId    = $this->route('slot');
        $parkingId = $this->route('parking') ?? $this->input('parking_id');

        return [
            'vehicle_type_id' => ['sometimes', 'required', 'integer', 'exists:vehicle_types,id'],

            /**
             * When updating slot_number, ignore THIS slot's current row
             * so the owner can re-save without hitting a false unique error.
             * Still enforce uniqueness within the same parking.
             */
            'slot_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('parking_slots', 'slot_number')
                    ->ignore($slotId)
                    ->where('parking_id', $parkingId)
                    ->whereNull('deleted_at'),
            ],

            'slot_type' => ['sometimes', 'required', 'in:standard,premium,ev,handicapped'],

            /**
             * Status update: owner can set slot to maintenance or back to available.
             * 'booked' and 'reserved' statuses are set by the booking system, not manually.
             * FUTURE: Allow admin to set 'reserved' for VIP slots.
             */
            'status' => ['sometimes', 'required', 'in:available,maintenance'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.exists' => 'The selected vehicle type does not exist.',
            'slot_number.unique'     => 'This slot number already exists in this parking location.',
            'slot_number.max'        => 'Slot number cannot exceed 20 characters.',
            'slot_type.in'           => 'Slot type must be: standard, premium, ev, or handicapped.',
            'status.in'              => 'Status must be: available or maintenance.',
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