<?php

namespace App\Http\Requests\AppSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * StoreAppSettingRequest
 * ============================================================
 *
 * Validates incoming data when a Super Admin creates a new
 * application setting via the Admin Panel.
 *
 * EXISTING MODEL FIELDS:
 *   key         → unique setting identifier (snake_case slug)
 *   value       → the setting value (stored as text, any format)
 *   group       → category group for Admin Panel display
 *   description → human-readable label shown in the settings form
 *
 * WHO CAN CREATE SETTINGS:
 *   Only Super Admins should be able to create new settings.
 *   Standard Admins can only update existing settings.
 *   Role enforcement is handled in the controller.
 *
 * KEY NAMING CONVENTION:
 *   Keys must be snake_case identifiers, e.g.:
 *     "commission_percent", "app_name", "support_email"
 *   We enforce this with a regex rule so the setting can be
 *   referenced predictably in code via AppSetting::getValue('key').
 *
 * FUTURE SCALABILITY:
 *   - Add validation for `type` column (string|boolean|integer|json)
 *     when the model gains that column.
 *   - Add `is_public` boolean field for frontend-accessible settings.
 *   - Add `sort_order` integer for custom ordering within groups.
 */
class StoreAppSettingRequest extends FormRequest
{
    /**
     * Super Admin authorization is enforced in the controller.
     * Return true here to allow the request through.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new AppSetting.
     */
    public function rules(): array
    {
        return [
            // ── Setting Key ───────────────────────────────────────
            // Must be unique across all settings (primary identifier).
            // Only lowercase letters, numbers, and underscores allowed.
            // This enforces consistent snake_case naming like:
            //   "commission_percent", "app_name", "razorpay_mode"
            'key' => [
                'required',
                'string',
                'max:100',
                // Only snake_case: lowercase, digits, underscores.
                'regex:/^[a-z][a-z0-9_]*$/',
                // Global uniqueness — no two settings can share a key.
                Rule::unique('app_settings', 'key'),
            ],

            // ── Setting Value ─────────────────────────────────────
            // Nullable — some settings may be intentionally blank
            // (e.g. an API key field that has not been configured yet).
            // No max length restriction at validation level because
            // some values may be long (e.g. terms & conditions HTML).
            'value' => [
                'nullable',
                'string',
            ],

            // ── Group ─────────────────────────────────────────────
            // Restricts to known group names so the Admin Panel's
            // section tabs are always predictable.
            // Extend this list as new setting categories are added.
            'group' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'general',       // Site name, contact details, address
                    'payment',       // Commission %, payment gateway settings
                    'notification',  // FCM config, notification preferences
                    'seo',           // Meta title, meta description, keywords
                    'social_media',  // Facebook, Instagram, Twitter links
                    'app',           // App version, maintenance mode, features
                ]),
            ],

            // ── Description ───────────────────────────────────────
            // Shown as a helper label in the Admin Panel settings form.
            // Required so any admin looking at the settings screen
            // understands what each key controls.
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Prepare and normalise input before validation runs.
     * Trims whitespace and lowercases the key so storage is consistent.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('key')) {
            // Normalise key: lowercase + trim.
            // e.g. " Commission_Percent " → "commission_percent"
            $this->merge([
                'key' => strtolower(trim($this->input('key'))),
            ]);
        }

        if ($this->filled('group')) {
            $this->merge([
                'group' => strtolower(trim($this->input('group'))),
            ]);
        }
    }

    /**
     * Human-readable validation error messages.
     */
    public function messages(): array
    {
        return [
            'key.required'       => 'A setting key is required.',
            'key.max'            => 'Setting key must not exceed 100 characters.',
            'key.regex'          => 'Setting key must be snake_case: only lowercase letters, numbers, and underscores (e.g. commission_percent).',
            'key.unique'         => 'A setting with this key already exists. Use the update endpoint to change its value.',
            'group.required'     => 'Please specify a group for this setting.',
            'group.in'           => 'Group must be one of: general, payment, notification, seo, social_media, app.',
            'description.max'    => 'Description must not exceed 500 characters.',
        ];
    }
}