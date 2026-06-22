<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_cms_pages_table
 *
 * Stores admin-managed static content pages such as:
 *   Privacy Policy, Terms & Conditions, About Us, Help/FAQ, etc.
 *
 * These pages are fetched by the Flutter app and rendered as
 * WebView or formatted text screens.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {

            $table->id();

            /**
             * URL-friendly unique identifier for this page.
             * Auto-generated from title in the Model's boot() method
             * but can also be set manually.
             *
             * Examples: 'privacy-policy', 'terms-and-conditions', 'about-us'
             *
             * unique() → prevents two pages with the same URL slug,
             * which would cause routing conflicts.
             */
            $table->string('slug')->unique()
                  ->comment('URL-safe identifier used in API routes (e.g. privacy-policy)');

            /**
             * Human-readable page title shown at the top of the page.
             * e.g. "Privacy Policy", "About Us"
             */
            $table->string('title')
                  ->comment('Display title of the CMS page');

            /**
             * The full page body content.
             * LONGTEXT supports up to ~4GB — more than enough for any page.
             * Can store:
             *   - Raw HTML (if rendered in a WebView)
             *   - Markdown (if the app renders it via a markdown library)
             *   - Plain text
             *
             * FUTURE: Add a `content_type` ENUM('html', 'markdown', 'plain')
             *         so the app knows how to render it.
             */
            $table->longText('content')
                  ->comment('Full page body — HTML, Markdown, or plain text');

            /**
             * Visibility toggle.
             * false (0) = draft / hidden → not returned by public API
             * true  (1) = published     → visible to app users
             *
             * Default true: new pages are live unless explicitly hidden.
             * Change to default(false) if you want a "draft first" workflow.
             */
            $table->boolean('is_active')->default(true)
                  ->comment('Whether this page is publicly visible in the app');

            // Soft deletes: removes from public view without permanent data loss.
            // Allows restoring accidentally deleted pages.
            $table->softDeletes();

            $table->timestamps();

            // ── INDEXES ──────────────────────────────────────────────────────
            // Index on is_active for fast "fetch all active pages" queries
            $table->index('is_active', 'idx_cms_active');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};