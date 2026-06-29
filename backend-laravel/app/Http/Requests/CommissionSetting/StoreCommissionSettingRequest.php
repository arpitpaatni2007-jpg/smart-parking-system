<?php

namespace App\Http\Requests\CommissionSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * ============================================================
 * StoreCommissionSettingRequest
 * ============================================================
 *
 * Validates incoming data when a Super Admin creates a new
 * commission setting record.
 *
 * EXISTING MODEL FIELDS ONLY:
 *   - commission_percent  → platform's cut (e.g. 20.00 = 20%)
 *   - owner_share_percent → owner's cut   (e.g. 80.00 = 80%)
 *   - status              → active | inactive
 *
 * BUSINESS RULE — MUST SUM TO 100:
 *   commission_percent + owner_share_percent === 100.00
 *   This is enforced in withValidator() after field rules pass.
 *   The model's isBalanced() method performs the same check —
 *   we validate it here before even reaching the model.
 *
 * ONLY ONE ACTIVE RECORD AT A TIME:
 *   If status is "active", no other record should currently be
 *   "active". The controller handles deactivating the previous
 *   active record in a transaction — this request simply ensures
 *   the new data is structurally valid.
 *
 * WHO CAN CREATE:
 *   Only Super Admins. Enforced in the controller.
 *
 * FUTURE SCALABILITY:
 *   - Add `effective_from` date when scheduled rate changes are needed.
 *   - Add `parking_id` FK for per-parking commission overrides.
 *   - Add `min_booking_amount` threshold for tiered commission tiers.
 */
class StoreCommissionSettingRequest extends FormRequest
{
    /**
     * Authorization is enforced inside the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field-level validation rules.
     */
    public function rules(): array
    {
        return [
            // ── Commission Percent (Platform's Share) ─────────────
            // The percentage the platform keeps from each booking.
            // Must be between 1 and 99 to leave room for the owner.
            // Stored as DECIMAL(5,2) — allows values like 18.50.
            'commission_percent' => [
                'required',
                'numeric',
                'min:1',
                'max:99',
                // Restrict to 2 decimal places to match the DB column precision.
                'regex:/^\d{1,2}(\.\d{1,2})?$/',
            ],

            // ── Owner Share Percent (Owner's Share) ───────────────
            // The percentage paid to the parking owner.
            // Must be between 1 and 99 to leave room for the platform.
            // Together with commission_percent, must equal exactly 100.
            'owner_share_percent' => [
                'required',
                'numeric',
                'min:1',
                'max:99',
                'regex:/^\d{1,2}(\.\d{1,2})?$/',
            ],

            // ── Status ────────────────────────────────────────────
            // "active"   → this is the current live commission rate.
            // "inactive" → historical record, not currently applied.
            // Defaults to "active" in prepareForValidation().
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Business-rule validation: the two percentages must sum to 100.
     *
     * We run this AFTER the field rules pass so we already know both
     * values are valid numbers before trying to add them together.
     *
     * @param  Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Skip the sum check if either field already failed
            // basic validation — no point checking 0 + invalid.
            if ($v->errors()->hasAny(['commission_percent', 'owner_share_percent'])) {
                return;
            }

            $commission  = (float) $this->input('commission_percent', 0);
            $ownerShare  = (float) $this->input('owner_share_percent', 0);
            $total       = round($commission + $ownerShare, 2);

            if ($total !== 100.00) {
                $v->errors()->add(
                    'owner_share_percent',
                    "commission_percent ({$commission}%) and owner_share_percent ({$ownerShare}%) " .
                    "must add up to exactly 100%. Current total: {$total}%."
                );
            }
        });
    }

    /**
     * Default the status to "active" when not provided.
     * A new commission setting is almost always meant to go live immediately.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('status')) {
            $this->merge(['status' => 'active']);
        }

        // Normalise percentages to 2 decimal places for consistent storage.
        if ($this->filled('commission_percent')) {
            $this->merge([
                'commission_percent' => round((float) $this->input('commission_percent'), 2),
            ]);
        }

        if ($this->filled('owner_share_percent')) {
            $this->merge([
                'owner_share_percent' => round((float) $this->input('owner_share_percent'), 2),
            ]);
        }
    }

    /**
     * Human-readable error messages.
     */
    public function messages(): array
    {
        return [
            'commission_percent.required'     => 'Platform commission percentage is required.',
            'commission_percent.numeric'      => 'Commission percentage must be a number.',
            'commission_percent.min'          => 'Commission percentage must be at least 1%.',
            'commission_percent.max'          => 'Commission percentage cannot exceed 99%.',
            'commission_percent.regex'        => 'Commission percentage must have at most 2 decimal places (e.g. 20 or 18.50).',
            'owner_share_percent.required'    => 'Owner share percentage is required.',
            'owner_share_percent.numeric'     => 'Owner share percentage must be a number.',
            'owner_share_percent.min'         => 'Owner share percentage must be at least 1%.',
            'owner_share_percent.max'         => 'Owner share percentage cannot exceed 99%.',
            'owner_share_percent.regex'       => 'Owner share percentage must have at most 2 decimal places (e.g. 80 or 81.50).',
            'status.in'                       => 'Status must be either active or inactive.',
        ];
    }
}