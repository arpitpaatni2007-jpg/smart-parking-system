<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * UpdateOwnerBankDetailRequest
 * ============================================================
 *
 * Validates partial updates to an owner's bank details.
 * All fields are optional — only sent fields are validated.
 *
 * IMPORTANT SECURITY NOTE:
 *   When an owner updates their bank details, the status should
 *   automatically reset to 'pending_verification' in the controller.
 *   This forces admin re-verification before the updated account
 *   can receive payouts. This prevents an owner from swapping bank
 *   accounts to intercept payouts without admin knowledge.
 */
class UpdateOwnerBankDetailRequest extends FormRequest
{
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
            'account_holder_name' => ['sometimes', 'required', 'string', 'max:255'],
            'bank_name'           => ['sometimes', 'required', 'string', 'max:255'],

            'account_number' => [
                'sometimes',
                'required',
                'string',
                'digits_between:9,18',
            ],

            /**
             * account_number_confirmation is required ONLY when
             * account_number is being updated.
             * 'required_with' = required if account_number is present.
             */
            'account_number_confirmation' => ['required_with:account_number', 'string'],

            'ifsc_code' => [
                'sometimes',
                'required',
                'string',
                'size:11',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.digits_between'              => 'Account number must be between 9 and 18 digits.',
            'account_number_confirmation.required_with'  => 'Please confirm the new account number.',
            'ifsc_code.size'                             => 'IFSC code must be exactly 11 characters.',
            'ifsc_code.regex'                            => 'Please enter a valid IFSC code (e.g. SBIN0001234).',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->ifsc_code) {
            $this->merge(['ifsc_code' => strtoupper($this->ifsc_code)]);
        }
    }

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