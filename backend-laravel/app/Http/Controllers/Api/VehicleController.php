<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * VehicleController
 * ============================================================
 *
 * Manages the authenticated user's registered vehicles.
 *
 * ENDPOINTS:
 *   GET    /api/v1/vehicles             → List own vehicles (user) or all (admin)
 *   POST   /api/v1/vehicles             → Register a new vehicle
 *   GET    /api/v1/vehicles/{vehicle}   → Single vehicle detail with documents
 *   PUT    /api/v1/vehicles/{vehicle}   → Update vehicle details
 *   DELETE /api/v1/vehicles/{vehicle}   → Soft-delete (deactivate)
 *
 * ACCESS RULES:
 *   Users  → can only manage their OWN vehicles
 *   Admin  → can view any vehicle (for compliance/support)
 *   Owner  → no special vehicle access (owners are also users for their personal vehicles)
 *
 * VEHICLE NUMBER NORMALIZATION:
 *   The Vehicle model's setVehicleNumberAttribute() mutator ensures
 *   plates are always stored as uppercase, no spaces: "HR26DQ8849".
 *   The StoreVehicleRequest also normalizes before validation.
 *
 * SAFETY ON DELETE:
 *   Cannot soft-delete a vehicle that has an active (confirmed/checked_in) booking.
 *   The vehicle record is preserved for historical booking records.
 *
 * FUTURE SCALABILITY:
 *   - Add VehiclePolicy for Laravel Policy-based authorization
 *   - Add `default_vehicle_id` to users table for quick booking
 *   - Add event: VehicleRegistered for welcome notification
 *   - Add ANPR (number plate recognition) verification integration
 */
