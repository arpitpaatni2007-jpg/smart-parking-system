<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * UpdateParkingImageRequest
 * ============================================================
 *
 * Validates update requests for a parking image.
 * The only meaningful update on an image is toggling is_primary.
 * (Replacing the actual image file = delete old + upload new.)
 */
class UpdateParkingImageRequest extends FormRequest
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
             * The only updatable field on a parking image is is_primary.
             * Owners can promote any image to be the thumbnail.
             *
             * required (not sometimes): this is the ONLY purpose of this endpoint.
             * Sending a request to update an image without specifying is_primary
             * would be a no-op, so we require it.
             */
            'is_primary' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_primary.required' => 'Please specify whether this should be the primary image (true or false).',
            'is_primary.boolean'  => 'is_primary must be true or false.',
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