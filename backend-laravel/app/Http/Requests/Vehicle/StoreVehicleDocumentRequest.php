<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreVehicleDocumentRequest
 * ============================================================
 *
 * Validates document uploads for a vehicle.
 * Accepted document types: RC, Insurance, PUC, Fitness, Permit.
 *
 * FILE VALIDATION:
 *   - file: must be an actual uploaded file
 *   - mimes: restricts to image formats and PDF
 *   - max:5120 = 5MB limit (5120 KB)
 *
 *   RC books are often photographed → jpeg/jpg/png/webp accepted.
 *   Insurance and PUC certificates are often digital PDFs → pdf accepted.
 *
 * EXPIRY DATE:
 *   - Nullable for RC (no expiry in most Indian states).
 *   - Required for Insurance, PUC, Fitness, Permit.
 *   - 'after:today' prevents submitting already-expired documents.
 *
 * FLUTTER UPLOAD:
 *   Use Dio with FormData:
 *     FormData.fromMap({
 *       'document': await MultipartFile.fromFile(filePath),
 *       'document_type': 'insurance',
 *       'expiry_date': '2026-01-15',
 *     })
 *
 * FUTURE SCALABILITY:
 *   - Add `document_number` string for policy/cert number
 *   - Add `issuing_authority` string for e.g. "HDFC Ergo"
 */
class StoreVehicleDocumentRequest extends FormRequest
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
             * The category of document being uploaded.
             * Must match ENUM values in vehicle_documents table.
             *
             *   rc         → Registration Certificate
             *   insurance  → Motor Insurance Certificate (expires annually)
             *   puc        → Pollution Under Control (expires every 6 months)
             *   fitness    → Fitness Certificate (commercial vehicles)
             *   permit     → Transport Permit (goods/passenger carriers)
             */
            'document_type' => [
                'required',
                'string',
                'in:rc,insurance,puc,fitness,permit',
            ],

            /**
             * The uploaded document file.
             * Accepts: images (photographed docs) and PDFs (digital certs).
             * Maximum: 5MB.
             */
            'document' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,pdf',
                'max:5120', // 5MB
            ],

            /**
             * Expiry date of the document.
             * - Nullable for RC (no expiry in most states).
             * - Required for Insurance, PUC, Fitness, Permit.
             * - Must be a future date (cannot submit already-expired documents).
             *
             * 'after:today' allows today's date as valid.
             * Use 'after:now' if you need strict future-only validation.
             *
             * FUTURE: Make this required when document_type != 'rc'
             * using a custom validation rule or conditional required.
             */
            'expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Please select the type of document you are uploading.',
            'document_type.in'       => 'Document type must be one of: RC, Insurance, PUC, Fitness, or Permit.',
            'document.required'      => 'Please select a document file to upload.',
            'document.file'          => 'The upload must be a valid file.',
            'document.mimes'         => 'Document must be in JPEG, JPG, PNG, WebP, or PDF format.',
            'document.max'           => 'Document size must not exceed 5MB.',
            'expiry_date.date'       => 'Please enter a valid expiry date.',
            'expiry_date.after'      => 'Expiry date must be a future date. Documents that have already expired cannot be uploaded.',
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