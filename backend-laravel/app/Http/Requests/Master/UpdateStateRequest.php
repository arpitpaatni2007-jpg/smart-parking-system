<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateStateRequest
 * ============================================================
 *
 * Validates incoming data when UPDATING an existing State record.
 *
 * The key difference from StoreStateRequest is the unique rule.
 * When updating, we must IGNORE the current record's own ID
 * in the uniqueness check — otherwise updating without changing
 * the name would fail with "name already exists".
 *
 * We also use 'sometimes' so partial updates are supported:
 * the client can send only the fields they want to change.
 */
class UpdateStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the state ID from the route parameter.
        // Route: PUT /api/v1/master/states/{state}
        // $this->route('state') returns the State model (route model binding)
        // or the raw ID depending on how the route is defined.
        $stateId = $this->route('state') instanceof \App\Models\State
            ? $this->route('state')->id
            : $this->route('state');

        return [
            // 'sometimes' = only validate this field if it's present in the request.
            // This enables partial (PATCH-style) updates even on PUT endpoints.
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                // Ignore this state's own ID so the current name doesn't
                // trigger the unique violation.
                Rule::unique('states', 'name')->ignore($stateId),
            ],

            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
                Rule::unique('states', 'code')->ignore($stateId),
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
            'name.required' => 'State name is required.',
            'name.unique'   => 'A state with this name already exists.',
            'name.max'      => 'State name must not exceed 100 characters.',
            'code.unique'   => 'This state code is already in use.',
            'code.max'      => 'State code must not exceed 10 characters.',
            'status.in'     => 'Status must be either active or inactive.',
        ];
    }
}