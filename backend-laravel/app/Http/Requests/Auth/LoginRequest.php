<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * LoginRequest — Form Request Validation
 * ============================================================
 *
 * Validates the incoming login payload before it reaches
 * the AuthController. Only checks that the fields are present
 * and properly formatted — credential verification (wrong password,
 * account not found) is handled in the controller.
 */
class LoginRequest extends FormRequest
{
    /**
     * Anyone can attempt to login — no prior auth required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the login request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Email used as the unique login identifier.
             * - required: must be present
             * - string: must be text type
             * - email: must be valid email format
             */
            'email' => ['required', 'string', 'email'],

            /**
             * Account password.
             * - required: must be present
             * - string: must be text type
             *
             * NOTE: We deliberately do NOT check min:8 here.
             * If a user's password was set when min was 6 characters,
             * we should still let them attempt login.
             * Password strength rules only apply at registration/reset.
             */
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Custom human-readable error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'Please enter your email address.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ];
    }

    /**
     * Return JSON error response on validation failure.
     * Required for API endpoints (prevents HTML redirect).
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