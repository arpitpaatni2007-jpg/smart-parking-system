<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreVehicleTypeRequest;
use App\Http\Requests\Master\UpdateVehicleTypeRequest;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * VehicleTypeController
 * ============================================================
 *
 * Manages the Vehicle Types master data.
 *
 * Vehicle types define what categories of vehicles the platform
 * supports: Two Wheeler, Four Wheeler, Taxi, EV, etc.
 *
 * USED BY:
 *   - Admin Panel: Manage vehicle type master list
 *   - Owner App: Vehicle type filter on "Add Parking" and slot setup
 *   - User App: Vehicle type selector on booking screen
 *   - PricingRule: Each pricing rule is tied to a vehicle type
 *   - ParkingSlot: Each slot supports a specific vehicle type
 *   - Vehicle: Each saved vehicle has a vehicle type
 *
 * DEACTIVATION RULE:
 *   A vehicle type cannot be deactivated if active parking slots
 *   or saved user vehicles still reference it.
 */
class VehicleTypeController extends Controller
{
    /**
     * GET /api/v1/master/vehicle-types
     *
     * QUERY PARAMETERS:
     *   ?search=wheeler     → search by name
     *   ?status=active      → filter by status
     *   ?per_page=15
     *
     * USED BY:
     *   - Admin Panel: Vehicle types list
     *   - Owner/User App: Vehicle type dropdown (pass status=active)
     */
    public function index(Request $request): JsonResponse
    {
        $query = VehicleType::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->input('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('name', 'asc');

        $perPage      = min((int) $request->input('per_page', 15), 100);
        $vehicleTypes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle types retrieved successfully.',
            'data'    => $vehicleTypes,
        ], 200);
    }

    /**
     * POST /api/v1/master/vehicle-types
     *
     * Create a new Vehicle Type.
     */
    public function store(StoreVehicleTypeRequest $request): JsonResponse
    {
        $vehicleType = VehicleType::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type created successfully.',
            'data'    => $vehicleType,
        ], 201);
    }

    /**
     * GET /api/v1/master/vehicle-types/{vehicle_type}
     *
     * Return a single Vehicle Type with usage counts.
     * Counts help admin understand impact before deactivating.
     */
    public function show(VehicleType $vehicleType): JsonResponse
    {
        // Load counts of related records to help admin understand
        // the impact of deactivating this vehicle type.
        $vehicleType->loadCount([
            'parkingSlots',
            'vehicles',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type retrieved successfully.',
            'data'    => $vehicleType,
        ], 200);
    }

    /**
     * PUT /api/v1/master/vehicle-types/{vehicle_type}
     *
     * Update an existing Vehicle Type.
     */
    public function update(UpdateVehicleTypeRequest $request, VehicleType $vehicleType): JsonResponse
    {
        $vehicleType->update($request->validated());
        $vehicleType->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type updated successfully.',
            'data'    => $vehicleType,
        ], 200);
    }

    /**
     * DELETE /api/v1/master/vehicle-types/{vehicle_type}
     *
     * Soft-delete (deactivate) a Vehicle Type.
     *
     * Blocked if active parking slots or saved vehicles reference it —
     * deactivating would silently break slot filtering and bookings.
     */
    public function destroy(VehicleType $vehicleType): JsonResponse
    {
        // Check for active parking slots of this type.
        $activeSlotCount = $vehicleType->parkingSlots()
            ->where('status', 'available')
            ->count();

        if ($activeSlotCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot deactivate. {$activeSlotCount} active parking " .
                             ($activeSlotCount === 1 ? 'slot uses' : 'slots use') .
                             " this vehicle type.",
                'data'    => null,
            ], 422);
        }

        // Check for user vehicles of this type.
        $vehicleCount = $vehicleType->vehicles()->count();

        if ($vehicleCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot deactivate. {$vehicleCount} saved " .
                             ($vehicleCount === 1 ? 'vehicle uses' : 'vehicles use') .
                             " this vehicle type.",
                'data'    => null,
            ], 422);
        }

        $vehicleType->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type deactivated successfully.',
            'data'    => null,
        ], 200);
    }

    /**
     * PATCH /api/v1/master/vehicle-types/{vehicle_type}/toggle-status
     *
     * Toggle Vehicle Type status.
     */
    public function toggleStatus(VehicleType $vehicleType): JsonResponse
    {
        if ($vehicleType->status === 'active') {
            $activeSlotCount = $vehicleType->parkingSlots()
                ->where('status', 'available')
                ->count();

            if ($activeSlotCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot deactivate. {$activeSlotCount} active parking " .
                                 ($activeSlotCount === 1 ? 'slot uses' : 'slots use') .
                                 " this vehicle type.",
                    'data'    => null,
                ], 422);
            }
        }

        $newStatus = $vehicleType->status === 'active' ? 'inactive' : 'active';
        $vehicleType->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Vehicle type status changed to {$newStatus}.",
            'data'    => $vehicleType->refresh(),
        ], 200);
    }
}