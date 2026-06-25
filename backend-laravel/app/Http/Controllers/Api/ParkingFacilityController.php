<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreParkingFacilityRequest;
use App\Http\Requests\Master\UpdateParkingFacilityRequest;
use App\Models\ParkingFacility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * ParkingFacilityController
 * ============================================================
 *
 * Manages the Parking Facilities master data.
 *
 * Parking facilities are amenities that a parking location offers:
 * CCTV, EV Charging, Covered Parking, Security Guard, Washroom,
 * Waiting Area, 24/7 Access, etc.
 *
 * These are stored in this master table and linked to individual
 * parking locations via the `parking_facility` pivot table.
 *
 * USED BY:
 *   - Admin Panel: Manage the facilities master list
 *   - Owner App: "Select Facilities" checklist when adding parking
 *   - User App: Facility-based search filter ("Show EV charging only")
 *   - Parking detail screen: Display facility icons
 *
 * DEACTIVATION RULE:
 *   A facility can always be deactivated. It just stops appearing
 *   in the "Add Parking" checklist going forward. Existing parkings
 *   that already have this facility keep their link in the pivot
 *   table — we don't retroactively remove them.
 */
class ParkingFacilityController extends Controller
{
    /**
     * GET /api/v1/master/parking-facilities
     *
     * QUERY PARAMETERS:
     *   ?search=cctv        → search by name or description
     *   ?status=active      → filter by status
     *   ?per_page=15
     *
     * USED BY:
     *   - Admin Panel: Facilities list page
     *   - Owner App: Full facility checklist (pass status=active, no pagination)
     *   - User App: Facility filter panel
     */
    public function index(Request $request): JsonResponse
    {
        $query = ParkingFacility::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('name', 'asc');

        $perPage   = min((int) $request->input('per_page', 15), 100);
        $facilities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Parking facilities retrieved successfully.',
            'data'    => $facilities,
        ], 200);
    }

    /**
     * POST /api/v1/master/parking-facilities
     *
     * Create a new Parking Facility.
     */
    public function store(StoreParkingFacilityRequest $request): JsonResponse
    {
        $facility = ParkingFacility::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Parking facility created successfully.',
            'data'    => $facility,
        ], 201);
    }

    /**
     * GET /api/v1/master/parking-facilities/{parking_facility}
     *
     * Return a single Parking Facility with how many parkings use it.
     */
    public function show(ParkingFacility $parkingFacility): JsonResponse
    {
        // Count how many parking locations have this facility.
        // Useful for admin to understand usage before deactivating.
        $parkingFacility->loadCount('parkings');

        return response()->json([
            'success' => true,
            'message' => 'Parking facility retrieved successfully.',
            'data'    => $parkingFacility,
        ], 200);
    }

    /**
     * PUT /api/v1/master/parking-facilities/{parking_facility}
     *
     * Update an existing Parking Facility.
     */
    public function update(UpdateParkingFacilityRequest $request, ParkingFacility $parkingFacility): JsonResponse
    {
        $parkingFacility->update($request->validated());
        $parkingFacility->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Parking facility updated successfully.',
            'data'    => $parkingFacility,
        ], 200);
    }

    /**
     * DELETE /api/v1/master/parking-facilities/{parking_facility}
     *
     * Soft-delete (deactivate) a Parking Facility.
     *
     * Unlike States/Cities, deactivating a facility is safe:
     *   - It stops appearing in new parking's facility checklist.
     *   - Existing parking → facility links are preserved (not removed).
     *   - The User App search filter simply won't show this option.
     *
     * We still show a warning if it's in use, but don't block it.
     */
    public function destroy(ParkingFacility $parkingFacility): JsonResponse
    {
        $parkingFacility->update(['status' => 'inactive']);

        // Count how many parkings had this facility (for info only).
        $parkingCount = $parkingFacility->parkings()->count();

        $message = 'Parking facility deactivated successfully.';
        if ($parkingCount > 0) {
            $message .= " Note: {$parkingCount} existing " .
                        ($parkingCount === 1 ? 'parking location' : 'parking locations') .
                        " will keep this facility tag on their profiles.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => null,
        ], 200);
    }

    /**
     * PATCH /api/v1/master/parking-facilities/{parking_facility}/toggle-status
     *
     * Toggle Parking Facility status between active and inactive.
     */
    public function toggleStatus(ParkingFacility $parkingFacility): JsonResponse
    {
        $newStatus = $parkingFacility->status === 'active' ? 'inactive' : 'active';
        $parkingFacility->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Parking facility status changed to {$newStatus}.",
            'data'    => $parkingFacility->refresh(),
        ], 200);
    }
}