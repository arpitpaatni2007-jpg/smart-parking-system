<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_parking_facility_mappings_table
 *
 * Pivot (junction) table connecting parkings ↔ parking_facilities.
 * Implements the many-to-many relationship between them.
 *
 * EXAMPLE DATA:
 *   parking_id=1, parking_facility_id=2  → "Green Valley" has "CCTV"
 *   parking_id=1, parking_facility_id=5  → "Green Valley" has "EV Charging"
 *   parking_id=2, parking_facility_id=2  → "City Parking" also has "CCTV"
 *
 * UNIQUE CONSTRAINT:
 *   The composite unique index on (parking_id, parking_facility_id)
 *   prevents the same facility being linked to the same parking twice.
 *   This is the correct way to handle pivot table deduplication.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parking_facility_mappings', function (Blueprint $table) {

            $table->id();

            /**
             * The parking that offers this facility.
             * CASCADE: if a parking is hard-deleted, its facility
             * associations are automatically removed too.
             */
            $table->unsignedBigInteger('parking_id')
                  ->comment('FK → parkings.id');
            $table->foreign('parking_id')
                  ->references('id')->on('parkings')
                  ->onDelete('cascade');

            /**
             * The facility being offered by this parking.
             * CASCADE: if a facility type is removed from the master list,
             * all parking associations with it are also cleaned up.
             */
            $table->unsignedBigInteger('parking_facility_id')
                  ->comment('FK → parking_facilities.id');
            $table->foreign('parking_facility_id')
                  ->references('id')->on('parking_facilities')
                  ->onDelete('cascade');

            $table->timestamps();

            // ── UNIQUE CONSTRAINT ─────────────────────────────────────────
            /**
             * Prevent duplicate mappings — a parking can't have the same
             * facility linked twice. This is the DB-level guard against
             * accidental double-inserts.
             *
             * e.g. parking_id=1 + parking_facility_id=2 can only exist once.
             */
            $table->unique(
                ['parking_id', 'parking_facility_id'],
                'unique_parking_facility'
            );
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_facility_mappings');
    }
};