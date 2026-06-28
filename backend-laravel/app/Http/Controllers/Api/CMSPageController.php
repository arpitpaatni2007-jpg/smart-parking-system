<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMSPage\StoreCMSPageRequest;
use App\Http\Requests\CMSPage\UpdateCMSPageRequest;
use App\Http\Resources\CMSPageResource;
use App\Models\CMSPage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * CMSPageController
 *
 * Manages static content pages (Privacy Policy, Terms, About Us, etc.)
 *
 * ENDPOINTS:
 *   GET    /api/v1/cms-pages              → List pages (admin: all; users: active only)
 *   POST   /api/v1/cms-pages              → Admin: create page
 *   GET    /api/v1/cms-pages/{page}       → Single page detail (resolved by slug)
 *   PUT    /api/v1/cms-pages/{page}       → Admin: update page
 *   DELETE /api/v1/cms-pages/{page}       → Admin: soft-delete page
 *   PATCH  /api/v1/cms-pages/{page}/toggle → Admin: toggle is_active
 *
 * ROUTE MODEL BINDING:
 *   CMSPage::getRouteKeyName() returns 'slug', so the {page} route
 *   parameter is resolved by slug, not id.
 *   e.g. GET /api/v1/cms-pages/privacy-policy
 *
 * ACCESS:
 *   index / show → All authenticated users (users see only active pages)
 *   store / update / destroy / toggle → Admin only
 *
 * NAMESPACE: App\Http\Controllers\Api (matches Artisan default location)
 */
