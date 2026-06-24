<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_refunds_table
 *
 * Tracks refund requests and their processing status for cancelled bookings.
 * One refund record per payment (full or partial amount can be refunded).
 *
 * ──────────────────────────────────────────────────────────────
 * REFUND vs PAYMENT — THE RELATIONSHIP:
 * ──────────────────────────────────────────────────────────────
 *   Payment    → the original charge ("user paid ₹120")
 *   Refund     → the reversal        ("we're sending back ₹90")
 *
 *   Payment.status moves to 'refunded' or 'partially_refunded'
 *   when the Refund.status reaches 'processed'.
 *   This update is done in the RefundService, not here.
 *
 * ──────────────────────────────────────────────────────────────
 * ONE-TO-ONE DESIGN (payment_id UNIQUE):
 * ──────────────────────────────────────────────────────────────
 *   Currently: one payment → one refund.
 *   This covers 99% of use cases (one cancellation per booking).
 *
 *   FUTURE: If multiple partial refunds per payment are needed
 *   (e.g. refund ₹50 now, ₹50 later), remove the UNIQUE constraint
 *   on payment_id and add a `refund_sequence` integer column.
 *
 * ──────────────────────────────────────────────────────────────
 * FOREIGN KEY:
 * ──────────────────────────────────────────────────────────────
 *   payment_id → payments.id   RESTRICT
 *   Cannot delete a payment that has a refund record against it.
 *   Financial audit trail must be preserved.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {

            $table->id();

            // ── PARENT PAYMENT ─────────────────────────────────────────────

            /**
             * The payment being refunded.
             * UNIQUE: enforces one-to-one — one payment gets one refund record.
             *
             * To support multiple refunds per payment in the future,
             * remove the ->unique() and handle it in application logic.
             *
             * RESTRICT: cannot delete a payment that has a refund against it.
             * The entire payment + refund chain must be preserved for audits.
             */
            $table->unsignedBigInteger('payment_id')->unique()
                  ->comment('FK → payments.id — UNIQUE: one refund record per payment; remove unique for multi-refund support');
            $table->foreign('payment_id')
                  ->references('id')->on('payments')
                  ->onDelete('restrict');

            // ── FINANCIAL DETAILS ──────────────────────────────────────────

            /**
             * The amount being refunded to the user.
             * May be LESS THAN payment.amount for partial refunds.
             * May EQUAL payment.amount for full refunds.
             * Can NEVER exceed payment.amount — enforced in application logic.
             *
             * DECIMAL(10,2): same precision as payments.amount.
             * NEVER use FLOAT for money values — see payments migration for reasoning.
             * unsigned(): refund amount cannot be negative.
             *
             * EXAMPLES:
             *   payment.amount = 120.00
             *   refund_amount  = 120.00  → full refund (user cancelled early)
             *   refund_amount  = 90.00   → partial refund (cancellation fee ₹30 kept)
             *   refund_amount  = 0.00    → no refund (but we still log the decision)
             */
            $table->decimal('refund_amount', 10, 2)->unsigned()
                  ->comment('Amount to refund — may be partial (< payment.amount) or full (= payment.amount); DECIMAL not FLOAT');

            // ── REASON & AUDIT ─────────────────────────────────────────────

            /**
             * Why this refund is being issued.
             * Used for:
             *   1. User communication: "Your refund reason: User cancelled booking."
             *   2. Admin audit: understand why refunds are being issued
             *   3. Analytics: which cancellation reason costs the most in refunds
             *
             * TEXT type: allows detailed explanations if needed.
             * In most cases, use the Refund::REASON_* constants for consistency.
             *
             * Examples:
             *   "User cancelled booking."
             *   "Parking facility was unavailable."
             *   "Admin manually issued refund — parking attendant error."
             */
            $table->text('refund_reason')
                  ->comment('Reason for refund — use Refund::REASON_* constants for standardized values; free text for edge cases');

            // ── STATUS ─────────────────────────────────────────────────────

            /**
             * Current state of this refund in the processing pipeline.
             *
             *   pending    → Refund decision made; not yet sent to gateway
             *                (waiting for: admin approval, policy check, or service)
             *
             *   processing → Refund API call made to gateway; waiting for bank
             *                (typically 5–10 business days in India)
             *
             *   processed  → Gateway confirmed refund sent to user's bank
             *                → set refunded_at = NOW()
             *                → update Payment.status = 'refunded'/'partially_refunded'
             *                → send user notification
             *
             *   failed     → Gateway could not process the refund
             *                (account closed, bank error, gateway rejection)
             *                → needs manual admin intervention and retry
             *
             * Default 'pending': refunds start as pending, requiring explicit
             * service action to move them through the pipeline.
             */
            $table->enum('refund_status', ['pending', 'processing', 'processed', 'failed'])
                  ->default('pending')
                  ->comment('pending→processing→processed (success) or →failed (needs retry); triggers Payment status update on processed');

            /**
             * Exact timestamp when the refund was confirmed as successfully processed.
             * NULL until refund_status reaches 'processed'.
             * Set by the RefundService when the gateway webhook confirms success.
             *
             * Use this for:
             *   - User communication: "Refunded on 15 Jan 2025"
             *   - Financial reports: "Total refunds processed in January"
             *   - SLA tracking: "Time from pending to processed"
             */
            $table->dateTime('refunded_at')->nullable()
                  ->comment('Timestamp when refund was confirmed by gateway — null until status = processed');

            // Soft deletes: refund records are financial evidence.
            // Never hard-delete — keep for audit and dispute resolution.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ────────────────────────────────────────────────────

            /**
             * Note: payment_id is already indexed via the unique() constraint above.
             */

            /**
             * Admin refund queue: "show all pending refunds, oldest first"
             * Used in admin panel refund management screen.
             * Query: WHERE refund_status = 'pending' ORDER BY created_at ASC
             */
            $table->index(['refund_status', 'created_at'], 'idx_refunds_status_date');

            /**
             * Financial reporting: "total refunds processed in date range"
             * Query: WHERE refund_status = 'processed' AND refunded_at BETWEEN ?
             * Used for monthly revenue reconciliation (refunds reduce net revenue).
             */
            $table->index(['refund_status', 'refunded_at'], 'idx_refunds_processed_date');

            /**
             * Failed refund retry: "find all failed refunds needing attention"
             * Query: WHERE refund_status = 'failed'
             * Index on refund_status alone serves this use case.
             * (Already covered by the first index above, but adding conceptual clarity here.)
             * Note: idx_refunds_status_date covers this — no duplicate index needed.
             */
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};