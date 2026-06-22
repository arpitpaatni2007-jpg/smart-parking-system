<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Cities Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * Cities sit at the second level of our location hierarchy.
 * Every parking location will be linked to a city, and every
 * city is linked to a state. This gives us a clean, consistent
 * way to organize and filter parking locations geographically.
 *
 * Location hierarchy:
 *   State (states) → City (cities) → Parking (parkings) → Slot (parking_slots)
 *
 * HOW IT WILL BE USED:
 *   - `parkings` table will have a `city_id` FK pointing here.
 *   - Owner App: City is picked during parking registration.
 *   - User App: City filter on the search/map screen.
 *   - Admin: City-level statistics and filtering.
 *
 * FUTURE SCALABILITY:
 *   - `lat` / `lng` columns can be added to support
 *     "Find parking near city center" features.
 *   - A `timezone` column can be added for multi-timezone
 *     support if we expand beyond India.
 *
 * MIGRATION ORDER:
 *   This migration MUST run AFTER the `states` migration
 *   because we have a foreign key pointing to `states.id`.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `cities` table.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // Which state this city belongs to.
            // `constrained()` automatically points to `states.id`.
            // `cascadeOnDelete()` means: if a state is deleted,
            // all its cities are automatically deleted too.
            // Think carefully before deleting states — it cascades!
            $table->foreignId('state_id')
                  ->constrained('states')
                  ->cascadeOnDelete();

            // City name, e.g. "Mumbai", "Bengaluru", "New Delhi".
            // Not globally unique — "Salem" exists in both Tamil
            // Nadu and Karnataka — but unique within a state.
            $table->string('name', 100);

            // Controls visibility in user-facing dropdowns and search.
            // "active"   → this city is live on the platform.
            // "inactive" → hidden from users (launching soon).
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();

            // -------------------------------------------------------
            // Composite unique index on (state_id + name):
            // Prevents "Mumbai" from being added twice under
            // "Maharashtra", while still allowing "Salem" to exist
            // in both Tamil Nadu and Karnataka.
            // -------------------------------------------------------
            $table->unique(['state_id', 'name']);

            // -------------------------------------------------------
            // Index on state_id for faster lookups:
            // When the user selects a state and we need to load
            // cities for that state, this index makes the query fast
            // even with thousands of cities in the table.
            // -------------------------------------------------------
            $table->index('state_id');

            // -------------------------------------------------------
            // Index on status for faster filtering:
            // Most queries will include WHERE status = 'active',
            // so indexing it avoids full table scans.
            // -------------------------------------------------------
            $table->index('status');
        });
    }

    /**
     * Reverse the migration.
     * Will fail if `parkings` or other tables still reference
     * city IDs — drop those tables first when rolling back.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};