class CMSPageController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List CMS Pages
    // =========================================================

    /**
     * Return a list of CMS pages.
     *
     * Admin: all pages (including inactive), with optional search.
     * Users/Owners: only active (is_active = true) pages.
     *
     * QUERY PARAMETERS:
     *   ?search=privacy      → admin: search by title or slug
     *   ?is_active=true      → admin: filter by active state
     *   ?per_page=20         → pagination (default 20, max 50)
     *
     * FLUTTER USE CASE:
     *   App Settings / Info screen loads this list to build the
     *   "Legal & Info" menu (Privacy Policy, Terms, About Us, FAQ, etc.)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user  = $request->user();
            $query = CMSPage::query();

            if ($user->hasRole('admin')) {
                // ── Admin: see all pages, with search and status filter ────
                if ($request->filled('search')) {
                    $term = '%' . $request->search . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('title', 'LIKE', $term)
                          ->orWhere('slug', 'LIKE', $term);
                    });
                }

                if ($request->has('is_active')) {
                    $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
                }
            } else {
                // ── Regular users: only active pages ──────────────────────
                $query->active();
            }

            // Order: by title alphabetically for consistent menu ordering
            $query->orderBy('title');

            $perPage = min((int) ($request->per_page ?? 20), 50);
            $pages   = $query->paginate($perPage);

            return $this->successResponse(
                CMSPageResource::collection($pages)->response()->getData(true),
                'CMS pages retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CMSPageController@index', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve pages.');
        }
    }

    // =========================================================
    // STORE — Create a CMS Page (Admin Only)
    // =========================================================

    /**
     * Create a new CMS page.
     *
     * The CMSPage model auto-generates the slug from the title
     * in its boot() method if slug is not explicitly provided.
     *
     * FLUTTER USE CASE:
     *   Admin creates "Refund Policy" → slug "refund-policy" auto-generated.
     *   Flutter app fetches: GET /api/v1/cms-pages/refund-policy
     *
     * @param  \App\Http\Requests\CMSPage\StoreCMSPageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCMSPageRequest $request): JsonResponse
    {
        try {
            // ── Admin-only guard ───────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse(
                    'Only administrators can create CMS pages.',
                    403
                );
            }

            $page = CMSPage::create([
                'title'     => $request->title,
                'slug'      => $request->slug,     // null = auto-generated by model boot()
                'content'   => $request->content,
                'is_active' => $request->boolean('is_active', true), // default: published
            ]);

            return $this->createdResponse(
                new CMSPageResource($page),
                'CMS page created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CMSPageController@store', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to create page.');
        }
    }

    // =========================================================
    // SHOW — Single CMS Page Detail
    // =========================================================

    /**
     * Return full content for a single CMS page.
     *
     * Route model binding resolves {page} by SLUG (not id) because
     * CMSPage::getRouteKeyName() returns 'slug'.
     *
     * e.g. GET /api/v1/cms-pages/privacy-policy
     *   → CMSPage::where('slug', 'privacy-policy')->firstOrFail()
     *
     * ACCESS:
     *   - Admin: can view any page (active or inactive)
     *   - Users: can only view active pages; inactive → 404
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\CMSPage      $page  (resolved by slug via route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, CMSPage $page): JsonResponse
    {
        try {
            // ── Non-admins cannot see inactive pages ───────────────────────
            if (! $request->user()->hasRole('admin') && ! $page->is_active) {
                return $this->notFoundResponse('Page not found.');
            }

            return $this->successResponse(
                new CMSPageResource($page),
                'Page retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CMSPageController@show', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve page.');
        }
    }

    // =========================================================
    // UPDATE — Update a CMS Page (Admin Only)
    // =========================================================

    /**
     * Update an existing CMS page (partial update supported).
     *
     * Only fields sent in the request are updated.
     * The slug unique rule in UpdateCMSPageRequest ignores the
     * current page's own row to prevent false conflicts.
     *
     * @param  \App\Http\Requests\CMSPage\UpdateCMSPageRequest $request
     * @param  \App\Models\CMSPage                             $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCMSPageRequest $request, CMSPage $page): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can update CMS pages.', 403);
            }

            // Build update array from only the fields that were sent
            $updateData = array_filter(
                $request->only(['title', 'slug', 'content']),
                fn ($value) => ! is_null($value)
            );

            // Handle boolean separately (array_filter strips false values)
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            if (empty($updateData)) {
                return $this->errorResponse('No update data provided.', 400);
            }

            $page->update($updateData);

            return $this->successResponse(
                new CMSPageResource($page->fresh()),
                'Page updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CMSPageController@update', [
                'page_slug' => $page->slug,
                'error'     => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update page.');
        }
    }

    // =========================================================
    // DESTROY — Soft-Delete a CMS Page (Admin Only)
    // =========================================================

    /**
     * Soft-delete a CMS page (sets deleted_at; does not hard-delete).
     *
     * WHY SOFT DELETE?
     *   Preserves audit history. A deleted page can be restored
     *   if it was removed accidentally.
     *   Soft-deleted pages are invisible to all API responses.
     *
     * SAFETY:
     *   Core legal pages (Privacy Policy, Terms) should be deactivated
     *   (is_active = false) rather than deleted. The toggle endpoint
     *   is the safer option for temporarily hiding a page.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\CMSPage      $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, CMSPage $page): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can delete CMS pages.', 403);
            }

            $page->delete();

            return $this->successResponse(null, 'Page deleted successfully.');

        } catch (Throwable $e) {
            Log::error('CMSPageController@destroy', [
                'page_slug' => $page->slug,
                'error'     => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete page.');
        }
    }

    // =========================================================
    // TOGGLE — Toggle is_active (Admin Only)
    // =========================================================

    /**
     * Toggle the is_active status of a CMS page.
     *
     * ROUTE: PATCH /api/v1/cms-pages/{page}/toggle
     *
     * Safer alternative to delete for temporarily hiding a page.
     * e.g. Hide "Promo Terms" after a campaign ends.
     *
     * Returns the updated page resource with the new is_active value.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\CMSPage      $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request, CMSPage $page): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can toggle page status.', 403);
            }

            $page->update(['is_active' => ! $page->is_active]);

            $statusLabel = $page->is_active ? 'published' : 'unpublished';

            return $this->successResponse(
                new CMSPageResource($page->fresh()),
                "Page \"{$page->title}\" has been {$statusLabel}."
            );

        } catch (Throwable $e) {
            Log::error('CMSPageController@toggle', [
                'page_slug' => $page->slug,
                'error'     => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to toggle page status.');
        }
    }
}