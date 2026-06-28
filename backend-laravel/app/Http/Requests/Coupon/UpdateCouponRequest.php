<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id ?? $this->route('coupon');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_\-]+$/',
                Rule::unique('coupons', 'code')->ignore($couponId)->whereNull('deleted_at'),
            ],
            'description'         => ['sometimes', 'nullable', 'string', 'max:500'],
            'discount_type'       => ['sometimes', 'required', 'string', 'in:flat,percentage'],
            'discount_value'      => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'max_discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0.01'],
            'min_booking_amount'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_limit'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'per_user_limit'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'valid_from'          => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'valid_until'         => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d H:i:s', 'after:valid_from'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique'         => 'This coupon code is already used by another coupon.',
            'code.regex'          => 'Code may only contain uppercase letters, numbers, hyphens, and underscores.',
            'discount_type.in'    => 'Discount type must be flat or percentage.',
            'valid_until.after'   => 'Expiry date must be after the start date.',
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