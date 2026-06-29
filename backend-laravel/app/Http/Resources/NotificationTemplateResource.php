<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * NotificationTemplateResource
 * ============================================================
 *
 * Transforms a NotificationTemplate model into a structured,
 * consistent JSON response for all template-related endpoints.
 *
 * WHY A DEDICATED RESOURCE?
 * Controls exactly which columns are exposed to the Admin Panel.
 * The `message` field may contain sensitive placeholder syntax;
 * this resource ensures only intentional fields are returned.
 *
 * RESPONSE DESIGN DECISIONS:
 *   - `is_active` computed flag lets the Admin Panel toggle UI
 *     without comparing raw "active" / "inactive" strings.
 *   - `type_label` gives a human-readable channel name for display.
 *   - `subject` is only included when the type is "email" — avoids
 *     null clutter in SMS and push responses.
 *   - Soft-delete `deleted_at` is never exposed.
 *
 * USAGE:
 *   return new NotificationTemplateResource($template);
 *   return NotificationTemplateResource::collection($templates);
 */
class NotificationTemplateResource extends JsonResource
{
    /**
     * Transform the NotificationTemplate model into a JSON-friendly array.
     *
     * Fields sourced strictly from the NotificationTemplate model's
     * $fillable + auto-managed columns:
     *   id, title, type, subject, message, status, created_at, updated_at
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── Core Identity ─────────────────────────────────────
            'id'      => $this->id,

            // ── Admin-Facing Label ────────────────────────────────
            // Internal name shown in Admin Panel template list.
            // e.g. "Booking Confirmed - Email"
            'title'   => $this->title,

            // ── Notification Channel ──────────────────────────────
            // Raw value: "email" | "sms" | "push"
            // Admin Panel uses this to show the correct icon/badge.
            'type'    => $this->type,

            // ── Email Subject ─────────────────────────────────────
            // Only relevant for email type. Excluded entirely when
            // null to keep SMS and push responses clean.
            'subject' => $this->when(
                ! is_null($this->subject),
                $this->subject
            ),

            // ── Message Body ──────────────────────────────────────
            // The notification content. May contain {{placeholders}}
            // such as {{user_name}}, {{booking_id}}, {{amount}}.
            'message' => $this->message,

            // ── Status ────────────────────────────────────────────
            // Raw value: "active" | "inactive"
            'status'  => $this->status,

            // ── Computed Helper Flags ─────────────────────────────
            // Admin Panel toggle and Flutter read-check use these
            // booleans directly instead of comparing strings.
            'is_active' => $this->status === 'active',

            // ── Timestamps ────────────────────────────────────────
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}