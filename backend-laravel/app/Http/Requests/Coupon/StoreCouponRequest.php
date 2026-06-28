<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code',
                'regex:/^[A-Z0-9_\-]+$/',
            ],
            'description'         => ['nullable', 'string', 'max:500'],
            'discount_type'       => ['required', 'string', 'in:flat,percentage'],
            'discount_value'      => ['required', 'numeric', 'min:0.01'],
            'max_discount_amount' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_if:discount_type,percentage',
            ],
            'min_booking_amount'  => ['nullable', 'numeric', 'min:0'],
            'usage_limit'         => ['nullable', 'integer', 'min:1'],
            'per_user_limit'      => ['nullable', 'integer', 'min:1'],
            'valid_from'          => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'valid_until'         => ['nullable', 'date', 'date_format:Y-m-d H:i:s', 'after:valid_from'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'                    => 'Coupon code is required.',
            'code.unique'                      => 'This coupon code already exists.',
            'code.regex'                       => 'Code may only contain uppercase letters, numbers, hyphens, and underscores.',
            'discount_type.in'                 => 'Discount type must be flat or percentage.',
            'discount_value.required'          => 'Discount value is required.',
            'discount_value.min'               => 'Discount value must be greater than zero.',
            'max_discount_amount.required_if'  => 'Max discount amount is required for percentage discounts.',
            'valid_until.after'                => 'Expiry date must be after the start date.',
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