class VehicleController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List Vehicles
    // =========================================================

    /**
     * Return a paginated list of vehicles.
     *
     * ACCESS:
     *   Users  → their own vehicles only
     *   Admin  → all vehicles (with user info for support)
     *
     * QUERY PARAMETERS:
     *   ?status=active             → filter by status
     *   ?vehicle_type_id=2         → filter by type (car, bike, etc.)
     *   ?search=HR26               → search by number plate, name, or brand
     *   ?per_page=15               → page size (max 50)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user  = $request->user();
            $query = Vehicle::query()->with('vehicleType');

            // ── Scope: users see only their vehicles; admins see all ────────
            if ($user->hasRole('admin')) {
                $query->with('user'); // Include owner info for admin support views
            } else {
                // Regular users and owners acting as users
                $query->forUser($user->id);
            }

            // ── Filter: by status ──────────────────────────────────────────
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                // Default: show all statuses for the user (they manage inactive too)
                // Soft-deleted are always excluded by Eloquent's SoftDeletes trait
            }

            // ── Filter: by vehicle type ────────────────────────────────────
            if ($request->filled('vehicle_type_id')) {
                $query->ofType((int) $request->vehicle_type_id);
            }

            // ── Search: by plate, name, or brand ───────────────────────────
            if ($request->filled('search')) {
                $term = '%' . $request->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('vehicle_number', 'LIKE', $term)
                      ->orWhere('vehicle_name', 'LIKE', $term)
                      ->orWhere('vehicle_brand', 'LIKE', $term);
                });
            }

            // ── Sort: active first, then by latest ─────────────────────────
            $query->orderByRaw("FIELD(status, 'active', 'inactive')")->latest();

            $perPage  = min((int) ($request->per_page ?? 15), 50);
            $vehicles = $query->paginate($perPage);

            return $this->successResponse(
                VehicleResource::collection($vehicles)->response()->getData(true),
                'Vehicles retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve vehicles.');
        }
    }

    // =========================================================
    // STORE — Register a New Vehicle
    // =========================================================

    /**
     * Register a new vehicle for the authenticated user.
     *
     * FLOW:
     *   1. StoreVehicleRequest validates all fields + plate uniqueness
     *   2. vehicle_number is normalized (uppercase, no spaces) by the request
     *   3. Create Vehicle record with owner = auth user
     *   4. Return the new vehicle
     *
     * NOTE: owner_id is always set to auth()->id() — users cannot
     * register vehicles on behalf of other users.
     *
     * @param  \App\Http\Requests\Vehicle\StoreVehicleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        try {
            $vehicle = Vehicle::create([
                'user_id'         => $request->user()->id, // Always the authenticated user
                'vehicle_type_id' => $request->vehicle_type_id,
                'vehicle_number'  => $request->vehicle_number, // Already normalized by request
                'vehicle_name'    => $request->vehicle_name,
                'vehicle_brand'   => $request->vehicle_brand,
                'vehicle_color'   => $request->vehicle_color,
                'status'          => Vehicle::STATUS_ACTIVE, // New vehicles are active by default
            ]);

            $vehicle->load('vehicleType');

            return $this->createdResponse(
                new VehicleResource($vehicle),
                'Vehicle registered successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleController@store failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to register vehicle. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Single Vehicle Detail
    // =========================================================

    /**
     * Return full details for a single vehicle, including all documents.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Vehicle      $vehicle  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You do not have access to this vehicle.');
            }

            // ── Eager-load all detail relationships ────────────────────────
            $vehicle->load([
                'vehicleType',
                'user',
                'documents' => fn ($q) => $q->orderByDesc('created_at'), // Latest docs first
            ]);

            return $this->successResponse(
                new VehicleResource($vehicle),
                'Vehicle retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve vehicle.');
        }
    }

    // =========================================================
    // UPDATE — Update Vehicle Details
    // =========================================================

    /**
     * Update a vehicle's details.
     *
     * Supports partial updates — only send the fields you want to change.
     * Changing vehicle_number validates uniqueness (excluding own row).
     *
     * @param  \App\Http\Requests\Vehicle\UpdateVehicleRequest $request
     * @param  \App\Models\Vehicle                             $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        try {
            // ── Authorization: only the vehicle owner or admin ─────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You can only update your own vehicles.');
            }

            // ── Extract only the fields that were actually sent ────────────
            $updateData = array_filter(
                $request->only([
                    'vehicle_type_id',
                    'vehicle_number',
                    'vehicle_name',
                    'vehicle_brand',
                    'vehicle_color',
                    'status',
                ]),
                fn ($value) => ! is_null($value)
            );

            if (empty($updateData)) {
                return $this->errorResponse('No update data provided.', 400);
            }

            $vehicle->update($updateData);
            $vehicle->load('vehicleType');

            return $this->successResponse(
                new VehicleResource($vehicle),
                'Vehicle updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleController@update failed', [
                'vehicle_id' => $vehicle->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update vehicle.');
        }
    }

    // =========================================================
    // DESTROY — Soft-Delete a Vehicle
    // =========================================================

    /**
     * Soft-delete a vehicle (mark as deleted without removing from DB).
     *
     * SAFETY CHECK:
     *   Cannot delete a vehicle that has an active or confirmed booking.
     *   Historical bookings referencing this vehicle must remain intact.
     *
     * WHY SOFT DELETE?
     *   Booking records reference vehicle_id. Hard-deleting a vehicle
     *   would break those booking history records. Soft delete (sets
     *   deleted_at) removes it from the user's list while preserving
     *   all historical references.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Vehicle      $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You can only remove your own vehicles.');
            }

            // ── Safety: check for active bookings ──────────────────────────
            $hasActiveBooking = $vehicle->bookings()
                ->whereIn('booking_status', [
                    'pending',
                    'confirmed',
                    'checked_in',
                ])
                ->exists();

            if ($hasActiveBooking) {
                return $this->errorResponse(
                    'Cannot remove this vehicle — it has an active booking. Please wait for the booking to complete or cancel it first.',
                    409
                );
            }

            $vehicle->delete(); // Soft delete — sets deleted_at timestamp

            return $this->successResponse(
                null,
                'Vehicle removed successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleController@destroy failed', [
                'vehicle_id' => $vehicle->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to remove vehicle.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Check if the authenticated user can access a given vehicle.
     *
     * Rules:
     *   - Admin: always yes (for support and compliance)
     *   - User:  yes only if they own the vehicle
     *
     * @param  \App\Models\User    $user
     * @param  \App\Models\Vehicle $vehicle
     * @return bool
     */
    private function canAccessVehicle($user, Vehicle $vehicle): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $vehicle->user_id === $user->id;
    }
}