<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * ChangePasswordRequest — Form Request Validation
 * ============================================================
 *
 * Validates the payload for POST /api/v1/auth/change-password.
 *
 * Fields validated here (structural rules only):
 *   - current_password: must be present and a string
 *   - password: new password, min 8 chars, confirmed
 *
 * NOTE: We do NOT verify current_password is CORRECT here.
 * That check (Hash::check) is done in the controller because:
 *   1. Form Requests don't have easy access to Hash::check logic
 *   2. A wrong current_password is a business logic error (401),
 *      not a validation error (422)
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Only authenticated users can change their password.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The user's current password — for identity verification.
             * We only validate it's present; correctness is checked in controller.
             */
            'current_password' => ['required', 'string'],

            /**
             * The new password.
             * - min:8: minimum length
             * - confirmed: must also send 'password_confirmation' with matching value
             * - different:current_password: new password must differ from current
             *
             * FUTURE: Add Laravel's Password rule for strength enforcement:
             *   \Illuminate\Validation\Rules\Password::min(8)
             *     ->letters()->mixedCase()->numbers()->symbols()
             */
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Please enter your current password.',
            'password.required'         => 'Please enter a new password.',
            'password.min'              => 'New password must be at least 8 characters.',
            'password.confirmed'        => 'New password and confirmation do not match.',
            'password.different'        => 'New password must be different from your current password.',
        ];
    }

    /**
     * Return JSON 422 on validation failure.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
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