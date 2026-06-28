<?php

namespace App\Http\Requests\AppSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdateAppSettingRequest
 * ============================================================
 *
 * Validates incoming data when an admin updates an existing
 * application setting.
 *
 * WHAT CAN BE UPDATED:
 *   - value       → the new setting value (most common operation)
 *   - group       → re-categorise a setting (Super Admin only)
 *   - description → update the helper text (Super Admin only)
 *
 * WHAT CANNOT BE CHANGED:
 *   - key → setting keys are permanent identifiers. Changing a key
 *           would silently break all code that references it via
 *           AppSetting::getValue('old_key'). Keys are immutable.
 *
 * PARTIAL UPDATES:
 *   All fields use 'sometimes' so an admin can update just the
 *   value without re-sending group and description.
 *
 * WHO CAN UPDATE:
 *   - Admin:       can update `value` only.
 *   - Super Admin: can update `value`, `group`, and `description`.
 *   Role restrictions on group/description are enforced in the
 *   controller — this request validates whichever fields arrive.
 *
 * FUTURE SCALABILITY:
 *   - Add validation for `type` when that column is added.
 *   - Add `is_public` boolean validation.
 *   - Add `sort_order` integer validation.
 */
class UpdateAppSettingRequest extends FormRequest
{
    /**
     * Authorization is handled inside the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an AppSetting.
     */
    public function rules(): array
    {
        return [
            // ── Value ─────────────────────────────────────────────
            // The most frequently updated field.
            // Nullable so settings can be cleared (e.g. removing
            // a logo URL, blanking a deprecated config value).
            // No max length at validation level — some values
            // (like CMS content) may be long.
            'value' => [
                'sometimes',
                'nullable',
                'string',
            ],

            // ── Group ─────────────────────────────────────────────
            // Allows re-categorising a setting (Super Admin only).
            // Same allowed values as StoreAppSettingRequest.
            'group' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::in([
                    'general',
                    'payment',
                    'notification',
                    'seo',
                    'social_media',
                    'app',
                ]),
            ],

            // ── Description ───────────────────────────────────────
            // Allows updating the helper text shown in the Admin Panel.
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Normalise input values before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('group')) {
            $this->merge([
                'group' => strtolower(trim($this->input('group'))),
            ]);
        }

        // Trim whitespace from value unless explicitly null.
        if ($this->has('value') && $this->input('value') !== null) {
            $this->merge([
                'value' => trim($this->input('value')),
            ]);
        }
    }

    /**
     * Human-readable validation error messages.
     */
    public function messages(): array
    {
        return [
            'group.required'     => 'Group is required when updating the group field.',
            'group.in'           => 'Group must be one of: general, payment, notification, seo, social_media, app.',
            'group.max'          => 'Group name must not exceed 50 characters.',
            'description.max'    => 'Description must not exceed 500 characters.',
        ];
    }
}