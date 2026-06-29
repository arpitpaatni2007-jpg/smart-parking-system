<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PricingRule\StorePricingRuleRequest;
use App\Http\Requests\PricingRule\UpdatePricingRuleRequest;
use App\Http\Resources\PricingRuleResource;
use App\Models\PricingRule;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * PricingRuleController
 *
 * Manages pricing rules that determine how much users are charged
 * for parking bookings based on vehicle type and billing cycle.
 *
 * ENDPOINTS:
 *   GET    /api/v1/pricing-rules                      → List rules (with filters)
 *   POST   /api/v1/pricing-rules                      → Admin: create rule
 *   GET    /api/v1/pricing-rules/{pricing_rule}       → Single rule detail
 *   PUT    /api/v1/pricing-rules/{pricing_rule}       → Admin: update rule
 *   DELETE /api/v1/pricing-rules/{pricing_rule}       → Admin: soft-delete rule
 *   PATCH  /api/v1/pricing-rules/{pricing_rule}/toggle → Admin: toggle status
 *
 * ACCESS:
 *   index / show → All authenticated users
 *     (users need to see pricing before booking)
 *   store / update / destroy / toggle → Admin only
 *
 * UNIQUENESS RULE:
 *   There should be at most ONE active rule per (vehicle_type_id, pricing_type).
 *   The store and update methods enforce this with a conflict check.
 *   Multiple inactive rules can coexist (historical records).
 *
 * NAMESPACE: App\Http\Controllers\Api
 */
