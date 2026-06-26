<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * QRBookingResource
 * ============================================================
 *
 * Transforms a QRBooking model into a clean JSON response.
 *
 * SECURITY NOTE ON qr_code TOKEN:
 *   The qr_code field contains the raw token string that gets
 *   encoded into the QR image. The Flutter app uses this token
 *   to render the QR image client-side (using a QR library).
 *
 *   This token is SAFE to expose to the booking's own user —
 *   it is a random string that only the gate scanner can validate.
 *   It does NOT contain sensitive booking data embedded in it.
 *
 *   The gate scanner calls POST /bookings/verify-qr?token=... to
 *   validate it server-side. The token alone has no value without
 *   the server-side lookup.
 */
class QRBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'booking_id' => $this->booking_id,

            // ── QR TOKEN (safe to expose to the booking owner) ─────────────
            'qr_code'    => $this->qr_code,

            // ── VALIDITY ───────────────────────────────────────────────────
            'qr_expiry'  => $this->qr_expiry?->toISOString(),
            'status'     => $this->status,
            'is_valid'   => $this->isValid(),    // false if status != active OR expired
            'is_expired' => $this->isExpired(),
            'expires_in' => $this->expiresIn(),  // "2 hours 15 minutes" or null

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}