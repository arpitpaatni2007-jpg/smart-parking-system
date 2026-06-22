<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * CMSPage Model
 *
 * Manages static content pages for the application — things like
 * "About Us", "Privacy Policy", "Terms & Conditions", "Help/FAQ".
 *
 * These pages are typically shown in:
 *   - The mobile app's Info/Settings section
 *   - The web frontend footer links
 *   - Legal compliance sections
 *
 * The `slug` field is a URL-friendly version of the title:
 *   "Privacy Policy"  →  "privacy-policy"
 *   "About Us"        →  "about-us"
 *
 * USAGE EXAMPLE:
 *   // In a Flutter API response:
 *   CMSPage::active()->where('slug', 'privacy-policy')->firstOrFail()
 *
 * FUTURE SCALABILITY:
 *   - Add `meta_title` and `meta_description` for SEO
 *   - Add `locale` for multi-language CMS pages
 *   - Add `sort_order` to control page listing sequence
 *   - Add `published_at` for scheduled publishing
 *   - Add a polymorphic `author_id` to track who last edited the page
 */
class CMSPage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Explicit table name.
     */
    protected $table = 'cms_pages';

    /**
     * Mass-assignable fields.
     */
    protected $fillable = [
        'slug',       // URL-safe identifier e.g. 'privacy-policy'
        'title',      // Display title e.g. 'Privacy Policy'
        'content',    // Full HTML or Markdown content body
        'is_active',  // Whether this page is publicly visible
    ];

    /**
     * Type casts.
     * Cast `is_active` to boolean so comparisons work naturally:
     *   if ($page->is_active) { ... }  ← works without == 1 check
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // BOOT — automatic slug generation
    // ─────────────────────────────────────────────

    /**
     * Boot the model and register event hooks.
     *
     * 'creating' fires before INSERT — auto-generates slug from title
     * if the slug wasn't explicitly provided.
     *
     * Example: title = "About Us" → slug = "about-us"
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($page) {
            // Only auto-generate slug if none was provided
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    /**
     * Scope: only publicly visible pages.
     * Usage: CMSPage::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─────────────────────────────────────────────
    // ROUTE MODEL BINDING (OPTIONAL)
    // ─────────────────────────────────────────────

    /**
     * Override the default route-model binding key.
     * By default Laravel binds by `id`, but CMS pages are
     * more naturally accessed by slug:
     *
     *   Route::get('/pages/{page}', ...)
     *   → automatically fetches CMSPage where slug = $slug
     *
     * Usage in routes: Route::get('/pages/{page:slug}', ...)
     * Or override here to make slug the default everywhere.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}