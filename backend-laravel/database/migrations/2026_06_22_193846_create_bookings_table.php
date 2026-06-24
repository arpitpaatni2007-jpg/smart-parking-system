<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Bookings Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * This is the central transaction table of the entire platform.
 * Every parking reservation creates one row here. It connects:
 *   - Who booked   (user_id)
 *   - Where        (parking_id + parking_slot_id)
 *   - What vehicle (vehicle_id)
 *   - When         (booking_start_time → booking_end_time)
 *   - How much     (amount)
 *   - What stage   (booking_status + payment_status)
 *
 * All downstream records — payments, QR codes, check-ins,
 * check-outs, commissions, reviews — reference this table's ID.
 *
 * BOOKING STATUS FLOW:
 *   pending → confirmed → checked_in → completed
 *                      ↓
 *                  cancelled / no_show
 *
 * PAYMENT STATUS FLOW:
 *   unpaid → paid → refunded
 *         → failed
 *
 * MIGRATION RUN ORDER:
 *   This table references: users, parkings, parking_slots, vehicles.
 *   All four must be migrated BEFORE this file runs.
 *
 * INDEXING STRATEGY:
 *   Bookings will grow to be the largest table in the system.
 *   We add carefully chosen indexes so common queries stay fast
 *   even with millions of rows.
 *
 * SOFT DELETES:
 *   We use soft deletes (deleted_at) instead of hard deletes.
 *   A booking is a financial record — we must never permanently
 *   lose it. Even if cancelled, the row stays with deleted_at set.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `bookings` table.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            // -------------------------------------------------------
            // Primary Key
            // -------------------------------------------------------
            $table->id();

            // -------------------------------------------------------
            // Unique Booking Reference
            // Format: BK + YYYYMMDD + 6 random digits = BK20260623123456
            // Generated in the model's boot() method — never user-set.
            // This is what users see on receipts, QR codes, support
            // tickets, and the "My Bookings" screen.
            // -------------------------------------------------------
            $table->string('booking_number', 25)->unique();

            // -------------------------------------------------------
            // Foreign Keys — core booking participants
            // -------------------------------------------------------

            // The customer who made the booking.
            // Restrict delete: cannot delete a user who has bookings.
            // This protects financial data integrity.
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // The parking location booked.
            $table->foreignId('parking_id')
                  ->constrained('parkings')
                  ->restrictOnDelete();

            // The specific slot within the parking.
            $table->foreignId('parking_slot_id')
                  ->constrained('parking_slots')
                  ->restrictOnDelete();

            // The customer's vehicle for this booking.
            $table->unsignedBigInteger('vehicle_id')->nullable();

            // -------------------------------------------------------
            // Booking Time Window
            // -------------------------------------------------------

            // When the customer intends to arrive.
            // Used for: slot availability checks, reminders, check-in validation.
            $table->dateTime('booking_start_time');

            // When the customer intends to leave.
            // Used to: calculate duration_hours and base amount.
            $table->dateTime('booking_end_time');

            // Actual time the QR code was scanned at entry.
            // Null until the owner/manager scans at check-in.
            // May differ from booking_start_time (early/late arrival).
            $table->dateTime('actual_checkin_time')->nullable();

            // Actual time the QR code was scanned at exit.
            // Null until the owner/manager scans at check-out.
            // Used to calculate extra_hours and extra_amount.
            $table->dateTime('actual_checkout_time')->nullable();

            // -------------------------------------------------------
            // Duration and Amount
            // -------------------------------------------------------

            // Number of hours booked (end_time - start_time).
            // Stored as a computed value (not just calculated on the fly)
            // so reports don't need to recalculate it every query.
            // E.g. 2.50 = 2 hours 30 minutes.
            $table->decimal('duration_hours', 5, 2)->default(0);

            // The base booking amount calculated at time of booking:
            //   duration_hours × price_per_hour (for vehicle type)
            // Does NOT include extra_amount from overtime.
            // Extra charges are stored on the CheckOut record.
            $table->decimal('amount', 10, 2)->default(0);

            // -------------------------------------------------------
            // Status Tracking
            // -------------------------------------------------------

            // Current stage of the booking lifecycle.
            // Values: pending | confirmed | checked_in | completed |
            //         cancelled | no_show
            // Every change is also recorded in booking_status_histories.
            $table->string('booking_status', 30)->default('pending');

            // Current payment state for this booking.
            // Values: unpaid | paid | failed | refunded
            // "unpaid" by default — changes to "paid" after Razorpay
            // webhook confirms successful payment.
            $table->string('payment_status', 30)->default('unpaid');

            // -------------------------------------------------------
            // Optional Notes
            // -------------------------------------------------------

            // Free-text notes about this booking.
            // Used for: admin remarks, special instructions from user,
            // or cancellation reasons.
            $table->text('notes')->nullable();

            // -------------------------------------------------------
            // Timestamps + Soft Deletes
            // -------------------------------------------------------

            // Standard created_at / updated_at managed by Laravel.
            $table->timestamps();

            // IMPORTANT: Soft delete — never hard-delete bookings.
            // Cancelled bookings still exist with deleted_at set.
            // This preserves financial history, audit trails, and
            // prevents orphaned payments/commission records.
            $table->softDeletes();

            // -------------------------------------------------------
            // INDEXES
            // -------------------------------------------------------
            // We add these because bookings is queried constantly with
            // many different filter combinations. Poor indexing here
            // directly causes slow API responses in the app.

            // booking_number is already unique (acts as an index).

            // User's booking list ("My Bookings" screen in app).
            // Most common query: WHERE user_id = ? ORDER BY created_at DESC
            $table->index(['user_id', 'booking_status'], 'idx_booking_user_status');

            // Owner's dashboard ("Today's Bookings" filter).
            // Query: WHERE parking_id = ? AND booking_start_time >= ?
            $table->index(['parking_id', 'booking_status'], 'idx_booking_parking_status');

            // Slot availability check (before creating a new booking).
            // "Is this slot already booked for this time window?"
            // Query: WHERE parking_slot_id = ? AND booking_start_time <= ?
            //        AND booking_end_time >= ?
            $table->index(['parking_slot_id', 'booking_start_time', 'booking_end_time'],
                          'idx_slot_time_window');

            // Payment status filter (for admin reports and refund processing).
            $table->index('payment_status', 'idx_booking_payment_status');

            // Date range queries for reports.
            // "Show all bookings between DATE_A and DATE_B"
            $table->index('booking_start_time', 'idx_booking_start_time');
        });
    }

    /**
     * Reverse the migration.
     *
     * NOTE: Tables that reference bookings (booking_status_histories,
     * check_ins, check_outs, qr_bookings, payments, commissions)
     * must be dropped BEFORE this table when rolling back.
     * Laravel handles this automatically if rollback runs in reverse
     * order of migration filenames.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};