<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_app_settings_table
 *
 * A flexible key-value configuration store.
 * All application settings that need to be admin-configurable
 * (without code changes or re-deployment) live here.
 *
 * DESIGN PATTERN: EAV (Entity-Attribute-Value) lite
 * Simple and effective for settings where you don't need
 * typed columns per attribute.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {

            $table->id();

            /**
             * The unique identifier for this setting.
             * Use dot-notation or underscores for namespacing:
             *   e.g. 'booking.grace_period', 'payment.gateway_mode'
             *
             * unique() ensures no duplicate keys — one setting per key.
             */
            $table->string('key')->unique()
                  ->comment('Unique machine-readable setting identifier (e.g. currency, app_name)');

            /**
             * The setting's current value.
             * Always stored as text — cast to correct type in application code.
             *
             * TEXT instead of VARCHAR to support longer values like JSON blobs
             * or multi-line text (e.g. terms of service snippets).
             *
             * Nullable: some settings can be intentionally unset.
             */
            $table->text('value')->nullable()
                  ->comment('Current value of the setting; always stored as string/text');

            /**
             * Logical grouping for UI organization.
             * e.g. 'general', 'booking', 'payment', 'notification', 'contact'
             *
             * Indexed for fast GROUP BY queries in admin panels.
             */
            $table->string('group')->default('general')
                  ->comment('Category/group for organizing settings in the admin panel');

            /**
             * Human-readable explanation of what this setting controls.
             * Displayed as helper text in the admin panel next to each field.
             * Nullable — optional for simple/obvious settings.
             */
            $table->string('description')->nullable()
                  ->comment('Admin-facing description of what this setting does');

            // Note: No soft deletes for settings — deletion is intentional.
            // Note: No status column — presence of the key means it's active.

            $table->timestamps();

            // ── INDEXES ──────────────────────────────────────────────────────
            // Index on `group` for loading all settings in a group at once
            $table->index('group', 'idx_settings_group');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};