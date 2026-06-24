<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_payment_transactions_table
 *
 * Logs every individual payment gateway API call attempt.
 * This is a technical audit log — one row per gateway interaction.
 *
 * ──────────────────────────────────────────────────────────────
 * PAYMENT vs PAYMENT_TRANSACTIONS — THE DISTINCTION:
 * ──────────────────────────────────────────────────────────────
 *   payments              → Business record ("booking BK-001 was paid ₹120")
 *   payment_transactions  → Technical record ("we called Razorpay at 10:32 AM
 *                            and got back: { status: 'captured', id: 'pay_abc' }")
 *
 * One payment can have MULTIPLE transactions (retry attempts).
 * Only ONE transaction will have status = 'success'.
 * All others are failed attempts — still valuable for debugging.
 *
 * ──────────────────────────────────────────────────────────────
 * IMMUTABILITY:
 * ──────────────────────────────────────────────────────────────
 * Transaction rows are NEVER updated or deleted.
 * They are written once (when the gateway responds) and then only read.
 * No soft deletes — they are permanent audit evidence.
 *
 * ──────────────────────────────────────────────────────────────
 * GATEWAY RESPONSE STORAGE:
 * ──────────────────────────────────────────────────────────────
 * gateway_response is stored as JSON (LONGTEXT in MySQL).
 * Full raw response is stored — never filter fields before saving.
 * Reason: Gateway APIs change; if you filter today, you might lose
 * data you need tomorrow. Raw storage is the safe choice.
 *
 * FOREIGN KEY:
 *   payment_id → payments.id   CASCADE
 *   If a payment is (soft) deleted, transactions remain (soft delete preserves them).
 *   Hard delete of payment cascades to remove its transactions too.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {

            $table->id();

            // ── PARENT PAYMENT ─────────────────────────────────────────────

            /**
             * The payment this transaction belongs to.
             * Multiple transactions can exist under one payment (retry attempts).
             *
             * CASCADE: if a payment record is hard-deleted, its transaction
             * logs are also removed. In practice, payments use soft deletes
             * so this cascade rarely fires.
             */
            $table->unsignedBigInteger('payment_id')
                  ->comment('FK → payments.id — which payment attempt this transaction log belongs to');
            $table->foreign('payment_id')
                  ->references('id')->on('payments')
                  ->onDelete('cascade');

            // ── GATEWAY IDENTIFICATION ─────────────────────────────────────

            /**
             * The payment gateway's own unique identifier for this transaction.
             * Returned by the gateway API in the response body.
             *
             * Examples:
             *   Razorpay:  "pay_OFj22lmMfLkH4d"
             *   PayU:      "8472938742"
             *   Paytm:     "20250115111212800110168"
             *   Stripe:    "ch_3OvXyLSJg1234567"
             *
             * UNIQUE: No two transactions should have the same gateway ID.
             * This protects against double-processing the same webhook.
             *
             * NOTE: On a failed attempt, the gateway may still return a
             * transaction_id for the declined attempt — store it here.
             */
            $table->string('transaction_id')->unique()
                  ->comment('Gateway-assigned unique ID for this specific call — used for reconciliation and webhook deduplication');

            /**
             * Which payment gateway processed this transaction.
             * Stored as a short lowercase string identifier.
             *
             * Examples: 'razorpay', 'payu', 'paytm', 'stripe', 'ccavenue', 'upi_direct'
             *
             * No ENUM here — new gateways can be added without a migration.
             * Max 50 chars covers any realistic gateway name.
             */
            $table->string('gateway_name', 50)
                  ->comment('Gateway identifier e.g. razorpay | payu | paytm | stripe — not enum so new gateways need no migration');

            // ── GATEWAY RESPONSE ───────────────────────────────────────────

            /**
             * Full raw JSON response body from the payment gateway.
             * Stored as TEXT (parsed to PHP array via Model cast).
             *
             * WHY STORE THE FULL RAW RESPONSE?
             *   1. Debugging: you can replay exactly what the gateway said
             *   2. Schema-free: gateway APIs change — raw storage is future-proof
             *   3. Audit evidence: for disputes, you have proof of gateway response
             *   4. Field extraction: use getResponseValue() helper to read fields
             *
             * LONGTEXT supports up to 4GB — handles any gateway response size.
             * Nullable: on 'initiated' status, response hasn't arrived yet.
             *
             * FUTURE: If this table grows very large and JSON querying is needed,
             * switch column type to JSON (MySQL 5.7.8+) for native JSON indexing.
             */
            $table->longText('gateway_response')->nullable()
                  ->comment('Full raw JSON response from gateway — store complete, never filter fields; null when transaction is first initiated');

            // ── STATUS ─────────────────────────────────────────────────────

            /**
             * The outcome of this specific gateway API call.
             *
             *   initiated → API request sent; response not yet received
             *   success   → Gateway confirmed successful payment
             *   failed    → Gateway explicitly declined (card declined, funds low, etc.)
             *   pending   → Gateway is processing asynchronously (UPI, net banking)
             *               — a webhook will arrive later to update Payment status
             *
             * NOTE: 'pending' here is different from Payment.status 'pending'.
             *   PaymentTransaction.pending = gateway processing async
             *   Payment.pending = payment not yet attempted
             */
            $table->enum('transaction_status', ['initiated', 'success', 'failed', 'pending'])
                  ->default('initiated')
                  ->comment('Gateway call outcome: initiated→success|failed|pending; pending means async (UPI/net banking webhook expected)');

            // No soft deletes — transaction logs are immutable audit evidence.
            // Never delete, never update. Append-only.

            $table->timestamps();
            // created_at = when the gateway API call was made
            // updated_at = when we received the response (if we update initiated→success)

            // ── INDEXES ────────────────────────────────────────────────────

            /**
             * Load all transactions for a payment (retry history).
             * Most common query: "show all attempts for payment X"
             * Note: payment_id is not unique — multiple transactions per payment.
             */
            $table->index(['payment_id', 'transaction_status'], 'idx_txn_payment_status');

            /**
             * Gateway reconciliation: "find all Razorpay transactions in January"
             * Used for monthly reconciliation with gateway settlement reports.
             */
            $table->index(['gateway_name', 'created_at'], 'idx_txn_gateway_date');

            /**
             * Pending webhook processing:
             * "Find all pending transactions older than 10 minutes to re-query status"
             * Used by a scheduled job that polls the gateway for async payment updates.
             */
            $table->index(['transaction_status', 'created_at'], 'idx_txn_status_date');

            /**
             * transaction_id is already indexed via the unique() constraint above.
             * No additional index needed for gateway ID lookups.
             */
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};