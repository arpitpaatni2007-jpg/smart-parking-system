<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * PaymentMethodController
 * ============================================================
 *
 * Handles all CRUD operations for payment methods.
 *
 * ENDPOINTS:
 *   GET    /api/v1/payment-methods                    → index   (list active methods for app)
 *   POST   /api/v1/payment-methods                    → store   (admin creates)
 *   GET    /api/v1/payment-methods/{payment_method}   → show    (single method detail)
 *   PUT    /api/v1/payment-methods/{payment_method}   → update  (admin updates)
 *   DELETE /api/v1/payment-methods/{payment_method}   → destroy (admin deletes)
 *
 * ACCESS CONTROL SUMMARY:
 *   index / show → Any authenticated user
 *                  (Flutter payment screen fetches active methods)
 *   store        → Admin only
 *   update       → Admin only
 *   destroy      → Admin only
 *
 * NOTE ON ROLE CHECKS:
 *   Role checks are done inline for clarity, consistent with
 *   ParkingController's pattern in this project.
 */
class PaymentMethodController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — List Payment Methods
    // =========================================================

    /**
     * Return a list of payment methods.
     *
     * FLUTTER APP USE CASE:
     *   Payment screen loads → calls this endpoint → shows only
     *   active methods with icons. Admin panel calls the same
     *   endpoint with ?all=true to see inactive ones too.
     *
     * SUPPORTED QUERY PARAMETERS:
     *   ?all=true   → Admin only: include inactive methods
     *   ?search=upi → Filter by name or description keyword
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PaymentMethod::query()->orderBy('name');

            // ── FILTER: active-only for regular users ──────────────────────
            // Admin passes ?all=true to see inactive methods in the panel.
            $showAll = $request->boolean('all') && $request->user()->hasRole('admin');

            if (! $showAll) {
                $query->active(); // scopeActive() → where status = 'active'
            }

            // ── SEARCH: by name or description ────────────────────────────
            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', $search)
                      ->orWhere('description', 'LIKE', $search);
                });
            }

            $paymentMethods = $query->get();

            return $this->successResponse(
                PaymentMethodResource::collection($paymentMethods),
                'Payment methods retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PaymentMethodController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve payment methods.');
        }
    }

    // =========================================================
    // STORE — Create a New Payment Method
    // =========================================================

    /**
     * Create a new payment method record.
     *
     * FLOW:
     *   1. StorePaymentMethodRequest validates all fields
     *   2. Role check: only admin can create payment methods
     *   3. Create the record with validated data
     *   4. Return the created resource
     *
     * STATUS DEFAULT:
     *   If the admin does not send a `status`, we default to
     *   "active" so the new method appears in the app immediately.
     *
     * @param  StorePaymentMethodRequest  $request
     * @return JsonResponse
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        try {
            // ── Role check: only admin can manage payment methods ──────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can create payment methods.'
                );
            }

            $paymentMethod = PaymentMethod::create([
                'name'        => $request->name,
                'icon'        => $request->icon,
                'description' => $request->description,
                // Default to active if admin does not explicitly set a status.
                'status'      => $request->status ?? PaymentMethod::STATUS_ACTIVE,
            ]);

            return $this->createdResponse(
                new PaymentMethodResource($paymentMethod),
                'Payment method created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PaymentMethodController@store failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->serverErrorResponse('Failed to create payment method. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Single Payment Method
    // =========================================================

    /**
     * Return full details for a single payment method.
     *
     * FLUTTER USE CASE:
     *   Confirmation screen may display the icon and description
     *   of the method chosen by the user.
     *
     * VISIBILITY:
     *   Regular users can only view active methods. Admins can
     *   view any method regardless of status.
     *
     * @param  Request        $request
     * @param  PaymentMethod  $paymentMethod  (route model binding)
     * @return JsonResponse
     */
    public function show(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            // ── Regular users may not view inactive methods ────────────────
            if (
                ! $request->user()->hasRole('admin') &&
                ! $paymentMethod->isActive()
            ) {
                return $this->notFoundResponse('Payment method not found.');
            }

            return $this->successResponse(
                new PaymentMethodResource($paymentMethod),
                'Payment method retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PaymentMethodController@show failed', [
                'payment_method_id' => $paymentMethod->id,
                'error'             => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve payment method.');
        }
    }

    // =========================================================
    // UPDATE — Update a Payment Method
    // =========================================================

    /**
     * Update an existing payment method.
     *
     * ADMIN PANEL USE CASE:
     *   Admin toggles status to "inactive" → Net Banking disappears
     *   from the Flutter payment screen without any app release.
     *
     * PARTIAL UPDATES:
     *   Only fields present in the request are updated.
     *   Uses $request->only([...]) so missing fields are not
     *   overwritten with null.
     *
     * @param  UpdatePaymentMethodRequest  $request
     * @param  PaymentMethod               $paymentMethod
     * @return JsonResponse
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can update payment methods.'
                );
            }

            // Update only the fields that were actually sent in the request.
            $paymentMethod->update(
                $request->only(['name', 'icon', 'description', 'status'])
            );

            return $this->successResponse(
                new PaymentMethodResource($paymentMethod),
                'Payment method updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PaymentMethodController@update failed', [
                'payment_method_id' => $paymentMethod->id,
                'error'             => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update payment method.');
        }
    }

    // =========================================================
    // DESTROY — Delete a Payment Method
    // =========================================================

    /**
     * Permanently delete a payment method.
     *
     * SAFETY CHECK:
     *   A payment method should not be deleted if it is referenced
     *   by existing payment records. Prefer setting status to
     *   "inactive" to retire a method gracefully instead.
     *
     *   The check below prevents hard-deletes that would leave
     *   orphaned `payment_method` values in the payments table.
     *
     * @param  Request        $request
     * @param  PaymentMethod  $paymentMethod
     * @return JsonResponse
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse(
                    'Only administrators can delete payment methods.'
                );
            }

            // ── Safety: block deletion if payments reference this method ───
            // The payments table stores payment_method as a string matching
            // the `name` column of this table (e.g. "upi", "card").
            // Deleting a method that has payment records would orphan them.
            $inUse = \App\Models\Payment::where('payment_method', $paymentMethod->name)->exists();

            if ($inUse) {
                return $this->errorResponse(
                    'Cannot delete this payment method — it is referenced by existing payment records. '
                    . 'Set its status to "inactive" to retire it instead.',
                    409 // 409 Conflict
                );
            }

            $paymentMethod->delete();

            return $this->successResponse(
                null,
                'Payment method deleted successfully.'
            );

        } catch (Throwable $e) {
            Log::error('PaymentMethodController@destroy failed', [
                'payment_method_id' => $paymentMethod->id,
                'error'             => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete payment method.');
        }
    }
}