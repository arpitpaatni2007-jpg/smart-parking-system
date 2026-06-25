<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * ParkingSlotResource
 * ============================================================
 *
 * Transforms a ParkingSlot model into a clean JSON response.
 * Used in:
 *   - Slot list for a parking (owner/admin view)
 *   - Booking slot selection (user view)
 *   - Real-time availability display
 */
class ParkingSlotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'slot_number' => $this->slot_number,
            'slot_type'   => $this->slot_type,
            'status'      => $this->status,
            'is_available'=> $this->status === 'available',

            /**
             * Vehicle type — loaded when showing slot details.
             * Tells the user/app what type of vehicle can use this slot.
             */
            'vehicle_type' => $this->whenLoaded('vehicleType', fn () => [
                'id'   => $this->vehicleType->id,
                'name' => $this->vehicleType->name,
                'icon' => $this->vehicleType->icon ?? null,
            ]),

            /**
             * Parking parent — included when slot is returned standalone
             * (not nested under a parking resource).
             */
            'parking' => $this->whenLoaded('parking', fn () => [
                'id'      => $this->parking->id,
                'name'    => $this->parking->name,
                'address' => $this->parking->address,
            ]),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}