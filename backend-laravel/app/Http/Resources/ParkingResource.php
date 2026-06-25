<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * ParkingResource
 * ============================================================
 *
 * Transforms a Parking model into a clean JSON response.
 * Controls WHAT is sent to the client — hides internal/sensitive
 * fields and formats data for Flutter consumption.
 *
 * CONDITIONAL LOADING (whenLoaded):
 *   Related data is only included if it was eager-loaded in the
 *   controller. This prevents N+1 queries when the relationship
 *   was forgotten to be loaded, and keeps list responses lean.
 *
 * USAGE:
 *   Single: new ParkingResource($parking)
 *   List:   ParkingResource::collection($parkings)
 */
class ParkingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'     => $this->id,
            'name'   => $this->name,
            'status' => $this->booking_status ?? $this->status,

            // ── LOCATION ──────────────────────────────────────────────────
            'address'   => $this->address,
            'latitude'  => (float) $this->latitude,
            'longitude' => (float) $this->longitude,

            /**
             * State and city — included if eager-loaded.
             * Controller loads them with: $parking->load('state', 'city')
             */
            'state' => $this->whenLoaded('state', fn () => [
                'id'   => $this->state->id,
                'name' => $this->state->name,
            ]),
            'city' => $this->whenLoaded('city', fn () => [
                'id'   => $this->city->id,
                'name' => $this->city->name,
            ]),

            // ── DESCRIPTION ───────────────────────────────────────────────
            'description' => $this->description,

            // ── CAPACITY ──────────────────────────────────────────────────
            'total_slots'     => (int) $this->total_slots,

            /**
             * Available slot count — computed from the slots relationship.
             * Only included when slots are eager-loaded.
             * Used to show "X slots available" in search results.
             */
            'available_slots' => $this->whenLoaded(
                'slots',
                fn () => $this->slots->where('status', 'available')->count()
            ),

            // ── OWNER ─────────────────────────────────────────────────────
            'owner' => $this->whenLoaded('owner', fn () => [
                'id'   => $this->owner->id,
                'name' => $this->owner->name,
            ]),

            // ── IMAGES ────────────────────────────────────────────────────
            /**
             * Primary image thumbnail URL.
             * Shown in search result cards.
             */
            'primary_image' => $this->whenLoaded('images', function () {
                $primary = $this->images->where('is_primary', true)->first();
                return $primary ? $primary->url : null;
            }),

            /**
             * Full image gallery — included on detail views.
             */
            'images' => $this->whenLoaded(
                'images',
                fn () => ParkingImageResource::collection($this->images)
            ),

            // ── FACILITIES ────────────────────────────────────────────────
            'facilities' => $this->whenLoaded('facilities', function () {
                return $this->facilities->map(fn ($f) => [
                    'id'   => $f->id,
                    'name' => $f->name,
                    'icon' => $f->icon ?? null,
                ]);
            }),

            // ── SLOTS ─────────────────────────────────────────────────────
            'slots' => $this->whenLoaded(
                'slots',
                fn () => ParkingSlotResource::collection($this->slots)
            ),

            // ── DISTANCE (if proximity search was used) ───────────────────
            /**
             * Distance in KM from user's location.
             * Only present when nearLocation() scope was applied in the query.
             * The Haversine formula adds a 'distance' attribute to each model.
             */
            'distance_km' => $this->when(
                isset($this->distance),
                fn () => round((float) $this->distance, 2)
            ),

            // ── TIMESTAMPS ────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}