<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationTemplate\StoreNotificationTemplateRequest;
use App\Http\Requests\NotificationTemplate\UpdateNotificationTemplateRequest;
use App\Http\Resources\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * NotificationTemplateController
 * ============================================================
 *
 * Handles all CRUD operations for notification templates.
 *
 * ENDPOINTS:
 *   GET    /api/v1/notification-templates                         → index
 *   POST   /api/v1/notification-templates                         → store
 *   GET    /api/v1/notification-templates/{notification_template} → show
 *   PUT    /api/v1/notification-templates/{notification_template} → update
 *   DELETE /api/v1/notification-templates/{notification_template} → destroy
 *
 * ACCESS CONTROL SUMMARY:
 *   index / show → Admin only
 *                  (templates are internal admin-panel resources;
 *                   the Flutter app never calls these directly)
 *   store        → Admin only
 *   update       → Admin only
 *   destroy      → Admin only
 *
 * SOFT DELETES:
 *   The NotificationTemplate model uses SoftDeletes. destroy()
 *   sets deleted_at rather than removing the row, preserving
 *   any historical reference to the template slug/title in logs.
 *
 * NOTE ON ROLE CHECKS:
 *   Role checks are done inline for clarity, consistent with
 *   ParkingController's and PaymentMethodController's pattern.
 */
class NotificationTemplateController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List Notification Templates
    // =========================================================

    /**
     * Return a list of notification templates.
     *
     * ADMIN PANEL USE CASE:
     *   Settings → Notification Templates page lists all templates
     *   with their type badge and status toggle.
     *
     * SUPPORTED QUERY PARAMETERS:
     *   ?type=email      → filter by channel (email | sms | push)
     *   ?status=active   → filter by status
     *   ?search=booking  → search by title or message content
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // ── Role check: admin only ─────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can manage notification templates.'
                );
            }

            $query = NotificationTemplate::query()->orderBy('title');

            // ── FILTER: by notification channel ───────────────────────────
            if ($request->filled('type')) {
                $query->ofType($request->type); // scopeOfType()
            }

            // ── FILTER: by status ──────────────────────────────────────────
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->active(); // scopeActive()
                } else {
                    $query->where('status', $request->status);
                }
            }

            // ── SEARCH: by title or message body ──────────────────────────
            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', $search)
                      ->orWhere('message', 'LIKE', $search);
                });
            }

            $templates = $query->get();

            return $this->successResponse(
                NotificationTemplateResource::collection($templates),
                'Notification templates retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationTemplateController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve notification templates.');
        }
    }

    // =========================================================
    // STORE — Create a New Notification Template
    // =========================================================

    /**
     * Create a new notification template.
     *
     * FLOW:
     *   1. StoreNotificationTemplateRequest validates all fields
     *   2. Role check: admin only
     *   3. Create the record with validated data
     *   4. Return the created resource
     *
     * STATUS DEFAULT:
     *   If admin does not send a `status`, we default to "active"
     *   so the template is immediately usable by the notification service.
     *
     * @param  StoreNotificationTemplateRequest  $request
     * @return JsonResponse
     */
    public function store(StoreNotificationTemplateRequest $request): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can create notification templates.'
                );
            }

            $template = NotificationTemplate::create([
                'title'   => $request->title,
                'type'    => $request->type,
                'subject' => $request->subject,   // null for sms/push — that's fine
                'message' => $request->message,
                'status'  => $request->status ?? 'active',
            ]);

            return $this->createdResponse(
                new NotificationTemplateResource($template),
                'Notification template created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationTemplateController@store failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to create notification template. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Single Notification Template
    // =========================================================

    /**
     * Return full details for a single notification template.
     *
     * ADMIN PANEL USE CASE:
     *   Admin clicks "Edit" on a template → the form pre-fills
     *   from this endpoint's response.
     *
     * @param  Request               $request
     * @param  NotificationTemplate  $notificationTemplate  (route model binding)
     * @return JsonResponse
     */
    public function show(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can view notification templates.'
                );
            }

            return $this->successResponse(
                new NotificationTemplateResource($notificationTemplate),
                'Notification template retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationTemplateController@show failed', [
                'notification_template_id' => $notificationTemplate->id,
                'error'                    => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve notification template.');
        }
    }

    // =========================================================
    // UPDATE — Update a Notification Template
    // =========================================================

    /**
     * Update an existing notification template.
     *
     * ADMIN PANEL USE CASE:
     *   Admin edits the message body to fix a typo, or toggles
     *   status to "inactive" to suspend a template temporarily.
     *
     * PARTIAL UPDATES:
     *   Only fields present in the request are updated.
     *   Uses $request->only([...]) so absent fields are not
     *   overwritten with null unintentionally.
     *
     * TYPE CHANGE NOTE:
     *   If admin changes type from "email" to "sms", the existing
     *   `subject` value is preserved in the DB but will no longer
     *   be shown in the resource (subject uses `when(! is_null)`).
     *   Admin should null it out explicitly if desired.
     *
     * @param  UpdateNotificationTemplateRequest  $request
     * @param  NotificationTemplate               $notificationTemplate
     * @return JsonResponse
     */
    public function update(
        UpdateNotificationTemplateRequest $request,
        NotificationTemplate $notificationTemplate
    ): JsonResponse {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can update notification templates.'
                );
            }

            // Update only the fields that were actually sent in the request.
            $notificationTemplate->update(
                $request->only(['title', 'type', 'subject', 'message', 'status'])
            );

            return $this->successResponse(
                new NotificationTemplateResource($notificationTemplate),
                'Notification template updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationTemplateController@update failed', [
                'notification_template_id' => $notificationTemplate->id,
                'error'                    => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update notification template.');
        }
    }

    // =========================================================
    // DESTROY — Soft Delete a Notification Template
    // =========================================================

    /**
     * Soft-delete a notification template.
     *
     * WHY SOFT DELETE?
     *   The NotificationTemplate model uses SoftDeletes. Deleting
     *   sets `deleted_at` rather than removing the row. Any
     *   notification logs or audit trails referencing the template
     *   title remain intact and queryable.
     *
     * SAFETY CHECK:
     *   Active templates should not be deleted outright. Admin is
     *   warned and prompted to deactivate first. This prevents
     *   the notification service from failing at runtime when it
     *   tries to load a template that no longer exists.
     *
     * @param  Request               $request
     * @param  NotificationTemplate  $notificationTemplate
     * @return JsonResponse
     */
    public function destroy(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can delete notification templates.'
                );
            }

            // ── Safety: warn admin before deleting an active template ──────
            // An active template may be in use by the notification service.
            // Soft-deleting it mid-operation could cause runtime failures.
            if ($notificationTemplate->status === 'active') {
                return $this->errorResponse(
                    'Cannot delete an active notification template. '
                    . 'Set its status to "inactive" before deleting.',
                    409 // 409 Conflict
                );
            }

            $notificationTemplate->delete(); // SoftDeletes → sets deleted_at

            return $this->successResponse(
                null,
                'Notification template deleted successfully.'
            );

        } catch (Throwable $e) {
            Log::error('NotificationTemplateController@destroy failed', [
                'notification_template_id' => $notificationTemplate->id,
                'error'                    => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete notification template.');
        }
    }
}