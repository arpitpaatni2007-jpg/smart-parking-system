<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_vehicle_documents_table
 *
 * Stores uploaded vehicle document files (RC, Insurance, PUC, etc.)
 * for compliance verification at parking facilities.
 *
 * DESIGN PHILOSOPHY:
 *   This table is a document registry — it records WHAT documents exist
 *   for a vehicle, WHERE the files are stored, and WHEN they expire.
 *   The actual files live on disk/S3; only the path is stored here.
 *
 * ONE VEHICLE — MULTIPLE DOCUMENT RECORDS:
 *   vehicle_id=5, type='rc'          → RC Book
 *   vehicle_id=5, type='insurance'   → Insurance (expires Jan 2026)
 *   vehicle_id=5, type='puc'         → PUC (expires Jul 2025)
 *
 *   When insurance renews, a NEW row is inserted with the new expiry.
 *   The old row is soft-deleted or marked 'expired'. This gives a
 *   complete document renewal audit trail.
 *
 * FOREIGN KEYS:
 *   vehicle_id → vehicles.id   CASCADE
 *     When a vehicle is hard-deleted (rare — we use soft deletes),
 *     all its document records are also removed.
 *     In practice, soft deletes on vehicles mean this cascade rarely triggers.
 *
 * NO UNIQUE CONSTRAINT ON (vehicle_id, document_type):
 *   We intentionally allow multiple rows of the same type per vehicle.
 *   Reason: insurance is renewed annually — both old (expired) and
 *   new (active) insurance records should coexist in history.
 *   Application logic ensures only one 'active' record per type at a time.
 *
 *   FUTURE: If you want DB-level enforcement of "one active per type",
 *   add: $table->unique(['vehicle_id', 'document_type', 'status']);
 *   But this only works cleanly with a filtered/partial unique index
 *   (MySQL 8.0.13+ supports functional indexes for this).
 *
 * SCHEDULED JOB REQUIREMENT:
 *   A scheduled job (Laravel Scheduler) should run daily to:
 *   - Find all documents WHERE status='active' AND expiry_date < TODAY
 *   - Update their status to 'expired'
 *   - Optionally send reminder notifications 30/7/1 days before expiry
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {

            $table->id();

            // ── PARENT VEHICLE ────────────────────────────────────────────

            /**
             * The vehicle this document belongs to.
             * CASCADE: if a vehicle record is hard-deleted, its document
             * records go with it. (Soft deletes on vehicles mean this
             * cascade rarely fires in practice.)
             */
            $table->unsignedBigInteger('vehicle_id')
                  ->comment('FK → vehicles.id — which vehicle this document belongs to');
            $table->foreign('vehicle_id')
                  ->references('id')->on('vehicles')
                  ->onDelete('cascade');

            // ── DOCUMENT IDENTITY ─────────────────────────────────────────

            /**
             * Category of the document.
             * ENUM restricts to known, valid document types — prevents
             * typos like "insurence" or "R.C" being stored.
             *
             *   rc         → Registration Certificate (proof of ownership)
             *   insurance  → Motor insurance certificate (mandatory by law)
             *   puc        → Pollution Under Control certificate
             *   fitness    → Fitness certificate (commercial vehicles only)
             *   permit     → Transport permit (goods/passenger carriers)
             *
             * FUTURE: Add 'drivers_license' if you expand to tracking
             *   driver information alongside vehicle documents.
             */
            $table->enum('document_type', ['rc', 'insurance', 'puc', 'fitness', 'permit'])
                  ->comment('Type of document: rc | insurance | puc | fitness | permit');

            /**
             * Relative path to the uploaded file.
             * File lives at: storage/app/public/{document_path}
             * Example: "vehicle-documents/rc_user5_vehicle3_abc123.jpg"
             *
             * Use Storage::url($document_path) to get the public URL.
             * Storing relative path (not full URL) makes the app
             * portable — you can switch from local to S3 by changing .env.
             *
             * FUTURE: Switch to TEXT if storing encrypted or long S3 URLs.
             */
            $table->string('document_path')
                  ->comment('Relative storage path e.g. vehicle-documents/rc_abc123.jpg — use Storage::url() for full URL');

            // ── VALIDITY / COMPLIANCE ─────────────────────────────────────

            /**
             * Date when this document expires.
             * NULL = document has no expiry (e.g., RC Book in most Indian states).
             *
             * DATE type (not DATETIME) — expiry is a calendar date, not a time.
             *
             * DOCUMENTS AND THEIR TYPICAL EXPIRY:
             *   rc        → Nullable (no expiry in most states; some states: 15 yrs)
             *   insurance → 1 year (renewable annually)
             *   puc       → 6 months for petrol; 1 year for new vehicles
             *   fitness   → 2 years for new commercial, 1 year thereafter
             *   permit    → 5 years (varies by state and permit type)
             */
            $table->date('expiry_date')->nullable()
                  ->comment('Date this document expires — null means no expiry (e.g. RC); use DATE not DATETIME');

            /**
             * Current status of this document record.
             *
             *   pending  → Uploaded by user, awaiting admin verification
             *   active   → Verified by admin, currently valid
             *   expired  → expiry_date has passed (auto-set by scheduled job)
             *   rejected → Admin rejected upload (wrong doc, blurry, mismatch)
             *
             * Default 'pending' — newly uploaded documents need verification
             * before they're trusted for compliance checks.
             *
             * ADMIN WORKFLOW:
             *   Upload → pending → [admin reviews] → active or rejected
             *   active → [scheduled job] → expired (when expiry_date passes)
             */
            $table->enum('status', ['pending', 'active', 'expired', 'rejected'])
                  ->default('pending')
                  ->comment('pending = awaiting review | active = verified | expired = past expiry | rejected = admin rejected');

            // Soft deletes — when a document is replaced/renewed, the old
            // record is soft-deleted (not hard-deleted) for history.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────

            /**
             * PRIMARY LOOKUP: "all documents for vehicle X"
             * Used in: vehicle detail screen, admin verification queue.
             * Composite index speeds up filtering by type and status too.
             */
            $table->index(['vehicle_id', 'document_type', 'status'], 'idx_vehicle_docs_lookup');

            /**
             * EXPIRY JOB INDEX: "find all active docs expiring before DATE"
             * Used by the daily scheduled job that auto-expires documents.
             * Query: WHERE status = 'active' AND expiry_date < TODAY
             *
             * Also used for expiry reminder notifications:
             * WHERE status = 'active' AND expiry_date BETWEEN TODAY AND TODAY+30
             */
            $table->index(['status', 'expiry_date'], 'idx_vehicle_docs_expiry');

            /**
             * ADMIN VERIFICATION QUEUE: "all pending documents, oldest first"
             * Query: WHERE status = 'pending' ORDER BY created_at ASC
             */
            $table->index(['status', 'created_at'], 'idx_vehicle_docs_status_date');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
    }
};