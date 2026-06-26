<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * CheckOutResource
 * ============================================================
 *
 * Transforms a CheckOut model into a clean JSON response.
 * Includes overstay information for the Flutter billing screen.
 */
class CheckOutResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'booking_id'    => $this->booking_id,
            'checkout_time' => $this->checkout_time?->toISOString(),

            // ── OVERSTAY DETAILS ────────────────────────────────────────────
            'extra_hours'  => (float) $this->extra_hours,
            'extra_amount' => (float) $this->extra_amount,
            'has_overstay' => $this->hasOverstay(), // convenience boolean for Flutter

            // ── TOTAL BILL ─────────────────────────────────────────────────
            /**
             * Total bill = booking.amount + checkout.extra_amount.
             * This is the final amount the user owes.
             * Use this on the Flutter billing/receipt screen.
             */
            'total_bill' => $this->totalBill(),

            'notes'     => $this->notes,
            'is_manual' => $this->isManual(),

            // ── WHO PROCESSED IT ───────────────────────────────────────────
            'checked_out_by' => $this->whenLoaded('checkedOutBy', fn () =>
                $this->checkedOutBy ? [
                    'id'   => $this->checkedOutBy->id,
                    'name' => $this->checkedOutBy->name,
                ] : null
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}