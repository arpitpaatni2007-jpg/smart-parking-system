<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Check-Outs Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * When a customer exits the parking and the manager scans their
 * QR code at the exit gate, this record is created.
 *
 * This is the financially critical event because:
 *   - We calculate if the customer stayed longer than booked
 *   - extra_hours and extra_amount are computed and stored here
 *   - The booking is marked "completed" after this
 *   - The parking slot is freed for the next booking
 *
 * EXTRA HOURS CALCULATION (done in BookingService, stored here):
 *   If customer booked 2 hours but stayed 3.5 hours:
 *     extra_hours  = 3.5 - 2.0 = 1.5 hours
 *     extra_amount = 1.5 × ₹40 (extra_hour_price) = ₹60
 *
 * If customer left early (before booked end_time):
 *     extra_hours  = 0.00
 *     extra_amount = 0.00
 *   (No refund for early checkout)
 *
 * One booking → one checkout (enforced by unique constraint).
 *
 * MIGRATION ORDER:
 *   Must run AFTER `bookings` and `users` tables.
 *   Should run AFTER `check_ins` (logical dependency, though
 *   no FK between them).
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `check_outs` table.
     */
    public function up(): void
    {
        Schema::create('check_outs', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // -------------------------------------------------------
            // The booking this check-out belongs to.
            //
            // UNIQUE constraint: one booking = one checkout.
            // Prevents double check-outs and the double extra-charge
            // bug that would result from them.
            // -------------------------------------------------------
            $table->foreignId('booking_id')
                  ->unique()                // ONE checkout per booking
                  ->constrained('bookings')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // The manager/owner who performed the exit QR scan.
            // Nullable for future ANPR/automated checkout support.
            // -------------------------------------------------------
            $table->foreignId('checked_out_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // -------------------------------------------------------
            // The precise datetime the QR was scanned at exit.
            //
            // This is the key value used to compute overtime:
            //   extra_hours = checkout_time - checkin_time - booked_hours
            // -------------------------------------------------------
            $table->dateTime('checkout_time');

            // -------------------------------------------------------
            // Overtime Tracking
            // -------------------------------------------------------

            // Number of hours the customer exceeded their booking.
            // 0.00 if they left on time or early.
            // Stored as decimal to handle partial hours:
            //   1.50 = 1 hour 30 minutes overtime.
            $table->decimal('extra_hours', 5, 2)->default(0.00);

            // The additional charge for overtime.
            // Calculated as: extra_hours × pricing_rule.extra_hour_price
            // 0.00 if no overtime.
            // This is the extra amount the customer owes ABOVE the
            // pre-paid booking amount.
            $table->decimal('extra_amount', 10, 2)->default(0.00);

            // -------------------------------------------------------
            // Optional notes from the manager at checkout.
            // Examples:
            //   "Customer paid ₹80 extra in cash"
            //   "Waived extra charge — first-time customer"
            //   "Vehicle had a flat tyre — delayed exit"
            // -------------------------------------------------------
            $table->text('notes')->nullable();

            // Managed automatically by Laravel.
            $table->timestamps();

            // -------------------------------------------------------
            // INDEX on checkout_time:
            // Used in analytics and reports:
            //   "How many check-outs happened today?"
            //   "Average parking duration this week?"
            // -------------------------------------------------------
            $table->index('checkout_time', 'idx_checkout_time');

            // INDEX on extra_amount > 0 queries (reports on overtime revenue).
            $table->index('extra_amount', 'idx_checkout_extra_amount');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_outs');
    }
};