<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Parking Facilities Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * A parking location can have many special features — CCTV,
 * EV Charging, Covered Parking, Security Guard, Washrooms,
 * Waiting Area, 24/7 Access, and more.
 *
 * This table is the master list of all those features (facilities).
 * Parkings link to this table via a many-to-many pivot table
 * (`parking_facility`) that gets created in the Parking module.
 *
 * WHY NOT USE JSON ON THE PARKINGS TABLE?
 * Storing facilities as a JSON array on `parkings` would make it
 * impossible to filter by facility efficiently. A proper pivot
 * table means we can do:
 *   "Find all EV-capable parkings near lat/lng"
 * ...with a simple JOIN, not by scanning and decoding JSON.
 *
 * HOW IT CONNECTS:
 *   parking_facilities (this table) ←→ parking_facility (pivot) ←→ parkings
 *
 * TABLES THAT REFERENCE THIS:
 *   - `parking_facility` pivot table (Parking module, built later)
 *
 * FUTURE SCALABILITY:
 *   - New facilities = new rows. No migration needed.
 *   - `icon` can store S3 URLs, Material icon names, or custom
 *     Flutter icon identifiers — whatever the mobile team needs.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `parking_facilities` table.
     */
    public function up(): void
    {
        Schema::create('parking_facilities', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // Facility name, e.g. "CCTV", "EV Charging", "Covered",
            // "Security", "Washroom", "Waiting Area", "24/7 Access".
            // Unique so the same facility doesn't appear twice
            // in the checklist.
            $table->string('name', 100)->unique();

            // Icon identifier for the UI.
            // In the Owner App screens we can see icon-based facility
            // indicators — CCTV icon, EV icon, security shield icon, etc.
            // This field stores whatever key/URL the mobile app needs.
            // Nullable since we may add facilities before finalizing icons.
            $table->string('icon', 255)->nullable();

            // Short description of what this facility means.
            // Shown in the Admin Panel when managing the facility list,
            // and potentially as a tooltip on the User App.
            // Example: "24-hour security personnel on premises."
            $table->text('description')->nullable();

            // Controls whether this facility appears in the
            // "Select Facilities" checklist when adding a parking.
            // "active"   → visible and available for selection.
            // "inactive" → hidden (maybe being phased out or renamed).
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();

            // Index on status for fast active-only queries.
            $table->index('status');
        });
    }

    /**
     * Reverse the migration.
     * NOTE: The `parking_facility` pivot table must be dropped
     * before this table if it has a FK referencing this table.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_facilities');
    }
};