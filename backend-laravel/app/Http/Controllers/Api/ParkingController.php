<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingRequest;
use App\Http\Requests\Parking\UpdateParkingRequest;
use App\Http\Resources\ParkingResource;
use App\Models\Parking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * ParkingController
 * ============================================================
 *
 * Handles all CRUD operations for parking locations.
 *
 * ENDPOINTS:
 *   GET    /api/v1/parkings              → index  (search/list)
 *   POST   /api/v1/parkings              → store  (owner creates)
 *   GET    /api/v1/parkings/{parking}    → show   (detail view)
 *   PUT    /api/v1/parkings/{parking}    → update (owner updates)
 *   DELETE /api/v1/parkings/{parking}    → destroy (owner/admin deletes)
 *
 * ACCESS CONTROL SUMMARY:
 *   index / show → Any authenticated user (users browse, owners manage)
 *   store        → Authenticated user with 'owner' role
 *   update       → Parking owner only (their own parking)
 *   destroy      → Parking owner only (their own parking)
 *
 * NOTE ON ROLE CHECKS:
 *   Role checks are done inline in this controller for clarity.
 *   In a larger app, move these to Laravel Policies:
 *   php artisan make:policy ParkingPolicy --model=Parking
 */
class ParkingController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List / Search Parkings
    // =========================================================

    /**
     * Return a paginated list of parking locations.
     *
     * SUPPORTED QUERY PARAMETERS:
     *   ?search=Green Valley   → search by name or address
     *   ?city_id=3             → filter by city
     *   ?state_id=1            → filter by state
     *   ?status=active         → filter by status
     *   ?lat=28.6139&lng=77.2090&radius=10 → proximity search (km)
     *   ?per_page=15           → pagination (default 15, max 50)
     *
     * FLUTTER APP USE CASE:
     *   User opens "Find Parking" screen → calls this endpoint.
     *   If user shares location → also sends lat/lng for nearby results.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // ── Build the base query ───────────────────────────────────────
            $query = Parking::query()
                ->with([
                    'state',
                    'city',
                    'images' => fn ($q) => $q->where('is_primary', true), // Only primary image in list
                    'facilities',
                ]);

            // ── FILTER: by owner (owner views only their own parkings) ─────
            if ($request->user()->hasRole('owner')) {
                $query->where('owner_id', $request->user()->id);
            }

            // ── FILTER: by status ──────────────────────────────────────────
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                // Regular users only see active parkings
                if (! $request->user()->hasRole(['admin', 'owner'])) {
                    $query->where('status', 'active');
                }
            }

            // ── FILTER: by city ────────────────────────────────────────────
            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            // ── FILTER: by state ───────────────────────────────────────────
            if ($request->filled('state_id')) {
                $query->where('state_id', $request->state_id);
            }

            // ── SEARCH: by name or address ─────────────────────────────────
            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', $search)
                      ->orWhere('address', 'LIKE', $search);
                });
            }

            // ── PROXIMITY SEARCH: using Haversine formula ──────────────────
            /**
             * When user shares their GPS location, we show nearest parkings.
             * The scopeNearLocation() on the Parking model runs the Haversine
             * formula and adds a 'distance' attribute to each result.
             *
             * Default radius: 10km if not specified.
             */
            if ($request->filled('lat') && $request->filled('lng')) {
                $lat    = (float) $request->lat;
                $lng    = (float) $request->lng;
                $radius = (float) ($request->radius ?? 10);

                // Validate coordinate ranges before using in SQL
                if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    return $this->errorResponse('Invalid GPS coordinates provided.', 422);
                }

                $query->nearLocation($lat, $lng, $radius)
                      ->orderBy('distance'); // Nearest first
            } else {
                // Default sort: newest first
                $query->latest();
            }

            // ── PAGINATION ────────────────────────────────────────────────
            $perPage = min((int) ($request->per_page ?? 15), 50); // Cap at 50
            $parkings = $query->paginate($perPage);

            return $this->successResponse(
                ParkingResource::collection($parkings)->response()->getData(true),
                'Parking locations retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve parking locations.');
        }
    }

    // =========================================================
    // STORE — Create a New Parking Location
    // =========================================================

    /**
     * Create a new parking location for the authenticated owner.
     *
     * FLOW:
     *   1. StoreParkingRequest validates all fields
     *   2. Role check: only 'owner' role can create parkings
     *   3. DB transaction: create parking + sync facilities
     *   4. Return the created parking
     *
     * DB TRANSACTION:
     *   Creating a parking + attaching facilities are two separate
     *   DB operations. If facilities sync fails, we don't want a
     *   parking with no facilities recorded. Transaction rolls both back.
     *
     * @param  \App\Http\Requests\Parking\StoreParkingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreParkingRequest $request): JsonResponse
    {
        try {
            // ── Role check: only owners can register parking locations ─────
            if (! $request->user()->hasRole('owner')) {
                return $this->forbiddenResponse(
                    'Only parking owners can register parking locations.'
                );
            }

            $parking = DB::transaction(function () use ($request) {
                // ── Create the parking record ──────────────────────────────
                $parking = Parking::create([
                    'owner_id'    => $request->user()->id, // Always the logged-in owner
                    'state_id'    => $request->state_id,
                    'city_id'     => $request->city_id,
                    'name'        => $request->name,
                    'description' => $request->description,
                    'address'     => $request->address,
                    'latitude'    => $request->latitude,
                    'longitude'   => $request->longitude,
                    'total_slots' => $request->total_slots ?? 0,
                    'status'      => 'pending', // Requires admin approval before going live
                ]);

                // ── Sync facilities (many-to-many) ─────────────────────────
                /**
                 * sync() on a BelongsToMany relationship:
                 *   - Attaches all facility IDs in the array
                 *   - Removes any previously attached IDs not in the array
                 *   - Does nothing if array is empty
                 */
                if ($request->filled('facility_ids')) {
                    $parking->facilities()->sync($request->facility_ids);
                }

                return $parking;
            });

            // ── Load relationships for the response ────────────────────────
            $parking->load(['state', 'city', 'facilities', 'images', 'owner']);

            return $this->createdResponse(
                new ParkingResource($parking),
                'Parking location registered successfully. It will be reviewed by admin before going live.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingController@store failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to create parking location. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Single Parking Detail
    // =========================================================

    /**
     * Return full details for a single parking location.
     *
     * EAGER LOADING:
     *   This loads ALL relationships — images gallery, all slots,
     *   facilities. This is the "detail page" endpoint.
     *   The index() endpoint only loads primary image to keep lists fast.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Parking $parking): JsonResponse
    {
        try {
            // Regular users can only see active parkings
            if (
                ! $request->user()->hasRole(['admin', 'owner']) &&
                $parking->status !== 'active'
            ) {
                return $this->notFoundResponse('Parking location not found.');
            }

            // Owner can only see their own parkings
            if (
                $request->user()->hasRole('owner') &&
                $parking->owner_id !== $request->user()->id
            ) {
                return $this->forbiddenResponse('You do not have access to this parking location.');
            }

            // Load all relationships for full detail view
            $parking->load([
                'state',
                'city',
                'owner',
                'images',       // Full gallery
                'facilities',
                'slots.vehicleType', // Slots with their vehicle type
            ]);

            return $this->successResponse(
                new ParkingResource($parking),
                'Parking location retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve parking location.');
        }
    }

    // =========================================================
    // UPDATE — Update a Parking Location
    // =========================================================

    /**
     * Update an existing parking location.
     *
     * AUTHORIZATION:
     *   Only the owner who created the parking can update it.
     *   Admin updates are handled via admin panel (not this API).
     *
     * STATUS NOTE:
     *   If an owner changes significant details (address, GPS), you might
     *   want to reset status to 'pending' for re-approval.
     *   For now, status can be set explicitly in the request.
     *
     * @param  \App\Http\Requests\Parking\UpdateParkingRequest $request
     * @param  \App\Models\Parking                             $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateParkingRequest $request, Parking $parking): JsonResponse
    {
        try {
            // ── Authorization: only the owner of this parking ──────────────
            if ($parking->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse(
                    'You can only update your own parking locations.'
                );
            }

            DB::transaction(function () use ($request, $parking) {
                // ── Update only fields that were sent in the request ───────
                $parking->update(
                    $request->only([
                        'state_id', 'city_id', 'name', 'description',
                        'address', 'latitude', 'longitude', 'total_slots', 'status',
                    ])
                );

                // ── Sync facilities if provided ────────────────────────────
                if ($request->has('facility_ids')) {
                    // sync() with null/empty array removes ALL facility links
                    $parking->facilities()->sync($request->facility_ids ?? []);
                }
            });

            $parking->load(['state', 'city', 'facilities', 'images', 'owner']);

            return $this->successResponse(
                new ParkingResource($parking),
                'Parking location updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingController@update failed', [
                'parking_id' => $parking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update parking location.');
        }
    }

    // =========================================================
    // DESTROY — Soft Delete a Parking Location
    // =========================================================

    /**
     * Soft-delete a parking location.
     *
     * WHY SOFT DELETE?
     *   Bookings reference parking_id. Hard-deleting a parking would
     *   break historical booking records. Soft delete marks it as
     *   deleted without removing the row.
     *
     * SAFETY CHECK:
     *   Cannot delete a parking with active/confirmed bookings.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Parking $parking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if ($parking->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse(
                    'You can only delete your own parking locations.'
                );
            }

            // ── Safety: check for active bookings ──────────────────────────
            $activeBookings = $parking->slots()
                ->whereHas('bookings', fn ($q) => $q->whereIn('booking_status', ['confirmed', 'checked_in']))
                ->exists();

            if ($activeBookings) {
                return $this->errorResponse(
                    'Cannot delete this parking — it has active bookings. Please wait for all active bookings to complete.',
                    409 // 409 Conflict
                );
            }

            $parking->delete(); // Soft delete (sets deleted_at)

            return $this->successResponse(
                null,
                'Parking location deleted successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingController@destroy failed', [
                'parking_id' => $parking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete parking location.');
        }
    }
}