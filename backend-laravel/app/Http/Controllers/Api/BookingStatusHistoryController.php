<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingStatusHistoryResource;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * BookingStatusHistoryController
 * ============================================================
 *
 * Read-only audit log of all status transitions for a booking.
 *
 * ENDPOINTS:
 *   GET /api/v1/bookings/{booking}/history         → Full history list
 *   GET /api/v1/bookings/{booking}/history/{entry} → Single history entry
 *
 * WHY READ-ONLY?
 *   Status history is an IMMUTABLE audit trail.
 *   Records are only CREATED by the system — never updated or deleted.
 *   Allowing manual creation/modification would defeat the audit purpose.
 *   If you need to add a manual note, that should go in booking.notes
 *   or a separate admin comment system.
 *
 * USE CASES:
 *   - Flutter app booking detail → show status timeline
 *   - Admin support → audit trail for dispute resolution
 *   - Owner dashboard → track booking lifecycle per parking
 *
 * ACCESS CONTROL:
 *   - User: can view history for their own bookings
 *   - Owner: can view history for bookings in their parking
 *   - Admin: can view any booking's history
 *
 * FUTURE SCALABILITY:
 *   - Add ?actor=user/system/admin filter for auditing
 *   - Add ?status= filter to find when booking reached a specific state
 *   - Add export endpoint for compliance reporting
 */
class BookingStatusHistoryController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List All History for a Booking
    // =========================================================

    /**
     * Return the complete status history for a booking, chronologically.
     *
     * Used by the Flutter app to render a booking status timeline:
     *   [pending] → [confirmed] → [checked_in] → [checked_out] → [completed]
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Booking $booking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessBooking($request->user(), $booking)) {
                return $this->forbiddenResponse(
                    'You do not have access to this booking\'s status history.'
                );
            }

            // ── Load history chronologically ───────────────────────────────
            $history = $booking->statusHistory()
                ->with('changedBy')            // Load the user who made the change
                ->oldest('created_at')         // Chronological order (first change first)
                ->get();

            return $this->successResponse(
                BookingStatusHistoryResource::collection($history),
                'Booking status history retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingStatusHistoryController@index failed', [
                'booking_id' => $booking->id,
                'user_id'    => $request->user()->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve booking status history.');
        }
    }

    // =========================================================
    // SHOW — Single History Entry
    // =========================================================

    /**
     * Return a single status history entry by its ID.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Booking      $booking
     * @param  int                      $historyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Booking $booking, int $historyId): JsonResponse
    {
        try {
            if (! $this->canAccessBooking($request->user(), $booking)) {
                return $this->forbiddenResponse(
                    'You do not have access to this booking\'s status history.'
                );
            }

            // ── Find history entry scoped to this booking ──────────────────
            $historyEntry = $booking->statusHistory()
                ->with('changedBy')
                ->find($historyId);

            if (! $historyEntry) {
                return $this->notFoundResponse('Status history entry not found for this booking.');
            }

            return $this->successResponse(
                new BookingStatusHistoryResource($historyEntry),
                'Status history entry retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('BookingStatusHistoryController@show failed', [
                'booking_id' => $booking->id,
                'history_id' => $historyId,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve history entry.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Determine if the requesting user can access the given booking.
     *
     * @param  mixed            $user
     * @param  \App\Models\Booking $booking
     * @return bool
     */
    private function canAccessBooking($user, Booking $booking): bool
    {
        // Admin can see everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner can see bookings for their parking locations
        if ($user->hasRole('owner')) {
            $booking->loadMissing('parking');
            return $booking->parking?->owner_id === $user->id;
        }

        // Regular user can only see their own bookings
        return $booking->user_id === $user->id;
    }
}