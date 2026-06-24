<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Notifications Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * This table is the notification inbox for every user in the
 * Smart Parking System — customers, parking owners, and admins.
 *
 * Every automated system event that triggers a notification
 * (booking confirmed, payment received, parking approved...)
 * creates one row per target user in this table. This row is
 * the permanent record of that notification. The actual delivery
 * attempt (via FCM push, email, SMS) is tracked separately in
 * `notification_logs`.
 *
 * WHY NOT USE LARAVEL'S BUILT-IN NOTIFICATION SYSTEM?
 * Laravel's default database notification driver also creates a
 * `notifications` table, but it uses a generic JSON `data` column
 * and a `type` column pointing to a PHP class. This is flexible
 * for a generic package but not ideal for our use case because:
 *   - We want structured, queryable columns (title, message, type)
 *   - We want our own read/unread logic with proper indexing
 *   - We want to link logs from multiple delivery channels
 *   - We want full control over the schema for future features
 *
 * RELATIONSHIPS:
 *   users (one) ←→ (many) notifications
 *   notifications (one) ←→ (many) notification_logs
 *
 * HOW THIS TABLE GROWS:
 * This will be one of the fastest-growing tables in the system.
 * Every booking event sends 2-3 notifications (user + owner + admin
 * for some events). At 1,000 bookings/day, that's ~3,000 rows/day
 * = ~1 million rows per year. The indexes below are carefully
 * chosen to keep queries fast at this scale.
 *
 * MIGRATION ORDER:
 *   Must run AFTER the `users` table.
 *   Must run BEFORE the `notification_logs` table.
 *
 * FUTURE SCALABILITY:
 *   - Archive notifications older than 6 months to a
 *     `notifications_archive` table using a scheduled job.
 *   - Consider partitioning by `sent_at` month once the table
 *     exceeds 10 million rows.
 *   - Add `reference_id` and `reference_type` columns for
 *     polymorphic deep-link support in the mobile app.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `notifications` table.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            // -------------------------------------------------------
            // Primary Key
            // -------------------------------------------------------
            $table->id();

            // -------------------------------------------------------
            // The user who receives this notification.
            //
            // restrictOnDelete: We do NOT want to delete notification
            // history if a user account is deleted. Instead, the user
            // deletion process should handle notifications separately
            // (e.g. soft-delete the user, archive or anonymize their
            // notifications). Using restrict forces us to think about
            // this rather than blindly cascading deletes.
            // -------------------------------------------------------
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // -------------------------------------------------------
            // Notification Content
            // -------------------------------------------------------

            // Short headline of the notification.
            // Shown in bold as the first line in the notification list.
            // Example: "Booking Confirmed ✅"
            // Example: "Your parking spot is ready"
            // Max 200 chars — enough for any push notification title.
            $table->string('title', 200);

            // The full notification body text.
            // Shown below the title in the notification list.
            // Example: "Your booking BK20260623123456 for Connaught
            //           Place Parking on 23 Jun at 10:00 AM is confirmed."
            // Using text (not varchar) to accommodate longer messages.
            $table->text('message');

            // -------------------------------------------------------
            // Notification Type
            // -------------------------------------------------------
            // A slug identifying which event triggered this notification.
            // Values match the TYPE_* constants in the Notification model.
            // Examples: booking_confirmed, payment_success, parking_approved
            //
            // WHY STORE THE TYPE?
            //   - Mobile app uses it to route notification taps to the
            //     correct screen (booking_confirmed → open booking detail).
            //   - Admin Panel can filter notifications by type.
            //   - Analytics: which notification types have the highest
            //     open rates?
            $table->string('notification_type', 60);

            // -------------------------------------------------------
            // Read Status
            // -------------------------------------------------------
            // Has the user seen/opened this notification?
            //
            // false (0) = unread  → shown with a blue dot / bold text
            // true  (1) = read    → shown without emphasis
            //
            // This field is updated when:
            //   a) User taps the notification in the app
            //   b) User taps "Mark all as read"
            //
            // DEFAULT: false — every new notification starts as unread.
            $table->boolean('is_read')->default(false);

            // -------------------------------------------------------
            // Sent Timestamp
            // -------------------------------------------------------
            // When this notification was sent to the delivery service
            // (FCM, email, etc.).
            //
            // WHY SEPARATE FROM `created_at`?
            //   - created_at = when the row was inserted in the DB
            //   - sent_at    = when the notification was dispatched
            //                  to the delivery channel
            //
            // For immediate notifications, these are practically the same.
            // For scheduled/queued notifications (future feature), they
            // may differ — the row is created now but delivery is
            // queued for a later time (e.g. "remind me 30 min before").
            //
            // Nullable because the row might be created before sending
            // (queued notifications sit in DB before dispatch).
            $table->dateTime('sent_at')->nullable();

            // Managed automatically by Laravel.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEXES
            // -------------------------------------------------------
            //
            // STRATEGY: Optimize for the most common queries first.
            //
            // QUERY 1 — Notification inbox (most common):
            //   "Load unread notifications for user X, newest first"
            //   WHERE user_id = ? AND is_read = 0 ORDER BY sent_at DESC
            //   → Composite index covers user_id + is_read filter.
            $table->index(
                ['user_id', 'is_read'],
                'idx_notification_user_read'
            );

            // QUERY 2 — Full notification list with type filter:
            //   "Load booking notifications for user X"
            //   WHERE user_id = ? AND notification_type = ?
            //   → Composite index covers both filters.
            $table->index(
                ['user_id', 'notification_type'],
                'idx_notification_user_type'
            );

            // QUERY 3 — Admin analytics / bulk operations:
            //   "Find all unread notifications from today"
            //   WHERE sent_at >= ? AND is_read = 0
            $table->index(
                ['sent_at', 'is_read'],
                'idx_notification_sent_read'
            );

            // QUERY 4 — Type-based analytics:
            //   "How many payment_failed notifications were sent this month?"
            $table->index('notification_type', 'idx_notification_type');
        });
    }

    /**
     * Reverse the migration.
     *
     * NOTE: `notification_logs` table must be dropped BEFORE this
     * table because it has a FK referencing `notifications.id`.
     * Laravel handles this automatically if migration filenames
     * are ordered correctly (notification_logs has a later timestamp).
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};