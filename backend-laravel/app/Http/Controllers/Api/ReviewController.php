<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Parking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * ReviewController
 * ============================================================
 *
 * Manages the complete review and rating lifecycle for parking
 * locations in the Smart Parking Management System.
 *
 * REVIEW FLOW:
 *   1. Customer completes a booking (checked_out → status: completed)
 *   2. App prompts user to rate their experience
 *   3. Customer submits → store()
 *   4. Review is created with is_approved = true (auto-approved by default)
 *      OR is_approved = false (if admin moderation is enabled)
 *   5. Review appears on the parking detail screen
 *   6. Customer can update their review → update()
 *   7. Admin can hide/show a review → update() with is_approved
 *   8. Customer or admin can delete → destroy()
 *
 * ENDPOINTS:
 *   GET    /api/v1/parkings/{parking}/reviews     → index  (public)
 *   POST   /api/v1/reviews                        → store  (customer only)
 *   GET    /api/v1/reviews/{review}               → show   (auth required)
 *   PUT    /api/v1/reviews/{review}               → update (author or admin)
 *   DELETE /api/v1/reviews/{review}               → destroy (author or admin)
 *   GET    /api/v1/my-reviews                     → myReviews (customer)
 *   PATCH  /api/v1/reviews/{review}/approve       → approve (admin)
 *   PATCH  /api/v1/reviews/{review}/hide          → hide (admin)
 *
 * ROLE-BASED BEHAVIOR:
 *   Customer   → Can create, read, update, delete their own reviews.
 *   Owner      → Can read reviews for their parking locations.
 *   Admin      → Can read all reviews, approve/hide/delete any review.
 *   Public     → Can read approved reviews for a parking (index).
 *
 * RATING CALCULATION:
 *   Parking average rating is computed on-the-fly from the reviews
 *   table — not stored as a column — ensuring it's always accurate.
 *   Future optimization: cache the average rating with a 5-minute TTL.
 *
 * FUTURE SCALABILITY:
 *   - Add review images (photo reviews) with S3 storage in Phase 3.
 *   - Add "owner reply" feature: parking owners can respond to reviews.
 *   - Add review helpfulness voting ("Was this review helpful?").
 *   - Add automatic review prompts via FCM notification 2 hours after
 *     checkout (scheduled via Laravel queue).
 *   - Add profanity filter on comment text before storage.
 */
