<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateCityRequest
 * ============================================================
 *
 * Validates incoming data when UPDATING an existing City record.
 *
 * The composite unique rule must ignore the current city's own ID
 * to allow updating without triggering a false duplicate error.
 */
class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cityId = $this->route('city') instanceof \App\Models\City
            ? $this->route('city')->id
            : $this->route('city');

        // Determine the state_id to use for the composite unique check.
        // If state_id is being changed, use the new value.
        // If not, fall back to the existing city's state_id.
        $stateId = $this->input('state_id')
            ?? ($this->route('city') instanceof \App\Models\City
                ? $this->route('city')->state_id
                : null);

        return [
            'state_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('states', 'id'),
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')
                    ->where('state_id', $stateId)
                    ->ignore($cityId),
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
            'state_id.required' => 'Please select a state for this city.',
            'state_id.exists'   => 'The selected state does not exist.',
            'name.required'     => 'City name is required.',
            'name.unique'       => 'This city already exists in the selected state.',
            'name.max'          => 'City name must not exceed 100 characters.',
            'status.in'         => 'Status must be either active or inactive.',
        ];
    }
}