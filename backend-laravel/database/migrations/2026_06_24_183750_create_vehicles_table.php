<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_vehicles_table
 *
 * Stores user-registered vehicles used for parking bookings.
 * Each vehicle belongs to one user and has one type (car, bike, etc.)
 *
 * FOREIGN KEYS:
 *
 *   user_id         → users.id          RESTRICT
 *     Don't delete a user who has vehicles registered.
 *     Admin must handle vehicle data before closing an account.
 *     (Vehicle history is important for audits and disputes.)
 *
 *   vehicle_type_id → vehicle_types.id  RESTRICT
 *     Don't delete a vehicle type that has vehicles.
 *     Master data should be deactivated, not deleted.
 *
 * SOFT DELETES:
 *   Vehicles are soft-deleted instead of hard-deleted because:
 *   1. Booking records reference vehicle_id — hard delete breaks history
 *   2. A user might restore a "deleted" vehicle (e.g., they sold it and bought it back)
 *   3. Admins can audit which vehicles were used for which bookings
 *
 * VEHICLE NUMBER UNIQUENESS:
 *   vehicle_number is UNIQUE globally — no two users can register the
 *   same number plate. In the real world, each plate belongs to one vehicle.
 *   Soft-deleted vehicles are EXCLUDED from this unique check by default
 *   in Laravel (unique index skips soft-deleted rows with deleted_at IS NOT NULL).
 *
 *   IMPORTANT: If you need the same plate to be re-registerable after soft-delete,
 *   use a unique index that includes deleted_at:
 *     $table->unique(['vehicle_number', 'deleted_at']);
 *   But this gets complex — simpler to keep it globally unique.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {

            $table->id();

            // ── OWNERSHIP ─────────────────────────────────────────────────

            /**
             * The user who registered and owns this vehicle.
             * RESTRICT: cannot delete a user while they have registered vehicles.
             * Admin must transfer or remove vehicles before deleting the user.
             */
            $table->unsignedBigInteger('user_id')
                  ->comment('FK → users.id — the user who registered this vehicle');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('restrict');

            /**
             * The category/type of this vehicle.
             * Determines slot type assignment and pricing rule.
             * RESTRICT: cannot delete a vehicle type that has vehicles assigned to it.
             */
            $table->unsignedBigInteger('vehicle_type_id')
                  ->comment('FK → vehicle_types.id — car, bike, truck, etc.');
            $table->foreign('vehicle_type_id')
                  ->references('id')->on('vehicle_types')
                  ->onDelete('restrict');

            // ── VEHICLE DETAILS ───────────────────────────────────────────

            /**
             * Vehicle registration number (number plate).
             * In India, format is: "HR26DQ8849", "DL01AB1234"
             * Stored normalized: uppercase, no spaces (enforced by Model mutator).
             *
             * UNIQUE: No two users can register the same plate.
             * One physical vehicle = one number plate = one registration.
             */
            $table->string('vehicle_number')->unique()
                  ->comment('Registration plate e.g. HR26DQ8849 — stored uppercase, no spaces; globally unique');

            /**
             * User-given nickname for this vehicle.
             * Makes it easy to identify in dropdowns (users often own multiple cars).
             * Examples: "My Swift", "Office Car", "Wife's Activa"
             */
            $table->string('vehicle_name')
                  ->comment('User-given display name e.g. "My Swift" — shown in booking dropdown');

            /**
             * Vehicle manufacturer / brand.
             * Examples: "Maruti Suzuki", "Honda", "Tata", "Royal Enfield"
             *
             * FUTURE: Normalize this into a `vehicle_brands` master table
             * and store brand_id FK here for standardized brand filtering.
             */
            $table->string('vehicle_brand')
                  ->comment('Manufacturer name e.g. Maruti Suzuki, Honda, Royal Enfield');

            /**
             * Vehicle color — as user describes it.
             * Examples: "White", "Midnight Black", "Candy Red"
             *
             * Free-form string — not an enum since color names are subjective.
             * Useful for visual identification at the gate.
             *
             * FUTURE: Add a `color_hex` column for standardized color codes.
             */
            $table->string('vehicle_color')
                  ->comment('Vehicle body color as described by user e.g. White, Midnight Black');

            // ── STATUS ────────────────────────────────────────────────────

            /**
             * Whether this vehicle is active/usable for bookings.
             *
             *   active   → Appears in booking dropdowns; can be used for bookings
             *   inactive → Hidden from booking screens; user deactivated (e.g. sold vehicle)
             *
             * Default 'active' — newly registered vehicles are immediately usable.
             */
            $table->enum('status', ['active', 'inactive'])->default('active')
                  ->comment('active = bookable | inactive = user deactivated (e.g. vehicle sold)');

            // ── SOFT DELETES + TIMESTAMPS ─────────────────────────────────

            /**
             * Soft delete timestamp.
             * NULL = vehicle is not deleted.
             * Non-null = vehicle was removed by user/admin but kept in DB.
             *
             * WHY SOFT DELETE:
             *   booking records reference vehicle_id. Hard-deleting a vehicle
             *   would leave those booking records with a broken FK reference.
             *   Soft delete keeps the vehicle in DB (invisible to users)
             *   so booking history remains intact.
             */
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────

            /**
             * "My Vehicles" screen: load all vehicles for a user.
             * Most common query — user opens the vehicle list while booking.
             * Composite index on (user_id, status) speeds up:
             *   WHERE user_id = ? AND status = 'active'
             */
            $table->index(['user_id', 'status'], 'idx_vehicles_user_status');

            /**
             * Vehicle type filter: "show me only cars" or
             * "how many bikes are registered in the system?"
             * Also used internally for slot-type matching.
             */
            $table->index('vehicle_type_id', 'idx_vehicles_type');

            /**
             * Gate lookup: "find the booking for vehicle with this plate"
             * vehicle_number is already UNIQUE (which creates an index)
             * so a separate index here is NOT needed — the unique index suffices.
             *
             * Mentioned here for documentation: vehicle_number is always indexed.
             */
            // (No additional index needed — unique() creates an index automatically)
        });
    }

    /**
     * Reverse the migration.
     * Called when running: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};