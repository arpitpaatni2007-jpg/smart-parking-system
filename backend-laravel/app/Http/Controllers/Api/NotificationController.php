<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * NotificationController
 * ============================================================
 *
 * Manages in-app notifications for the Smart Parking system.
 *
 * ENDPOINTS:
 *   GET    /api/v1/notifications                       → Paginated inbox
 *   POST   /api/v1/notifications                       → Admin: create & send
 *   GET    /api/v1/notifications/{notification}        → Single notification detail
 *   DELETE /api/v1/notifications/{notification}        → Delete (soft)
 *   PATCH  /api/v1/notifications/{notification}/read   → Mark one as read
 *   PATCH  /api/v1/notifications/read-all             → Mark ALL as read
 *   GET    /api/v1/notifications/unread-count          → Badge count
 *
 * ACCESS RULES:
 *   Users  → see & manage their OWN notifications only
 *   Admin  → can create notifications AND view any user's notifications
 *   Owner  → same as user for their personal notifications
 *
 * FLUTTER INTEGRATION:
 *   On app startup:
 *     GET /notifications/unread-count → update badge number
 *   On notification bell tap:
 *     GET /notifications → load inbox
 *   On notification tap:
 *     PATCH /notifications/{id}/read → mark read, navigate to content
 *   On "Mark all read" button:
 *     PATCH /notifications/read-all → clear badge
 *
 * FUTURE SCALABILITY:
 *   - Add NotificationService class for system-generated notifications
 *   - Add event listeners: BookingConfirmed → create booking notification
 *   - Add push notification dispatch via Firebase (FCM) in the service
 *   - Add `reference_type` + `reference_id` columns for deep linking
 *   - Add bulk-delete endpoint: DELETE /notifications with id[] array
 *   - Add WebSocket broadcasting for real-time badge updates
 */
