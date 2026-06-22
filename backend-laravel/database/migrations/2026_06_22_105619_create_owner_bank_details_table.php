<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_owner_bank_details_table
 *
 * Stores banking information for parking owners to receive payouts.
 * One owner has at most one active bank detail record (enforced via
 * unique constraint on owner_id).
 *
 * SECURITY NOTE:
 *   This table contains sensitive PII. In production:
 *   - Enable MySQL's encryption at rest (TDE)
 *   - Encrypt account_number at the application level
 *   - Restrict DB user permissions to this table
 *   - Enable query-level audit logging
 *
 * FOREIGN KEY:
 *   owner_id → users.id
 *   RESTRICT: do not allow deleting a user who has bank details on file.
 *   Admin must clear bank details first (e.g. after account closure).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('owner_bank_details', function (Blueprint $table) {

            $table->id();

            /**
             * The parking owner this bank detail belongs to.
             * UNIQUE: enforces one-to-one — each owner can have
             * only one bank detail record at a time.
             *
             * RESTRICT on delete: prevents orphaning a payout record
             * by accidentally deleting the owner user.
             */
            $table->unsignedBigInteger('owner_id')
                  ->unique()
                  ->comment('FK → users.id — one bank record per owner (unique enforced)');
            $table->foreign('owner_id')
                  ->references('id')->on('users')
                  ->onDelete('restrict');

            /**
             * Full legal name of the account holder as per bank records.
             * Must match exactly for payout processing to succeed.
             * e.g. "RAJESH KUMAR SHARMA" (banks use uppercase)
             */
            $table->string('account_holder_name')
                  ->comment('Legal name on the bank account — must match bank records exactly');

            /**
             * Name of the bank.
             * Free-form string — not an enum because India has 50+ banks
             * and new banks get added/merged frequently.
             * e.g. "State Bank of India", "HDFC Bank", "ICICI Bank"
             *
             * FUTURE: Normalize this into a separate `banks` master table.
             */
            $table->string('bank_name')
                  ->comment('Full name of the bank e.g. State Bank of India');

            /**
             * Bank account number.
             * Indian account numbers are 9–18 digits.
             * Stored as string (not integer) to:
             *   1. Preserve leading zeros if any
             *   2. Support international account formats in the future
             *
             * PRODUCTION: Encrypt this value before storing.
             * Use: $table->text('account_number') if storing encrypted (longer)
             */
            $table->string('account_number')
                  ->comment('Bank account number — ENCRYPT IN PRODUCTION before storing');

            /**
             * Indian Financial System Code.
             * Format: 4 alpha (bank code) + '0' + 6 alphanumeric (branch code)
             * Total: exactly 11 characters.
             * e.g. "SBIN0001234", "HDFC0000123"
             *
             * Used by NEFT/RTGS/IMPS transfer systems to identify the branch.
             */
            $table->string('ifsc_code', 11)
                  ->comment('11-character IFSC code e.g. SBIN0001234 — identifies the bank branch');

            /**
             * Verification and activation status.
             *
             *   pending_verification → Owner submitted; awaiting admin review
             *   active              → Verified by admin; eligible for payouts
             *   inactive            → Manually disabled; payouts paused
             *
             * New submissions default to pending_verification so an admin
             * manually verifies before any real money moves.
             */
            $table->enum('status', ['pending_verification', 'active', 'inactive'])
                  ->default('pending_verification')
                  ->comment('pending_verification = awaiting admin review | active = payout-eligible');

            // Soft deletes: audit trail for bank detail changes.
            // If an owner updates their bank account, the old record is
            // soft-deleted so there's a history of which account was used
            // for historical payouts.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────
            // Note: owner_id already has an index via the unique() constraint above.

            // Index for admin panel: "show all pending verifications"
            $table->index('status', 'idx_bank_details_status');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_bank_details');
    }
};