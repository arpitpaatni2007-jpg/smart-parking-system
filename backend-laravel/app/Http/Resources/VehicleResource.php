<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * VehicleResource
 * ============================================================
 *
 * Transforms a Vehicle model into a structured JSON response.
 *
 * DESIGN PRINCIPLES:
 *   - whenLoaded() is used for all relationships — no N+1 risk.
 *     If the relationship wasn't eager-loaded, its key is omitted.
 *   - Computed attributes (displayLabel, isActive, hasValidDocuments)
 *     use the model's helper methods — no logic in the resource.
 *   - The response is designed for the Flutter "My Vehicles" screen
 *     and the booking slot selection dropdown.
 *
 * TWO RESPONSE MODES:
 *   List view (index): minimal — no document list
 *   Detail view (show): full — includes documents and booking history
 *   The same resource handles both via whenLoaded().
 */
class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'             => $this->id,
            'vehicle_number' => $this->vehicle_number, // Already normalized: "HR26DQ8849"
            'vehicle_name'   => $this->vehicle_name,   // User nickname: "My Swift"
            'vehicle_brand'  => $this->vehicle_brand,
            'vehicle_color'  => $this->vehicle_color,

            // ── STATUS ─────────────────────────────────────────────────────
            'status'    => $this->status,
            'is_active' => $this->isActive(),

            // ── DISPLAY HELPERS ────────────────────────────────────────────
            /**
             * Pre-computed label for use in Flutter dropdowns.
             * e.g. "My Swift (HR26DQ8849)"
             * Avoids duplicate string formatting in the Flutter app.
             */
            'display_label' => $this->displayLabel(),

            // ── VEHICLE TYPE ───────────────────────────────────────────────
            /**
             * Vehicle type from master table.
             * Included only when eager-loaded: $vehicle->load('vehicleType')
             * Used to determine slot type for booking.
             */
            'vehicle_type' => $this->whenLoaded('vehicleType', fn () => [
                'id'   => $this->vehicleType->id,
                'name' => $this->vehicleType->name,
                'icon' => $this->vehicleType->icon ?? null,
            ]),

            // ── OWNER ──────────────────────────────────────────────────────
            /**
             * Only included for admin/owner responses.
             * Regular user responses skip this (not loaded).
             */
            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            // ── DOCUMENT SUMMARY ───────────────────────────────────────────
            /**
             * Quick compliance check flag — used in the "My Vehicles" list
             * to show a warning badge if documents are missing or expired.
             */
            'has_valid_documents' => $this->hasValidDocuments(),

            /**
             * Full document list — included only on detail view.
             * Controller loads this for show() but not index().
             */
            'documents' => $this->whenLoaded(
                'documents',
                fn () => VehicleDocumentResource::collection($this->documents)
            ),

            // ── TIMESTAMPS ─────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}