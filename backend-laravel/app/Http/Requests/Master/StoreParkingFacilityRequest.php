<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreParkingFacilityRequest
 * ============================================================
 *
 * Validates data for CREATING a new ParkingFacility.
 *
 * Parking facilities are the amenities checklist shown in the
 * Owner App when adding a parking: CCTV, EV Charging, Covered,
 * Security, Washroom, 24/7 Access, etc.
 */
class StoreParkingFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('parking_facilities', 'name'),
            ],

            // Icon key for mobile UI — CSS class, Flutter icon name, or S3 URL.
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Short description for Admin Panel management screen.
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
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
            'name.unique'   => 'This facility already exists.',
            'name.max'      => 'Facility name must not exceed 100 characters.',
            'icon.max'      => 'Icon identifier must not exceed 255 characters.',
            'status.in'     => 'Status must be either active or inactive.',
        ];
    }
}