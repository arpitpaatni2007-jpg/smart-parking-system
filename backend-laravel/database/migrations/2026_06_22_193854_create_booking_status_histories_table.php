<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Booking Status Histories Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * The `bookings` table only shows the current booking_status.
 * This table is an append-only log of every status transition
 * that ever happened on every booking.
 *
 * It answers questions like:
 *   "When exactly was this booking confirmed?"
 *   "Who checked the customer in?"
 *   "Why was this booking cancelled?"
 *   "Was this booking ever stuck in pending for too long?"
 *
 * This is critical for:
 *   - Support team debugging customer complaints
 *   - Admin Panel booking timeline display
 *   - Fraud detection (suspicious check-in patterns)
 *   - Business analytics (average time from pending → confirmed)
 *
 * DESIGN PRINCIPLE: Append-only.
 * Rows are ONLY inserted here, never updated or deleted.
 * This makes it a true immutable audit log.
 *
 * MIGRATION ORDER:
 *   Must run AFTER `bookings` table (we have a FK on booking_id).
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `booking_status_histories` table.
     */
    public function up(): void
    {
        Schema::create('booking_status_histories', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // -------------------------------------------------------
            // Which booking this history entry belongs to.
            // CASCADE delete: if a booking is hard-deleted (shouldn't
            // happen due to soft deletes, but as a safety net), its
            // history is also cleaned up.
            // -------------------------------------------------------
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // Status Transition
            // -------------------------------------------------------

            // The status BEFORE this change.
            // Nullable for the very first entry (booking just created)
            // — there is no "old" status when a booking is first made.
            $table->string('old_status', 30)->nullable();

            // The status AFTER this change.
            // Never nullable — we always know what we're changing to.
            $table->string('new_status', 30);

            // -------------------------------------------------------
            // Context
            // -------------------------------------------------------

            // Free-text explanation of why the status changed.
            // Examples:
            //   "Payment verified via Razorpay webhook"
            //   "Customer requested cancellation via app"
            //   "No check-in 30 minutes after start time — auto no_show"
            //   "Admin manually resolved disputed booking"
            $table->text('remarks')->nullable();

            // Who or what triggered this status change.
            // Values:
            //   - A numeric user_id (as a string) for human actors
            //     (admin, manager, customer themselves)
            //   - "system" for automated scheduled jobs
            //   - "webhook" for payment gateway callbacks
            //   - "api" for general API-triggered changes
            //
            // We store this as a string (not an integer FK) because
            // "system" and "webhook" are not real user IDs.
            // If you need the actor's name, join with users table
            // when changed_by is numeric.
            $table->string('changed_by', 50)->nullable();

            // -------------------------------------------------------
            // Timestamps
            // -------------------------------------------------------
            // created_at is the EXACT time the status changed.
            // updated_at is less meaningful here (rows aren't updated)
            // but kept for framework consistency.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEXES
            // -------------------------------------------------------

            // The most common query: load full history for one booking.
            // "Show timeline for booking BK20260623123456"
            $table->index(['booking_id', 'created_at'], 'idx_status_history_booking');

            // Analytics query: "How many bookings reached 'completed' today?"
            $table->index('new_status', 'idx_status_history_new_status');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status_histories');
    }
};