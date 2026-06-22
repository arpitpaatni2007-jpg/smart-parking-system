<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateProfileRequest — Form Request Validation
 * ============================================================
 *
 * Validates the payload for PUT /api/v1/auth/profile.
 *
 * KEY DIFFERENCE FROM RegisterRequest:
 *   All fields are optional here (sometimes keyword).
 *   The user only sends what they want to change.
 *   If a field is absent, it keeps its current value.
 *
 * UNIQUE RULE EXCLUSION:
 *   The email and phone unique rules MUST exclude the current
 *   user's own record. Without this, updating any other field
 *   would fail with "email already taken" even though it's
 *   THEIR OWN email.
 *
 *   Rule::unique('users', 'email')->ignore($this->user()->id)
 *   → "must be unique in users.email EXCEPT for this user's row"
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Only authenticated users can update their profile.
     * auth:sanctum middleware already verified the token before
     * this request class runs, so $this->user() is available.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules for profile update.
     * All fields are 'sometimes' — only validated if present in the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            /**
             * Name update — optional.
             * 'sometimes' means: only run this rule IF 'name' is in the request.
             * If the user doesn't send 'name', this rule is skipped entirely.
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * Email update — optional but must be unique (excluding self).
             *
             * Rule::unique('users', 'email')->ignore($userId)
             * → "unique in users table, email column, but IGNORE row with id = $userId"
             * This allows the user to submit their own email without a conflict.
             */
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            /**
             * Phone update — optional but must be unique (excluding self).
             */
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
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
            'name.required'    => 'Name cannot be empty.',
            'name.max'         => 'Name cannot exceed 255 characters.',

            'email.required'   => 'Email address cannot be empty.',
            'email.email'      => 'Please enter a valid email address.',
            'email.unique'     => 'This email address is already in use by another account.',

            'phone.required'   => 'Phone number cannot be empty.',
            'phone.unique'     => 'This phone number is already registered to another account.',
            'phone.max'        => 'Phone number cannot exceed 20 characters.',
        ];
    }

    /**
     * Return JSON error response on validation failure (API standard).
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