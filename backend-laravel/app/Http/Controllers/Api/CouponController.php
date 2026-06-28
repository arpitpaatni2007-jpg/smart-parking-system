<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * CouponController
 *
 * ENDPOINTS:
 *   GET    /api/v1/coupons              → Admin: list all coupons (paginated + search)
 *   POST   /api/v1/coupons              → Admin: create coupon
 *   GET    /api/v1/coupons/{coupon}     → Admin: single coupon detail
 *   PUT    /api/v1/coupons/{coupon}     → Admin: update coupon
 *   DELETE /api/v1/coupons/{coupon}     → Admin: soft-delete coupon
 *   POST   /api/v1/coupons/apply        → Any auth user: validate & apply coupon
 *
 * ACCESS:
 *   CRUD → Admin only
 *   apply endpoint → Any authenticated user
 *
 * NAMESPACE: App\Http\Controllers\Api
 */
class CouponController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX
    // =========================================================

    /**
     * List all coupons (admin only, paginated).
     *
     * QUERY PARAMS:
     *   ?search=SAVE10       → search by code or description
     *   ?is_active=true      → filter active/inactive
     *   ?discount_type=flat  → filter by type
     *   ?per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can manage coupons.');
            }

            $query = Coupon::query();

            if ($request->filled('search')) {
                $term = '%' . $request->search . '%';
                $query->where(fn ($q) => $q->where('code', 'LIKE', $term)
                                           ->orWhere('description', 'LIKE', $term));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('discount_type')) {
                $query->where('discount_type', $request->discount_type);
            }

            $coupons = $query->latest()->paginate(min((int) ($request->per_page ?? 15), 50));

            return $this->successResponse(
                CouponResource::collection($coupons)->response()->getData(true),
                'Coupons retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CouponController@index', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve coupons.');
        }
    }

    // =========================================================
    // STORE
    // =========================================================

    /**
     * Create a new coupon (admin only).
     */
    public function store(StoreCouponRequest $request): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can create coupons.');
            }

            $coupon = Coupon::create([
                'code'                => $request->code,
                'description'         => $request->description,
                'discount_type'       => $request->discount_type,
                'discount_value'      => $request->discount_value,
                'max_discount_amount' => $request->max_discount_amount,
                'min_booking_amount'  => $request->min_booking_amount,
                'usage_limit'         => $request->usage_limit,
                'per_user_limit'      => $request->per_user_limit,
                'valid_from'          => $request->valid_from,
                'valid_until'         => $request->valid_until,
                'is_active'           => $request->boolean('is_active', true),
                'used_count'          => 0,
            ]);

            return $this->createdResponse(
                new CouponResource($coupon),
                'Coupon created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CouponController@store', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to create coupon.');
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

    /**
     * Get a single coupon (admin only).
     */
    public function show(Request $request, Coupon $coupon): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can view coupon details.');
            }

            return $this->successResponse(
                new CouponResource($coupon),
                'Coupon retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CouponController@show', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve coupon.');
        }
    }

    // =========================================================
    // UPDATE
    // =========================================================

    /**
     * Update a coupon (admin only).
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can update coupons.');
            }

            $updateData = array_filter(
                $request->only([
                    'code', 'description', 'discount_type', 'discount_value',
                    'max_discount_amount', 'min_booking_amount', 'usage_limit',
                    'per_user_limit', 'valid_from', 'valid_until', 'is_active',
                ]),
                fn ($v) => ! is_null($v)
            );

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            $coupon->update($updateData);

            return $this->successResponse(
                new CouponResource($coupon->fresh()),
                'Coupon updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('CouponController@update', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to update coupon.');
        }
    }

    // =========================================================
    // DESTROY
    // =========================================================

    /**
     * Soft-delete a coupon (admin only).
     */
    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can delete coupons.');
            }

            $coupon->delete();

            return $this->successResponse(null, 'Coupon deleted successfully.');

        } catch (Throwable $e) {
            Log::error('CouponController@destroy', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to delete coupon.');
        }
    }

    // =========================================================
    // APPLY
    // =========================================================

    /**
     * Validate and calculate the discount for a coupon code.
     *
     * DOES NOT save anything — purely a calculation/validation endpoint.
     * The actual booking creation will pass coupon_code and store
     * the discount in the booking record.
     *
     * ROUTE: POST /api/v1/coupons/apply
     *
     * Returns:
     *   {
     *     "coupon":          { ...CouponResource... },
     *     "original_amount": 200.00,
     *     "discount_amount": 20.00,
     *     "final_amount":    180.00
     *   }
     */
    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        try {
            $user          = $request->user();
            $bookingAmount = (float) $request->booking_amount;

            // ── Find the coupon ────────────────────────────────────────────
            $coupon = Coupon::where('code', $request->code)
                ->where('is_active', true)
                ->first();

            if (! $coupon) {
                return $this->errorResponse('Invalid or inactive coupon code.', 422);
            }

            // ── Check validity window ──────────────────────────────────────
            if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
                return $this->errorResponse(
                    'This coupon is not yet active. It becomes valid on ' .
                    $coupon->valid_from->format('d M Y') . '.',
                    422
                );
            }

            if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
                return $this->errorResponse('This coupon has expired.', 422);
            }

            // ── Check global usage limit ───────────────────────────────────
            if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                return $this->errorResponse('This coupon has reached its maximum usage limit.', 422);
            }

            // ── Check per-user limit ───────────────────────────────────────
            if ($coupon->per_user_limit !== null) {
                // Count how many times this user has used this coupon
                // via bookings that reference this coupon_id
                $userUsageCount = \App\Models\Booking::where('user_id', $user->id)
                    ->where('coupon_id', $coupon->id)
                    ->whereNotIn('booking_status', ['cancelled', 'expired'])
                    ->count();

                if ($userUsageCount >= $coupon->per_user_limit) {
                    return $this->errorResponse(
                        "You have already used this coupon {$coupon->per_user_limit} time(s). No further uses are allowed.",
                        422
                    );
                }
            }

            // ── Check minimum booking amount ───────────────────────────────
            if ($coupon->min_booking_amount && $bookingAmount < (float) $coupon->min_booking_amount) {
                $min = number_format($coupon->min_booking_amount, 2);
                return $this->errorResponse(
                    "This coupon requires a minimum booking amount of ₹{$min}.",
                    422
                );
            }

            // ── Calculate discount ─────────────────────────────────────────
            $discountAmount = 0.00;

            if ($coupon->discount_type === 'flat') {
                $discountAmount = min((float) $coupon->discount_value, $bookingAmount);
            } elseif ($coupon->discount_type === 'percentage') {
                $raw            = $bookingAmount * ((float) $coupon->discount_value / 100);
                $discountAmount = $coupon->max_discount_amount
                    ? min($raw, (float) $coupon->max_discount_amount)
                    : $raw;
            }

            $discountAmount = round($discountAmount, 2);
            $finalAmount    = round(max(0, $bookingAmount - $discountAmount), 2);

            return $this->successResponse(
                [
                    'coupon'          => new CouponResource($coupon),
                    'original_amount' => $bookingAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount'    => $finalAmount,
                ],
                "Coupon applied! You save ₹{$discountAmount}."
            );

        } catch (Throwable $e) {
            Log::error('CouponController@apply', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to apply coupon.');
        }
    }
}