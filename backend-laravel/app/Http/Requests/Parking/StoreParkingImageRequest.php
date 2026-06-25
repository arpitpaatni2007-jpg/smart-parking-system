<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreParkingImageRequest
 * ============================================================
 *
 * Validates image upload requests for a parking location.
 *
 * IMAGE VALIDATION NOTES:
 *   - mimes:jpeg,jpg,png,webp — restricts to web-optimized formats
 *   - max:5120 — 5MB limit (5120 KB). Adjust based on your storage plan.
 *   - Actual file storage is handled in ParkingImageController.
 *
 * FLUTTER UPLOAD:
 *   Flutter sends images as multipart/form-data.
 *   Use Dio with FormData in Flutter:
 *     FormData.fromMap({'image': await MultipartFile.fromFile(path)})
 */
class StoreParkingImageRequest extends FormRequest
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
            'parking_id' => ['required', 'integer', 'exists:parkings,id'],

            /**
             * The uploaded image file.
             * required: must be provided
             * file: must be an actual uploaded file (not just a string)
             * image: must be an image type (checked by PHP's getimagesize())
             * mimes: further restrict to specific formats
             * max:5120: maximum 5MB file size
             */
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],

            /**
             * Whether this should be the primary thumbnail image.
             * boolean: accepts true/false, 1/0, "true"/"false", "1"/"0"
             *
             * If set to true, ParkingImageController will demote any
             * existing primary image before saving this one.
             */
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'parking_id.required' => 'Parking location is required.',
            'parking_id.exists'   => 'The selected parking location does not exist.',
            'image.required'      => 'Please select an image to upload.',
            'image.file'          => 'The upload must be a valid file.',
            'image.image'         => 'The uploaded file must be an image.',
            'image.mimes'         => 'Image must be in JPEG, JPG, PNG, or WebP format.',
            'image.max'           => 'Image size must not exceed 5MB.',
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