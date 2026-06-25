<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreOwnerBankDetailRequest
 * ============================================================
 *
 * Validates bank detail submission from a parking owner.
 * These details are used for payout processing.
 *
 * IFSC VALIDATION:
 *   Indian IFSC codes are exactly 11 characters:
 *   - First 4: Bank code (alphabets only) e.g. "SBIN", "HDFC"
 *   - 5th:     Always '0' (zero digit)
 *   - Last 6:  Branch code (alphanumeric) e.g. "001234"
 *   Example: "SBIN0001234", "HDFC0000123"
 *
 * ACCOUNT NUMBER:
 *   Indian bank account numbers are 9 to 18 digits.
 *   Stored as string to preserve leading zeros and support
 *   international formats in the future.
 *
 * SECURITY:
 *   In the controller, account_number should be encrypted
 *   before storing: encrypt($request->account_number)
 */
class StoreOwnerBankDetailRequest extends FormRequest
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
            /**
             * Full legal name as it appears on the bank account.
             * Must match bank records exactly for payout to succeed.
             */
            'account_holder_name' => ['required', 'string', 'max:255'],

            /**
             * Bank name — free text since there are too many banks to enumerate.
             * e.g. "State Bank of India", "HDFC Bank", "ICICI Bank"
             */
            'bank_name' => ['required', 'string', 'max:255'],

            /**
             * Account number — 9 to 18 digits (Indian standard).
             * 'digits_between' validates it's purely numeric and within range.
             * Stored as string to handle leading zeros.
             */
            'account_number' => [
                'required',
                'string',
                'digits_between:9,18',
            ],

            /**
             * Confirm account number to prevent typos.
             * 'confirmed' rule checks that 'account_number_confirmation'
             * field is also present and matches 'account_number'.
             */
            'account_number_confirmation' => ['required', 'string'],

            /**
             * IFSC code — exactly 11 characters.
             * Regex: 4 uppercase letters + digit 0 + 6 alphanumeric chars.
             */
            'ifsc_code' => [
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
            'account_holder_name.required'         => 'Please enter the account holder\'s full name.',
            'bank_name.required'                   => 'Please enter your bank name.',
            'account_number.required'              => 'Please enter your bank account number.',
            'account_number.digits_between'        => 'Account number must be between 9 and 18 digits.',
            'account_number_confirmation.required' => 'Please confirm your account number.',
            'ifsc_code.required'                   => 'Please enter your bank\'s IFSC code.',
            'ifsc_code.size'                       => 'IFSC code must be exactly 11 characters.',
            'ifsc_code.regex'                      => 'Please enter a valid IFSC code (e.g. SBIN0001234).',
        ];
    }

    /**
     * Normalize input before validation.
     * Converts IFSC to uppercase so regex works regardless of input case.
     */
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