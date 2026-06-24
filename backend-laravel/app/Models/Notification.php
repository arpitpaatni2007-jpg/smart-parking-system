<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * Notification Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Our Smart Parking System needs to communicate with users and
 * owners at key moments — booking confirmed, payment received,
 * check-in reminder, check-out done, parking approved, and more.
 *
 * This model represents ONE notification that was sent (or needs
 * to be sent) to ONE user. Think of it as the notification "record"
 * — the what, who, and when of a notification.
 *
 * The actual DELIVERY (sending via FCM, email, SMS) is tracked
 * separately in the `notification_logs` table (NotificationLog
 * model). This split is intentional and important:
 *
 *   Notification      = what was communicated and to whom
 *   NotificationLog   = how it was delivered and whether it worked
 *
 * WHY SEPARATE THESE TWO CONCERNS?
 *   A single notification might be delivered via MULTIPLE channels.
 *   Example: "Booking Confirmed" might send:
 *     → Push notification via FCM (channel 1)
 *     → SMS via Twilio         (channel 2, future)
 *     → Email via Mailgun      (channel 3, future)
 *
 *   Each delivery attempt gets its own NotificationLog row with
 *   its own delivery_status. But there's only ONE Notification
 *   row (the source record). This design avoids duplicating the
 *   title/message for every delivery channel.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   USER APP — NOTIFICATION SCREEN:
 *     The "Notifications" section in the User App (visible in the
 *     Profile screen) shows a list of notifications for the logged-in
 *     user. This data comes from this table filtered by user_id,
 *     ordered by sent_at descending.
 *
 *   OWNER APP — NOTIFICATIONS:
 *     Parking owners receive notifications for new bookings, check-ins,
 *     payment receipts, and parking approval/rejection. Same table,
 *     filtered to their user_id.
 *
 *   ADMIN PANEL — NOTIFICATION MANAGEMENT:
 *     Admin can send bulk notifications (e.g. "Platform maintenance
 *     tonight at 2 AM"). Each target user gets one row here, and
 *     the FCM delivery attempt gets one row in notification_logs.
 *
 *   BADGE COUNT:
 *     The mobile app shows an unread notification count badge.
 *     This is calculated as:
 *       SELECT COUNT(*) FROM notifications
 *       WHERE user_id = ? AND is_read = 0
 *
 *   MARK AS READ:
 *     When a user opens a notification in the app, the API updates
 *     is_read = true on that row.
 *
 * NOTIFICATION TYPE VALUES (see constants below):
 *   booking_confirmed, booking_cancelled, booking_reminder,
 *   payment_success, payment_failed, refund_processed,
 *   checkin_success, checkout_success,
 *   parking_approved, parking_rejected, settlement_processed,
 *   new_booking_owner, general, promotional
 *
 * FUTURE SCALABILITY:
 *   - Add `reference_id` (bigint nullable) and `reference_type`
 *     (varchar nullable) for polymorphic linking — so a booking
 *     notification links directly to the booking record, and
 *     tapping it in the app opens the right screen.
 *   - Add `action_url` (varchar nullable) for deep link URLs
 *     that open specific app screens on notification tap.
 *   - Add `image_url` for rich push notifications with images.
 *   - Add `scheduled_at` datetime for scheduled notifications
 *     (future: send at optimal open time using ML).
 *   - Add `priority` (high | normal | low) for FCM delivery
 *     priority settings.
 *   - For platforms at scale, this table will grow very fast.
 *     Partition by `sent_at` month, and archive old rows to cold
 *     storage after 6 months.
 *
 * @property int                         $id
 * @property int                         $user_id
 * @property string                      $title
 * @property string                      $message
 * @property string                      $notification_type
 * @property bool                        $is_read
 * @property \Carbon\Carbon|null         $sent_at
 * @property \Carbon\Carbon              $created_at
 * @property \Carbon\Carbon              $updated_at
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     *
     * IMPORTANT: We specify this explicitly because "notifications"
     * is also the table used by Laravel's built-in database
     * notification driver. Since we're building our OWN custom
     * notification system (not using Laravel's Notifiable trait
     * approach), we name the table explicitly to be crystal clear
     * about which table this model manages.
     */
    protected $table = 'notifications';

    /**
     * Fields allowed for mass assignment.
     *
     * NOTE: `is_read` is intentionally included here because the
     * notification service sets it to false on creation, and the
     * "mark as read" API sets it to true. Both are internal
     * operations — not raw user input for creation.
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'notification_type',
        'is_read',
        'sent_at',
    ];

    /**
     * Type-cast database columns to proper PHP types.
     *
     * WHY CAST `is_read` AS BOOLEAN?
     * MySQL stores boolean values as TINYINT(1) (0 or 1).
     * Without this cast, $notification->is_read would return
     * "0" or "1" as strings, which causes bugs in conditions:
     *   if ($notification->is_read) // "0" is truthy in PHP!
     * With the cast, we get proper true/false values.
     *
     * WHY CAST `sent_at` AS DATETIME?
     * We get a Carbon instance, enabling:
     *   $notification->sent_at->diffForHumans()  // "2 hours ago"
     *   $notification->sent_at->format('d M Y')  // "23 Jun 2026"
     */
    protected $casts = [
        'user_id'   => 'integer',
        'is_read'   => 'boolean',
        'sent_at'   => 'datetime',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    /**
     * Default attribute values applied when creating a new Notification.
     *
     * Every new notification starts as unread. We never want to
     * accidentally create a notification that's already marked read.
     */
    protected $attributes = [
        'is_read' => false,
    ];

    /*
    |--------------------------------------------------------------------
    | Notification Type Constants
    |--------------------------------------------------------------------
    |
    | These constants define every possible value for `notification_type`.
    | They serve as the single source of truth — used by:
    |   - This model (for type-based scopes)
    |   - NotificationService (when creating notifications)
    |   - NotificationTemplate model (matching TYPE_* constants)
    |   - Mobile app (for routing taps to correct screens)
    |
    | NAMING CONVENTION: TYPE_VERB_NOUN or TYPE_NOUN_ACTION
    | Consistent naming makes searching the codebase easier.
    */

    // ── Authentication ───────────────────────────────────────────────
    /** OTP was sent for login verification */
    public const TYPE_OTP_LOGIN              = 'otp_login';

    /** New user registered successfully */
    public const TYPE_WELCOME                = 'welcome';

    // ── Booking Lifecycle ────────────────────────────────────────────
    /** Booking confirmed after payment */
    public const TYPE_BOOKING_CONFIRMED      = 'booking_confirmed';

    /** Booking was cancelled (by user or admin) */
    public const TYPE_BOOKING_CANCELLED      = 'booking_cancelled';

    /** Pre-arrival reminder sent before booking_start_time */
    public const TYPE_BOOKING_REMINDER       = 'booking_reminder';

    // ── Check-In / Check-Out ─────────────────────────────────────────
    /** Customer successfully checked in at the parking */
    public const TYPE_CHECKIN_SUCCESS        = 'checkin_success';

    /** Customer successfully checked out from the parking */
    public const TYPE_CHECKOUT_SUCCESS       = 'checkout_success';

    // ── Payment ──────────────────────────────────────────────────────
    /** Payment received successfully */
    public const TYPE_PAYMENT_SUCCESS        = 'payment_success';

    /** Payment attempt failed */
    public const TYPE_PAYMENT_FAILED         = 'payment_failed';

    /** Refund has been processed */
    public const TYPE_REFUND_PROCESSED       = 'refund_processed';

    // ── Owner Notifications ──────────────────────────────────────────
    /** A new booking was made at the owner's parking */
    public const TYPE_NEW_BOOKING_OWNER      = 'new_booking_owner';

    /** Owner's parking location was approved by admin */
    public const TYPE_PARKING_APPROVED       = 'parking_approved';

    /** Owner's parking location was rejected by admin */
    public const TYPE_PARKING_REJECTED       = 'parking_rejected';

    /** Settlement payout has been processed for the owner */
    public const TYPE_SETTLEMENT_PROCESSED   = 'settlement_processed';

    // ── Admin / System ───────────────────────────────────────────────
    /** General system-wide announcement */
    public const TYPE_GENERAL                = 'general';

    /** Promotional offer or campaign */
    public const TYPE_PROMOTIONAL            = 'promotional';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * Each Notification BELONGS TO one User.
     *
     * This is the recipient of the notification.
     * Could be a customer, parking owner, or admin — any user_id.
     *
     * Usage:
     *   $notification->user->name       // "Rahul Sharma"
     *   $notification->user->fcm_token  // for FCM delivery
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A Notification HAS MANY NotificationLogs.
     *
     * Each delivery attempt (FCM push, email, SMS) gets its own
     * log entry. A single notification can have multiple logs if:
     *   a) It's delivered via multiple channels (push + email)
     *   b) A delivery failed and was retried
     *
     * Usage:
     *   $notification->logs                     // all delivery logs
     *   $notification->logs()->where('delivery_status', 'delivered')->count()
     *   $notification->logs()->latest()->first() // most recent attempt
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'notification_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    |
    | Scopes make common queries reusable and readable throughout
    | the codebase. The notification API will rely heavily on these.
    */

    /**
     * Scope: only unread notifications.
     *
     * This is the most frequently used scope — it powers:
     *   - The notification badge count on the mobile app
     *   - The "Unread" tab in the notification screen
     *
     * Usage:
     *   Notification::forUser($userId)->unread()->count()
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: only read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: filter notifications for a specific user.
     * Used in both the User App and Owner App notification screens.
     *
     * Usage:
     *   Notification::forUser(auth()->id())->latest('sent_at')->paginate(20)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by notification type.
     *
     * Usage:
     *   Notification::forUser($id)->ofType(Notification::TYPE_BOOKING_CONFIRMED)->get()
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope: notifications sent after a specific datetime.
     * Useful for loading "new notifications since last app open".
     *
     * Usage:
     *   Notification::forUser($id)->sentAfter($lastOpenedAt)->get()
     */
    public function scopeSentAfter($query, $datetime)
    {
        return $query->where('sent_at', '>=', $datetime);
    }

    /**
     * Scope: order by most recently sent first.
     * Standard ordering for notification list screens.
     *
     * Usage:
     *   Notification::forUser($id)->latest('sent_at')->paginate(20)
     *   (Can also use ->latestFirst() for readability)
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('sent_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Mark this notification as read.
     *
     * Called when a user taps/opens a notification in the app.
     * Returns true on success, false on failure.
     *
     * Usage:
     *   $notification->markAsRead();
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true; // Already read — no DB write needed
        }

        return $this->update(['is_read' => true]);
    }

    /**
     * Mark this notification as unread.
     *
     * Less common but useful for admin tools that reset
     * notification state for testing purposes.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    /**
     * Check whether this notification has been read.
     *
     * Simple boolean helper — avoids checking the raw field
     * directly in views or conditions.
     */
    public function isRead(): bool
    {
        return $this->is_read === true;
    }

    /**
     * Check whether this notification is still unread.
     */
    public function isUnread(): bool
    {
        return $this->is_read === false;
    }

    /**
     * Get the unread notification count for a specific user.
     *
     * This is a static convenience method used to populate the
     * badge count in the mobile app. Called by the API endpoint
     * that returns the user's notification summary.
     *
     * Usage:
     *   $unreadCount = Notification::unreadCountFor(auth()->id());
     *   // Returns: 5
     */
    public static function unreadCountFor(int $userId): int
    {
        return static::where('user_id', $userId)
                     ->where('is_read', false)
                     ->count();
    }

    /**
     * Mark ALL unread notifications as read for a given user.
     *
     * Called when a user taps "Mark all as read" in the app,
     * or when they open the notifications screen (depending
     * on UX decision).
     *
     * Usage:
     *   Notification::markAllReadFor(auth()->id());
     */
    public static function markAllReadFor(int $userId): int
    {
        // Returns the number of rows updated.
        return static::where('user_id', $userId)
                     ->where('is_read', false)
                     ->update(['is_read' => true]);
    }
};