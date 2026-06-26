<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * UpdateVehicleDocumentRequest
 * ============================================================
 *
 * Validates update requests for a vehicle document.
 *
 * WHAT CAN BE UPDATED:
 *   Users can update the expiry_date and replace the document file.
 *   They CANNOT change document_type — that would mean a different
 *   document entirely (delete the old one and upload a new one).
 *
 * ADMIN UPDATES:
 *   Admins can additionally update the `status` field to verify,
 *   reject, or expire a document. This is enforced in the controller
 *   (not in this Form Request) using role checks.
 *
 * RENEWAL FLOW:
 *   When a document (e.g. insurance) is renewed, the recommended
 *   approach is: upload a new document (POST) rather than updating
 *   the old one. This preserves the document renewal history.
 *
 *   However, if the user accidentally uploaded wrong expiry date or
 *   the wrong file, this UPDATE endpoint allows them to correct it
 *   (while the document is still in 'pending' status).
 *
 * FUTURE SCALABILITY:
 *   - Restrict updates to 'pending' documents only (cannot update verified ones)
 *   - Add `rejection_reason` field (for admin use when rejecting)
 */
class UpdateVehicleDocumentRequest extends FormRequest
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
             * Optionally replace the document file.
             * All same constraints as the store request.
             */
            'document' => [
                'sometimes',
                'file',
                'mimes:jpeg,jpg,png,webp,pdf',
                'max:5120',
            ],

            /**
             * Update/correct the expiry date.
             * Must still be a future date.
             */
            'expiry_date' => ['sometimes', 'nullable', 'date', 'after:today'],

            /**
             * Admin-only: update the verification status.
             * Enforced in the controller — non-admins cannot send this field.
             *
             * 'sometimes' means: only validate if present in the request.
             */
            'status' => ['sometimes', 'required', 'in:pending,active,expired,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.file'        => 'The replacement must be a valid file.',
            'document.mimes'       => 'Document must be in JPEG, JPG, PNG, WebP, or PDF format.',
            'document.max'         => 'Document size must not exceed 5MB.',
            'expiry_date.date'     => 'Please enter a valid expiry date.',
            'expiry_date.after'    => 'Expiry date must be a future date.',
            'status.in'            => 'Status must be one of: pending, active, expired, rejected.',
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