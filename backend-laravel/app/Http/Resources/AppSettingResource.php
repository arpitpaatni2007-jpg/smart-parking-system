<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * AppSettingResource
 * ============================================================
 *
 * Transforms an AppSetting model into a structured JSON response.
 *
 * MODEL FIELDS AVAILABLE (from existing AppSetting model):
 *   - key         → unique setting identifier (e.g. "commission_percent")
 *   - value       → the setting's current value (stored as text)
 *   - group       → category grouping (e.g. "general", "payment", "notification")
 *   - description → human-readable explanation of what this setting controls
 *
 * WHY THIS RESOURCE EXISTS:
 * The AppSetting model stores ALL platform configuration as
 * key-value pairs. Some values may be sensitive (e.g. API keys,
 * secret thresholds). This resource:
 *   - Controls exactly which fields are exposed
 *   - Casts value to the appropriate PHP type based on context
 *   - Groups settings cleanly for Admin Panel display
 *   - Never exposes raw internal config keys not meant for the API
 *
 * USAGE:
 *   return new AppSettingResource($setting);
 *   return AppSettingResource::collection($settings);
 *
 * COLLECTION USAGE (grouped by group field):
 *   The controller may pass a grouped collection. For individual
 *   settings, use new AppSettingResource($setting).
 *
 * FUTURE SCALABILITY:
 *   - Add `type` (string | boolean | integer | json) when the model
 *     gains a type column — allows the frontend to render the
 *     correct input control (toggle vs text vs number).
 *   - Add `is_public` flag when some settings should be readable
 *     by non-admin users (e.g. app_name, support_phone).
 *   - Add `last_updated_by` to show which admin changed a setting.
 */
class AppSettingResource extends JsonResource
{
    /**
     * Transform the AppSetting model into a JSON-friendly array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── Core Identity ──────────────────────────────────────
            'id'          => $this->id,

            // ── Setting Key ────────────────────────────────────────
            // The unique machine-readable identifier for this setting.
            // Examples: "commission_percent", "app_name", "support_email"
            // Snake_case convention matches the model's stored format.
            'key'         => $this->key,

            // ── Setting Value ──────────────────────────────────────
            // Always stored as text in the DB. We cast it here to the
            // most appropriate type for JSON serialisation:
            //   - "true"/"false" strings → boolean
            //   - Numeric strings        → integer or float
            //   - Everything else        → string (as stored)
            // This avoids the frontend receiving "20" instead of 20
            // for numeric settings like commission_percent.
            'value'       => $this->castValue($this->value),

            // ── Group ─────────────────────────────────────────────
            // Category this setting belongs to.
            // Used by the Admin Panel to render settings in sections:
            //   general | payment | notification | seo | social_media | app
            'group'       => $this->group,

            // ── Description ───────────────────────────────────────
            // Human-readable explanation shown as a label/tooltip in
            // the Admin Panel settings form.
            'description' => $this->description,

            // ── Timestamps ────────────────────────────────────────
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Intelligently cast a string value to the most appropriate PHP type.
     *
     * AppSetting stores all values as VARCHAR/TEXT in the database.
     * This helper converts them to proper types before JSON serialisation
     * so the API consumer receives `20` instead of `"20"` for numbers,
     * and `true` instead of `"true"` for booleans.
     *
     * CAST RULES (in order of precedence):
     *   1. null / empty string        → null
     *   2. "true" / "false" (case-insensitive) → bool
     *   3. Pure integer string ("20") → int
     *   4. Pure float string ("3.14") → float
     *   5. Valid JSON object/array    → decoded array
     *   6. Everything else            → string (unchanged)
     *
     * @param  string|null  $value  The raw stored string value.
     * @return mixed                Appropriately typed PHP value.
     */
    private function castValue(mixed $value): mixed
    {
        // Null or empty string → return null.
        if ($value === null || $value === '') {
            return null;
        }

        // Boolean strings.
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }

        // Pure integer (no leading zeros, no decimal point).
        if (ctype_digit(ltrim($value, '-')) && !str_contains($value, '.')) {
            return (int) $value;
        }

        // Numeric float.
        if (is_numeric($value) && str_contains($value, '.')) {
            return (float) $value;
        }

        // JSON object or array (starts with { or [).
        if (
            (str_starts_with($value, '{') && str_ends_with($value, '}')) ||
            (str_starts_with($value, '[') && str_ends_with($value, ']'))
        ) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Default: return as-is string.
        return $value;
    }
}