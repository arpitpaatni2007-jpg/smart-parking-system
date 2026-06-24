<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_payments_table
 *
 * Stores one payment record per booking.
 * This is the financial settlement record for each parking session.
 *
 * ──────────────────────────────────────────────────────────────
 * WHY user_id IS DENORMALIZED HERE:
 * ──────────────────────────────────────────────────────────────
 * We could get user_id via: Payment → Booking → user_id
 * But storing it directly on payments means:
 *   - "All payments by user X" → single-table query, no JOIN
 *   - Payment history API is faster
 *   - Financial reporting doesn't need to JOIN bookings
 *
 * Tradeoff: slight redundancy. Worth it for query performance.
 * Always set payments.user_id = bookings.user_id at creation time.
 *
 * ──────────────────────────────────────────────────────────────
 * DECIMAL vs FLOAT for money:
 * ──────────────────────────────────────────────────────────────
 * NEVER use FLOAT for monetary values.
 * FLOAT stores binary approximations: 120.30 might become 120.2999999...
 * DECIMAL(10,2) stores exact values: 120.30 is always 120.30.
 * This matters for: totals, refund calculations, settlement reports.
 *
 * ──────────────────────────────────────────────────────────────
 * FOREIGN KEYS:
 * ──────────────────────────────────────────────────────────────
 *   booking_id        → bookings.id        RESTRICT
 *   user_id           → users.id           RESTRICT
 *   payment_method_id → payment_methods.id RESTRICT
 *
 * All use RESTRICT because deleting these referenced records
 * while payments exist would break financial integrity.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            // ── BOOKING REFERENCE ──────────────────────────────────────────

            /**
             * The booking this payment settles.
             * UNIQUE: one booking can only have one payment record.
             * (Multiple gateway attempts are tracked in payment_transactions, not here.)
             *
             * RESTRICT: cannot delete a booking that has a payment against it.
             * Financial records must be preserved for auditing.
             */
            $table->unsignedBigInteger('booking_id')->unique()
                  ->comment('FK → bookings.id — UNIQUE: one payment per booking; multiple gateway attempts go in payment_transactions');
            $table->foreign('booking_id')
                  ->references('id')->on('bookings')
                  ->onDelete('restrict');

            // ── USER REFERENCE (DENORMALIZED) ──────────────────────────────

            /**
             * The user who made this payment.
             * Denormalized from booking.user_id for faster payment-centric queries.
             * Must always equal booking.user_id — set by service layer at creation.
             *
             * RESTRICT: cannot delete a user with payment history.
             */
            $table->unsignedBigInteger('user_id')
                  ->comment('FK → users.id — denormalized from booking.user_id for fast payment history queries');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('restrict');

            // ── FINANCIAL DETAILS ──────────────────────────────────────────

            /**
             * Total amount charged for this booking.
             * DECIMAL(10,2): exact decimal — never FLOAT for money.
             * Range: 0.00 to 99,999,999.99 — suitable for INR and most currencies.
             *
             * This is the ORIGINAL charged amount.
             * Refunded amounts are tracked in the refunds table.
             * Net amount = this amount − refunds.refund_amount.
             */
            $table->decimal('amount', 10, 2)
                  ->comment('Total amount charged — DECIMAL not FLOAT; original charge before any refunds');

            /**
             * The payment channel used by the user.
             * References the payment_methods master table (Phase 1).
             * Examples: UPI, Credit Card, Debit Card, Net Banking, Wallet.
             *
             * RESTRICT: cannot delete a payment method that has payments.
             */
            $table->unsignedBigInteger('payment_method_id')
                  ->comment('FK → payment_methods.id — UPI, card, net banking, wallet, etc.');
            $table->foreign('payment_method_id')
                  ->references('id')->on('payment_methods')
                  ->onDelete('restrict');

            // ── STATUS ─────────────────────────────────────────────────────

            /**
             * Current state of this payment.
             *
             *   pending            → Payment initiated; awaiting gateway confirmation
             *   paid               → Gateway confirmed; money received
             *   failed             → Gateway rejected / user cancelled / timeout
             *   refunded           → Full amount refunded to user
             *   partially_refunded → Partial amount refunded (e.g. early checkout)
             *
             * Default 'pending': payments are created before the gateway is called.
             * The service layer updates this after receiving the gateway webhook/callback.
             */
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'partially_refunded',
            ])->default('pending')
              ->comment('pending→paid on success; pending→failed on failure; paid→refunded on cancellation');

            // ── GATEWAY REFERENCE ──────────────────────────────────────────

            /**
             * Unique reference ID issued by the payment gateway after success.
             * This is what you use to:
             *   - Initiate a refund via gateway API
             *   - Reconcile with bank settlement reports
             *   - Answer customer queries: "my payment went through"
             *
             * Nullable because it's only available after gateway confirmation.
             * Set to NULL initially; updated by the payment webhook handler.
             *
             * Examples by gateway:
             *   Razorpay:  "pay_OFj22lmMfLkH4d"
             *   PayU:      "8472938742"
             *   Paytm:     "20250115111212800110168"
             *   UPI:       "UPI/CR/123456789012/HDFC"
             *
             * UNIQUE (nullable): two payments cannot share the same gateway reference.
             * MySQL allows multiple NULL values even in a UNIQUE column.
             */
            $table->string('payment_reference')->nullable()->unique()
                  ->comment('Gateway-issued transaction reference — set after success; used for refunds and reconciliation');

            /**
             * Exact timestamp when the payment was confirmed as successful.
             * NULL = not yet paid (pending or failed).
             * Set by the webhook handler when gateway confirms payment.
             *
             * IMPORTANT: This is the TIME of payment, not booking.
             * Use this for financial reporting ("revenue on 15 Jan") not booking dates.
             */
            $table->dateTime('paid_at')->nullable()
                  ->comment('Timestamp of payment confirmation — null until paid; used for financial date filtering');

            // Soft deletes: financial records must never be hard-deleted.
            // Regulatory compliance may require payment records for 7+ years.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ────────────────────────────────────────────────────

            /**
             * User payment history: "show all payments by user X, newest first"
             * Flutter app's Payment History screen.
             * Composite: filters by user + status in one index.
             */
            $table->index(['user_id', 'payment_status'], 'idx_payments_user_status');

            /**
             * Revenue report: "all paid payments in January 2025"
             * Financial dashboard, owner settlement calculations.
             * Composite: filters paid payments by date range.
             */
            $table->index(['payment_status', 'paid_at'], 'idx_payments_status_date');

            /**
             * Gateway reconciliation: find payment by reference number.
             * payment_reference is already UNIQUE (has an index).
             * The unique constraint creates this index automatically.
             * No need to add a separate index here.
             */

            /**
             * Pending payment cleanup: "find stale pending payments older than 15 min"
             * Used by a scheduled job to auto-expire abandoned payment sessions.
             */
            $table->index(['payment_status', 'created_at'], 'idx_payments_pending_cleanup');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};