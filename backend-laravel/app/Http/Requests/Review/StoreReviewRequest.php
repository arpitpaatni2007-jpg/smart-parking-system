<?php

namespace App\Http\Requests\Review;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * ============================================================
 * StoreReviewRequest
 * ============================================================
 *
 * Validates all incoming data when a customer submits a new
 * review for a parking location.
 *
 * VALIDATION LAYERS:
 *   1. Field-level rules  → type, required, range
 *   2. Business rules     → via withValidator() after field rules pass
 *
 * BUSINESS RULES ENFORCED:
 *   1. The booking_id must belong to the authenticated user.
 *   2. The booking must be in "completed" status — only customers
 *      who have actually parked can leave a review.
 *   3. The booking.parking_id must match the submitted parking_id.
 *   4. A user can only submit ONE review per booking.
 *   5. A user can only submit ONE review per parking location.
 *
 * WHO CAN REVIEW:
 *   Only customers (role: customer) can write reviews.
 *   Owners and admins cannot review parkings they manage.
 *   This is enforced via the route middleware or inside the
 *   controller — this request focuses on data validity.
 *
 * FUTURE SCALABILITY:
 *   - Add `images[]` file array when photo reviews are supported.
 *   - Add `tags[]` array (e.g. ["clean", "safe", "well_lit"])
 *     when structured tag-based reviews are introduced.
 *   - Remove the one-review-per-parking limit if we move to a
 *     model where users can review after every visit.
 */
class StoreReviewRequest extends FormRequest
{
    /**
     * Only authenticated customers can submit reviews.
     * Detailed role checking is in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field-level validation rules.
     */
    public function rules(): array
    {
        return [
            // ── Required: Which parking is being reviewed ─────────
            'parking_id' => [
                'required',
                'integer',
                // Must be an approved, publicly visible parking.
                Rule::exists('parkings', 'id')->where('status', 'approved'),
            ],

            // ── Required: Which booking this review is tied to ────
            // Linking to a booking proves the reviewer actually parked.
            'booking_id' => [
                'required',
                'integer',
                Rule::exists('bookings', 'id'),
            ],

            // ── Required: Star rating ─────────────────────────────
            // Integer 1–5 only. No half-stars at the DB level.
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            // ── Optional: Written review ──────────────────────────
            // Not mandatory so users can do quick star-only reviews.
            // Max 1000 chars keeps comments concise.
            'comment' => [
                'nullable',
                'string',
                'min:5',   // If provided, must be at least 5 chars (no spam)
                'max:1000',
            ],
        ];
    }

    /**
     * Business-rule validation after field rules pass.
     *
     * These checks require DB queries, so they run here via
     * withValidator() rather than inline in rules().
     *
     * @param  Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Skip DB checks if basic field rules already failed —
            // no point querying with invalid data.
            if ($v->errors()->any()) {
                return;
            }

            $userId    = $this->user()->id;
            $parkingId = $this->integer('parking_id');
            $bookingId = $this->integer('booking_id');

            // ── Rule 1: Booking must belong to this user ──────────
            $booking = Booking::find($bookingId);

            if (!$booking) {
                $v->errors()->add('booking_id', 'The specified booking does not exist.');
                return;
            }

            if ($booking->user_id !== $userId) {
                $v->errors()->add('booking_id', 'This booking does not belong to your account.');
                return;
            }

            // ── Rule 2: Booking must be completed ─────────────────
            // A review only makes sense after the customer has parked.
            // Preventing reviews on pending/confirmed bookings stops
            // users from "pre-reviewing" places they haven't visited.
            if ($booking->booking_status !== 'completed') {
                $v->errors()->add(
                    'booking_id',
                    'You can only review a parking location after your booking is completed. ' .
                    'Current booking status: ' . $booking->booking_status . '.'
                );
                return;
            }

            // ── Rule 3: Booking must be for this parking ───────────
            // Prevents a user from using a completed booking at Parking A
            // to submit a review for Parking B.
            if ((int) $booking->parking_id !== $parkingId) {
                $v->errors()->add(
                    'parking_id',
                    'The selected booking is not for this parking location.'
                );
                return;
            }

            // ── Rule 4: One review per booking ─────────────────────
            // Each booking generates exactly one review slot.
            $reviewForBookingExists = Review::where('booking_id', $bookingId)->exists();

            if ($reviewForBookingExists) {
                $v->errors()->add(
                    'booking_id',
                    'You have already submitted a review for this booking.'
                );
                return;
            }

            // ── Rule 5: One review per parking per user ────────────
            // Even if a user has multiple bookings at the same parking,
            // they can only leave one review per parking location.
            // (Remove this rule if you want per-visit reviews.)
            $reviewForParkingExists = Review::where('user_id', $userId)
                ->where('parking_id', $parkingId)
                ->exists();

            if ($reviewForParkingExists) {
                $v->errors()->add(
                    'parking_id',
                    'You have already submitted a review for this parking location. ' .
                    'You can update your existing review instead.'
                );
            }
        });
    }

    /**
     * Human-readable validation error messages.
     */
    public function messages(): array
    {
        return [
            'parking_id.required' => 'Please specify which parking location you are reviewing.',
            'parking_id.integer'  => 'Invalid parking ID format.',
            'parking_id.exists'   => 'The specified parking location is not available for reviews.',
            'booking_id.required' => 'A completed booking is required to submit a review.',
            'booking_id.integer'  => 'Invalid booking ID format.',
            'booking_id.exists'   => 'The specified booking does not exist.',
            'rating.required'     => 'Please provide a star rating between 1 and 5.',
            'rating.integer'      => 'Rating must be a whole number (1–5).',
            'rating.min'          => 'Rating must be at least 1 star.',
            'rating.max'          => 'Rating cannot exceed 5 stars.',
            'comment.min'         => 'If you write a comment, please use at least 5 characters.',
            'comment.max'         => 'Comment must not exceed 1000 characters.',
            'comment.string'      => 'Comment must be a valid text string.',
        ];
    }
}