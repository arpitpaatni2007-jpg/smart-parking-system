<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * OwnerBankDetailResource
 * ============================================================
 *
 * Transforms an OwnerBankDetail model into a clean JSON response.
 *
 * SECURITY:
 *   account_number is MASKED in the response — only last 4 digits shown.
 *   This uses the getMaskedAccountNumberAttribute() accessor on the model.
 *   Full account number is NEVER sent to the client.
 *
 *   The model also has $hidden = ['account_number'] which protects it
 *   if the model is accidentally serialized without this resource.
 */
class OwnerBankDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,

            // ── BANK DETAILS (safe to show) ───────────────────────────────
            'account_holder_name'    => $this->account_holder_name,
            'bank_name'              => $this->bank_name,
            'ifsc_code'              => $this->ifsc_code,

            /**
             * MASKED account number — shows only last 4 digits.
             * "12345678901" → "*******8901"
             * Uses the accessor: $model->masked_account_number
             *
             * NEVER expose the full account_number in an API response.
             */
            'account_number_masked'  => $this->masked_account_number,

            // ── OWNER ─────────────────────────────────────────────────────
            'owner' => $this->whenLoaded('owner', fn () => [
                'id'   => $this->owner->id,
                'name' => $this->owner->name,
            ]),

            // ── TIMESTAMPS ────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}