class ReviewController extends Controller
{
    /**
     * GET /api/v1/parkings/{parking}/reviews
     *
     * Return a paginated list of approved reviews for a specific
     * parking location.
     *
     * This endpoint is accessible to anyone (public) because
     * reviews are visible on the parking detail screen to all
     * users — even before they log in.
     *
     * QUERY PARAMETERS:
     *   ?rating=5           → filter by star rating (1–5)
     *   ?sort=latest        → sort by newest first (default)
     *   ?sort=highest       → sort by highest rating first
     *   ?sort=lowest        → sort by lowest rating first
     *   ?per_page=10
     *
     * ADMIN VIEW:
     *   Admins can additionally pass ?approved=false to see
     *   hidden/unapproved reviews for moderation.
     */
    public function index(Request $request, Parking $parking): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user?->role?->name, ['super_admin', 'admin']);
        $isOwner = $user?->role?->name === 'parking_owner'
            && $parking->owner_id === $user->id;

        // ── Base Query ────────────────────────────────────────────
        $query = Review::query()
            ->where('parking_id', $parking->id)
            ->with([
                'user:id,name,profile_photo',
                'booking:id,booking_number,booking_start_time',
            ]);

        // ── Visibility Scope ──────────────────────────────────────
        // Public and customers only see approved reviews.
        // Admins and the parking owner can see all reviews
        // (including hidden/unapproved) for moderation.
        if ($isAdmin || $isOwner) {
            // Admin/Owner: optionally filter by approval status.
            if ($request->filled('approved')) {
                $query->where('is_approved', $request->boolean('approved'));
            }
            // Default: show all if no filter given.
        } else {
            // Everyone else: only approved reviews.
            $query->where('is_approved', true);
        }

        // ── Rating Filter ─────────────────────────────────────────
        if ($request->filled('rating')) {
            $query->where('rating', $request->integer('rating'));
        }

        // ── Sorting ───────────────────────────────────────────────
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'highest' => $query->orderBy('rating', 'desc')->orderBy('created_at', 'desc'),
            'lowest'  => $query->orderBy('rating', 'asc')->orderBy('created_at', 'desc'),
            default   => $query->orderBy('created_at', 'desc'), // 'latest'
        };

        // ── Rating Summary (for the parking detail header) ────────
        // Computed with a single aggregation query — efficient.
        $ratingSummary = Review::where('parking_id', $parking->id)
            ->where('is_approved', true)
            ->select(
                DB::raw('COUNT(*) as total_reviews'),
                DB::raw('AVG(rating) as average_rating'),
                DB::raw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star'),
                DB::raw('SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star'),
                DB::raw('SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star'),
                DB::raw('SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star'),
                DB::raw('SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star')
            )
            ->first();

        // ── Paginate ──────────────────────────────────────────────
        $perPage = min($request->integer('per_page', 10), 50);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Reviews retrieved successfully.',
            'data'    => [
                'parking'        => [
                    'id'   => $parking->id,
                    'name' => $parking->name,
                ],
                'rating_summary' => [
                    'average_rating' => $ratingSummary->total_reviews > 0
                        ? round((float) $ratingSummary->average_rating, 1)
                        : 0,
                    'total_reviews'  => (int) $ratingSummary->total_reviews,
                    'breakdown'      => [
                        5 => (int) $ratingSummary->five_star,
                        4 => (int) $ratingSummary->four_star,
                        3 => (int) $ratingSummary->three_star,
                        2 => (int) $ratingSummary->two_star,
                        1 => (int) $ratingSummary->one_star,
                    ],
                ],
                'reviews'        => ReviewResource::collection($reviews)->response()->getData(true),
            ],
        ], 200);
    }

    /**
     * POST /api/v1/reviews
     *
     * Submit a new review for a parking location.
     *
     * WHO CAN USE THIS:
     *   Only customers with a COMPLETED booking at the parking.
     *   All business rules (booking ownership, completion status,
     *   one-review-per-parking) are enforced in StoreReviewRequest.
     *
     * AUTO-APPROVAL:
     *   By default, reviews are auto-approved (is_approved = true).
     *   Change the default to false if manual moderation is required.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only customers can write reviews.
        if ($user->role?->name !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Only customers can submit parking reviews.',
                'data'    => null,
            ], 403);
        }

        $review = Review::create([
            'user_id'     => $user->id,
            'parking_id'  => $request->integer('parking_id'),
            'booking_id'  => $request->integer('booking_id'),
            'rating'      => $request->integer('rating'),
            'comment'     => $request->input('comment'),
            // Auto-approve reviews. Set to false to enable moderation queue.
            'is_approved' => true,
        ]);

        // Load relationships for the response.
        $review->load([
            'user:id,name,profile_photo',
            'parking:id,name',
            'booking:id,booking_number,booking_start_time',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. Thank you for your feedback!',
            'data'    => new ReviewResource($review),
        ], 201);
    }

    /**
     * GET /api/v1/reviews/{review}
     *
     * Return a single review's full detail.
     *
     * WHO CAN SEE THIS:
     *   - Any authenticated user can view an approved review.
     *   - Admins can view any review (approved or not).
     *   - The review's author can always view their own review.
     */
    public function show(Request $request, Review $review): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user?->role?->name, ['super_admin', 'admin']);
        $isAuthor = $user?->id === $review->user_id;

        // Unapproved reviews are private — only admin or author can see them.
        if (!$review->is_approved && !$isAdmin && !$isAuthor) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
                'data'    => null,
            ], 404);
        }

        $review->load([
            'user:id,name,profile_photo',
            'parking:id,name,address',
            'booking:id,booking_number,booking_start_time',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review retrieved successfully.',
            'data'    => new ReviewResource($review),
        ], 200);
    }

    /**
     * PUT /api/v1/reviews/{review}
     *
     * Update an existing review.
     *
     * CUSTOMERS can update:
     *   - rating   (their star rating)
     *   - comment  (their written feedback)
     *
     * ADMINS can additionally update:
     *   - is_approved (show or hide the review)
     *
     * RESTRICTION:
     *   Customers can only update their OWN reviews.
     *   Admins can update any review.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);
        $isAuthor = $user->id === $review->user_id;

        // Authorization: must be the author or an admin.
        if (!$isAdmin && !$isAuthor) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this review.',
                'data'    => null,
            ], 403);
        }

        // Build the update payload.
        $updateData = [];

        if ($request->has('rating')) {
            $updateData['rating'] = $request->integer('rating');
        }

        if ($request->has('comment')) {
            $updateData['comment'] = $request->input('comment');
        }

        // Only admins can change is_approved.
        // We strip this field from non-admin requests as an extra safety net
        // (the request class already rejects it with a validation error,
        // but this prevents any edge-case bypass).
        if ($isAdmin && $request->has('is_approved')) {
            $updateData['is_approved'] = $request->boolean('is_approved');
        }

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid fields provided to update.',
                'data'    => null,
            ], 422);
        }

        $review->update($updateData);

        $review->refresh()->load([
            'user:id,name,profile_photo',
            'parking:id,name',
            'booking:id,booking_number,booking_start_time',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data'    => new ReviewResource($review),
        ], 200);
    }

    /**
     * DELETE /api/v1/reviews/{review}
     *
     * Delete a review.
     *
     * WHO CAN DELETE:
     *   - The review's author (customer can delete their own review)
     *   - Admin (can delete any review for content moderation)
     *
     * We do a hard delete here — reviews are not financial records
     * so preserving them after user deletion is not required.
     * If audit trail is needed later, add SoftDeletes to the model.
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        $user     = $request->user();
        $isAdmin  = in_array($user->role?->name, ['super_admin', 'admin']);
        $isAuthor = $user->id === $review->user_id;

        if (!$isAdmin && !$isAuthor) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this review.',
                'data'    => null,
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
            'data'    => null,
        ], 200);
    }

    /**
     * GET /api/v1/my-reviews
     *
     * Return a paginated list of all reviews written by the
     * currently authenticated customer.
     *
     * Used in the "My Reviews" section of the User App profile.
     *
     * QUERY PARAMETERS:
     *   ?per_page=10
     */
    public function myReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        $reviews = Review::where('user_id', $user->id)
            ->with([
                'parking:id,name,address',
                'booking:id,booking_number,booking_start_time',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(min($request->integer('per_page', 10), 50));

        return response()->json([
            'success' => true,
            'message' => 'Your reviews retrieved successfully.',
            'data'    => ReviewResource::collection($reviews)->response()->getData(true),
        ], 200);
    }

    /**
     * PATCH /api/v1/reviews/{review}/approve
     *
     * Admin: approve a hidden/pending review so it becomes
     * publicly visible on the parking detail screen.
     *
     * Used in the Admin Panel's Review Moderation section.
     */
    public function approve(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve reviews.',
                'data'    => null,
            ], 403);
        }

        if ($review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This review is already approved.',
                'data'    => null,
            ], 422);
        }

        $review->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Review approved. It is now publicly visible.',
            'data'    => new ReviewResource($review->refresh()),
        ], 200);
    }

    /**
     * PATCH /api/v1/reviews/{review}/hide
     *
     * Admin: hide an approved review (e.g. for inappropriate content).
     * The review is NOT deleted — it is marked is_approved = false
     * so it disappears from public view but remains in the database.
     *
     * Used in the Admin Panel's Review Moderation section.
     */
    public function hide(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to hide reviews.',
                'data'    => null,
            ], 403);
        }

        if (!$review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This review is already hidden.',
                'data'    => null,
            ], 422);
        }

        $review->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Review hidden. It is no longer publicly visible.',
            'data'    => new ReviewResource($review->refresh()),
        ], 200);
    }
}