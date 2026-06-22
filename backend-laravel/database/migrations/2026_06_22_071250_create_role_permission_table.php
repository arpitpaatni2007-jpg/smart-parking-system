<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create role_permissions Pivot Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * We already have two tables — `roles` and `permissions`.
 * But right now they have no connection to each other.
 * This pivot table is the bridge between them.
 *
 * Think of it this way:
 *   - `roles` says "Admin exists"
 *   - `permissions` says "approve_parking exists"
 *   - `role_permissions` says "Admin CAN approve_parking"
 *
 * This is a standard many-to-many relationship:
 *   - One Role can have MANY Permissions
 *     (Admin has: approve_parking, manage_users, view_reports...)
 *   - One Permission can belong to MANY Roles
 *     (view_reports is shared by Admin AND Super Admin)
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *   - When a user logs in, we load their role.
 *   - We then load the permissions attached to that role
 *     via this pivot table.
 *   - Middleware will query this table to decide:
 *     "Does this role have the 'approve_parking' permission?
 *      If yes, allow the request. If no, return 403."
 *   - The Admin Panel's "Roles & Permissions" screen will
 *     read/write this table when an admin checks or unchecks
 *     a permission checkbox for a role.
 *
 * FUTURE SCALABILITY:
 *   - Adding a new permission to a role is a single new row
 *     in this table — no code changes needed.
 *   - Removing a permission from a role is a single DELETE.
 *   - If we later want user-level permissions (overriding the
 *     role), we can add a separate `user_permissions` table
 *     following the same pattern without touching this one.
 *   - `timestamps()` is included so we have an audit trail
 *     of when a permission was granted to a role. Useful
 *     for debugging or security reviews later.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `role_permissions` pivot table.
     *
     * IMPORTANT: This migration MUST run AFTER both the `roles`
     * and `permissions` migrations. We ensure this by using a
     * later timestamp in the filename.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            // -------------------------------------------------------
            // Foreign key: which Role this row belongs to.
            // `unsignedBigInteger` matches the `id` type in `roles`.
            // `constrained()` auto-points to the `roles` table.
            // `cascadeOnDelete()` means: if a Role is deleted,
            // all its permission assignments are automatically
            // cleaned up too — no orphaned rows left behind.
            // -------------------------------------------------------
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // Foreign key: which Permission is being granted.
            // Same pattern — if a Permission is deleted from the
            // `permissions` table, this row is deleted too.
            // -------------------------------------------------------
            $table->foreignId('permission_id')
                  ->constrained('permissions')
                  ->cascadeOnDelete();

            // -------------------------------------------------------
            // Composite Primary Key
            // The combination of role_id + permission_id must be
            // unique. This prevents the same permission being
            // accidentally added to the same role twice.
            // This also doubles as an index, making lookups fast.
            // -------------------------------------------------------
            $table->primary(['role_id', 'permission_id']);

            // -------------------------------------------------------
            // Timestamps on a pivot table are optional but useful.
            // `withTimestamps()` on the Eloquent relationship side
            // will fill these automatically.
            // Helps answer questions like:
            // "When was the 'manage_commission' permission added
            //  to the Admin role?"
            // -------------------------------------------------------
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     * `php artisan migrate:rollback` will drop this table.
     *
     * Drop order matters: this table references `roles` and
     * `permissions`, so it must be dropped BEFORE those tables
     * (which is handled automatically by rollback order).
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};