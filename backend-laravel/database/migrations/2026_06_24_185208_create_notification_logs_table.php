<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Notification Logs Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * Every time the system ATTEMPTS to deliver a notification
 * (via FCM push, email, or SMS), one row is inserted here.
 * This table is the delivery receipt log — it tells us whether
 * each delivery attempt worked and what happened if it didn't.
 *
 * RELATIONSHIP TO NOTIFICATIONS TABLE:
 *   notifications         = the notification itself (what + who)
 *   notification_logs     = how each delivery attempt went (result)
 *
 *   One notification can have MANY log entries because:
 *     a) Delivery may be retried after a failure
 *     b) Same notification may go via multiple channels (FCM + email)
 *
 * EXAMPLE DATA (for a booking_confirmed notification):
 *
 *   notifications (1 row):
 *   ┌────┬────────────┬──────────────────────────┬───────────────────┐
 *   │ id │ user_id    │ title                    │ notification_type  │
 *   ├────┼────────────┼──────────────────────────┼───────────────────┤
 *   │ 1  │ 42         │ "Booking Confirmed ✅"    │ booking_confirmed  │
 *   └────┴────────────┴──────────────────────────┴───────────────────┘
 *
 *   notification_logs (2 rows — FCM failed then retry succeeded):
 *   ┌────┬─────────────────┬─────────┬───────────┬──────────────────────────────────┐
 *   │ id │ notification_id │ channel │ status    │ response_message                  │
 *   ├────┼─────────────────┼─────────┼───────────┼──────────────────────────────────┤
 *   │ 1  │ 1               │ fcm     │ failed    │ "UNREGISTERED: Invalid FCM token" │
 *   │ 2  │ 1               │ fcm     │ delivered │ "projects/.../messages/abc123"    │
 *   └────┴─────────────────┴─────────┴───────────┴──────────────────────────────────┘
 *
 * HOW THIS TABLE CONNECTS TO THE SYSTEM:
 *   → NotificationService creates rows after each FCM/email send
 *   → Admin Panel reads this for delivery status display
 *   → Retry scheduler reads failed rows and re-attempts delivery
 *   → Analytics queries this for delivery success rate reports
 *
 * MIGRATION ORDER:
 *   Must run AFTER `notifications` table (FK on notification_id).
 *
 * GROWTH RATE WARNING:
 *   This table grows faster than `notifications`. At 3,000
 *   notifications/day with average 1.2 log rows each, that's
 *   ~1.3 million rows/year. Archive rows older than 90 days
 *   to keep query performance fast.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `notification_logs` table.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            // -------------------------------------------------------
            // Primary Key
            // -------------------------------------------------------
            $table->id();

            // -------------------------------------------------------
            // Parent Notification Reference
            //
            // Links back to the notification this log is about.
            //
            // cascadeOnDelete: if a notification is deleted, all
            // its delivery logs are deleted too. This is correct
            // because logs without a parent notification are
            // meaningless orphan records.
            //
            // NOTE: We do not use unique() here because one
            // notification CAN have multiple log rows (retries,
            // multi-channel delivery).
            // -------------------------------------------------------
            $table->foreignId('notification_id')
                  ->constrained('notifications')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // Delivery Channel
            //
            // Which channel was used to deliver this notification.
            // Values: fcm | email | sms | in_app | whatsapp
            //
            // Stored as a varchar (not an enum) so we can add new
            // channels in the future without running a migration
            // to alter the enum definition.
            //
            // Use the CHANNEL_* constants in NotificationLog model.
            // -------------------------------------------------------
            $table->string('delivery_channel', 30);

            // -------------------------------------------------------
            // Delivery Status
            //
            // The outcome of this specific delivery attempt.
            // Values: pending | sent | delivered | failed
            //
            // PENDING   → row created but FCM not yet called
            //             (async/queued notifications start here)
            // SENT      → FCM accepted the request but no receipt yet
            //             (mostly relevant for email which has delays)
            // DELIVERED → confirmed delivery to the device/inbox
            // FAILED    → provider returned an error
            //
            // Use STATUS_* constants in NotificationLog model.
            $table->string('delivery_status', 20)->default('pending');

            // -------------------------------------------------------
            // Provider Response
            //
            // The raw response message from the delivery provider.
            // Stored for debugging and support purposes.
            //
            // On SUCCESS (FCM):
            //   "projects/smart-parking-app/messages/0:1234567890%abc"
            //
            // On FAILURE (FCM):
            //   "UNREGISTERED: The registration token is not valid."
            //   "INVALID_ARGUMENT: The device token is malformed."
            //   "QUOTA_EXCEEDED: Messaging quota exceeded for this project."
            //
            // On SUCCESS (email):
            //   "Message-ID: <abc123@mailgun.org>"
            //
            // Nullable because the first row may be created (status=pending)
            // before the provider is actually called.
            // Text type because some provider responses can be long JSON.
            // -------------------------------------------------------
            $table->text('response_message')->nullable();

            // Managed automatically by Laravel.
            // `created_at` is especially useful here — it tells us
            // exactly when each delivery attempt happened, which
            // is important for the retry job's time window logic.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEXES
            // -------------------------------------------------------

            // QUERY 1 — Load all logs for one notification:
            //   "Show delivery history for notification #101"
            //   Used in Admin Panel notification detail view.
            //   WHERE notification_id = ? ORDER BY created_at ASC
            $table->index(
                ['notification_id', 'created_at'],
                'idx_notif_log_notification'
            );

            // QUERY 2 — Retry job for failed deliveries:
            //   "Find all failed FCM deliveries from the last hour"
            //   WHERE delivery_channel = 'fcm'
            //   AND delivery_status = 'failed'
            //   AND created_at >= ?
            $table->index(
                ['delivery_channel', 'delivery_status'],
                'idx_notif_log_channel_status'
            );

            // QUERY 3 — Analytics / admin dashboard:
            //   "What is the FCM delivery success rate today?"
            //   WHERE delivery_status = 'failed'
            //   AND created_at >= DATE(NOW())
            $table->index(
                ['delivery_status', 'created_at'],
                'idx_notif_log_status_date'
            );
        });
    }

    /**
     * Reverse the migration.
     *
     * This table has no tables referencing it (it's a leaf table),
     * so it can always be dropped safely without FK conflicts.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};