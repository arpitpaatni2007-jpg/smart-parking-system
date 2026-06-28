<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],

            /**
             * flat        → fixed amount off
             * percentage  → % off with optional cap
             * free_hours  → X free hours on a booking
             */
            'offer_type'          => ['required', 'string', 'in:flat,percentage,free_hours'],
            'discount_value'      => ['required', 'numeric', 'min:0.01'],
            'max_discount_amount' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_if:offer_type,percentage',
            ],
            'min_booking_amount'  => ['nullable', 'numeric', 'min:0'],

            /**
             * 'all'              → applies to every user
             * 'first_booking'    → applies only to the user's very first booking
             * 'specific_parking' → applies only to the specified parking_id
             */
            'applicable_to'       => ['required', 'string', 'in:all,first_booking,specific_parking'],

            'parking_id'          => [
                'nullable',
                'integer',
                'exists:parkings,id',
                'required_if:applicable_to,specific_parking',
            ],

            /**
             * Optional banner image upload (displayed in the Flutter offers screen).
             */
            'banner_image'        => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],

            'valid_from'          => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'valid_until'         => ['nullable', 'date', 'date_format:Y-m-d H:i:s', 'after:valid_from'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                   => 'Offer title is required.',
            'offer_type.in'                    => 'Offer type must be flat, percentage, or free_hours.',
            'discount_value.required'          => 'Discount value is required.',
            'max_discount_amount.required_if'  => 'Max discount amount is required for percentage offers.',
            'applicable_to.in'                 => 'applicable_to must be: all, first_booking, or specific_parking.',
            'parking_id.required_if'           => 'A parking location is required for specific_parking offers.',
            'parking_id.exists'                => 'The selected parking location does not exist.',
            'banner_image.mimes'               => 'Banner must be JPEG, JPG, PNG, or WebP.',
            'banner_image.max'                 => 'Banner image must not exceed 2MB.',
            'valid_until.after'                => 'Expiry date must be after the start date.',
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