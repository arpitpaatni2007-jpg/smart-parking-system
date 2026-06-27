<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * NotificationResource
 * ============================================================
 *
 * Transforms a Notification model into a clean JSON response
 * optimised for the Flutter app's notification bell and inbox.
 *
 * DESIGN DECISIONS:
 *   - `preview` (80 chars) for list view; `message` for detail view.
 *     Both are always included — Flutter decides which to show.
 *   - `is_read` cast to boolean (0/1 from DB → true/false for JSON).
 *   - `sent_at` used as the display timestamp (not created_at),
 *     because a notification can be created before it is dispatched.
 *   - `type_icon` is a suggested icon name so Flutter doesn't need
 *     to implement its own type → icon mapping.
 *
 * USAGE:
 *   Single : new NotificationResource($notification)
 *   List   : NotificationResource::collection($notifications)
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'   => $this->id,

            // ── CONTENT ────────────────────────────────────────────────────
            'title'   => $this->title,

            /**
             * Full message body — shown on notification detail / expanded view.
             */
            'message' => $this->message,

            /**
             * Short preview (80 chars) for notification list cards.
             * Uses the model's preview() helper method.
             * Saves Flutter from implementing truncation logic.
             */
            'preview' => $this->preview(80),

            // ── CLASSIFICATION ─────────────────────────────────────────────
            'notification_type' => $this->notification_type,

            /**
             * Suggested icon name for the Flutter app.
             * Maps the notification_type to a Material icon name.
             * Flutter usage: Icon(icons[notification.type_icon])
             *
             * FUTURE: Move this mapping to a config file or DB column
             * so icons can be changed without a code deployment.
             */
            'type_icon' => $this->resolveTypeIcon(),

            // ── READ STATE ─────────────────────────────────────────────────
            /**
             * Cast to boolean by the model — true/false in JSON.
             * Used to render: read/unread styling, bold text, dot badges.
             */
            'is_read' => (bool) $this->is_read,

            // ── TIMING ─────────────────────────────────────────────────────
            /**
             * When the notification was dispatched.
             * NULL if the notification was queued but not yet sent.
             * Flutter uses this as the display timestamp in the inbox.
             */
            'sent_at'    => $this->sent_at?->toISOString(),

            /**
             * Human-readable relative time.
             * e.g. "2 minutes ago", "3 hours ago", "Yesterday"
             * Uses Carbon's diffForHumans() — saves Flutter doing date math.
             *
             * Falls back to created_at if sent_at is null (queued notification).
             */
            'time_ago' => ($this->sent_at ?? $this->created_at)?->diffForHumans(),

            // ── RECIPIENT (included for admin views only) ──────────────────
            /**
             * Only loaded when the controller eager-loads the user relationship.
             * Regular user responses skip this (not loaded → key omitted).
             */
            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),

            // ── TIMESTAMPS ─────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Map notification_type to a suggested Material icon name.
     * Flutter can use this directly in: Icon(Icons.{type_icon})
     * or map it to a custom asset icon.
     *
     * @return string
     */
    private function resolveTypeIcon(): string
    {
        return match($this->notification_type) {
            'booking' => 'local_parking',
            'payment' => 'payment',
            'vehicle' => 'directions_car',
            'promo'   => 'local_offer',
            'system'  => 'info_outline',
            default   => 'notifications',
        };
    }
}