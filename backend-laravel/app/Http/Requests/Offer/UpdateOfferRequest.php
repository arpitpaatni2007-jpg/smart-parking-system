<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'               => ['sometimes', 'required', 'string', 'max:255'],
            'description'         => ['sometimes', 'nullable', 'string', 'max:1000'],
            'offer_type'          => ['sometimes', 'required', 'string', 'in:flat,percentage,free_hours'],
            'discount_value'      => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'max_discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0.01'],
            'min_booking_amount'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'applicable_to'       => ['sometimes', 'required', 'string', 'in:all,first_booking,specific_parking'],
            'parking_id'          => ['sometimes', 'nullable', 'integer', 'exists:parkings,id'],
            'banner_image'        => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'valid_from'          => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'valid_until'         => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d H:i:s', 'after:valid_from'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'offer_type.in'       => 'Offer type must be flat, percentage, or free_hours.',
            'applicable_to.in'    => 'applicable_to must be: all, first_booking, or specific_parking.',
            'parking_id.exists'   => 'The selected parking location does not exist.',
            'banner_image.mimes'  => 'Banner must be JPEG, JPG, PNG, or WebP.',
            'banner_image.max'    => 'Banner image must not exceed 2MB.',
            'valid_until.after'   => 'Expiry date must be after the start date.',
        ];
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