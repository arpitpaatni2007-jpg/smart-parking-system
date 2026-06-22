<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_parkings_table
 *
 * Core table for all parking locations in the system.
 * Each row represents one physical parking facility
 * owned and managed by an owner user.
 *
 * FOREIGN KEYS:
 *   owner_id → users.id      (who owns this parking)
 *   state_id → states.id     (location: state)
 *   city_id  → cities.id     (location: city)
 *
 * SPATIAL NOTE:
 *   latitude/longitude stored as DECIMAL(10,7) — industry standard
 *   for GPS coordinates. 7 decimal places gives ~1cm precision.
 *   Alternative: Use MySQL POINT type with spatial index for
 *   native geo queries (upgrade path for high-traffic scale).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parkings', function (Blueprint $table) {

            $table->id();

            // ── OWNER ─────────────────────────────────────────────────────
            /**
             * The user who owns this parking location.
             * Must have the 'owner' role in the RBAC system.
             *
             * RESTRICT on delete: prevents accidentally deleting a user
             * who still has registered parking locations.
             * Admin must transfer or remove the parking first.
             */
            $table->unsignedBigInteger('owner_id')
                  ->comment('FK → users.id — the parking facility owner');
            $table->foreign('owner_id')
                  ->references('id')->on('users')
                  ->onDelete('restrict');

            // ── LOCATION ──────────────────────────────────────────────────
            /**
             * State reference — for state-level filtering.
             * RESTRICT: don't delete a state that still has parkings.
             */
            $table->unsignedBigInteger('state_id')
                  ->comment('FK → states.id');
            $table->foreign('state_id')
                  ->references('id')->on('states')
                  ->onDelete('restrict');

            /**
             * City reference — primary location filter for user search.
             * RESTRICT: don't delete a city that still has parkings.
             */
            $table->unsignedBigInteger('city_id')
                  ->comment('FK → cities.id');
            $table->foreign('city_id')
                  ->references('id')->on('cities')
                  ->onDelete('restrict');

            // ── DETAILS ───────────────────────────────────────────────────
            /**
             * Public display name for this parking location.
             * Shown in search results and booking screens.
             * e.g. "Green Valley Multi-Level Parking"
             */
            $table->string('name')
                  ->comment('Display name of the parking location');

            /**
             * Optional longer description.
             * e.g. "24-hour secured parking near City Mall with CCTV"
             * Nullable — owners may skip this initially.
             */
            $table->text('description')->nullable()
                  ->comment('Optional detailed description for the app listing');

            /**
             * Full street address.
             * e.g. "Plot 12, Sector 18, Gurugram, Haryana 122015"
             * Shown in app detail screen and used as fallback if GPS fails.
             */
            $table->text('address')
                  ->comment('Full street address of the parking location');

            // ── GPS COORDINATES ───────────────────────────────────────────
            /**
             * GPS latitude.
             * DECIMAL(10,7): range −90 to +90, precision to ~1.1cm.
             * Standard for storing lat/lng in relational databases.
             * Example: 28.4594965 (Gurugram, Haryana)
             */
            $table->decimal('latitude', 10, 7)
                  ->comment('GPS latitude for map pin display and proximity search');

            /**
             * GPS longitude.
             * DECIMAL(10,7): range −180 to +180, precision to ~1.1cm.
             * Example: 77.0266383 (Gurugram, Haryana)
             */
            $table->decimal('longitude', 10, 7)
                  ->comment('GPS longitude for map pin display and proximity search');

            // ── CAPACITY ──────────────────────────────────────────────────
            /**
             * Total physical slot count across all vehicle types.
             * Denormalized here for quick display in search results
             * (avoids a COUNT query on parking_slots every time).
             *
             * Keep in sync with actual ParkingSlot records via
             * observers or service layer when slots are added/removed.
             *
             * FUTURE: Consider replacing with a computed/virtual column.
             */
            $table->unsignedInteger('total_slots')->default(0)
                  ->comment('Total slot count — denormalized for quick reads; keep in sync with parking_slots');

            // ── STATUS ────────────────────────────────────────────────────
            /**
             * Operational status of this parking location.
             *
             *   pending  → Submitted by owner, awaiting admin approval
             *   active   → Live and bookable by users
             *   inactive → Temporarily closed or disabled by admin/owner
             */
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')
                  ->comment('pending = awaiting approval | active = live | inactive = disabled');

            // Soft deletes — removed parkings are archived, not destroyed.
            // Historical bookings referencing this parking remain intact.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────
            // Composite index for the most common search query:
            // "active parkings in city X"
            $table->index(['city_id', 'status'], 'idx_parkings_city_status');

            // Index for owner dashboard: "all my parkings"
            $table->index(['owner_id', 'status'], 'idx_parkings_owner_status');

            // Index for GPS proximity queries (Haversine formula scans these)
            $table->index(['latitude', 'longitude'], 'idx_parkings_location');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('parkings');
    }
};