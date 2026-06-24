<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * NotificationLog Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Creating a Notification record (in the `notifications` table)
 * tells us WHAT was sent and TO WHOM. But it doesn't tell us:
 *
 *   → Was the FCM push notification actually delivered?
 *   → Did Firebase return an error? If so, what error?
 *   → Was a retry attempted? Did it succeed on the second try?
 *   → Was this notification delivered via push AND email?
 *
 * NotificationLog answers all these questions. It is the
 * DELIVERY RECEIPT for each notification — one row per delivery
 * attempt per channel.
 *
 * REAL-WORLD ANALOGY:
 *   If Notification is the letter you wrote and put in the
 *   post box, NotificationLog is the tracking history:
 *     "Picked up at 10:00 AM → Arrived at sorting hub 2:00 PM →
 *      Out for delivery 8:00 AM → Delivered 9:30 AM"
 *
 * HOW MULTIPLE LOGS PER NOTIFICATION WORKS:
 *
 *   Scenario 1 — Single channel success:
 *     notifications row: id=101, type=booking_confirmed
 *     notification_logs row: id=1, notification_id=101,
 *                            channel=fcm, status=delivered
 *
 *   Scenario 2 — FCM fails, retry succeeds:
 *     notifications row: id=102, type=payment_success
 *     notification_logs row: id=2, notification_id=102,
 *                            channel=fcm, status=failed,
 *                            response_message="Device token not found"
 *     notification_logs row: id=3, notification_id=102,
 *                            channel=fcm, status=delivered
 *                            (after token refresh and retry)
 *
 *   Scenario 3 — Multi-channel delivery (future):
 *     notifications row: id=103, type=booking_reminder
 *     notification_logs row: id=4, notification_id=103,
 *                            channel=fcm, status=delivered
 *     notification_logs row: id=5, notification_id=103,
 *                            channel=email, status=delivered
 *     notification_logs row: id=6, notification_id=103,
 *                            channel=sms, status=failed
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   NOTIFICATION SERVICE (built later):
 *     After every FCM send attempt, the service creates a log:
 *       NotificationLog::create([
 *           'notification_id'  => $notification->id,
 *           'delivery_channel' => NotificationLog::CHANNEL_FCM,
 *           'delivery_status'  => $success
 *               ? NotificationLog::STATUS_DELIVERED
 *               : NotificationLog::STATUS_FAILED,
 *           'response_message' => $fcmResponse,
 *       ]);
 *
 *   ADMIN PANEL — NOTIFICATION MANAGEMENT:
 *     Admin can see a delivery report for any notification:
 *       - "Push: Delivered ✅"
 *       - "Email: Failed ❌ — Invalid email address"
 *     This data comes from this model.
 *
 *   FAILED NOTIFICATION RETRY:
 *     A scheduled job queries:
 *       NotificationLog::failed()->where('created_at', '>', $cutoff)
 *     ...and retries delivery. Each retry creates a new log row
 *     so there's a complete attempt history.
 *
 *   ANALYTICS:
 *     - FCM delivery success rate this week
 *     - Most common failure reasons
 *     - Average time from notification creation to delivery
 *
 * FUTURE SCALABILITY:
 *   - Add `attempted_at` datetime for when the delivery was tried
 *     (vs `created_at` which is when the log row was inserted —
 *     for queued/async delivery these may differ).
 *   - Add `delivered_at` datetime for confirmed delivery timestamp
 *     (Firebase now supports delivery receipts via Analytics).
 *   - Add `opened_at` datetime for tracking notification open rates.
 *   - Add `retry_count` integer to limit retries and track attempts.
 *   - Add `provider_message_id` (varchar) to store the unique ID
 *     that Firebase / email provider returns on send — useful for
 *     looking up delivery status from the provider's dashboard.
 *
 * IMPORTANT NOTE ON TABLE GROWTH:
 *   notification_logs will grow faster than notifications because
 *   each notification may generate multiple log rows (retries,
 *   multi-channel). Archiving old rows after 3-6 months is advised.
 *
 * @property int         $id
 * @property int         $notification_id
 * @property string      $delivery_channel
 * @property string      $delivery_status
 * @property string|null $response_message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationLog extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'notification_logs';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'notification_id',
        'delivery_channel',
        'delivery_status',
        'response_message',
    ];

    /**
     * Type-cast database columns.
     */
    protected $casts = [
        'notification_id' => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Delivery Channel Constants
    |--------------------------------------------------------------------
    |
    | Every possible delivery channel our system supports (now or later).
    | Using constants here means the NotificationService never
    | hardcodes "fcm" as a plain string — it always uses
    | NotificationLog::CHANNEL_FCM.
    |
    | If we add a new channel (e.g. WhatsApp), we add a constant here
    | and it's immediately available everywhere in the codebase.
    */

    /** Firebase Cloud Messaging — push notifications to mobile devices */
    public const CHANNEL_FCM      = 'fcm';

    /** Email delivery (future — Mailgun, SES, Mailchimp) */
    public const CHANNEL_EMAIL    = 'email';

    /** SMS delivery (future — Twilio, MSG91) */
    public const CHANNEL_SMS      = 'sms';

    /** In-app notification (stored in DB, shown in app's notification tab) */
    public const CHANNEL_IN_APP   = 'in_app';

    /** WhatsApp Business API (future) */
    public const CHANNEL_WHATSAPP = 'whatsapp';

    /*
    |--------------------------------------------------------------------
    | Delivery Status Constants
    |--------------------------------------------------------------------
    |
    | Every possible outcome of a delivery attempt.
    */

    /**
     * The notification was successfully delivered.
     * FCM accepted the message and confirmed delivery to the device.
     */
    public const STATUS_DELIVERED = 'delivered';

    /**
     * The delivery attempt failed.
     * Check `response_message` for the error from the provider.
     * Common FCM failures: invalid device token, app uninstalled,
     * device offline too long, quota exceeded.
     */
    public const STATUS_FAILED    = 'failed';

    /**
     * The notification is queued and waiting to be sent.
     * Set when the notification is placed in the Laravel queue
     * but not yet dispatched to FCM/email/SMS.
     */
    public const STATUS_PENDING   = 'pending';

    /**
     * Delivery was attempted but the device/server hasn't
     * confirmed receipt yet. More common with email than FCM.
     */
    public const STATUS_SENT      = 'sent';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A NotificationLog BELONGS TO one Notification.
     *
     * This gives us access to the notification content and the
     * target user from any log record.
     *
     * Usage:
     *   $log->notification->title         // "Booking Confirmed ✅"
     *   $log->notification->user->name    // "Rahul Sharma"
     *   $log->notification->user->mobile  // "9876543210"
     *
     * This is useful in the retry job:
     *   $failedLogs = NotificationLog::failed()->with('notification.user')->get()
     *   foreach ($failedLogs as $log) {
     *       $token = $log->notification->user->fcm_token;
     *       // retry FCM delivery...
     *   }
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only successfully delivered logs.
     *
     * Used in analytics: "How many notifications were delivered today?"
     *
     * Usage:
     *   NotificationLog::delivered()->whereDate('created_at', today())->count()
     */
    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', self::STATUS_DELIVERED);
    }

    /**
     * Scope: only failed delivery logs.
     *
     * Used by the retry job to find failed deliveries that
     * should be re-attempted.
     *
     * Usage:
     *   NotificationLog::failed()->with('notification.user')->get()
     */
    public function scopeFailed($query)
    {
        return $query->where('delivery_status', self::STATUS_FAILED);
    }

    /**
     * Scope: only pending (queued, not yet sent) logs.
     *
     * Used to detect stuck notifications in the queue.
     *
     * Usage:
     *   NotificationLog::pending()->where('created_at', '<', now()->subMinutes(5))
     */
    public function scopePending($query)
    {
        return $query->where('delivery_status', self::STATUS_PENDING);
    }

    /**
     * Scope: filter by delivery channel.
     *
     * Usage:
     *   NotificationLog::viaChannel(NotificationLog::CHANNEL_FCM)->failed()->count()
     */
    public function scopeViaChannel($query, string $channel)
    {
        return $query->where('delivery_channel', $channel);
    }

    /**
     * Scope: filter logs for a specific notification.
     *
     * Usage:
     *   NotificationLog::forNotification($notificationId)->get()
     */
    public function scopeForNotification($query, int $notificationId)
    {
        return $query->where('notification_id', $notificationId)
                     ->orderBy('created_at', 'asc');
    }

    /*
    |--------------------------------------------------------------------
    | Static Helpers
    |--------------------------------------------------------------------
    */

    /**
     * Record a successful delivery in one clean method call.
     *
     * Called by NotificationService after FCM confirms delivery.
     *
     * Usage:
     *   NotificationLog::recordDelivery(
     *       notificationId: $notification->id,
     *       channel: NotificationLog::CHANNEL_FCM,
     *       response: 'projects/smart-parking/messages/123456'
     *   );
     *
     * @param  int         $notificationId  The parent Notification ID.
     * @param  string      $channel         Delivery channel (use CHANNEL_* constants).
     * @param  string|null $response        Raw response from the delivery provider.
     * @return static                        The created log record.
     */
    public static function recordDelivery(
        int $notificationId,
        string $channel,
        ?string $response = null
    ): static {
        return static::create([
            'notification_id'  => $notificationId,
            'delivery_channel' => $channel,
            'delivery_status'  => self::STATUS_DELIVERED,
            'response_message' => $response,
        ]);
    }

    /**
     * Record a failed delivery in one clean method call.
     *
     * Called by NotificationService when FCM or email provider
     * returns an error response.
     *
     * Usage:
     *   NotificationLog::recordFailure(
     *       notificationId: $notification->id,
     *       channel: NotificationLog::CHANNEL_FCM,
     *       response: 'UNREGISTERED: The registration token is not valid'
     *   );
     *
     * @param  int         $notificationId  The parent Notification ID.
     * @param  string      $channel         Delivery channel (use CHANNEL_* constants).
     * @param  string|null $response        Error message from the provider.
     * @return static                        The created log record.
     */
    public static function recordFailure(
        int $notificationId,
        string $channel,
        ?string $response = null
    ): static {
        return static::create([
            'notification_id'  => $notificationId,
            'delivery_channel' => $channel,
            'delivery_status'  => self::STATUS_FAILED,
            'response_message' => $response,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | Instance Helpers
    |--------------------------------------------------------------------
    */

    /**
     * Check if this delivery attempt was successful.
     */
    public function isDelivered(): bool
    {
        return $this->delivery_status === self::STATUS_DELIVERED;
    }

    /**
     * Check if this delivery attempt failed.
     */
    public function isFailed(): bool
    {
        return $this->delivery_status === self::STATUS_FAILED;
    }

    /**
     * Check if this log is still in pending/queued state.
     */
    public function isPending(): bool
    {
        return $this->delivery_status === self::STATUS_PENDING;
    }
}