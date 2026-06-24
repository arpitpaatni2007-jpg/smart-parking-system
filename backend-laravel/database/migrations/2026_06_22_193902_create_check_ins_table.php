<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Check-Ins Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * When a customer arrives at the parking and the manager scans
 * their QR code at the entry gate, that event is recorded here.
 *
 * This is separate from the `bookings` table because:
 *   - A booking is a reservation (made in advance).
 *   - A check-in is a physical event (happens on arrival).
 *   - We need to store WHO did the scan and precise WHEN.
 *   - The UNIQUE constraint prevents accidental double check-ins.
 *
 * One booking → one check-in (enforced by unique constraint).
 *
 * HOW IT CONNECTS:
 *   `bookings` (one) ←→ (one) `check_ins`
 *   `users` (manager/owner) → `check_ins.checked_in_by`
 *
 * MIGRATION ORDER:
 *   Must run AFTER `bookings` and `users` tables.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `check_ins` table.
     */
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // -------------------------------------------------------
            // The booking this check-in belongs to.
            //
            // UNIQUE constraint enforces one-to-one relationship:
            // A booking can only ever be checked in once.
            // This is a critical business rule — prevents a customer
            // from entering twice or a manager from double-scanning.
            //
            // cascadeOnDelete: if a booking is force-deleted
            // (edge case — normally soft deleted), its check-in
            // record is also removed.
            // -------------------------------------------------------
            $table->foreignId('booking_id')
                  ->unique()                // ONE check-in per booking
                  ->constrained('bookings')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // Who performed the check-in scan.
            // This is the user_id of the Parking Manager or Owner
            // who scanned the QR code in the Owner App.
            //
            // Nullable because:
            //   a) Future ANPR/IoT auto check-ins won't have a human.
            //   b) If the user account is deleted, this shouldn't
            //      break the check-in record (nullOnDelete).
            // -------------------------------------------------------
            $table->foreignId('checked_in_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // -------------------------------------------------------
            // The precise datetime the QR was scanned at entry.
            //
            // This is NOT the booking_start_time — customers may
            // arrive early or late. This is the ACTUAL arrival time
            // used for:
            //   - Calculating total time spent (with checkout_time)
            //   - Displaying "Checked in at 10:45 AM" in the app
            //   - Overtime calculation at check-out
            // -------------------------------------------------------
            $table->dateTime('checkin_time');

            // -------------------------------------------------------
            // Optional notes about this check-in.
            // Examples:
            //   "Vehicle number manually verified — camera issue"
            //   "Customer arrived early, allowed in"
            //   "Manually checked in — QR scanner offline"
            // -------------------------------------------------------
            $table->text('notes')->nullable();

            // Managed automatically by Laravel.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEX on checkin_time:
            // Used in analytics: "How many check-ins happened today?"
            // Also used for detecting overnight stays.
            // -------------------------------------------------------
            $table->index('checkin_time', 'idx_checkin_time');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};