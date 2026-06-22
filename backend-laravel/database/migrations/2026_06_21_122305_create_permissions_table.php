<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Permissions Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * A Role tells us WHO a user is. A Permission tells us WHAT
 * that user is allowed to DO. Keeping permissions in their own
 * table (separate from roles) lets us mix and match — the same
 * permission (e.g. "view_reports") can be given to more than
 * one role (e.g. both "Admin" and "Super Admin").
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *  - Each row here is one specific action in the system, e.g.
 *    "approve_parking", "manage_bookings", "scan_qr_checkin",
 *    "manage_commission", "view_earnings".
 *  - The `module` column groups related permissions together
 *    (e.g. "Parking Management", "Booking Management", "Reports")
 *    so the Admin Panel's "Roles & Permissions" screen can show
 *    them in organized sections instead of one long flat list.
 *  - Permissions get linked to roles through a `role_permissions`
 *    pivot table, which will be created in a later step.
 *
 * FUTURE SCALABILITY:
 *  - As we add new modules (Booking System, Payments, QR System,
 *    etc.), we simply add new permission rows under the right
 *    module — no structural changes needed.
 *  - Grouping by `module` also makes it much easier to build a
 *    permission-matrix UI later (rows = permissions grouped by
 *    module, columns = roles, checkboxes = access).
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `permissions` table.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            // Standard auto-incrementing primary key.
            $table->id();

            // The permission's unique key/name, e.g. "approve_parking".
            // We keep this as a short, code-friendly slug (snake_case)
            // rather than a sentence, since this value may also be
            // checked directly in code (e.g. in middleware later).
            $table->string('name', 150)->unique();

            // Groups this permission under a feature area, e.g.
            // "User Management", "Parking Management", "Bookings",
            // "Reports". Helps organize the Admin Panel's permission
            // screen and keeps related permissions together.
            $table->string('module', 100);

            // A short, human-friendly explanation of what this
            // permission actually allows. Helpful for whoever is
            // assigning permissions to roles later, so they don't
            // have to guess what "manage_commission" really means.
            $table->text('description')->nullable();

            // created_at and updated_at timestamps, managed
            // automatically by Laravel.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration — drops the `permissions` table.
     * Run automatically when you do `php artisan migrate:rollback`.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};