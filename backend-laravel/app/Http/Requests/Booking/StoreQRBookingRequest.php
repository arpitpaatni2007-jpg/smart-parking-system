<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreQRBookingRequest
 * ============================================================
 *
 * Validates the payload when generating a QR code for a booking.
 *
 * NOTE ON QR GENERATION:
 *   In most cases, a QR code is generated AUTOMATICALLY when a
 *   booking is confirmed (in BookingController@store). This request
 *   handles MANUAL regeneration — when:
 *     - The QR has expired
 *     - The user requests a fresh QR from their booking detail screen
 *     - A lost QR needs to be reissued
 *
 * WHAT THE SERVER GENERATES (not from client input):
 *   - qr_code: cryptographically random token (Str::random(64))
 *   - qr_expiry: booking_end_time + grace period (e.g. +30 minutes)
 *   - status: 'active'
 *
 * WHAT THE CLIENT PROVIDES:
 *   - Nothing mandatory — the booking_id comes from the route param
 *   - Optional: custom expiry if the business allows it
 *
 * FUTURE:
 *   - Add `expiry_hours` integer for custom expiry duration
 */
class StoreQRBookingRequest extends FormRequest
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
             * No required fields — booking_id comes from the route parameter.
             * All QR data is generated server-side for security.
             *
             * FUTURE: Add optional fields here as needed, e.g.:
             *   'note' => ['nullable', 'string', 'max:100'],
             */
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