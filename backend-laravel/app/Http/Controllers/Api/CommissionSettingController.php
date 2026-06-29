<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommissionSetting\StoreCommissionSettingRequest;
use App\Http\Requests\CommissionSetting\UpdateCommissionSettingRequest;
use App\Http\Resources\CommissionSettingResource;
use App\Models\CommissionSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * CommissionSettingController
 * ============================================================
 *
 * Manages the platform commission configuration for the Smart
 * Parking Management System.
 *
 * HOW COMMISSION WORKS:
 * When a customer pays ₹100 for parking:
 *   commission_percent  = 20% → Platform keeps ₹20
 *   owner_share_percent = 80% → Parking owner receives ₹80
 *
 * Both percentages are stored in the CommissionSetting model
 * and must ALWAYS sum to exactly 100%.
 *
 * ONLY ONE ACTIVE RECORD AT A TIME:
 * The system enforces a single active commission setting at any
 * given time. Activating a new record automatically deactivates
 * the previously active one. Historical records (inactive) are
 * retained for audit and reconciliation purposes.
 *
 * EXISTING MODEL:
 *   App\Models\CommissionSetting
 *   Fillable: commission_percent, owner_share_percent, status
 *   Methods:  isBalanced(), getActive() (static), calculatePlatformFee(),
 *             calculateOwnerAmount(), scopeActive()
 *
 * ENDPOINTS:
 *   GET    /api/v1/commission-settings           → index   (list all)
 *   POST   /api/v1/commission-settings           → store   (create + activate)
 *   GET    /api/v1/commission-settings/active    → active  (current live rate)
 *   GET    /api/v1/commission-settings/{id}      → show    (single record)
 *   PUT    /api/v1/commission-settings/{id}      → update  (modify + re-activate)
 *   DELETE /api/v1/commission-settings/{id}      → destroy (Super Admin only)
 *   PATCH  /api/v1/commission-settings/{id}/activate → activate (set as live)
 *
 * ROLE ACCESS:
 *   Super Admin → full CRUD + delete + activate
 *   Admin       → GET only (read-only view of commission rates)
 *   Others      → no access
 *
 * FUTURE SCALABILITY:
 *   - Add `parking_id` FK for per-parking commission overrides.
 *   - Add `effective_from` / `effective_to` for scheduling rate changes.
 *   - Add `type` (global | per_parking | tiered) for multi-tier commission.
 *   - Cache the active setting result (Redis, TTL 10 min) since it is
 *     read on every booking payment — not just admin panel views.
 */
