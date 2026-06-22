<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Vehicle Types Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * Our parking system handles multiple vehicle categories —
 * two-wheelers (bikes/scooters), four-wheelers (cars/SUVs),
 * taxis, and potentially EV vehicles with charging needs.
 *
 * This table is the master list of all vehicle types the
 * platform supports. Other tables reference this table's IDs
 * instead of storing vehicle type strings directly. This keeps
 * data consistent and makes the system flexible.
 *
 * Tables that will reference `vehicle_types.id`:
 *   - `parking_slots`   → slot_vehicle_type_id
 *   - `vehicles`        → vehicle_type_id (user's saved vehicles)
 *   - `parking_prices`  → vehicle_type_id (if we add a prices table later)
 *
 * HOW IT WILL BE USED:
 *   - "Add Parking" flow: Owner specifies which vehicle types
 *     their parking supports.
 *   - "Book Parking" flow: User picks their vehicle type and
 *     sees only matching available slots + correct pricing.
 *   - Slot creation: Each slot is assigned to one vehicle type.
 *
 * FUTURE SCALABILITY:
 *   - New vehicle types (EV Truck, Auto Rickshaw, Bus) = new rows.
 *   - `icon` stores an icon identifier so the UI can display
 *     appropriate symbols without code changes.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `vehicle_types` table.
     */
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // Vehicle type name, e.g. "Two Wheeler", "Four Wheeler", "Taxi".
            // Unique so the same type can't be added twice.
            $table->string('name', 100)->unique();

            // Icon identifier for the UI.
            // Could be a CSS class (e.g. "fa-motorcycle"),
            // a Flutter icon name, or an S3 image URL.
            // Nullable since icons can be added after the initial setup.
            $table->string('icon', 255)->nullable();

            // Human-readable description shown in the Admin Panel
            // and possibly on the user-facing booking screen.
            // Example: "Includes motorcycles, scooters, and mopeds."
            $table->text('description')->nullable();

            // Controls whether this vehicle type is available
            // for booking and slot assignment.
            // "active"   → visible and usable across the platform.
            // "inactive" → hidden from users (feature temporarily paused).
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();

            // Index on status — most queries filter by status = 'active'.
            $table->index('status');
        });
    }

    /**
     * Reverse the migration.
     * NOTE: If `parking_slots` or `vehicles` already reference
     * vehicle_type_id, those tables must be dropped first.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};