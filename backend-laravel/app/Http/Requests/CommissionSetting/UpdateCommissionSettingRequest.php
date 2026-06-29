<?php

namespace App\Http\Requests\CommissionSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * ============================================================
 * UpdateCommissionSettingRequest
 * ============================================================
 *
 * Validates incoming data when a Super Admin updates an
 * existing commission setting record.
 *
 * EXISTING MODEL FIELDS ONLY:
 *   - commission_percent  → platform's cut (decimal, 1–99)
 *   - owner_share_percent → owner's cut   (decimal, 1–99)
 *   - status              → active | inactive
 *
 * PARTIAL UPDATES:
 *   All fields use 'sometimes' so an admin can update just the
 *   status (to deactivate a setting) without re-sending the
 *   percentage values.
 *
 * SUM-TO-100 RULE ON PARTIAL UPDATES:
 *   The 100% balance rule only applies when BOTH percentage fields
 *   are present in the request. If only one percentage is sent,
 *   we must resolve the other from the existing DB record before
 *   checking the sum. This logic is handled in withValidator().
 *
 * BUSINESS RULE — ONE ACTIVE RECORD:
 *   If status is changed to "active", the controller will
 *   deactivate any other currently active record inside a
 *   DB transaction. This request just validates the data shape.
 *
 * FUTURE SCALABILITY:
 *   - Add `effective_from` date for scheduled rate updates.
 *   - Add `reason` note field for audit trail of rate changes.
 */
class UpdateCommissionSettingRequest extends FormRequest
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
     *
     * All fields are 'sometimes' — only validated when present.
     */
    public function rules(): array
    {
        return [
            // ── Commission Percent ────────────────────────────────
            'commission_percent' => [
                'sometimes',
                'required',
                'numeric',
                'min:1',
                'max:99',
                'regex:/^\d{1,2}(\.\d{1,2})?$/',
            ],

            // ── Owner Share Percent ───────────────────────────────
            'owner_share_percent' => [
                'sometimes',
                'required',
                'numeric',
                'min:1',
                'max:99',
                'regex:/^\d{1,2}(\.\d{1,2})?$/',
            ],

            // ── Status ────────────────────────────────────────────
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Business-rule validation: percentage sum must equal 100.
     *
     * Handles three scenarios on update:
     *
     *   A) BOTH percentages provided in request:
     *      → Validate that commission + owner_share == 100 directly.
     *
     *   B) ONLY commission_percent provided:
     *      → Load owner_share_percent from the existing DB record.
     *      → Check commission (new) + owner_share (existing) == 100.
     *
     *   C) ONLY owner_share_percent provided:
     *      → Load commission_percent from the existing DB record.
     *      → Check commission (existing) + owner_share (new) == 100.
     *
     *   D) NEITHER percentage provided (only status update):
     *      → Skip the sum check entirely.
     *
     * @param  Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Skip if percentage fields already failed basic validation.
            if ($v->errors()->hasAny(['commission_percent', 'owner_share_percent'])) {
                return;
            }

            $hasCommission  = $this->has('commission_percent');
            $hasOwnerShare  = $this->has('owner_share_percent');

            // Scenario D: no percentage fields — nothing to check.
            if (!$hasCommission && !$hasOwnerShare) {
                return;
            }

            // Retrieve the existing record for resolving missing values.
            // Route model binding provides the CommissionSetting instance
            // via the route parameter. We access it from the route here.
            $existing = $this->route('commission_setting')
                ?? $this->route('commissionSetting');

            // Resolve effective values (request value OR existing DB value).
            $commission = $hasCommission
                ? (float) $this->input('commission_percent')
                : (float) ($existing?->commission_percent ?? 0);

            $ownerShare = $hasOwnerShare
                ? (float) $this->input('owner_share_percent')
                : (float) ($existing?->owner_share_percent ?? 0);

            $total = round($commission + $ownerShare, 2);

            if ($total !== 100.00) {
                // Attach the error to whichever percentage field was
                // provided in the request so the message is contextual.
                $errorField = $hasOwnerShare ? 'owner_share_percent' : 'commission_percent';

                $v->errors()->add(
                    $errorField,
                    "commission_percent ({$commission}%) and owner_share_percent ({$ownerShare}%) " .
                    "must add up to exactly 100%. Current total: {$total}%."
                );
            }
        });
    }

    /**
     * Normalise percentage values before validation.
     */
    protected function prepareForValidation(): void
    {
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
            'commission_percent.required'     => 'Commission percentage is required when provided.',
            'commission_percent.numeric'      => 'Commission percentage must be a number.',
            'commission_percent.min'          => 'Commission percentage must be at least 1%.',
            'commission_percent.max'          => 'Commission percentage cannot exceed 99%.',
            'commission_percent.regex'        => 'Commission percentage must have at most 2 decimal places (e.g. 20 or 18.50).',
            'owner_share_percent.required'    => 'Owner share percentage is required when provided.',
            'owner_share_percent.numeric'     => 'Owner share percentage must be a number.',
            'owner_share_percent.min'         => 'Owner share percentage must be at least 1%.',
            'owner_share_percent.max'         => 'Owner share percentage cannot exceed 99%.',
            'owner_share_percent.regex'       => 'Owner share percentage must have at most 2 decimal places (e.g. 80 or 81.50).',
            'status.required'                 => 'Status is required when provided.',
            'status.in'                       => 'Status must be either active or inactive.',
        ];
    }
}