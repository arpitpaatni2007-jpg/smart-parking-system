<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * BookingStatusHistoryResource
 * ============================================================
 *
 * Transforms a BookingStatusHistory model into a clean JSON response.
 * Used in the booking detail's timeline/audit view.
 */
class BookingStatusHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'remarks'    => $this->remarks,

            /**
             * changed_by null = system action (auto-expiry, webhook, etc.)
             * changed_by non-null = human actor (user, staff, admin)
             */
            'changed_by' => $this->whenLoaded('changedBy', fn () =>
                $this->changedBy ? [
                    'id'   => $this->changedBy->id,
                    'name' => $this->changedBy->name,
                ] : null
            ),
            'is_system_change' => is_null($this->changed_by),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}