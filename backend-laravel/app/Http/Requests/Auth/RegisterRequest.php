<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * RegisterRequest — Form Request Validation
 * ============================================================
 *
 * WHY FORM REQUESTS?
 * Instead of writing validation logic inside the controller
 * (which makes controllers bloated), we move it here.
 * Laravel automatically runs this before the controller method
 * is even called. If validation fails, a 422 JSON response
 * is returned automatically — the controller never runs.
 *
 * This keeps controllers clean and validation reusable.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means ANYONE can attempt to register.
     * (Authorization is open — authentication happens after registration.)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules applied to the incoming HTTP request.
     *
     * These run BEFORE the RegisterController method executes.
     * If any rule fails, Laravel returns a 422 Unprocessable Entity
     * response with the error messages automatically.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Full name of the registering user.
             * - required: cannot be empty
             * - string: must be text
             * - max:255: prevents overflow attacks and DB truncation
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Email address — used as login credential.
             * - required: must be provided
             * - string: must be text
             * - email: must be a valid email format (uses DNS validation)
             * - max:255: prevents overflow
             * - unique:users,email: no two users can share an email
             */
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],

            /**
             * Phone number.
             * - required: must be provided
             * - string: stored as string (preserves leading + for international)
             * - max:20: covers international formats e.g. "+91 98765 43210"
             * - unique:users,phone: one account per phone number
             *
             * FUTURE: Add regex validation for specific country format:
             *   'regex:/^[6-9]\d{9}$/'  ← for Indian 10-digit mobile numbers
             */
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],

            /**
             * Password for account security.
             * - required: must be provided
             * - string: must be text
             * - min:8: minimum 8 characters (NIST recommendation)
             * - confirmed: request must also contain `password_confirmation`
             *              matching this value exactly
             *
             * FUTURE: Add Laravel's Password rule for stronger enforcement:
             *   Password::min(8)->letters()->mixedCase()->numbers()->symbols()
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * Role the user is registering as.
             * - required: must specify a role
             * - integer: must be a whole number (ID)
             * - exists:roles,id: the role_id must exist in the roles table
             *
             * SECURITY NOTE: In production, restrict which role_ids are
             * publicly registerable. For example, 'admin' role should NOT
             * be registerable via the public API. Enforce this in
             * AuthController::register() after validation.
             */
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    /**
     * Custom human-readable error messages.
     * Overrides Laravel's default messages for better UX.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'       => 'Please enter your full name.',
            'name.max'            => 'Name cannot exceed 255 characters.',

            'email.required'      => 'Please enter your email address.',
            'email.email'         => 'Please enter a valid email address.',
            'email.unique'        => 'This email address is already registered. Please login instead.',

            'phone.required'      => 'Please enter your phone number.',
            'phone.unique'        => 'This phone number is already registered.',
            'phone.max'           => 'Phone number cannot exceed 20 characters.',

            'password.required'   => 'Please set a password for your account.',
            'password.min'        => 'Password must be at least 8 characters long.',
            'password.confirmed'  => 'Password and confirmation do not match.',

            'role_id.required'    => 'Please select a registration role.',
            'role_id.integer'     => 'Invalid role selected.',
            'role_id.exists'      => 'The selected role does not exist.',
        ];
    }

    /**
     * Custom attribute names used in validation error messages.
     * e.g. Instead of "The role_id field is required" → "The role field is required"
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'role',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * Instead of redirecting (default Laravel behavior for web),
     * we throw a JSON response — required for API endpoints.
     *
     * This returns:
     * HTTP 422 Unprocessable Entity
     * {
     *   "success": false,
     *   "message": "Validation failed.",
     *   "errors": { "email": ["This email address is already registered."] }
     * }
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