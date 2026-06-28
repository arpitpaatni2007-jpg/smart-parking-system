<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * ReviewResource
 * ============================================================
 *
 * Transforms a Review model into a structured JSON response.
 *
 * WHY THIS RESOURCE EXISTS:
 * The Review model links users, parkings, and bookings. Returning
 * the raw model would expose foreign key IDs without context and
 * could leak unrelated data. This resource:
 *   - Controls exactly which fields are returned
 *   - Conditionally loads relationships to prevent N+1 queries
 *   - Computes display-friendly values (star label, time ago)
 *   - Ensures a consistent response across all review endpoints
 *
 * USAGE:
 *   return new ReviewResource($review);
 *   return ReviewResource::collection($reviews);
 *
 * FUTURE SCALABILITY:
 *   - Add `owner_reply` field when parking owners can respond
 *     to reviews (store in a review_replies table).
 *   - Add `helpful_count` when a "Mark as helpful" feature is added.
 *   - Add `images` whenLoaded() when photo reviews are supported.
 *   - Add `is_verified_booking` to confirm the reviewer actually
 *     parked there (already derived from booking_id relation).
 */
class ReviewResource extends JsonResource
{
    /**
     * Transform the Review model into a JSON-friendly array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── Core Identity ──────────────────────────────────────
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'parking_id'  => $this->parking_id,
            'booking_id'  => $this->booking_id,

            // ── Review Content ────────────────────────────────────
            'rating'      => (int) $this->rating,          // 1–5 integer
            'comment'     => $this->comment,

            // ── Moderation Status ─────────────────────────────────
            // is_approved: true  → visible publicly on parking page
            // is_approved: false → hidden (pending admin approval or reported)
            'is_approved' => (bool) $this->is_approved,

            // ── Timestamps ────────────────────────────────────────
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),

            // ── Computed Display Helpers ──────────────────────────
            // These save the mobile app from having to compute
            // display-ready values from raw data.
            'rating_label' => $this->getRatingLabel(),   // "Excellent", "Good", etc.
            'time_ago'     => $this->created_at?->diffForHumans(), // "2 days ago"
            'can_edit'     => $this->canEdit($request),  // whether requester can edit

            // ── Conditionally Loaded Relationships ────────────────
            // Only appear in the response when the controller has
            // eager-loaded them. Avoids silent N+1 queries.

            // The user who wrote this review.
            // Only show name and avatar — never email or phone.
            'user' => $this->whenLoaded('user', fn () => [
                'id'            => $this->user->id,
                'name'          => $this->user->name,
                'profile_photo' => $this->user->profile_photo ?? null,
            ]),

            // The parking location this review is for.
            // Useful when listing reviews from a user's profile page
            // ("Reviews you've written" screen).
            'parking' => $this->whenLoaded('parking', fn () => [
                'id'      => $this->parking->id,
                'name'    => $this->parking->name,
                'address' => $this->parking->address,
                'city'    => $this->parking->city?->name,
            ]),

            // The booking that verified this review.
            // Confirms the reviewer actually used the parking.
            'booking' => $this->whenLoaded('booking', fn () => [
                'id'             => $this->booking->id,
                'booking_number' => $this->booking->booking_number,
                'visit_date'     => $this->booking->booking_start_time
                    ?->toDateString(),
            ]),
        ];
    }

    /**
     * Return a human-readable label for the numeric rating.
     * Used on the parking detail screen alongside the star display.
     *
     * @return string
     */
    private function getRatingLabel(): string
    {
        return match ((int) $this->rating) {
            5       => 'Excellent',
            4       => 'Good',
            3       => 'Average',
            2       => 'Poor',
            1       => 'Very Poor',
            default => 'Not Rated',
        };
    }

    /**
     * Determine whether the currently authenticated user can edit
     * or delete this review.
     *
     * TRUE when:
     *   - The user is the review's author (user_id matches), OR
     *   - The user is an admin or super_admin
     *
     * This powers the edit/delete button visibility in the app.
     */
    private function canEdit(Request $request): bool
    {
        $authUser = $request->user();

        if (!$authUser) {
            return false;
        }

        if ($authUser->id === $this->user_id) {
            return true;
        }

        return in_array($authUser->role?->name, ['super_admin', 'admin']);
    }
}