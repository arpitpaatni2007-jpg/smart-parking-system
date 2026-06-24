<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreVehicleTypeRequest
 * ============================================================
 *
 * Validates data for CREATING a new VehicleType.
 *
 * Vehicle types (Two Wheeler, Four Wheeler, Taxi) are shared
 * across parking slots, vehicles, and pricing rules — so their
 * names must be unique to avoid confusion.
 */
class StoreVehicleTypeRequest extends FormRequest
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
                Rule::unique('vehicle_types', 'name'),
            ],

            // Icon can be a CSS class, Flutter icon name, or S3 URL.
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],

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
            'name.required' => 'Vehicle type name is required.',
            'name.unique'   => 'This vehicle type already exists.',
            'name.max'      => 'Vehicle type name must not exceed 100 characters.',
            'icon.max'      => 'Icon identifier must not exceed 255 characters.',
            'status.in'     => 'Status must be either active or inactive.',
        ];
    }
}