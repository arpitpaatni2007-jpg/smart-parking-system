<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreCityRequest
 * ============================================================
 *
 * Validates incoming data when CREATING a new City record.
 *
 * KEY UNIQUENESS RULE:
 * City names are unique per state — "Salem" can exist in both
 * Tamil Nadu and Karnataka, but not twice in Tamil Nadu.
 * We enforce this with a composite unique check on (state_id, name).
 */
class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Must be a valid, existing state ID.
            // 'exists' rule queries the states table automatically.
            'state_id' => [
                'required',
                'integer',
                Rule::exists('states', 'id'),
            ],

            // City name, max 100 chars.
            // Unique within the same state (composite unique check).
            'name' => [
                'required',
                'string',
                'max:100',
                // Composite unique: name must be unique for this state_id.
                Rule::unique('cities', 'name')
                    ->where('state_id', $this->input('state_id')),
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
            'state_id.required' => 'Please select a state for this city.',
            'state_id.exists'   => 'The selected state does not exist.',
            'name.required'     => 'City name is required.',
            'name.unique'       => 'This city already exists in the selected state.',
            'name.max'          => 'City name must not exceed 100 characters.',
            'status.in'         => 'Status must be either active or inactive.',
        ];
    }
}