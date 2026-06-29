<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'commission_percent'  => (float) $this->commission_percent,
            'owner_share_percent' => (float) $this->owner_share_percent,
            'status'              => $this->status,
            'is_active'           => $this->status === 'active',
            'is_balanced'         => $this->isBalanced(),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}