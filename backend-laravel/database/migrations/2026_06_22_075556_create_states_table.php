<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create States Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * The `states` table is the top level of our location hierarchy.
 * Every parking location in the system will have an address
 * that includes a city, and every city belongs to a state.
 * This table is the master list of Indian states (or any
 * countries/regions if we expand later).
 *
 * Location hierarchy in our system:
 *   State → City → Parking Location → Parking Slot
 *
 * HOW IT WILL BE USED:
 *   - Owner App: "Add Parking" form shows a state dropdown.
 *   - User App: "Search" screen can filter by state → city.
 *   - Admin Panel: Reports can be filtered by state.
 *   - The `cities` table has a `state_id` FK pointing here.
 *
 * FUTURE SCALABILITY:
 *   - The `code` column (e.g. "MH", "DL") is short enough for
 *     SMS messages and compact UI labels.
 *   - Adding a `country_id` FK here later would let us go
 *     multi-country without restructuring the table.
 *   - `status` allows disabling states where we haven't launched
 *     yet — keeps the dropdown clean for users.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `states` table.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // Full state name, e.g. "Maharashtra", "Delhi", "Karnataka".
            // Unique constraint prevents duplicate state entries.
            $table->string('name', 100)->unique();

            // Short state code, e.g. "MH", "DL", "KA".
            // Nullable because we may add states before assigning codes.
            // Unique so two states can't share the same code.
            $table->string('code', 10)->unique()->nullable();

            // Controls whether this state appears in dropdowns.
            // "active"   → platform is live in this state.
            // "inactive" → state exists in DB but hidden from users.
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     * NOTE: If cities already exist for a state, the DB will
     * throw a foreign key error. Always rollback cities first,
     * or the FK cascades handle cleanup automatically.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};