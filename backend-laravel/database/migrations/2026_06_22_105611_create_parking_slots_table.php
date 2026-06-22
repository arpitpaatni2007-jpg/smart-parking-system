<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_parking_slots_table
 *
 * Individual bookable slot units within a parking location.
 * Each row represents one physical parking bay/space.
 *
 * FOREIGN KEYS:
 *   parking_id      → parkings.id       (which parking this slot belongs to)
 *   vehicle_type_id → vehicle_types.id  (what vehicle type this slot accepts)
 *
 * STATUS is the most frequently updated column in the entire system —
 * it changes with every booking create/complete/cancel event.
 * Index it accordingly.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $table) {

            $table->id();

            /**
             * The parking location this slot belongs to.
             * CASCADE: removing a parking removes all its slots.
             * Historical bookings referencing this slot are handled
             * by the Booking module's FK constraint (set to RESTRICT or SET NULL).
             */
            $table->unsignedBigInteger('parking_id')
                  ->comment('FK → parkings.id');
            $table->foreign('parking_id')
                  ->references('id')->on('parkings')
                  ->onDelete('cascade');

            /**
             * Vehicle type this slot is designed for.
             * RESTRICT: prevent deleting a vehicle type that has
             * slots assigned to it. Admin must reassign first.
             */
            $table->unsignedBigInteger('vehicle_type_id')
                  ->comment('FK → vehicle_types.id — defines which vehicle category can use this slot');
            $table->foreign('vehicle_type_id')
                  ->references('id')->on('vehicle_types')
                  ->onDelete('restrict');

            /**
             * Physical identifier printed/displayed on the slot sign.
             * Examples: "A1", "B-03", "EV-01", "HC-2"
             *
             * Unique per parking (not globally) — "A1" can exist in
             * multiple parkings, but not twice in the same one.
             * Composite unique enforced below.
             */
            $table->string('slot_number')
                  ->comment('Physical slot label e.g. A1, B-03, EV-01 — unique within each parking');

            /**
             * Slot category / type of space.
             *
             *   standard    → Regular parking space (most common)
             *   premium     → Covered, wider, or specially located space
             *   ev          → Equipped with EV charging hardware
             *   handicapped → Wider space near entrance for accessibility
             *
             * FUTURE: Add 'valet', 'motorcycle_bay', 'oversized'
             */
            $table->enum('slot_type', ['standard', 'premium', 'ev', 'handicapped'])
                  ->default('standard')
                  ->comment('Type of parking space: standard | premium | ev | handicapped');

            /**
             * Real-time occupancy status of this slot.
             *
             *   available   → Empty, can be booked immediately
             *   booked      → Currently occupied by an active booking
             *   reserved    → Held by owner for VIP/special use
             *   maintenance → Temporarily out of service
             *
             * This column is updated frequently — optimistic locking
             * or row-level locking should be used in the booking service
             * to prevent double-booking race conditions.
             */
            $table->enum('status', ['available', 'booked', 'reserved', 'maintenance'])
                  ->default('available')
                  ->comment('Real-time status: available | booked | reserved | maintenance');

            // Soft deletes: decommissioned slots are archived, not deleted.
            // Preserves referential integrity with past bookings.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────

            /**
             * Unique slot number per parking.
             * Prevents creating two "A1" slots in the same parking.
             */
            $table->unique(['parking_id', 'slot_number'], 'unique_slot_per_parking');

            /**
             * Composite index for the most common availability query:
             * "find available car slots in parking X"
             * WHERE parking_id = ? AND vehicle_type_id = ? AND status = 'available'
             */
            $table->index(
                ['parking_id', 'vehicle_type_id', 'status'],
                'idx_slots_availability'
            );

            /**
             * Index on status alone for admin dashboard queries:
             * "how many slots are currently booked across all parkings?"
             */
            $table->index('status', 'idx_slots_status');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_slots');
    }
};