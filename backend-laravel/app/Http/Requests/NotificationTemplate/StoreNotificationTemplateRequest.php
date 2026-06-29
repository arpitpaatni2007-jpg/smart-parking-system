<?php

namespace App\Http\Requests\NotificationTemplate;

use App\Models\NotificationTemplate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================
 * StoreNotificationTemplateRequest
 * ============================================================
 *
 * Validates incoming data when an admin creates a new
 * notification template (email, sms, or push).
 *
 * WHY A FORM REQUEST?
 * Keeps all field-level validation out of the controller.
 * The controller's store() only runs after every field here
 * is confirmed valid — cleaner separation of concerns.
 *
 * AUTHORIZATION:
 *   Only admin users should create templates. Role enforcement
 *   is handled in the controller (consistent with the pattern
 *   used across this project), so authorize() returns true.
 *
 * VALIDATION NOTES:
 *   - `subject` is required only when `type` is "email".
 *     SMS and push templates have no subject line.
 *   - `status` defaults to "active" in the controller if omitted.
 *   - `title` has no uniqueness constraint at DB level, but a
 *     descriptive title is required for admin-panel readability.
 *   - `type` is constrained to the three TYPE_* constants
 *     defined on the model to prevent invalid channel values.
 */
class StoreNotificationTemplateRequest extends FormRequest
{
    /**
     * All admin routes are guarded by auth middleware.
     * Role enforcement is handled in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a notification template.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ── Required ──────────────────────────────────────────
            // Internal admin-facing label, e.g. "Booking Confirmed - Email"
            'title'   => ['required', 'string', 'max:255'],

            // Notification channel — must match one of the model constants.
            'type'    => [
                'required',
                'string',
                'in:' . implode(',', [
                    NotificationTemplate::TYPE_EMAIL,
                    NotificationTemplate::TYPE_SMS,
                    NotificationTemplate::TYPE_PUSH,
                ]),
            ],

            // The body/content of the notification.
            // May contain {{placeholder}} tokens for runtime interpolation.
            'message' => ['required', 'string'],

            // ── Conditional ───────────────────────────────────────
            // Subject is only meaningful — and required — for email templates.
            // SMS and push types must not be forced to supply one.
            'subject' => ['nullable', 'required_if:type,email', 'string', 'max:255'],

            // ── Optional ──────────────────────────────────────────
            // Defaults to "active" in the controller when not supplied.
            'status'  => [
                'nullable',
                'string',
                'in:active,inactive',
            ],
        ];
    }

    /**
     * Human-readable error messages for each rule.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'      => 'A template title is required.',
            'title.max'           => 'The title may not exceed 255 characters.',
            'type.required'       => 'A notification type is required.',
            'type.in'             => 'Type must be one of: email, sms, push.',
            'message.required'    => 'A message body is required.',
            'subject.required_if' => 'A subject line is required for email templates.',
            'subject.max'         => 'The subject may not exceed 255 characters.',
            'status.in'           => 'Status must be either "active" or "inactive".',
        ];
    }
}