class CommissionSettingController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/commission-settings
     *
     * Return a paginated list of all commission setting records.
     * Most recent first so the admin sees the current rate at top.
     *
     * QUERY PARAMETERS:
     *   ?status=active   → filter to only active/inactive records
     *   ?per_page=15
     *
     * ACCESS: Super Admin, Admin (read-only)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse(
                'You are not authorized to view commission settings.',
                403
            );
        }

        $query = CommissionSetting::query();

        // Optional status filter.
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Most recently created / updated first — active record
        // will almost always be the most recent entry.
        $query->orderByRaw("FIELD(status, 'active', 'inactive')")
              ->orderBy('created_at', 'desc');

        $perPage  = min($request->integer('per_page', 15), 100);
        $settings = $query->paginate($perPage);

        return $this->successResponse(
            CommissionSettingResource::collection($settings)->response()->getData(true),
            'Commission settings retrieved successfully.'
        );
    }

    /**
     * GET /api/v1/commission-settings/active
     *
     * Return the CURRENT active commission setting.
     *
     * This is the endpoint the mobile app and booking service call
     * to find out what the current commission rate is.
     * It uses the model's static getActive() method.
     *
     * NOTE: Register this route BEFORE the {commission_setting} param
     * route in api.php to prevent "active" being matched as an ID.
     *
     * ACCESS: Super Admin, Admin
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse(
                'You are not authorized to view commission settings.',
                403
            );
        }

        $setting = CommissionSetting::getActive();

        if (!$setting) {
            return $this->notFoundResponse(
                'No active commission setting found. Please create and activate a commission setting.'
            );
        }

        return $this->successResponse(
            new CommissionSettingResource($setting),
            'Active commission setting retrieved successfully.'
        );
    }

    /**
     * GET /api/v1/commission-settings/{commission_setting}
     *
     * Return a single commission setting record by ID.
     *
     * ACCESS: Super Admin, Admin
     */
    public function show(Request $request, CommissionSetting $commissionSetting): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse(
                'You are not authorized to view commission settings.',
                403
            );
        }

        return $this->successResponse(
            new CommissionSettingResource($commissionSetting),
            'Commission setting retrieved successfully.'
        );
    }

    /**
     * POST /api/v1/commission-settings
     *
     * Create a new commission setting.
     *
     * ACTIVATION BEHAVIOR:
     * If the new setting's status is "active", we deactivate any
     * existing active record inside a DB transaction. This ensures
     * only ONE record is ever "active" at any given time.
     *
     * BALANCE CHECK:
     * The request class validates commission + owner_share === 100%.
     * We also call isBalanced() as a final server-side guard.
     *
     * ACCESS: Super Admin only
     */
    public function store(StoreCommissionSettingRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can create commission settings.',
                403
            );
        }

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // If the new setting is being activated, deactivate all
            // existing active records first.
            if (($validated['status'] ?? 'active') === 'active') {
                CommissionSetting::where('status', 'active')
                    ->update(['status' => 'inactive']);
            }

            // Create the new commission setting.
            $setting = CommissionSetting::create([
                'commission_percent'  => $validated['commission_percent'],
                'owner_share_percent' => $validated['owner_share_percent'],
                'status'              => $validated['status'] ?? 'active',
            ]);

            // Final guard: verify the model agrees the percentages are balanced.
            if (!$setting->isBalanced()) {
                DB::rollBack();
                return $this->errorResponse(
                    'Commission and owner share percentages do not balance to 100%. ' .
                    'Please review the values and try again.',
                    422
                );
            }

            DB::commit();

            return $this->createdResponse(
                new CommissionSettingResource($setting),
                'Commission setting created successfully.' .
                ($setting->status === 'active'
                    ? ' This is now the active commission rate for all bookings.'
                    : '')
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CommissionSetting store failed', [
                'user_id' => $user->id,
                'input'   => $validated,
                'error'   => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to create commission setting. Please try again.'
            );
        }
    }

    /**
     * PUT /api/v1/commission-settings/{commission_setting}
     *
     * Update an existing commission setting.
     *
     * PARTIAL UPDATE SUPPORT:
     * Only the fields present in the request are updated.
     * The UpdateCommissionSettingRequest withValidator() handles
     * the 100% balance check even on partial updates by resolving
     * missing values from the existing record.
     *
     * RE-ACTIVATION ON UPDATE:
     * If the updated record is set to "active", any other currently
     * active record is deactivated inside a transaction.
     *
     * ACCESS: Super Admin only
     */
    public function update(
        UpdateCommissionSettingRequest $request,
        CommissionSetting $commissionSetting
    ): JsonResponse {
        $user = $request->user();
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can update commission settings.',
                403
            );
        }

        $validated = $request->validated();

        if (empty($validated)) {
            return $this->errorResponse(
                'No valid fields provided to update. Allowed fields: commission_percent, owner_share_percent, status.',
                422
            );
        }

        try {
            DB::beginTransaction();

            $isActivating = isset($validated['status'])
                && $validated['status'] === 'active'
                && $commissionSetting->status !== 'active';

            // If we're activating this record, deactivate any other
            // currently active record (excluding this one).
            if ($isActivating) {
                CommissionSetting::where('status', 'active')
                    ->where('id', '!=', $commissionSetting->id)
                    ->update(['status' => 'inactive']);
            }

            $commissionSetting->update($validated);
            $commissionSetting->refresh();

            // Final balance guard after update.
            if (!$commissionSetting->isBalanced()) {
                DB::rollBack();
                return $this->errorResponse(
                    'After this update, commission_percent (' .
                    $commissionSetting->commission_percent .
                    '%) and owner_share_percent (' .
                    $commissionSetting->owner_share_percent .
                    '%) do not add up to 100%. Update rolled back.',
                    422
                );
            }

            DB::commit();

            $message = 'Commission setting updated successfully.';
            if ($isActivating) {
                $message .= ' This is now the active commission rate for all new bookings.';
            }

            return $this->successResponse(
                new CommissionSettingResource($commissionSetting),
                $message
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CommissionSetting update failed', [
                'user_id'    => $user->id,
                'setting_id' => $commissionSetting->id,
                'input'      => $validated,
                'error'      => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to update commission setting. Please try again.'
            );
        }
    }

    /**
     * DELETE /api/v1/commission-settings/{commission_setting}
     *
     * Permanently delete a commission setting record.
     *
     * RESTRICTIONS:
     *   - Cannot delete the currently ACTIVE record.
     *     Deleting the active setting would leave the platform with
     *     no commission rule — all new booking payment splits would fail.
     *   - Cannot delete a record that has bookings referencing it
     *     (enforced via FK constraint — the commissions table has
     *     a FK to commission_settings if applicable in your schema).
     *
     * Super Admin only.
     */
    public function destroy(Request $request, CommissionSetting $commissionSetting): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can delete commission settings.',
                403
            );
        }

        // Cannot delete the currently active setting.
        if ($commissionSetting->status === 'active') {
            return $this->errorResponse(
                'The active commission setting cannot be deleted. ' .
                'Please activate a different setting first, then delete this one.',
                422
            );
        }

        try {
            $commissionSetting->delete();

            return $this->successResponse(
                null,
                'Commission setting deleted successfully.'
            );

        } catch (\Throwable $e) {
            Log::error('CommissionSetting delete failed', [
                'user_id'    => $user->id,
                'setting_id' => $commissionSetting->id,
                'error'      => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to delete commission setting. ' .
                'It may be referenced by existing booking commission records.'
            );
        }
    }

    /**
     * PATCH /api/v1/commission-settings/{commission_setting}/activate
     *
     * Set a specific commission setting as the active (live) rate.
     *
     * WHAT THIS DOES:
     *   1. Validates the target record is currently inactive.
     *   2. Sets all other records to "inactive" in a transaction.
     *   3. Sets the target record to "active".
     *
     * USE CASE:
     * An admin wants to roll back to a previous commission rate
     * without creating a new record. They just re-activate the
     * historical record with this endpoint.
     *
     * ACCESS: Super Admin only
     */
    public function activate(Request $request, CommissionSetting $commissionSetting): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can activate commission settings.',
                403
            );
        }

        // Already active — nothing to do.
        if ($commissionSetting->status === 'active') {
            return $this->errorResponse(
                'This commission setting is already active.',
                422
            );
        }

        // Safety check: the setting should be balanced before activating.
        if (!$commissionSetting->isBalanced()) {
            return $this->errorResponse(
                'This commission setting cannot be activated because commission_percent (' .
                $commissionSetting->commission_percent .
                '%) and owner_share_percent (' .
                $commissionSetting->owner_share_percent .
                '%) do not add up to 100%.',
                422
            );
        }

        try {
            DB::beginTransaction();

            // Deactivate all currently active records.
            CommissionSetting::where('status', 'active')
                ->where('id', '!=', $commissionSetting->id)
                ->update(['status' => 'inactive']);

            // Activate the target record.
            $commissionSetting->update(['status' => 'active']);

            DB::commit();

            return $this->successResponse(
                new CommissionSettingResource($commissionSetting->refresh()),
                'Commission setting activated successfully. ' .
                "Platform commission: {$commissionSetting->commission_percent}%, " .
                "Owner share: {$commissionSetting->owner_share_percent}%."
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CommissionSetting activation failed', [
                'user_id'    => $user->id,
                'setting_id' => $commissionSetting->id,
                'error'      => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to activate commission setting. Please try again.'
            );
        }
    }
}