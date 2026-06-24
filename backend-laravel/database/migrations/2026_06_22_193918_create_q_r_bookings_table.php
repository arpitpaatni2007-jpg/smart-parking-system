<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create QR Bookings Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * After a customer pays for a booking, they receive a QR code
 * on the "Booking Confirmed" screen in the User App. That QR
 * code is what they show at the parking gate to get in and out.
 *
 * This table stores the unique token behind each QR code.
 *
 * QR LIFECYCLE:
 *   1. Booking payment confirmed
 *      → QRBooking::generateForBooking($booking) is called
 *      → New row inserted: status = "active"
 *
 *   2. Customer arrives → Manager scans QR at entry
 *      → QRBooking::validateToken($token) is called
 *      → If valid → CheckIn created → booking status = "checked_in"
 *      → QR status stays "active" (still needed for exit scan)
 *
 *   3. Customer leaves → Manager scans QR at exit
 *      → Same token validated again
 *      → CheckOut created → booking status = "completed"
 *      → QR status updated to "used"
 *
 *   4. If booking cancelled before check-in
 *      → QR status updated to "revoked"
 *      → Customer can't use it even if they have it
 *
 *   5. If customer never showed up + qr_expiry passed
 *      → Scheduled job sets status = "expired"
 *
 * SECURITY DECISIONS:
 *   - Token is 128 characters of cryptographic randomness.
 *   - Token is stored as plain text (NOT hashed) so we can
 *     do an exact-match lookup on scan. Unlike passwords,
 *     QR tokens aren't sensitive stored data — the threat
 *     model is "someone guessing a valid token", which is
 *     prevented by length + randomness, not hashing.
 *   - qr_expiry is set to booking_end_time + 2 hours as a
 *     grace period for late parkers.
 *
 * MIGRATION ORDER:
 *   Must run AFTER `bookings` table.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `qr_bookings` table.
     */
    public function up(): void
    {
        Schema::create('qr_bookings', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // -------------------------------------------------------
            // The booking this QR belongs to.
            //
            // UNIQUE: One booking → one QR record.
            // If a QR needs to be regenerated, we UPDATE the existing
            // row rather than inserting a new one. This keeps things
            // clean and prevents a booking from ever having two
            // active QR codes simultaneously.
            // -------------------------------------------------------
            $table->foreignId('booking_id')
                  ->unique()
                  ->constrained('bookings')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // The QR Token
            //
            // This is the raw string that gets encoded into the QR
            // image. When scanned, the device decodes it back to this
            // string and sends it to the API for validation.
            //
            // 128 characters of random string = 6.4 × 10^204
            // possible values. Practically unguessable.
            //
            // Indexed for fast lookup — scanning a QR triggers an
            // immediate exact-match query on this column.
            // -------------------------------------------------------
            $table->string('qr_code', 128)->unique();

            // -------------------------------------------------------
            // Expiry Datetime
            //
            // QRs are not valid indefinitely. If qr_expiry has passed,
            // the scan API rejects the token even if status is "active".
            //
            // Set to: booking.booking_end_time + 2 hours (grace period)
            //
            // This grace period matters because:
            //   - A customer may be running 10-20 min late
            //   - booking_end_time + 2h ensures a late arrival can
            //     still use their valid QR during a reasonable window
            // -------------------------------------------------------
            $table->dateTime('qr_expiry');

            // -------------------------------------------------------
            // QR Status
            //
            // active  → generated after payment, ready to be scanned
            // used    → booking fully completed (both scan events done)
            // expired → qr_expiry passed without full use
            // revoked → booking was cancelled, QR invalidated
            // -------------------------------------------------------
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEXES
            // -------------------------------------------------------

            // qr_code already has unique() which creates an index.
            // This is the most critical index in this table —
            // every QR scan query hits this column.

            // Index on status for the scheduled expiry cleanup job:
            // "Find all active QRs where qr_expiry < now()"
            $table->index(['status', 'qr_expiry'], 'idx_qr_status_expiry');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_bookings');
    }
};