class NotificationController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — Notification Inbox
    // =========================================================

    /**
     * Return a paginated list of the authenticated user's notifications.
     *
     * QUERY PARAMETERS:
     *   ?type=booking           → filter by notification_type
     *   ?is_read=false          → filter unread or read notifications
     *   ?search=booking+confirm → search in title or message
     *   ?per_page=20            → items per page (default 20, max 50)
     *
     * ORDERING:
     *   Unread notifications float to the top, then sorted by sent_at desc.
     *   This mirrors standard notification inbox behaviour (Gmail, WhatsApp, etc.)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user  = $request->user();
            $query = Notification::query();

            // ── Scope by role ──────────────────────────────────────────────
            if ($user->hasRole('admin') && $request->filled('user_id')) {
                // Admin can view a specific user's notifications for support
                $query->forUser((int) $request->user_id)->with('user');
            } else {
                // Users and owners always see only their own notifications
                $query->forUser($user->id);
            }

            // ── Filter: only sent notifications (not queued ones) ──────────
            // Queued/scheduled notifications should not appear in the inbox yet
            $query->sent();

            // ── Filter: by notification type ───────────────────────────────
            if ($request->filled('type')) {
                $query->ofType($request->type);
            }

            // ── Filter: by read state ──────────────────────────────────────
            // Accepts: ?is_read=true, ?is_read=false, ?is_read=0, ?is_read=1
            if ($request->has('is_read')) {
                $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);
                $isRead ? $query->read() : $query->unread();
            }

            // ── Search: in title or message body ───────────────────────────
            if ($request->filled('search')) {
                $term = '%' . $request->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'LIKE', $term)
                      ->orWhere('message', 'LIKE', $term);
                });
            }

            // ── Sort: unread first, then newest sent_at ────────────────────
            /**
             * is_read ASC → false (0) comes before true (1), so unread float up.
             * sent_at DESC → newest notification first within each group.
             */
            $query->orderBy('is_read', 'asc')
                  ->orderByDesc('sent_at');

            // ── Paginate ───────────────────────────────────────────────────
            $perPage       = min((int) ($request->per_page ?? 20), 50);
            $notifications = $query->paginate($perPage);

            // ── Append unread count to the response for badge sync ─────────
            /**
             * Including the unread count in the list response saves the Flutter
             * app from making a separate /unread-count request after loading the inbox.
             */
            $unreadCount = Notification::forUser($user->id)->sent()->unread()->count();

            $data                       = NotificationResource::collection($notifications)->response()->getData(true);
            $data['meta']['unread_count'] = $unreadCount;

            return $this->successResponse($data, 'Notifications retrieved successfully.');

        } catch (Throwable $e) {
            Log::error('NotificationController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve notifications.');
        }
    }

    // =========================================================
    // STORE — Create & Send a Notification (Admin Only)
    // =========================================================

    /**
     * Manually create and send a notification to a user.
     * Used by admins for system announcements, support messages,
     * or testing the notification system.
     *
     * NORMAL FLOW (system-generated):
     *   System events (BookingConfirmed, PaymentReceived, etc.)
     *   create notifications programmatically via a service class.
     *   They bypass this endpoint entirely.
     *
     * THIS ENDPOINT is for exceptional/manual cases only:
     *   - Broadcast system maintenance alert
     *   - Send a support reply notification
     *   - Test notification during development
     *
     * @param  \App\Http\Requests\Notification\StoreNotificationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        try {
            // ── Admin-only endpoint ────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can manually create notifications.'
                );
            }

            $notification = Notification::create([
                'user_id'           => $request->user_id,
                'title'             => $request->title,
                'message'           => $request->message,
                'notification_type' => $request->notification_type,
                'is_read'           => false,          // Always unread on creation
                'sent_at'           => now(),          // Mark as sent immediately
            ]);

            /**
             * FUTURE: Dispatch push notification here.
             * Example with FCM (Firebase Cloud Messaging):
             *
             *   $user = $notification->user;
             *   if ($user->fcm_token) {
             *       FCMService::send($user->fcm_token, [
             *           'title'   => $notification->title,
             *           'body'    => $notification->preview(),
             *           'type'    => $notification->notification_type,
             *       ]);
             *   }
             *
             * Or dispatch a queued job:
             *   dispatch(new SendPushNotificationJob($notification));
             */

            return $this->createdResponse(
                new NotificationResource($notification),
                'Notification sent successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@store failed', [
                'admin_id' => $request->user()->id,
                'error'    => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to send notification.');
        }
    }

    // =========================================================
    // SHOW — Single Notification Detail
    // =========================================================

    /**
     * Return full details for a single notification.
     *
     * AUTO-MARK AS READ:
     *   Opening a notification detail view automatically marks it as read.
     *   This mirrors how email clients work (opening = reading).
     *   The updated is_read state is returned in the response so
     *   Flutter can update its local state without a second request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Notification $notification): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessNotification($request->user(), $notification)) {
                return $this->forbiddenResponse('You do not have access to this notification.');
            }

            // ── Auto-mark as read when viewed ──────────────────────────────
            /**
             * markAsRead() checks if already read before writing to DB,
             * so repeated views don't cause redundant UPDATE queries.
             */
            $notification->markAsRead();

            return $this->successResponse(
                new NotificationResource($notification),
                'Notification retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve notification.');
        }
    }

    // =========================================================
    // MARK AS READ — Mark a Single Notification Read
    // =========================================================

    /**
     * Explicitly mark a single notification as read.
     *
     * WHEN TO USE vs show():
     *   - show() is for viewing the full notification detail (auto-marks read)
     *   - markAsRead() is for swipe-to-mark-read in the notification list
     *     without navigating to the detail view
     *
     * ROUTE: PATCH /api/v1/notifications/{notification}/read
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessNotification($request->user(), $notification)) {
                return $this->forbiddenResponse('You do not have access to this notification.');
            }

            // ── Already read — return early without a DB write ─────────────
            if ($notification->is_read) {
                return $this->successResponse(
                    new NotificationResource($notification),
                    'Notification was already marked as read.'
                );
            }

            $notification->markAsRead();

            // ── Include updated unread count for badge sync ────────────────
            $unreadCount = Notification::forUser($request->user()->id)
                ->sent()
                ->unread()
                ->count();

            return $this->successResponse(
                [
                    'notification' => new NotificationResource($notification),
                    'unread_count' => $unreadCount,
                ],
                'Notification marked as read.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@markAsRead failed', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to mark notification as read.');
        }
    }

    // =========================================================
    // MARK ALL AS READ — Clear the Entire Inbox
    // =========================================================

    /**
     * Mark ALL of the authenticated user's unread notifications as read.
     *
     * USE CASE:
     *   User taps "Mark all as read" in the Flutter notification inbox.
     *   Clears the notification bell badge instantly.
     *
     * PERFORMANCE:
     *   Uses a bulk UPDATE query instead of loading and updating each
     *   model individually. Much more efficient for users with many
     *   unread notifications.
     *
     * ROUTE: PATCH /api/v1/notifications/read-all
     *
     * IMPORTANT ROUTING NOTE:
     *   This route MUST be defined BEFORE the {notification} param route
     *   in api.php to prevent Laravel treating "read-all" as a model ID.
     *   See the routes block at the bottom of this file.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            /**
             * Bulk UPDATE via query builder.
             * Far more efficient than: $notifications->each->markAsRead()
             *
             * Only updates unread notifications that have been sent (sent_at IS NOT NULL).
             * Queued notifications (sent_at IS NULL) are excluded.
             */
            $updatedCount = Notification::forUser($user->id)
                ->sent()
                ->unread()
                ->update(['is_read' => true]);

            return $this->successResponse(
                [
                    'marked_read_count' => $updatedCount,
                    'unread_count'      => 0, // All clear
                ],
                $updatedCount > 0
                    ? "All {$updatedCount} notification(s) marked as read."
                    : 'No unread notifications to mark.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@markAllAsRead failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to mark all notifications as read.');
        }
    }

    // =========================================================
    // UNREAD COUNT — Badge Count for Flutter App
    // =========================================================

    /**
     * Return the count of unread notifications for the authenticated user.
     *
     * USE CASE:
     *   Called by Flutter on:
     *     - App startup (to initialise the badge number)
     *     - App resume from background (to refresh the badge)
     *     - After processing a push notification payload
     *
     *   This is a lightweight endpoint — COUNT query only, no data loading.
     *
     * ROUTE: GET /api/v1/notifications/unread-count
     *
     * IMPORTANT ROUTING NOTE:
     *   Must be declared BEFORE apiResource() or the {notification} route.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = Notification::forUser($request->user()->id)
                ->sent()
                ->unread()
                ->count();

            return $this->successResponse(
                ['unread_count' => $count],
                'Unread notification count retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@unreadCount failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve unread count.');
        }
    }

    // =========================================================
    // DESTROY — Soft-Delete a Notification
    // =========================================================

    /**
     * Soft-delete a single notification for the authenticated user.
     *
     * WHY SOFT DELETE?
     *   Preserves notification history for admin audit and analytics.
     *   A soft-deleted notification disappears from the user's inbox
     *   but the record remains in the DB for reporting purposes.
     *
     *   Example: "How many booking confirmation notifications were sent last month?"
     *   — answered from the notifications table, including soft-deleted ones.
     *
     * AUTHORIZATION:
     *   Users can only delete their own notifications.
     *   Admins can delete any notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessNotification($request->user(), $notification)) {
                return $this->forbiddenResponse('You can only delete your own notifications.');
            }

            $notification->delete(); // Soft delete — sets deleted_at

            // ── Return updated unread count for Flutter badge sync ─────────
            $unreadCount = Notification::forUser($request->user()->id)
                ->sent()
                ->unread()
                ->count();

            return $this->successResponse(
                ['unread_count' => $unreadCount],
                'Notification deleted successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationController@destroy failed', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete notification.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Check whether the authenticated user may access a notification.
     *
     * Rules:
     *   - Admin: always yes (for support and reporting)
     *   - Anyone else: only if the notification belongs to them
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Notification $notification
     * @return bool
     */
    private function canAccessNotification($user, Notification $notification): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $notification->user_id === $user->id;
    }
}