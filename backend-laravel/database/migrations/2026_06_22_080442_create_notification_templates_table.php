<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_notification_templates_table
 *
 * Stores admin-configurable message templates for emails, SMS,
 * and push notifications. Decouples message content from code.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {

            $table->id();

            /**
             * Internal admin label for identifying this template.
             * e.g. "Booking Confirmed - Email", "OTP SMS"
             * Not shown to end users.
             */
            $table->string('title')
                  ->comment('Internal name to identify this template in the admin panel');

            /**
             * Notification delivery channel.
             * ENUM enforces only valid values — no typos like "Email" vs "email".
             */
            $table->enum('type', ['email', 'sms', 'push'])
                  ->comment('Delivery channel: email | sms | push notification');

            /**
             * Email subject line.
             * Nullable because SMS and push notifications don't have a subject.
             * For push notifications, the `title` column in the model serves as heading.
             */
            $table->string('subject')->nullable()
                  ->comment('Email subject line; null for sms and push types');

            /**
             * The notification body / message content.
             * TEXT type supports up to 65,535 characters — enough for any email.
             * Can contain {{placeholder}} tokens replaced at send time.
             *
             * FUTURE: Switch to LONGTEXT if you need HTML email templates with inline styles.
             */
            $table->text('message')
                  ->comment('Message body; may contain {{variable}} placeholder tokens');

            /**
             * Controls whether this template is currently usable.
             * Inactive templates are ignored by the notification service.
             */
            $table->enum('status', ['active', 'inactive'])->default('active')
                  ->comment('Whether this template is available for sending');

            // Soft delete support — deactivate templates without losing history
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};