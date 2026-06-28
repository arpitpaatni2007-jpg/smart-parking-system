<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            /**
             * The coupon code the user wants to apply.
             * Normalized to uppercase in prepareForValidation().
             */
            'code' => ['required', 'string', 'max:50'],

            /**
             * The booking amount to apply the coupon against.
             * Used to validate min_booking_amount and calculate discount.
             */
            'booking_amount' => ['required', 'numeric', 'min:0.01'],

            /**
             * Optional: the booking_id being created.
             * Used to check per_user_limit (how many times this user
             * has already used this coupon).
             */
            'booking_id' => ['sometimes', 'nullable', 'integer', 'exists:bookings,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'           => 'Please enter a coupon code.',
            'booking_amount.required' => 'Booking amount is required to validate the coupon.',
            'booking_amount.min'      => 'Booking amount must be greater than zero.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
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