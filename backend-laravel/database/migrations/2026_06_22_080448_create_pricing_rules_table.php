<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_pricing_rules_table
 *
 * Stores vehicle-type-specific pricing configurations.
 * Each row defines what a particular vehicle type is charged,
 * and how the charge is calculated (hourly, daily, or monthly).
 *
 * FOREIGN KEY:
 *   vehicle_type_id → vehicle_types.id
 *   Uses CASCADE on delete: if a vehicle type is deleted, its pricing
 *   rules are also deleted to prevent orphaned records.
 *
 *   ALTERNATIVE: Use RESTRICT if you want to prevent deleting a vehicle
 *   type that still has active pricing rules — safer in production.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {

            $table->id();

            /**
             * Foreign key to vehicle_types table.
             * unsignedBigInteger matches the `id` (bigIncrements) in vehicle_types.
             * constrained() auto-detects table name from the column name.
             *
             * onDelete('cascade'): deleting a vehicle type removes its pricing rules.
             * Change to onDelete('restrict') for stricter referential integrity.
             */
            $table->unsignedBigInteger('vehicle_type_id')
                  ->comment('References vehicle_types.id — defines which vehicle this price applies to');

            $table->foreign('vehicle_type_id')
                  ->references('id')
                  ->on('vehicle_types')
                  ->onDelete('cascade');

            /**
             * Pricing strategy / billing cycle.
             * ENUM prevents invalid values from being stored.
             *
             *   hourly  → Per-hour billing (most common for short-stay parking)
             *   daily   → Flat per-day rate
             *   monthly → Subscription-style monthly rate
             */
            $table->enum('pricing_type', ['hourly', 'daily', 'monthly'])
                  ->comment('Billing cycle: hourly | daily | monthly');

            /**
             * Base price — the first unit charge.
             * For hourly: price of the first hour.
             * For daily/monthly: the flat rate.
             *
             * DECIMAL(10,2): supports values up to 99,999,999.99
             * Suitable for INR and most global currencies.
             * unsigned() prevents accidental negative prices.
             */
            $table->decimal('base_price', 10, 2)->unsigned()
                  ->comment('First-hour price (hourly) or flat rate (daily/monthly)');

            /**
             * Extra hour price — charge for every hour AFTER the first.
             * Only relevant for 'hourly' pricing type.
             * Set to 0.00 for daily/monthly (not applicable).
             *
             * Default 0.00 makes it optional in daily/monthly scenarios.
             */
            $table->decimal('extra_hour_price', 10, 2)->unsigned()->default(0.00)
                  ->comment('Charge per additional hour beyond the base; 0 for daily/monthly');

            /**
             * Activation status.
             * Only 'active' rules are used during booking price calculation.
             */
            $table->enum('status', ['active', 'inactive'])->default('active')
                  ->comment('Whether this pricing rule is currently applied during bookings');

            // Soft deletes — retain historical pricing for old bookings
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ──────────────────────────────────────────────────────
            // Composite index: speeds up lookups like
            // "find active hourly rule for vehicle_type_id = 2"
            $table->index(['vehicle_type_id', 'pricing_type', 'status'], 'idx_pricing_lookup');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};