<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * CheckInResource
 * ============================================================
 *
 * Transforms a CheckIn model into a clean JSON response.
 */
class CheckInResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'booking_id'   => $this->booking_id,
            'checkin_time' => $this->checkin_time?->toISOString(),
            'notes'        => $this->notes,
            'is_manual'    => $this->isManual(),   // true = human processed, false = automated

            // ── WHO PROCESSED IT ───────────────────────────────────────────
            'checked_in_by' => $this->whenLoaded('checkedInBy', fn () =>
                $this->checkedInBy ? [
                    'id'   => $this->checkedInBy->id,
                    'name' => $this->checkedInBy->name,
                ] : null
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}