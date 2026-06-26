<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateVehicleRequest
 * ============================================================
 *
 * Validates partial updates to a user's vehicle.
 * All fields are optional (sometimes) — only sent fields
 * are validated and updated. This supports PATCH-style updates.
 *
 * UNIQUE RULE ON UPDATE:
 *   When updating vehicle_number, we must exclude the CURRENT
 *   vehicle's own row so the owner can re-submit without conflict.
 *   Rule::unique()->ignore($currentVehicleId) handles this.
 *
 * STATUS CHANGE:
 *   Owners can deactivate (status → 'inactive') their own vehicle.
 *   e.g. when they sell the vehicle and want to hide it from bookings.
 *   Reactivating (status → 'active') is also allowed.
 */
class UpdateVehicleRequest extends FormRequest
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
        // The current vehicle's ID from the route: /vehicles/{vehicle}
        // Used in the unique ignore rule so re-saving the same plate doesn't fail.
        $vehicleId = $this->route('vehicle')?->id ?? $this->route('vehicle');

        return [
            'vehicle_type_id' => ['sometimes', 'required', 'integer', 'exists:vehicle_types,id'],

            /**
             * If vehicle_number is being updated, ignore the current vehicle's
             * own row in the unique check (Rule::unique()->ignore()).
             * Still enforces uniqueness against all OTHER vehicles.
             */
            'vehicle_number' => [
                'sometimes',
                'required',
                'string',
                'min:5',
                'max:15',
                Rule::unique('vehicles', 'vehicle_number')
                    ->ignore($vehicleId)
                    ->whereNull('deleted_at'),
            ],

            'vehicle_name'  => ['sometimes', 'required', 'string', 'max:100'],
            'vehicle_brand' => ['sometimes', 'required', 'string', 'max:100'],
            'vehicle_color' => ['sometimes', 'required', 'string', 'max:50'],

            /**
             * Status toggle: owners can activate or deactivate vehicles.
             * Inactive vehicles don't appear in booking dropdowns.
             */
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.exists'  => 'The selected vehicle type does not exist.',
            'vehicle_number.min'      => 'Vehicle registration number seems too short.',
            'vehicle_number.max'      => 'Vehicle registration number cannot exceed 15 characters.',
            'vehicle_number.unique'   => 'This vehicle registration number is already registered by another account.',
            'vehicle_name.max'        => 'Vehicle name cannot exceed 100 characters.',
            'vehicle_brand.max'       => 'Vehicle brand cannot exceed 100 characters.',
            'vehicle_color.max'       => 'Vehicle color cannot exceed 50 characters.',
            'status.in'               => 'Status must be either "active" or "inactive".',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('vehicle_number')) {
            $this->merge([
                'vehicle_number' => strtoupper(str_replace(' ', '', $this->vehicle_number)),
            ]);
        }
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