<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_commission_settings_table
 *
 * Creates the commission_settings table which stores platform revenue
 * sharing rules between the platform operator and parking facility owners.
 *
 * DESIGN NOTE:
 *   Using DECIMAL(5,2) for percentages allows values like 99.99
 *   without floating-point precision issues you'd get with FLOAT.
 */
return new class extends Migration
{
    /**
     * Run the migrations — create the table.
     */
    public function up(): void
    {
        Schema::create('commission_settings', function (Blueprint $table) {

            // Primary key — auto-incrementing unsigned big integer
            $table->id();

            /**
             * Platform commission percentage.
             * DECIMAL(5,2): stores up to 999.99 (more than enough for %)
             * e.g. 20.00 means the platform takes 20% of each booking.
             * unsigned() ensures no negative values are stored.
             */
            $table->decimal('commission_percent', 5, 2)->unsigned()
                  ->comment('Percentage the platform retains from each booking');

            /**
             * Parking owner's share percentage.
             * Should always be (100 - commission_percent) but stored separately
             * for quick reads without calculation overhead.
             */
            $table->decimal('owner_share_percent', 5, 2)->unsigned()
                  ->comment('Percentage paid out to the parking facility owner');

            /**
             * Status flag — controls whether this rule is currently in use.
             * ENUM restricts values to a safe set (no freeform strings).
             * Default 'active' so newly inserted records are immediately usable.
             *
             * FUTURE: Add 'archived' for historical audit trail.
             */
            $table->enum('status', ['active', 'inactive'])->default('active')
                  ->comment('Whether this commission rule is currently applied');

            // Soft deletes: marks records as deleted without removing them.
            // Useful for audit trails — you can always see what rate was active.
            $table->softDeletes();

            // Laravel's standard created_at / updated_at timestamp columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration — drop the table.
     * Called when you run: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_settings');
    }
};