<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreParkingSlotRequest
 * ============================================================
 *
 * Validates the incoming request when an OWNER adds a new
 * parking slot to one of their parking locations.
 *
 * SLOT NUMBER UNIQUENESS:
 *   slot_number must be unique WITHIN a parking, not globally.
 *   "A1" can exist in two different parkings.
 *   Rule::unique()->where('parking_id', ...) enforces this.
 *
 * NOTE: parking_id comes from the route parameter in the controller
 * (nested resource), but we also accept it in the body as a fallback.
 */
class StoreParkingSlotRequest extends FormRequest
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
        // Get parking_id from route parameter (nested resource URL)
        // e.g. /api/v1/parkings/{parking}/slots
        $parkingId = $this->route('parking') ?? $this->input('parking_id');

        return [
            'parking_id' => ['required', 'integer', 'exists:parkings,id'],

            /**
             * vehicle_type_id: must exist in vehicle_types master table.
             * This determines which vehicle category can use this slot.
             */
            'vehicle_type_id' => ['required', 'integer', 'exists:vehicle_types,id'],

            /**
             * slot_number: alphanumeric label printed on the slot sign.
             * e.g. "A1", "B-03", "EV-01", "HC-2"
             *
             * Must be unique within the same parking location.
             * Rule::unique()->where() creates a conditional unique check.
             */
            'slot_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('parking_slots', 'slot_number')
                    ->where('parking_id', $parkingId)
                    ->whereNull('deleted_at'), // Exclude soft-deleted slots
            ],

            /**
             * slot_type: the category of parking space.
             * Must match the ENUM values defined in the migration.
             */
            'slot_type' => ['required', 'in:standard,premium,ev,handicapped'],

            /**
             * status: initial status of the slot.
             * Defaults to 'available' in DB, but owner can set 'maintenance'
             * if the slot is under construction at time of adding.
             */
            'status' => ['sometimes', 'in:available,maintenance'],
        ];
    }

    public function messages(): array
    {
        return [
            'parking_id.required'       => 'Parking location is required.',
            'parking_id.exists'         => 'The selected parking location does not exist.',
            'vehicle_type_id.required'  => 'Please select a vehicle type for this slot.',
            'vehicle_type_id.exists'    => 'The selected vehicle type does not exist.',
            'slot_number.required'      => 'Please enter a slot number (e.g. A1, B-03).',
            'slot_number.unique'        => 'This slot number already exists in this parking location.',
            'slot_number.max'           => 'Slot number cannot exceed 20 characters.',
            'slot_type.required'        => 'Please select a slot type.',
            'slot_type.in'              => 'Slot type must be: standard, premium, ev, or handicapped.',
            'status.in'                 => 'Status must be: available or maintenance.',
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