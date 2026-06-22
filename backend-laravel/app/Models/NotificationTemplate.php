<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NotificationTemplate Model
 *
 * Manages reusable notification templates for all system-triggered
 * communications. Instead of hardcoding message strings in controllers,
 * admins can manage them from the dashboard.
 *
 * Template types supported:
 *   - email  → Sent via Mail (uses `subject` + `message` as HTML body)
 *   - sms    → Sent via SMS gateway (uses `message` as plain text)
 *   - push   → Sent as mobile push notification (uses `title` + `message`)
 *
 * Variable Interpolation (FUTURE):
 *   Use placeholders like {{user_name}}, {{booking_id}}, {{amount}}
 *   in the `message` field. Your notification service replaces them at runtime.
 *
 * FUTURE SCALABILITY:
 *   - Add `locale` column for multi-language support
 *   - Add `variables` JSON column to document what placeholders are valid
 *   - Add `last_used_at` timestamp for analytics
 */
class NotificationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Explicit table name declaration.
     */
    protected $table = 'notification_templates';

    /**
     * Mass-assignable fields.
     */
    protected $fillable = [
        'title',    // Internal admin-facing label (e.g. "Booking Confirmed - Email")
        'type',     // Channel: 'email' | 'sms' | 'push'
        'subject',  // Email subject line (null for sms/push)
        'message',  // The body/content of the notification
        'status',   // 'active' | 'inactive'
    ];

    /**
     * Type casts for clean attribute access.
     */
    protected $casts = [
        // No special casts needed currently.
        // FUTURE: cast `variables` to 'array' when that column is added.
    ];

    // ─────────────────────────────────────────────
    // CONSTANTS — avoid magic strings in your code
    // ─────────────────────────────────────────────

    /** Notification sent via email */
    const TYPE_EMAIL = 'email';

    /** Notification sent via SMS */
    const TYPE_SMS = 'sms';

    /** Notification sent as mobile push */
    const TYPE_PUSH = 'push';

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    /**
     * Scope: only active templates.
     * Usage: NotificationTemplate::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: filter by notification type.
     * Usage: NotificationTemplate::ofType('email')->first()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $type  One of the TYPE_* constants
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    /**
     * Replace placeholder tokens in the message with actual values.
     *
     * Example:
     *   $template->render(['user_name' => 'Arpit', 'booking_id' => '123'])
     *   turns "Hello {{user_name}}, your booking {{booking_id}} is confirmed."
     *   into  "Hello Arpit, your booking 123 is confirmed."
     *
     * @param  array $variables  Key-value pairs to substitute
     * @return string
     */
    public function render(array $variables = []): string
    {
        $message = $this->message;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{{$key}}}", $value, $message);
        }

        return $message;
    }
}