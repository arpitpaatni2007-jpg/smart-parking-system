<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'description'         => $this->description,
            'discount_type'       => $this->discount_type,        // 'flat' | 'percentage'
            'discount_value'      => (float) $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount
                                        ? (float) $this->max_discount_amount
                                        : null,
            'min_booking_amount'  => $this->min_booking_amount
                                        ? (float) $this->min_booking_amount
                                        : null,
            'usage_limit'         => $this->usage_limit,          // null = unlimited
            'used_count'          => (int) $this->used_count,
            'remaining_uses'      => $this->usage_limit !== null
                                        ? max(0, $this->usage_limit - $this->used_count)
                                        : null,
            'per_user_limit'      => $this->per_user_limit,       // null = unlimited per user
            'valid_from'          => $this->valid_from?->toISOString(),
            'valid_until'         => $this->valid_until?->toISOString(),
            'is_active'           => (bool) $this->is_active,
            'is_expired'          => $this->valid_until
                                        ? now()->gt($this->valid_until)
                                        : false,
            'is_valid'            => $this->is_active
                                        && (! $this->valid_from   || now()->gte($this->valid_from))
                                        && (! $this->valid_until  || now()->lte($this->valid_until))
                                        && ($this->usage_limit === null || $this->used_count < $this->usage_limit),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}