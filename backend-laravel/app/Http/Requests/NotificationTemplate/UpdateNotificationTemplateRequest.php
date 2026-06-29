<?php

namespace App\Http\Requests\NotificationTemplate;

use App\Models\NotificationTemplate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================
 * UpdateNotificationTemplateRequest
 * ============================================================
 *
 * Validates incoming data when an admin updates an existing
 * notification template record.
 *
 * KEY DIFFERENCE FROM StoreNotificationTemplateRequest:
 *   All fields are optional (uses `sometimes`). The admin panel
 *   may send only the fields that changed. The controller uses
 *   $request->only([...]) to apply just what was sent.
 *
 * CONDITIONAL SUBJECT RULE:
 *   If `type` is being changed TO "email" in this update, a
 *   `subject` becomes required. If `type` is not being changed
 *   in this request, the existing subject on the record is
 *   preserved — we do not force re-submission of unchanged fields.
 *
 * AUTHORIZATION:
 *   Role enforcement is handled in the controller, consistent
 *   with the pattern used across this project.
 */
class UpdateNotificationTemplateRequest extends FormRequest
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
     * Validation rules for updating a notification template.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ── All fields optional on update ─────────────────────
            'title'   => ['sometimes', 'required', 'string', 'max:255'],

            'type'    => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', [
                    NotificationTemplate::TYPE_EMAIL,
                    NotificationTemplate::TYPE_SMS,
                    NotificationTemplate::TYPE_PUSH,
                ]),
            ],

            'message' => ['sometimes', 'required', 'string'],

            // Subject is required only when the type being set is "email".
            // If the admin is not touching `type`, this rule is lenient.
            'subject' => ['sometimes', 'nullable', 'required_if:type,email', 'string', 'max:255'],

            'status'  => ['sometimes', 'required', 'string', 'in:active,inactive'],
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
            'type.required'       => 'A notification type is required when provided.',
            'type.in'             => 'Type must be one of: email, sms, push.',
            'message.required'    => 'A message body is required when provided.',
            'subject.required_if' => 'A subject line is required for email templates.',
            'subject.max'         => 'The subject may not exceed 255 characters.',
            'status.required'     => 'Status is required when provided.',
            'status.in'           => 'Status must be either "active" or "inactive".',
        ];
    }
}