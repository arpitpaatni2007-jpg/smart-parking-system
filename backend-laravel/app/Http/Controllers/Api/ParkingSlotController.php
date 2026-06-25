<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingSlotRequest;
use App\Http\Requests\Parking\UpdateParkingSlotRequest;
use App\Http\Resources\ParkingSlotResource;
use App\Models\Parking;
use App\Models\ParkingSlot;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * ParkingSlotController
 * ============================================================
 *
 * Manages individual parking slots within a parking location.
 *
 * NESTED RESOURCE ROUTES (under parkings):
 *   GET    /api/v1/parkings/{parking}/slots          → index
 *   POST   /api/v1/parkings/{parking}/slots          → store
 *   GET    /api/v1/parkings/{parking}/slots/{slot}   → show
 *   PUT    /api/v1/parkings/{parking}/slots/{slot}   → update
 *   DELETE /api/v1/parkings/{parking}/slots/{slot}   → destroy
 *
 * All routes are scoped to a parent parking — you always know
 * which parking a slot belongs to from the URL context.
 *
 * ROUTE MODEL BINDING:
 *   Laravel automatically resolves {parking} → Parking model
 *   and {slot} → ParkingSlot model from the route parameters.
 */
class ParkingSlotController extends Controller
{
    use ApiResponse;

    /**
     * Helper: verify the authenticated user owns the parking.
     * Reused across all methods to avoid repetition.
     */
    private function ownsParking(Request $request, Parking $parking): bool
    {
        return $parking->owner_id === $request->user()->id;
    }

    // =========================================================
    // INDEX — List Slots for a Parking
    // =========================================================

    /**
     * Return all slots for a given parking location.
     *
     * QUERY PARAMETERS:
     *   ?vehicle_type_id=2  → filter by vehicle type (e.g. only car slots)
     *   ?slot_type=ev       → filter by slot category
     *   ?status=available   → filter by availability
     *
     * FLUTTER USE CASE:
     *   Booking flow: user picks "Car" → see available car slots.
     *   Owner dashboard: manage all slots in their parking.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Parking $parking): JsonResponse
    {
        try {
            $query = $parking->slots()->with('vehicleType');

            // Filter by vehicle type
            if ($request->filled('vehicle_type_id')) {
                $query->where('vehicle_type_id', $request->vehicle_type_id);
            }

            // Filter by slot category
            if ($request->filled('slot_type')) {
                $query->where('slot_type', $request->slot_type);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // For users (not owner/admin): only show available slots
            if (! $request->user()->hasRole(['admin', 'owner'])) {
                $query->where('status', 'available');
            }

            $slots = $query->orderBy('slot_number')->get();

            return $this->successResponse(
                ParkingSlotResource::collection($slots),
                'Parking slots retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingSlotController@index failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve parking slots.');
        }
    }

    // =========================================================
    // STORE — Add a New Slot to a Parking
    // =========================================================

    /**
     * Add a new slot to the given parking location.
     *
     * Only the owner of the parking can add slots.
     *
     * NOTE ON total_slots:
     *   When a slot is added, we increment parking.total_slots.
     *   This keeps the denormalized count in sync.
     *
     * @param  \App\Http\Requests\Parking\StoreParkingSlotRequest $request
     * @param  \App\Models\Parking                                $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreParkingSlotRequest $request, Parking $parking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if (! $this->ownsParking($request, $parking)) {
                return $this->forbiddenResponse('You can only add slots to your own parking locations.');
            }

            $slot = ParkingSlot::create([
                'parking_id'      => $parking->id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'slot_number'     => $request->slot_number,
                'slot_type'       => $request->slot_type,
                'status'          => $request->status ?? 'available',
            ]);

            // ── Keep total_slots count in sync ─────────────────────────────
            /**
             * Increment the denormalized total_slots counter.
             * increment() is atomic (uses SQL INCREMENT) — safe for concurrency.
             */
            $parking->increment('total_slots');

            $slot->load('vehicleType');

