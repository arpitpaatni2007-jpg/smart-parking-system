<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreStateRequest
 * ============================================================
 *
 * Validates incoming data when CREATING a new State record.
 *
 * WHY A FORM REQUEST INSTEAD OF VALIDATING IN THE CONTROLLER?
 *   - Keeps the controller clean and focused only on logic.
 *   - Validation rules are reusable and independently testable.
 *   - Laravel automatically returns a 422 JSON response with
 *     field-level errors if validation fails — no try/catch needed.
 *   - If the request is unauthorized, Laravel returns 403 before
 *     the controller even runs.
 */
class StoreStateRequest extends FormRequest
{
    /**
     * Only authenticated users with the right role can create states.
     * We return true here and let the route middleware (auth:sanctum +
     * role check) handle authorization so the logic stays in one place.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a State.
     */
    public function rules(): array
    {
        return [
            // State name is required, must be a string, max 100 chars,
            // and must be unique in the states table.
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('states', 'name'),
            ],

            // Short state code, e.g. "MH", "DL", "KA".
            // Optional but must be unique if provided.
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('states', 'code'),
            ],

            // Status defaults to "active" in the DB, so optional here.
            // Must be one of the two allowed values if provided.
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Human-friendly error messages for each rule.
     * These are returned in the JSON validation error response.
     */
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