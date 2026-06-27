<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ============================================================
 * StoreNotificationRequest
 * ============================================================
 *
 * Validates the payload when an ADMIN or SYSTEM creates a
 * notification to send to a user.
 *
 * WHO CREATES NOTIFICATIONS?
 *   In a production system, most notifications are created
 *   programmatically by the system — triggered by events:
 *     - BookingConfirmed   → booking notification
 *     - PaymentReceived    → payment notification
 *     - DocumentExpiring   → vehicle notification
 *
 *   This request handles the rare case where an ADMIN manually
 *   creates a notification from the API (e.g. system announcements,
 *   targeted promo messages, or test messages during development).
 *
 *   Regular users CANNOT create notifications via this endpoint —
 *   that is enforced in the controller's authorization check.
 *
 * WHAT THE SYSTEM SETS (not from this request):
 *   - sent_at: set to NOW() automatically in the controller
 *   - is_read: always starts as false (unread)
 *
 * FUTURE SCALABILITY:
 *   - Add `send_to_all` boolean for broadcast announcements
 *   - Add `scheduled_at` datetime for deferred/scheduled sending
 *   - Add `reference_type` + `reference_id` for deep-link context
 *     e.g. reference_type='Booking', reference_id=42 → tap opens booking
 */
class StoreNotificationRequest extends FormRequest
{
    /**
     * Only admins can manually create notifications via the API.
     * System-generated notifications bypass this request entirely.
     * Role enforcement is done in the controller.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * The target user who should receive this notification.
             * Must be an existing user in the system.
             */
            'user_id' => ['required', 'integer', 'exists:users,id'],

            /**
             * Short heading shown in the notification list card.
             * Kept brief — shown as the bold title in the Flutter app.
             * e.g. "Booking Confirmed! ✅", "System Maintenance"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * Full notification body text.
             * Shown when the user opens/expands the notification.
             * Can be longer than the title — use TEXT type.
             */
            'message' => ['required', 'string', 'max:2000'],

            /**
             * Category of this notification.
             * Drives: icon shown, inbox filter tab, deep-link routing in Flutter.
             *
             *   booking → Booking lifecycle events
             *   payment → Payment/refund events
             *   vehicle → Document expiry reminders, verification
             *   system  → Admin announcements, maintenance alerts
             *   promo   → Promotional offers, discount codes
             */
            'notification_type' => [
                'required',
                'string',
                'in:booking,payment,vehicle,system,promo',
            ],
        ];
    }

    /**
     * Human-readable error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required'             => 'Please specify the recipient user.',
            'user_id.exists'               => 'The specified user does not exist.',
            'title.required'               => 'Please provide a notification title.',
            'title.max'                    => 'Title cannot exceed 255 characters.',
            'message.required'             => 'Please provide the notification message body.',
            'message.max'                  => 'Message cannot exceed 2000 characters.',
            'notification_type.required'   => 'Please specify a notification type.',
            'notification_type.in'         => 'Notification type must be one of: booking, payment, vehicle, system, promo.',
        ];
    }

    /**
     * Return JSON 422 on validation failure.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}