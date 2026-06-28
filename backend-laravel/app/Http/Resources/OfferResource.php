<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'offer_type'         => $this->offer_type,          // 'flat' | 'percentage' | 'free_hours'
            'discount_value'     => (float) $this->discount_value,
            'max_discount_amount'=> $this->max_discount_amount
                                       ? (float) $this->max_discount_amount
                                       : null,
            'min_booking_amount' => $this->min_booking_amount
                                       ? (float) $this->min_booking_amount
                                       : null,
            'applicable_to'      => $this->applicable_to,       // 'all' | 'first_booking' | 'specific_parking'
            'parking_id'         => $this->parking_id,
            'parking'            => $this->whenLoaded('parking', fn () => [
                'id'   => $this->parking->id,
                'name' => $this->parking->name,
            ]),
            'banner_image'       => $this->banner_image ?? null,
            'valid_from'         => $this->valid_from?->toISOString(),
            'valid_until'        => $this->valid_until?->toISOString(),
            'is_active'          => (bool) $this->is_active,
            'is_expired'         => $this->valid_until
                                       ? now()->gt($this->valid_until)
                                       : false,
            'is_currently_valid' => $this->is_active
                                       && (! $this->valid_from  || now()->gte($this->valid_from))
                                       && (! $this->valid_until || now()->lte($this->valid_until)),
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
        ];
    }
}