<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreStateRequest;
use App\Http\Requests\Master\UpdateStateRequest;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * StateController
 * ============================================================
 *
 * Manages the States master data for the Smart Parking System.
 *
 * States are the top level of our location hierarchy:
 *   State → City → Parking Location → Parking Slot
 *
 * These APIs are used by:
 *   - Admin Panel: manage the master list of states
 *   - Owner App: populate the state dropdown when adding a parking
 *   - User App: filter/search parking by state
 *
 * All endpoints are protected by auth:sanctum middleware.
 * Write operations (create/update/delete) additionally require
 * the 'super_admin' or 'admin' role — enforced via route middleware.
 *
 * RESPONSE FORMAT (consistent across all endpoints):
 * {
 *     "success": true | false,
 *     "message": "Human-readable status message",
 *     "data": { ... } | null
 * }
 */
class StateController extends Controller
{
    /**
     * GET /api/v1/master/states
     *
     * Return a paginated, searchable list of states.
     *
     * QUERY PARAMETERS:
     *   ?search=maharashtra     → filter by name or code (case-insensitive)
     *   ?status=active          → filter by status
     *   ?per_page=15            → items per page (default: 15, max: 100)
     *   ?page=2                 → page number
     *
     * USED BY:
     *   - Admin Panel: States list page with search and pagination
     *   - Owner/User App: Full state dropdown (no pagination, status=active)
     */
    public function index(Request $request): JsonResponse
    {
        // Start building the query.
        $query = State::query();

        // ── Search ───────────────────────────────────────────────────
        // If a search term is provided, filter by name OR code.
        // LIKE '%term%' gives partial match — "mah" finds "Maharashtra".
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // ── Status Filter ─────────────────────────────────────────────
        // Allow filtering to only active states (for dropdowns) or
        // only inactive states (for admin review).
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // ── Ordering ──────────────────────────────────────────────────
        // Alphabetical by name is the most natural order for a
        // state list in a dropdown or admin table.
        $query->orderBy('name', 'asc');

        // ── Pagination ────────────────────────────────────────────────
        // Clamp per_page between 1 and 100 to prevent abuse.
        $perPage = min((int) $request->input('per_page', 15), 100);
        $states  = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'States retrieved successfully.',
            'data'    => $states,
        ], 200);
    }

    /**
     * POST /api/v1/master/states
     *
     * Create a new State record.
     *
     * The StoreStateRequest handles validation before this method runs.
     * If validation fails, Laravel automatically returns a 422 response.
     *
     * USED BY: Admin Panel — Add State form
     */
    public function store(StoreStateRequest $request): JsonResponse
    {
        // validatedData() returns only the fields that passed validation.
        // This prevents mass-assignment of unexpected fields.
        $state = State::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'State created successfully.',
            'data'    => $state,
        ], 201); // 201 Created
    }

    /**
     * GET /api/v1/master/states/{state}
     *
     * Return a single State record with its cities count.
     *
     * Route model binding automatically finds the State by ID
     * and returns a 404 JSON response if not found.
     *
     * USED BY:
     *   - Admin Panel: View State detail / Edit form pre-fill
     *   - APIs that need full state data with city context
     */
    public function show(State $state): JsonResponse
    {
        // Load the count of cities in this state without loading
        // all city records — efficient and informative.
        $state->loadCount('cities');

        return response()->json([
            'success' => true,
            'message' => 'State retrieved successfully.',
            'data'    => $state,
        ], 200);
    }

    /**
     * PUT /api/v1/master/states/{state}
     *
     * Update an existing State record.
     *
     * UpdateStateRequest ignores the current record in unique checks
     * so the name/code can be "updated" to the same value without
     * triggering a false unique violation.
     *
     * USED BY: Admin Panel — Edit State form
     */
    public function update(UpdateStateRequest $request, State $state): JsonResponse
    {
        $state->update($request->validated());

        // Reload the model to get the latest DB values
        // (in case any DB triggers or observers modified fields).
        $state->refresh();

        return response()->json([
            'success' => true,
            'message' => 'State updated successfully.',
            'data'    => $state,
        ], 200);
    }

    /**
     * DELETE /api/v1/master/states/{state}
     *
     * Soft-delete a State by setting its status to "inactive".
     *
     * WHY NOT HARD DELETE?
     *   States may already be referenced by cities, and cities by
     *   parkings. A hard delete would break those FK relationships
     *   or cascade-delete all related data — too destructive.
     *
     *   Setting status to "inactive" effectively hides the state
     *   from dropdowns and search results without losing any data.
     *
     * USED BY: Admin Panel — Deactivate State action
     */
    public function destroy(State $state): JsonResponse
    {
        // Block deactivation if the state has active cities.
        // Deactivating a state with active cities would silently
        // break the city dropdowns for any parkings in those cities.
        $activeCityCount = $state->cities()->where('status', 'active')->count();

        if ($activeCityCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot deactivate this state. It has {$activeCityCount} active " .
                             ($activeCityCount === 1 ? 'city' : 'cities') .
                             ". Please deactivate all cities in this state first.",
                'data'    => null,
            ], 422); // 422 Unprocessable Entity
        }

        $state->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'State deactivated successfully.',
            'data'    => null,
        ], 200);
    }

    /**
     * PATCH /api/v1/master/states/{state}/toggle-status
     *
     * Toggle a State's status between "active" and "inactive".
     *
     * This is a convenience endpoint for the Admin Panel's
     * quick status toggle switch — avoids sending a full update
     * payload just to flip a status.
     *
     * USED BY: Admin Panel — Status toggle switch on States list
     */
    public function toggleStatus(State $state): JsonResponse
    {
        // If trying to activate a state, no dependency check needed.
        // If trying to deactivate, check for active cities.
        if ($state->status === 'active') {
            $activeCityCount = $state->cities()->where('status', 'active')->count();

            if ($activeCityCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot deactivate. This state has {$activeCityCount} active " .
                                 ($activeCityCount === 1 ? 'city' : 'cities') . ".",
                    'data'    => null,
                ], 422);
            }
        }

        $newStatus = $state->status === 'active' ? 'inactive' : 'active';
        $state->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "State status changed to {$newStatus}.",
            'data'    => $state->refresh(),
        ], 200);
    }
}