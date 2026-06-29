<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * PaymentMethodResource
 * ============================================================
 *
 * Transforms a PaymentMethod model into a structured, consistent
 * JSON response for all payment-method-related API endpoints.
 *
 * WHY A DEDICATED RESOURCE?
 * Controls exactly which columns are exposed to the Flutter app
 * and Admin Panel. Internal fields or future gateway-routing
 * columns are never accidentally leaked.
 *
 * RESPONSE DESIGN DECISIONS:
 *   - `is_active` computed flag lets the Flutter app avoid
 *     comparing strings ("active" / "inactive") itself.
 *   - Timestamps are ISO strings for consistent parsing in Dart.
 *
 * USAGE:
 *   return new PaymentMethodResource($paymentMethod);
 *   return PaymentMethodResource::collection($paymentMethods);
 */
class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the PaymentMethod model into a JSON-friendly array.
     *
     * Fields sourced strictly from the PaymentMethod model's
     * $fillable + $casts + auto-managed columns:
     *   id, name, icon, description, status, created_at, updated_at
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── Core Identity ─────────────────────────────────────
            'id'          => $this->id,

            // ── Display Fields ────────────────────────────────────
            // `name`        → slug-style identifier: upi, card, net_banking, wallet
            // `icon`        → URL or icon key for the Flutter payment screen
            // `description` → Human-readable label shown below the icon
            'name'        => $this->name,
            'icon'        => $this->icon,
            'description' => $this->description,

            // ── Status ────────────────────────────────────────────
            // Raw value: "active" | "inactive"
            // Admin Panel reads this for the toggle control.
            'status'      => $this->status,

            // ── Computed Helper Flag ──────────────────────────────
            // Flutter payment screen uses this boolean directly
            // instead of comparing the `status` string itself.
            'is_active'   => $this->isActive(),

            // ── Timestamps ────────────────────────────────────────
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}