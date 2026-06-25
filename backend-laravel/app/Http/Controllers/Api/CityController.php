<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreCityRequest;
use App\Http\Requests\Master\UpdateCityRequest;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * CityController
 * ============================================================
 *
 * Manages the Cities master data for the Smart Parking System.
 *
 * Cities sit at the second level of the location hierarchy:
 *   State → City → Parking Location → Parking Slot
 *
 * USED BY:
 *   - Admin Panel: Manage city master data
 *   - Owner App: City dropdown (filtered by selected state)
 *   - User App: Search and filter parking by city
 *
 * The key feature here is the state_id dependency:
 *   - The list endpoint supports filtering by state_id so the
 *     Owner App can load only cities for the selected state.
 *   - Creation requires a valid state_id.
 *   - Deactivation checks for active parkings in the city.
 */
class CityController extends Controller
{
    /**
     * GET /api/v1/master/cities
     *
     * Return a paginated, searchable list of cities.
     *
     * QUERY PARAMETERS:
     *   ?state_id=5         → filter cities by state (used by cascaded dropdowns)
     *   ?search=mumbai      → search by city name
     *   ?status=active      → filter by status
     *   ?per_page=15
     *
     * USED BY:
     *   - Admin Panel: Cities list with state filter
     *   - Owner App: City dropdown after user selects a state
     *   - User App: City-based search filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = City::query()->with('state:id,name,code');

        // ── State Filter ──────────────────────────────────────────────
        // The cascaded dropdown in Owner/User App sends state_id to
        // get only cities belonging to that state.
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->input('state_id'));
        }

        // ── Search ────────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // ── Status Filter ─────────────────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('name', 'asc');

        $perPage = min((int) $request->input('per_page', 15), 100);
        $cities  = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Cities retrieved successfully.',
            'data'    => $cities,
        ], 200);
    }

    /**
     * POST /api/v1/master/cities
     *
     * Create a new City record linked to a State.
     */
    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = City::create($request->validated());

        // Load the state relationship so the response includes
        // the state name — useful for the admin panel confirmation view.
        $city->load('state:id,name,code');

        return response()->json([
            'success' => true,
            'message' => 'City created successfully.',
            'data'    => $city,
        ], 201);
    }

    /**
     * GET /api/v1/master/cities/{city}
     *
     * Return a single City with its State and parking count.
     */
    public function show(City $city): JsonResponse
    {
        $city->load('state:id,name,code');
        $city->loadCount('parkings');

        return response()->json([
            'success' => true,
            'message' => 'City retrieved successfully.',
            'data'    => $city,
        ], 200);
    }

    /**
     * PUT /api/v1/master/cities/{city}
     *
     * Update an existing City record.
     */
    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $city->update($request->validated());
        $city->refresh()->load('state:id,name,code');

        return response()->json([
            'success' => true,
            'message' => 'City updated successfully.',
            'data'    => $city,
        ], 200);
    }

    /**
     * DELETE /api/v1/master/cities/{city}
     *
     * Soft-delete (deactivate) a City.
     *
     * Blocked if the city has active parking locations —
     * deactivating would break those listings.
     */
    public function destroy(City $city): JsonResponse
    {
        // Check if any active parkings exist in this city.
        $activeParkingCount = $city->parkings()
            ->where('status', 'approved')
            ->count();

        if ($activeParkingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot deactivate this city. It has {$activeParkingCount} active " .
                             ($activeParkingCount === 1 ? 'parking location' : 'parking locations') . ".",
                'data'    => null,
            ], 422);
        }

        $city->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'City deactivated successfully.',
            'data'    => null,
        ], 200);
    }

    /**
     * PATCH /api/v1/master/cities/{city}/toggle-status
     *
     * Toggle City status between active and inactive.
     */
    public function toggleStatus(City $city): JsonResponse
    {
        if ($city->status === 'active') {
            $activeParkingCount = $city->parkings()
                ->where('status', 'approved')
                ->count();

            if ($activeParkingCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot deactivate. This city has {$activeParkingCount} active " .
                                 ($activeParkingCount === 1 ? 'parking location' : 'parking locations') . ".",
                    'data'    => null,
                ], 422);
            }
        }

        $newStatus = $city->status === 'active' ? 'inactive' : 'active';
        $city->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "City status changed to {$newStatus}.",
            'data'    => $city->refresh()->load('state:id,name,code'),
        ], 200);
    }

    /**
     * GET /api/v1/master/states/{state}/cities
     *
     * Return all active cities for a given state.
     *
     * This endpoint is specifically for the cascaded dropdown:
     *   1. User selects a State
     *   2. App calls this endpoint with the state's ID
     *   3. City dropdown is populated with the response
     *
     * No pagination here — dropdowns need the full list.
     * Only active cities are returned for dropdowns.
     */
    public function byState(State $state): JsonResponse
    {
        $cities = $state->cities()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'status']);

        return response()->json([
            'success' => true,
            'message' => "Cities for {$state->name} retrieved successfully.",
            'data'    => [
                'state'  => $state->only(['id', 'name', 'code']),
                'cities' => $cities,
            ],
        ], 200);
    }
}