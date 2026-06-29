<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PricingRuleResource
 *
 * Transforms a PricingRule model into a structured JSON response.
 *
 * USED BY:
 *   - Admin panel: manage all pricing rules
 *   - Owner: view pricing applied to their parking slots
 *   - Booking flow: show the price breakdown before confirming
 *   - Flutter app: display "₹30 first hour, ₹20/hr after" on slot detail screen
 *
 * PRICING DISPLAY HELPER:
 *   `pricing_display` is a pre-formatted human-readable string so the
 *   Flutter app does not need to implement its own pricing type formatting.
 *   e.g. "₹30 first hour, ₹20/hr extra" or "₹200 per day"
 */
class PricingRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'             => $this->id,

            // ── VEHICLE TYPE ───────────────────────────────────────────────
            'vehicle_type_id' => $this->vehicle_type_id,

            /**
             * Vehicle type detail — included when eager-loaded.
             * Controller loads it with: $rule->load('vehicleType')
             */
            'vehicle_type' => $this->whenLoaded('vehicleType', fn () => [
                'id'   => $this->vehicleType->id,
                'name' => $this->vehicleType->name,
                'icon' => $this->vehicleType->icon ?? null,
            ]),

            // ── PRICING CONFIGURATION ─────────────────────────────────────
            /**
             * Billing cycle: 'hourly' | 'daily' | 'monthly'
             */
            'pricing_type'     => $this->pricing_type,

            /**
             * Base charge for the first unit (hour/day/month).
             * Cast to float — model stores as DECIMAL(10,2).
             */
            'base_price'       => (float) $this->base_price,

            /**
             * Charge for each additional hour beyond the first.
             * Only meaningful for 'hourly' pricing_type.
             * 0.00 for daily/monthly rules.
             */
            'extra_hour_price' => (float) $this->extra_hour_price,

            // ── STATUS ─────────────────────────────────────────────────────
            'status'    => $this->status,
            'is_active' => $this->status === 'active',

            // ── DISPLAY HELPER ─────────────────────────────────────────────
            /**
             * Pre-formatted pricing summary string for Flutter UI display.
             * Avoids conditional formatting logic in the Flutter app.
             *
             * Examples:
             *   hourly (with extra): "₹30 first hour, ₹20/hr after"
             *   hourly (no extra):   "₹30 per hour"
             *   daily:               "₹200 per day"
             *   monthly:             "₹3,000 per month"
             */
            'pricing_display' => $this->buildPricingDisplay(),

            // ── TIMESTAMPS ─────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    // =========================================================
    // PRIVATE HELPER
    // =========================================================

    /**
     * Build a human-readable pricing display string.
     *
     * @return string
     */
    private function buildPricingDisplay(): string
    {
        $base  = number_format((float) $this->base_price, 2);
        $extra = (float) $this->extra_hour_price;

        return match ($this->pricing_type) {
            'daily'   => "₹{$base} per day",
            'monthly' => "₹{$base} per month",
            default   => $extra > 0
                ? "₹{$base} first hour, ₹" . number_format($extra, 2) . '/hr after'
                : "₹{$base} per hour",
        };
    }
}