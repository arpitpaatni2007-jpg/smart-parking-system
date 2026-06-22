<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * AppSetting Model
 *
 * A flexible key-value store for all application-wide configuration
 * that admins need to change without touching code or .env files.
 *
 * Think of this as an "admin-configurable config file" stored in MySQL.
 *
 * EXAMPLE ROWS:
 * ┌─────────────────────────┬───────────────┬──────────┬──────────────────────────────────┐
 * │ key                     │ value         │ group    │ description                      │
 * ├─────────────────────────┼───────────────┼──────────┼──────────────────────────────────┤
 * │ app_name                │ ParkEase      │ general  │ Application display name          │
 * │ currency                │ INR           │ general  │ Default currency code             │
 * │ support_email           │ help@park.com │ contact  │ Support email shown in app        │
 * │ booking_grace_minutes   │ 10            │ booking  │ Minutes before booking expires    │
 * │ max_advance_booking_days│ 30            │ booking  │ How far ahead users can book      │
 * └─────────────────────────┴───────────────┴──────────┴──────────────────────────────────┘
 *
 * USAGE:
 *   AppSetting::getValue('currency', 'INR')  → returns setting value or default
 *   AppSetting::setValue('app_name', 'ParkNow')  → updates or inserts
 *
 * FUTURE SCALABILITY:
 *   - Add `type` column ('string', 'boolean', 'json', 'integer') for auto-casting
 *   - Add `is_public` flag for settings safe to expose via public API
 *   - Add `is_encrypted` for storing secrets (API keys, webhook secrets)
 *   - Cache with Redis: Cache::rememberForever("setting:{$key}", fn() => ...)
 */
class AppSetting extends Model
{
    use HasFactory;

    /**
     * Explicit table name.
     */
    protected $table = 'app_settings';

    /**
     * Mass-assignable fields.
     */
    protected $fillable = [
        'key',          // Unique identifier string (e.g. 'currency', 'app_name')
        'value',        // The setting's current value (always stored as string)
        'group',        // Logical group for UI organization (e.g. 'general', 'booking')
        'description',  // Human-readable explanation for admin panel display
    ];

    // No SoftDeletes: settings are configuration data, not transactional records.
    // Deleting a setting key is intentional, not accidental.

    // ─────────────────────────────────────────────
    // STATIC HELPERS — convenient read/write API
    // ─────────────────────────────────────────────

    /**
     * Retrieve a setting's value by its key.
     *
     * @param  string $key      The setting key to look up
     * @param  mixed  $default  Fallback value if key doesn't exist
     * @return mixed
     *
     * Usage:
     *   $currency = AppSetting::getValue('currency', 'INR');
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Create or update a setting by key.
     * Uses Laravel's firstOrCreate + update pattern (upsert).
     *
     * @param  string $key    The setting key
     * @param  string $value  The new value to store
     * @return static
     *
     * Usage:
     *   AppSetting::setValue('app_name', 'ParkNow');
     */
    public static function setValue(string $key, string $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    /**
     * Scope: filter settings by group.
     * Usage: AppSetting::inGroup('booking')->get()
     *
     * Useful for loading only the settings needed for a specific admin panel section.
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}