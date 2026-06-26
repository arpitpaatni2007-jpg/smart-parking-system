<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * PaymentResource
 * ============================================================
 *
 * Transforms a Payment model into a structured, consistent
 * JSON response for all payment-related API endpoints.
 *
 * WHY A DEDICATED RESOURCE?
 * Payments contain sensitive data (gateway transaction IDs,
 * refund details, amounts). This resource controls exactly
 * what is exposed — never returning raw model columns that
 * might include internal reconciliation fields, raw Razorpay
 * webhook payloads, or fields meant only for server-side use.
 *
 * RESPONSE DESIGN DECISIONS:
 *   - Amounts are returned as strings to avoid floating-point
 *     precision issues in JSON ("160.00" not 160.000001)
 *   - Gateway IDs are always included for receipt display
 *   - Sensitive fields like Razorpay signature are NEVER exposed
 *   - Relationships are conditional (only if loaded by controller)
 *
 * USAGE:
 *   return new PaymentResource($payment);
 *   return PaymentResource::collection($payments);
 */
class PaymentResource extends JsonResource
{
    /**
     * Transform the Payment model into a JSON-friendly array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── Core Payment Identity ─────────────────────────────
            'id'                => $this->id,
            'booking_id'        => $this->booking_id,
            'user_id'           => $this->user_id,

            // ── Financial Data ────────────────────────────────────
            // Using (string) cast to ensure consistent decimal format
            // "160.00" in JSON rather than a raw PHP float.
            'amount'            => number_format((float) $this->amount, 2, '.', ''),
            'currency'          => $this->currency ?? 'INR',

            // ── Payment Method & Gateway ──────────────────────────
            'payment_method'    => $this->payment_method,   // upi, card, net_banking, wallet
            'payment_gateway'   => $this->payment_gateway,  // razorpay (future: payu, stripe)

            // ── Status ────────────────────────────────────────────
            'status'            => $this->status,  // pending | success | failed | refunded

            // ── Gateway Reference IDs ─────────────────────────────
            // These are needed by the mobile app to display receipts
            // and by the admin panel for manual reconciliation.
            // We include them here but NEVER the razorpay_signature
            // (that's a server-side security field only).
            'razorpay_order_id'   => $this->razorpay_order_id,
            'razorpay_payment_id' => $this->razorpay_payment_id,
            'transaction_id'      => $this->transaction_id,

            // ── Refund Information ────────────────────────────────
            // Only shown when a refund exists. Using when() so the
            // key doesn't appear at all in non-refunded payments —
            // avoids null clutter in the app response.
            'refund_id'           => $this->when(
                !is_null($this->refund_id),
                $this->refund_id
            ),
            'refund_amount'       => $this->when(
                !is_null($this->refund_amount),
                number_format((float) ($this->refund_amount ?? 0), 2, '.', '')
            ),
            'refunded_at'         => $this->when(
                !is_null($this->refunded_at),
                $this->refunded_at?->toDateTimeString()
            ),

            // ── Timestamps ────────────────────────────────────────
            'paid_at'    => $this->paid_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // ── Computed Helper Fields ────────────────────────────
            // These let the mobile app avoid computing statuses itself.
            'is_successful' => $this->status === 'success',
            'is_refunded'   => $this->status === 'refunded',
            'is_pending'    => $this->status === 'pending',
            'is_failed'     => $this->status === 'failed',

            // ── Conditionally Loaded Relationships ────────────────
            // These only appear in the JSON when the controller has
            // eager-loaded them. Prevents N+1 queries on list views.

            // The user who made this payment
            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            // The booking this payment is for
            'booking' => $this->whenLoaded('booking', fn () => [
                'id'             => $this->booking->id,
                'booking_number' => $this->booking->booking_number,
                'booking_status' => $this->booking->booking_status,
                'payment_status' => $this->booking->payment_status,
                'start_time'     => $this->booking->booking_start_time?->toDateTimeString(),
                'end_time'       => $this->booking->booking_end_time?->toDateTimeString(),
                'duration_hours' => $this->booking->duration_hours,
                'parking'        => $this->booking->parking ? [
                    'id'      => $this->booking->parking->id,
                    'name'    => $this->booking->parking->name,
                    'address' => $this->booking->parking->address,
                ] : null,
                'slot'           => $this->booking->parkingSlot ? [
                    'slot_number' => $this->booking->parkingSlot->slot_number,
                    'floor'       => $this->booking->parkingSlot->floor,
                ] : null,
                'vehicle'        => $this->booking->vehicle ? [
                    'vehicle_number' => $this->booking->vehicle->vehicle_number,
                    'vehicle_type'   => $this->booking->vehicle->vehicle_type,
                ] : null,
            ]),

            // Individual transaction log entries (full audit trail)
            'transactions' => $this->whenLoaded('transactions',
                fn () => $this->transactions->map(fn ($t) => [
                    'id'             => $t->id,
                    'type'           => $t->type,           // charge | refund | verification
                    'amount'         => number_format((float) $t->amount, 2, '.', ''),
                    'status'         => $t->status,
                    'gateway_ref'    => $t->gateway_ref,
                    'response_code'  => $t->response_code,
                    'created_at'     => $t->created_at?->toDateTimeString(),
                ])
            ),
        ];
    }
}