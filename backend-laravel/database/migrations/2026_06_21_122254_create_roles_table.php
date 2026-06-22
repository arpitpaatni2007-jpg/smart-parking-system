
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Roles Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * Every user in our Smart Parking System belongs to exactly
 * one role — Super Admin, Admin, Parking Owner, Parking Manager,
 * or Customer. Instead of hardcoding these as plain text on the
 * `users` table, we keep them in their own table.
 *
 * This gives us:
 *  - A single, clean source of truth for "what roles exist"
 *  - The ability to attach permissions to a role later
 *  - The ability to enable/disable a role without deleting data
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *  - We'll seed this table with our 5 starting roles.
 *  - The `users` table will reference this table using a
 *    `role_id` foreign key (added separately, not in this task).
 *  - Middleware will check a logged-in user's role before
 *    allowing access to certain routes/screens.
 *    Example: only "Admin" or "Super Admin" can open the
 *    "Approve Parking" screen in the Admin Panel.
 *
 * FUTURE SCALABILITY:
 *  - Adding a new role later (e.g. "Support Agent") is just a
 *    new database row — no migration changes needed.
 *  - The `status` column lets us soft-disable a role instead of
 *    deleting it, which keeps historical data (old users, logs)
 *    intact even if a role is retired.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `roles` table.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            // Standard auto-incrementing primary key.
            $table->id();

            // The role's display name, e.g. "Super Admin", "Customer".
            // Marked unique so we never accidentally create the same
            // role twice.
            $table->string('name', 100)->unique();

            // A short, human-friendly explanation of what this role
            // is for. Mainly shown in the Admin Panel so whoever is
            // managing roles understands each one at a glance.
            $table->text('description')->nullable();

            // Lets us turn a role "off" without deleting it.
            // Using a plain string here (rather than boolean) so we
            // have room to add more states later if needed, e.g.
            // "active", "inactive". Defaults to "active" so newly
            // created roles work immediately.
            $table->string('status', 20)->default('active');

            // created_at and updated_at timestamps, managed
            // automatically by Laravel.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration — drops the `roles` table.
     * Run automatically when you do `php artisan migrate:rollback`.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};