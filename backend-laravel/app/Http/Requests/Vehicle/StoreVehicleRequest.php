<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreVehicleRequest
 * ============================================================
 *
 * Validates the payload when a user registers a new vehicle.
 *
 * VEHICLE NUMBER NORMALIZATION:
 *   The Vehicle model's setVehicleNumberAttribute() mutator
 *   auto-normalizes the plate to uppercase, no spaces before saving:
 *     "hr 26 dq 8849" → "HR26DQ8849"
 *
 *   The unique rule checks AFTER normalization would occur,
 *   but since the mutator fires on save (not on validation),
 *   we normalize in prepareForValidation() before the rule runs.
 *
 * VEHICLE NUMBER UNIQUENESS:
 *   A given registration plate can only be registered by one user.
 *   In India, each vehicle has exactly one number plate.
 *   Soft-deleted vehicles are excluded from the unique check
 *   (deleted_at IS NULL is the default Eloquent scope).
 *
 * FUTURE SCALABILITY:
 *   - Add `fuel_type` enum('petrol','diesel','electric','cng','hybrid')
 *   - Add `vehicle_year` for year-of-manufacture filtering
 *   - Add `fasttag_id` string for FASTag payment integration
 */
class StoreVehicleRequest extends FormRequest
{
    /**
     * Only authenticated users can register vehicles.
     */
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
             * Vehicle type from the master table.
             * Determines slot assignment and pricing.
             * e.g. Car → car slot, Bike → bike slot.
             */
            'vehicle_type_id' => ['required', 'integer', 'exists:vehicle_types,id'],

            /**
             * Registration plate — the unique identifier for a vehicle.
             * Normalized to uppercase + no spaces in prepareForValidation().
             * e.g. "HR26DQ8849", "DL01AB1234"
             *
             * unique:vehicles,vehicle_number — globally unique across all users.
             * One plate = one vehicle = one account.
             */
            'vehicle_number' => [
                'required',
                'string',
                'min:5',
                'max:15',
                Rule::unique('vehicles', 'vehicle_number')->whereNull('deleted_at'),
            ],

            /**
             * User-given nickname for easy identification in dropdowns.
             * e.g. "My Swift", "Office Car", "Wife's Activa"
             */
            'vehicle_name' => ['required', 'string', 'max:100'],

            /**
             * Manufacturer name.
             * e.g. "Maruti Suzuki", "Honda", "Tata", "Royal Enfield"
             * Free text — not enum because new brands are added frequently.
             */
            'vehicle_brand' => ['required', 'string', 'max:100'],

            /**
             * Body color as described by the owner.
             * e.g. "White", "Midnight Black", "Candy Red"
             * Free text — not enum (colour names are subjective).
             */
            'vehicle_color' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Custom error messages for better UX.
     */
    public function messages(): array
    {
        return [
            'vehicle_type_id.required' => 'Please select a vehicle type (Car, Bike, etc.).',
            'vehicle_type_id.exists'   => 'The selected vehicle type does not exist.',
            'vehicle_number.required'  => 'Please enter the vehicle registration number.',
            'vehicle_number.min'       => 'Vehicle registration number seems too short.',
            'vehicle_number.max'       => 'Vehicle registration number cannot exceed 15 characters.',
            'vehicle_number.unique'    => 'This vehicle registration number is already registered in the system.',
            'vehicle_name.required'    => 'Please give your vehicle a name (e.g. "My Swift").',
            'vehicle_name.max'         => 'Vehicle name cannot exceed 100 characters.',
            'vehicle_brand.required'   => 'Please enter the vehicle brand (e.g. "Maruti Suzuki").',
            'vehicle_brand.max'        => 'Vehicle brand cannot exceed 100 characters.',
            'vehicle_color.required'   => 'Please enter the vehicle color.',
            'vehicle_color.max'        => 'Vehicle color cannot exceed 50 characters.',
        ];
    }

    /**
     * Normalize the vehicle number before validation runs.
     * Converts to uppercase and strips spaces so the unique check
     * works consistently regardless of how the user typed the plate.
     *
     * e.g. "hr 26 dq 8849" → "HR26DQ8849" (before unique rule fires)
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('vehicle_number')) {
            $this->merge([
                'vehicle_number' => strtoupper(str_replace(' ', '', $this->vehicle_number)),
            ]);
        }
    }

    /**
     * Return JSON 422 on validation failure.
     */
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