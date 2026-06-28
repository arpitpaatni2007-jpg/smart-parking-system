<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * ============================================================
 * UpdateReviewRequest
 * ============================================================
 *
 * Validates incoming data when a customer or admin updates
 * an existing review.
 *
 * WHAT CAN BE UPDATED:
 *   - rating  → customer can change their star rating
 *   - comment → customer can update or remove their comment
 *   - is_approved → admin only: moderate/hide a review
 *
 * WHAT CANNOT BE CHANGED:
 *   - parking_id → the review is permanently tied to that parking
 *   - booking_id → the review is permanently tied to that booking
 *   - user_id    → review ownership cannot be transferred
 *
 * PARTIAL UPDATES:
 *   All fields use 'sometimes' so the customer can update just
 *   the comment without re-sending the rating, and vice versa.
 *
 * AUTHORIZATION:
 *   - Customer: can only update their OWN review, and only
 *     rating/comment — not is_approved.
 *   - Admin:    can update any review, including is_approved
 *     (to hide inappropriate content without deleting it).
 *   Authorization logic is enforced in the controller, not here.
 *
 * FUTURE SCALABILITY:
 *   - Add `images[]` array when photo review editing is supported.
 *   - Add `tags[]` array for structured tag editing.
 *   - Add `admin_note` field for internal moderation notes that
 *     are visible to admins but not displayed publicly.
 */
class UpdateReviewRequest extends FormRequest
{
    /**
     * Authorization is handled in the controller.
     * This returns true to allow the request through to controller logic.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field-level validation rules for a review update.
     */
    public function rules(): array
    {
        $authUser = $this->user();
        $isAdmin  = in_array($authUser?->role?->name, ['super_admin', 'admin']);

        $rules = [
            // ── Rating (customer-editable) ────────────────────────
            // 'sometimes' means only validate if this field is present.
            // Allows partial updates (just updating comment without rating).
            'rating' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            // ── Comment (customer-editable) ───────────────────────
            // Nullable — customer can remove their comment by sending null.
            'comment' => [
                'sometimes',
                'nullable',
                'string',
                'min:5',
                'max:1000',
            ],
        ];

        // ── is_approved (admin only) ──────────────────────────────
        // Customers must not be able to approve their own reviews.
        // Only inject this rule for admin users.
        if ($isAdmin) {
            $rules['is_approved'] = [
                'sometimes',
                'boolean',
            ];
        }

        return $rules;
    }

    /**
     * Additional business-rule validation.
     *
     * Ensures non-admin users cannot sneak in the is_approved field
     * even if they send it in the request body.
     *
     * @param  Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {

            if ($v->errors()->any()) {
                return;
            }

            $authUser = $this->user();
            $isAdmin  = in_array($authUser?->role?->name, ['super_admin', 'admin']);

            // ── Guard: Customers cannot set is_approved ────────────
            // Even if a customer somehow passes validation, the controller
            // further strips this field. We add a validation error here
            // so the customer gets a clear response instead of silent rejection.
            if (!$isAdmin && $this->has('is_approved')) {
                $v->errors()->add(
                    'is_approved',
                    'You are not authorized to change the approval status of a review.'
                );
            }

            // ── Guard: At least one updatable field must be present ─
            // Reject empty update requests (no fields provided).
            $isAdmin = in_array($authUser?->role?->name, ['super_admin', 'admin']);
            $updateableFields = $isAdmin
                ? ['rating', 'comment', 'is_approved']
                : ['rating', 'comment'];

            $hasAtLeastOneField = collect($updateableFields)
                ->some(fn ($field) => $this->has($field));

            if (!$hasAtLeastOneField) {
                $v->errors()->add(
                    'rating',
                    'Please provide at least one field to update (rating or comment).'
                );
            }
        });
    }

    /**
     * Human-readable error messages.
     */
    public function messages(): array
    {
        return [
            'rating.required'      => 'Rating is required when provided.',
            'rating.integer'       => 'Rating must be a whole number.',
            'rating.min'           => 'Rating must be at least 1 star.',
            'rating.max'           => 'Rating cannot exceed 5 stars.',
            'comment.string'       => 'Comment must be a valid text string.',
            'comment.min'          => 'If you write a comment, please use at least 5 characters.',
            'comment.max'          => 'Comment must not exceed 1000 characters.',
            'is_approved.boolean'  => 'is_approved must be true or false.',
        ];
    }
}