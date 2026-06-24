<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateParkingFacilityRequest
 * ============================================================
 */
class UpdateParkingFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $facilityId = $this->route('parking_facility') instanceof \App\Models\ParkingFacility
            ? $this->route('parking_facility')->id
            : $this->route('parking_facility');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('parking_facilities', 'name')->ignore($facilityId),
            ],

            'icon' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Facility name is required.',
            'name.unique'   => 'This facility name is already in use.',
            'name.max'      => 'Facility name must not exceed 100 characters.',
            'status.in'     => 'Status must be either active or inactive.',
        ];
    }
}