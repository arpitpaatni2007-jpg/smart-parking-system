<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CMSPageResource
 *
 * Transforms a CMSPage model into a structured JSON response
 * for the Flutter app's Info/Settings and legal content screens.
 *
 * TWO RESPONSE MODES via the same resource:
 *   List  (index): slug, title, is_active — no content body (keeps list response lightweight)
 *   Detail (show): all fields including full content body
 *
 * The controller controls which mode is used by passing a second
 * constructor argument: new CMSPageResource($page, true) for full detail.
 * We achieve this cleanly using the $this->with() pattern below, or
 * simply by always including content (it's static text — not expensive).
 *
 * CONTENT FORMAT NOTE:
 *   The content field stores HTML or Markdown.
 *   The Flutter app renders it in a WebView or a Markdown widget.
 *   The API does not transform or strip the content — delivered as-is.
 */
class CMSPageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'    => $this->id,

            /**
             * URL-safe unique identifier.
             * e.g. "privacy-policy", "about-us", "terms-and-conditions"
             * Used by Flutter to build deep links and cache pages locally.
             */
            'slug'  => $this->slug,

            /**
             * Human-readable page title.
             * Shown as the screen/page heading in Flutter.
             * e.g. "Privacy Policy", "About Us"
             */
            'title' => $this->title,

            // ── CONTENT ────────────────────────────────────────────────────
            /**
             * Full page body — HTML or Markdown.
             * Delivered as-is; Flutter decides how to render.
             * Included in both list and detail responses since CMS pages
             * are small static documents (not large datasets).
             *
             * FUTURE: If pages become very large, move content to the
             * show() response only and exclude from index() by passing
             * $withoutFields = ['content'] to the resource constructor.
             */
            'content' => $this->content,

            // ── STATUS ─────────────────────────────────────────────────────
            /**
             * Whether this page is publicly visible.
             * Cast to boolean by the model — true/false in JSON.
             * Flutter uses this to show/hide pages from the settings menu.
             */
            'is_active' => (bool) $this->is_active,

            // ── TIMESTAMPS ─────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}