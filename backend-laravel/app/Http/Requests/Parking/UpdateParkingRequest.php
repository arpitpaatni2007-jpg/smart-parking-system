<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * UpdateParkingRequest
 * ============================================================
 *
 * Validates the incoming request when an OWNER updates an
 * existing parking location. All fields are optional (sometimes)
 * — only the fields sent will be validated and updated.
 *
 * PARTIAL UPDATE SUPPORT:
 *   'sometimes' means: only validate this field if it is present
 *   in the request. If a field is not sent, its existing DB
 *   value remains unchanged.
 *
 *   This supports PATCH-style partial updates via PUT endpoint.
 */
class UpdateParkingRequest extends FormRequest
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
            'state_id'    => ['sometimes', 'required', 'integer', 'exists:states,id'],
            'city_id'     => ['sometimes', 'required', 'integer', 'exists:cities,id'],
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'address'     => ['sometimes', 'required', 'string', 'max:1000'],
            'latitude'    => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude'   => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'total_slots' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],

            /**
             * Status can only be changed by admin in real systems.
             * For now we allow owner to set active/inactive.
             * FUTURE: restrict 'active' status change to admin only.
             */
            'status' => ['sometimes', 'required', 'in:active,inactive,pending'],

            // Facilities array for sync (replaces all existing facility links)
            'facility_ids'   => ['sometimes', 'nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:parking_facilities,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'state_id.exists'       => 'The selected state does not exist.',
            'city_id.exists'        => 'The selected city does not exist.',
            'name.max'              => 'Parking name cannot exceed 255 characters.',
            'latitude.between'      => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between'     => 'Longitude must be between -180 and 180 degrees.',
            'total_slots.min'       => 'Your parking must have at least 1 slot.',
            'status.in'             => 'Status must be one of: active, inactive, pending.',
            'facility_ids.*.exists' => 'One or more selected facilities do not exist.',
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