class PricingRuleController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List Pricing Rules
    // =========================================================

    /**
     * Return a paginated list of pricing rules.
     *
     * QUERY PARAMETERS:
     *   ?vehicle_type_id=2   → filter by vehicle type
     *   ?pricing_type=hourly → filter by billing cycle (hourly|daily|monthly)
     *   ?status=active       → filter by status
     *   ?per_page=15         → page size (default 15, max 50)
     *
     * FLUTTER USE CASE:
     *   Booking flow: load active pricing rules for a vehicle type
     *   to show the user the expected cost before confirming.
     *   e.g. GET /pricing-rules?vehicle_type_id=2&status=active
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PricingRule::with('vehicleType');

            // ── Filter: by vehicle type ────────────────────────────────────
            if ($request->filled('vehicle_type_id')) {
                $query->where('vehicle_type_id', (int) $request->vehicle_type_id);
            }

            // ── Filter: by pricing type ────────────────────────────────────
            if ($request->filled('pricing_type')) {
                $query->ofType($request->pricing_type);
            }

            // ── Filter: by status ──────────────────────────────────────────
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                // Non-admin users see only active rules by default
                if (! $request->user()->hasRole('admin')) {
                    $query->active();
                }
            }

            // ── Order: vehicle_type then pricing_type for logical grouping ──
            $query->orderBy('vehicle_type_id')->orderBy('pricing_type');

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $rules   = $query->paginate($perPage);

            return $this->successResponse(
                PricingRuleResource::collection($rules)->response()->getData(true),
                'Pricing rules retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PricingRuleController@index', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve pricing rules.');
        }
    }

    // =========================================================
    // STORE — Create a Pricing Rule (Admin Only)
    // =========================================================

    /**
     * Create a new pricing rule.
     *
     * CONFLICT CHECK:
     *   If an active rule already exists for the same (vehicle_type_id,
     *   pricing_type) combination, return a 409 Conflict.
     *   The admin must deactivate the existing rule before creating a
     *   new one, to avoid pricing ambiguity during booking calculations.
     *
     * DEFAULT VALUES:
     *   extra_hour_price defaults to 0.00 for non-hourly pricing types.
     *   status defaults to 'active' so the rule is applied immediately.
     *
     * @param  \App\Http\Requests\PricingRule\StorePricingRuleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePricingRuleRequest $request): JsonResponse
    {
        try {
            // ── Admin-only guard ───────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse(
                    'Only administrators can create pricing rules.',
                    403
                );
            }

            $incomingStatus = $request->input('status', 'active');

            // ── Conflict check: one active rule per (vehicle_type, pricing_type) ─
            if ($incomingStatus === 'active') {
                $conflict = PricingRule::where('vehicle_type_id', $request->vehicle_type_id)
                    ->where('pricing_type',    $request->pricing_type)
                    ->where('status',          'active')
                    ->exists();

                if ($conflict) {
                    return $this->errorResponse(
                        'An active pricing rule already exists for this vehicle type and pricing cycle. ' .
                        'Please deactivate the existing rule before creating a new one.',
                        409
                    );
                }
            }

            $rule = PricingRule::create([
                'vehicle_type_id'  => $request->vehicle_type_id,
                'pricing_type'     => $request->pricing_type,
                'base_price'       => $request->base_price,
                'extra_hour_price' => $request->pricing_type === 'hourly'
                                        ? ($request->extra_hour_price ?? 0.00)
                                        : 0.00,
                'status'           => $incomingStatus,
            ]);

            $rule->load('vehicleType');

            return $this->createdResponse(
                new PricingRuleResource($rule),
                'Pricing rule created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PricingRuleController@store', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to create pricing rule.');
        }
    }

    // =========================================================
    // SHOW — Single Pricing Rule
    // =========================================================

    /**
     * Return full detail for a single pricing rule.
     *
     * All authenticated users can view individual rules
     * (needed for the booking detail screen to show pricing breakdown).
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\PricingRule  $pricingRule  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, PricingRule $pricingRule): JsonResponse
    {
        try {
            // Non-admins can only view active rules
            if (! $request->user()->hasRole('admin') && $pricingRule->status !== 'active') {
                return $this->notFoundResponse('Pricing rule not found.');
            }

            $pricingRule->load('vehicleType');

            return $this->successResponse(
                new PricingRuleResource($pricingRule),
                'Pricing rule retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PricingRuleController@show', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve pricing rule.');
        }
    }

    // =========================================================
    // UPDATE — Update a Pricing Rule (Admin Only)
    // =========================================================

    /**
     * Update an existing pricing rule (partial update supported).
     *
     * CONFLICT CHECK ON ACTIVATION:
     *   If the update sets status to 'active' and another active rule
     *   already exists for the same (vehicle_type_id, pricing_type),
     *   a 409 Conflict is returned. The admin must deactivate the other
     *   rule first, or use the toggle endpoint to swap them.
     *
     * EXTRA HOUR PRICE RESET:
     *   If pricing_type is changed to 'daily' or 'monthly', extra_hour_price
     *   is automatically reset to 0.00 (it has no meaning for non-hourly plans).
     *
     * @param  \App\Http\Requests\PricingRule\UpdatePricingRuleRequest $request
     * @param  \App\Models\PricingRule                                 $pricingRule
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePricingRuleRequest $request, PricingRule $pricingRule): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can update pricing rules.', 403);
            }

            // Resolve effective values after update
            $effectivePricingType   = $request->input('pricing_type',    $pricingRule->pricing_type);
            $effectiveVehicleTypeId = $request->input('vehicle_type_id', $pricingRule->vehicle_type_id);
            $effectiveStatus        = $request->input('status',          $pricingRule->status);

            // ── Conflict check when activating or changing the type ────────
            if ($effectiveStatus === 'active') {
                $conflict = PricingRule::where('vehicle_type_id', $effectiveVehicleTypeId)
                    ->where('pricing_type',    $effectivePricingType)
                    ->where('status',          'active')
                    ->where('id',              '!=', $pricingRule->id) // exclude self
                    ->exists();

                if ($conflict) {
                    return $this->errorResponse(
                        'Another active pricing rule already exists for this vehicle type and pricing cycle. ' .
                        'Deactivate it first before activating this rule.',
                        409
                    );
                }
            }

            // ── Build the update array ─────────────────────────────────────
            $updateData = array_filter(
                $request->only(['vehicle_type_id', 'pricing_type', 'base_price', 'status']),
                fn ($v) => ! is_null($v)
            );

            // Handle extra_hour_price
            if ($effectivePricingType !== 'hourly') {
                // Non-hourly plan: always reset extra_hour_price to 0
                $updateData['extra_hour_price'] = 0.00;
            } elseif ($request->has('extra_hour_price')) {
                $updateData['extra_hour_price'] = $request->extra_hour_price ?? 0.00;
            }

            if (empty($updateData)) {
                return $this->errorResponse('No update data provided.', 400);
            }

            $pricingRule->update($updateData);
            $pricingRule->load('vehicleType');

            return $this->successResponse(
                new PricingRuleResource($pricingRule->fresh()->load('vehicleType')),
                'Pricing rule updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PricingRuleController@update', [
                'pricing_rule_id' => $pricingRule->id,
                'error'           => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update pricing rule.');
        }
    }

    // =========================================================
    // DESTROY — Soft-Delete a Pricing Rule (Admin Only)
    // =========================================================

    /**
     * Soft-delete a pricing rule.
     *
     * SAFETY CHECK:
     *   Active rules in use cannot be deleted directly — they must be
     *   deactivated first. This prevents deleting a rule that is currently
     *   being referenced by the booking price calculation flow.
     *
     *   Inactive rules can be soft-deleted to clean up the list.
     *
     * WHY SOFT DELETE?
     *   Historical bookings reference the pricing that was applied at the
     *   time of booking. Keeping the rule in the DB (soft-deleted) preserves
     *   the ability to audit "what price was charged and why" for old bookings.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\PricingRule  $pricingRule
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, PricingRule $pricingRule): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can delete pricing rules.', 403);
            }

            // ── Safety: prevent deleting an active rule ────────────────────
            if ($pricingRule->status === 'active') {
                return $this->errorResponse(
                    'Active pricing rules cannot be deleted. Please deactivate the rule first using the toggle endpoint.',
                    409
                );
            }

            $pricingRule->delete(); // Soft delete — sets deleted_at

            return $this->successResponse(null, 'Pricing rule deleted successfully.');

        } catch (Throwable $e) {
            Log::error('PricingRuleController@destroy', [
                'pricing_rule_id' => $pricingRule->id,
                'error'           => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete pricing rule.');
        }
    }

    // =========================================================
    // TOGGLE — Toggle Status (Admin Only)
    // =========================================================

    /**
     * Toggle the status of a pricing rule between 'active' and 'inactive'.
     *
     * ROUTE: PATCH /api/v1/pricing-rules/{pricing_rule}/toggle
     *
     * ACTIVATION CONFLICT CHECK:
     *   If toggling from inactive → active and another active rule exists
     *   for the same (vehicle_type_id, pricing_type), a 409 is returned.
     *   This prevents two active rules competing during price calculation.
     *
     * USE CASE — SWAPPING PRICING:
     *   To update pricing without a gap in availability:
     *   1. Create a new rule (status: inactive)
     *   2. Toggle the old rule → inactive
     *   3. Toggle the new rule → active
     *   This ensures there is always exactly one active rule.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\PricingRule  $pricingRule
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request, PricingRule $pricingRule): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->errorResponse('Only administrators can toggle pricing rule status.', 403);
            }

            $newStatus = $pricingRule->status === 'active' ? 'inactive' : 'active';

            // ── Conflict check when activating ─────────────────────────────
            if ($newStatus === 'active') {
                $conflict = PricingRule::where('vehicle_type_id', $pricingRule->vehicle_type_id)
                    ->where('pricing_type',    $pricingRule->pricing_type)
                    ->where('status',          'active')
                    ->where('id',              '!=', $pricingRule->id)
                    ->exists();

                if ($conflict) {
                    return $this->errorResponse(
                        'Another active rule already exists for this vehicle type and pricing cycle. ' .
                        'Deactivate it first before activating this one.',
                        409
                    );
                }
            }

            $pricingRule->update(['status' => $newStatus]);
            $pricingRule->load('vehicleType');

            $label = $newStatus === 'active' ? 'activated' : 'deactivated';

            return $this->successResponse(
                new PricingRuleResource($pricingRule->fresh()->load('vehicleType')),
                "Pricing rule {$label} successfully."
            );

        } catch (Throwable $e) {
            Log::error('PricingRuleController@toggle', [
                'pricing_rule_id' => $pricingRule->id,
                'error'           => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to toggle pricing rule status.');
        }
    }
}