            return $this->createdResponse(
                new ParkingSlotResource($slot),
                'Parking slot added successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingSlotController@store failed', [
                'parking_id' => $parking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to add parking slot.');
        }
    }

    // =========================================================
    // SHOW — Get Single Slot Detail
    // =========================================================

    /**
     * Return details for a single parking slot.
     *
     * Route model binding automatically resolves {slot} to a ParkingSlot,
     * but we must verify it actually belongs to the given {parking}.
     * Without this check, someone could request:
     *   GET /parkings/1/slots/999  (slot 999 belongs to parking 5!)
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @param  \App\Models\ParkingSlot  $slot
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Parking $parking, ParkingSlot $slot): JsonResponse
    {
        try {
            // ── Verify slot belongs to this parking ────────────────────────
            if ($slot->parking_id !== $parking->id) {
                return $this->notFoundResponse('Slot not found in this parking location.');
            }

            $slot->load(['vehicleType', 'parking']);

            return $this->successResponse(
                new ParkingSlotResource($slot),
                'Parking slot retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingSlotController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve parking slot.');
        }
    }

    // =========================================================
    // UPDATE — Update a Slot
    // =========================================================

    /**
     * Update a parking slot's details.
     *
     * COMMON USE CASES:
     *   - Owner puts slot in maintenance: status → 'maintenance'
     *   - Owner brings slot back online: status → 'available'
     *   - Owner upgrades slot to EV: slot_type → 'ev'
     *
     * RESTRICTION:
     *   Status cannot be changed to 'booked' or 'reserved' manually.
     *   Those are set by the booking system only.
     *
     * @param  \App\Http\Requests\Parking\UpdateParkingSlotRequest $request
     * @param  \App\Models\Parking                                 $parking
     * @param  \App\Models\ParkingSlot                             $slot
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateParkingSlotRequest $request, Parking $parking, ParkingSlot $slot): JsonResponse
    {
        try {
            if (! $this->ownsParking($request, $parking)) {
                return $this->forbiddenResponse('You can only update slots in your own parking locations.');
            }

            if ($slot->parking_id !== $parking->id) {
                return $this->notFoundResponse('Slot not found in this parking location.');
            }

            // Prevent manually changing a currently-booked slot's status
            if ($slot->status === 'booked' && $request->has('status')) {
                return $this->errorResponse(
                    'Cannot change status of a slot that is currently booked. Status will update automatically when booking ends.',
                    409
                );
            }

            $slot->update(
                $request->only(['vehicle_type_id', 'slot_number', 'slot_type', 'status'])
            );

            $slot->load('vehicleType');

            return $this->successResponse(
                new ParkingSlotResource($slot),
                'Parking slot updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingSlotController@update failed', [
                'slot_id' => $slot->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update parking slot.');
        }
    }

    // =========================================================
    // DESTROY — Delete a Slot
    // =========================================================

    /**
     * Soft-delete a parking slot.
     *
     * Cannot delete a slot that is currently booked.
     * Decrements parking.total_slots count after deletion.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @param  \App\Models\ParkingSlot  $slot
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Parking $parking, ParkingSlot $slot): JsonResponse
    {
        try {
            if (! $this->ownsParking($request, $parking)) {
                return $this->forbiddenResponse('You can only delete slots from your own parking locations.');
            }

            if ($slot->parking_id !== $parking->id) {
                return $this->notFoundResponse('Slot not found in this parking location.');
            }

            // ── Safety: cannot delete a currently-booked slot ──────────────
            if ($slot->status === 'booked') {
                return $this->errorResponse(
                    'Cannot delete a slot that is currently booked. Wait for the booking to complete.',
                    409
                );
            }

            $slot->delete(); // Soft delete

            // ── Decrement the denormalized total_slots count ───────────────
            if ($parking->total_slots > 0) {
                $parking->decrement('total_slots');
            }

            return $this->successResponse(null, 'Parking slot deleted successfully.');

        } catch (Throwable $e) {
            Log::error('ParkingSlotController@destroy failed', [
                'slot_id' => $slot->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete parking slot.');
        }
    }
}