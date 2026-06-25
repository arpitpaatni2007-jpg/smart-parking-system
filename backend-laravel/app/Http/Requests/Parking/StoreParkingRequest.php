<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreParkingRequest
 * ============================================================
 *
 * Validates the incoming request when an OWNER creates a new
 * parking location. Runs automatically before ParkingController::store().
 *
 * WHO CAN CREATE A PARKING?
 *   Only authenticated users with the 'owner' role.
 *   The authorization check (role guard) is done in the controller
 *   using middleware/policies — not in authorize() here, because
 *   authorize() only has access to the request, not role logic.
 *
 * COORDINATE VALIDATION:
 *   latitude:  -90 to 90    (South Pole to North Pole)
 *   longitude: -180 to 180  (West to East)
 *   These are standard WGS84 GPS coordinate ranges.
 */
class StoreParkingRequest extends FormRequest
{
    /**
     * All authenticated users may attempt this request.
     * Role-based access (owner only) is enforced via route middleware.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ── LOCATION REFERENCES ───────────────────────────────────────
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id'  => ['required', 'integer', 'exists:cities,id'],

            // ── PARKING DETAILS ───────────────────────────────────────────
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address'     => ['required', 'string', 'max:1000'],

            // ── GPS COORDINATES ───────────────────────────────────────────
            /**
             * Latitude: -90 to +90 degrees
             * 'numeric' accepts both integers and decimals (28.6139, -33.8688)
             */
            'latitude'  => ['required', 'numeric', 'between:-90,90'],

            /**
             * Longitude: -180 to +180 degrees
             */
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // ── CAPACITY ──────────────────────────────────────────────────
            /**
             * total_slots: must be a positive integer.
             * min:1 prevents registering a parking with 0 slots.
             * max:10000 prevents absurdly large values.
             */
            'total_slots' => ['required', 'integer', 'min:1', 'max:10000'],

            // ── FACILITIES (optional many-to-many) ────────────────────────
            /**
             * Array of parking_facility IDs to associate.
             * e.g. [1, 3, 5] → CCTV, EV Charging, Covered
             *
             * nullable: facilities are optional at creation time.
             * array: must be an array type (not a string)
             * min:0: can be an empty array
             */
            'facility_ids'   => ['nullable', 'array', 'min:0'],
            'facility_ids.*' => ['integer', 'exists:parking_facilities,id'],
        ];
    }

    /**
     * Custom human-readable error messages.
     */
    public function messages(): array
    {
        return [
            'state_id.required'    => 'Please select a state.',
            'state_id.exists'      => 'The selected state does not exist.',
            'city_id.required'     => 'Please select a city.',
            'city_id.exists'       => 'The selected city does not exist.',
            'name.required'        => 'Please enter a name for your parking location.',
            'name.max'             => 'Parking name cannot exceed 255 characters.',
            'address.required'     => 'Please enter the full address of your parking location.',
            'latitude.required'    => 'GPS latitude is required.',
            'latitude.between'     => 'Latitude must be between -90 and 90 degrees.',
            'longitude.required'   => 'GPS longitude is required.',
            'longitude.between'    => 'Longitude must be between -180 and 180 degrees.',
            'total_slots.required' => 'Please enter the total number of parking slots.',
            'total_slots.min'      => 'Your parking must have at least 1 slot.',
            'total_slots.max'      => 'Total slots cannot exceed 10,000.',
            'facility_ids.array'   => 'Facilities must be provided as an array of IDs.',
            'facility_ids.*.exists'=> 'One or more selected facilities do not exist.',
        ];
    }

    /**
     * Return JSON 422 on validation failure (API standard).
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