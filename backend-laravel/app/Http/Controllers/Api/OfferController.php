<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * OfferController
 *
 * ENDPOINTS:
 *   GET    /api/v1/offers             → List active offers (all users) or all offers (admin)
 *   POST   /api/v1/offers             → Admin: create offer (supports banner image upload)
 *   GET    /api/v1/offers/{offer}     → Single offer detail
 *   PUT    /api/v1/offers/{offer}     → Admin: update offer
 *   DELETE /api/v1/offers/{offer}     → Admin: soft-delete offer
 *
 * ACCESS:
 *   index / show → All authenticated users (users see only active/valid offers)
 *   store / update / destroy → Admin only
 *
 * BANNER IMAGE STORAGE:
 *   Stored in storage/app/public/offer-banners/ as UUID-named files.
 *   Public URL via Storage::url($path).
 *   Run: php artisan storage:link
 *
 * NAMESPACE: App\Http\Controllers\Api
 */
class OfferController extends Controller
{
    use ApiResponse;

    private const STORAGE_DIR = 'offer-banners';

    // =========================================================
    // INDEX
    // =========================================================

    /**
     * List offers.
     *
     * Admin: all offers (including inactive), with search and filters.
     * Users/Owners: only active, currently-valid offers.
     *
     * QUERY PARAMS:
     *   ?search=weekend        → search title/description (admin only)
     *   ?is_active=true        → admin: filter by active state
     *   ?applicable_to=all     → filter by applicability type
     *   ?parking_id=3          → filter by specific parking
     *   ?per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user  = $request->user();
            $query = Offer::with('parking:id,name');

            if ($user->hasRole('admin')) {
                // Admin sees everything
                if ($request->filled('search')) {
                    $term = '%' . $request->search . '%';
                    $query->where(fn ($q) => $q->where('title', 'LIKE', $term)
                                               ->orWhere('description', 'LIKE', $term));
                }

                if ($request->has('is_active')) {
                    $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
                }

            } else {
                // Regular users: only active + currently valid offers
                $query->where('is_active', true)
                      ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
                      ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()));
            }

            if ($request->filled('applicable_to')) {
                $query->where('applicable_to', $request->applicable_to);
            }

            if ($request->filled('parking_id')) {
                $query->where(fn ($q) => $q->where('parking_id', $request->parking_id)
                                           ->orWhere('applicable_to', 'all'));
            }

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $offers  = $query->latest()->paginate($perPage);

            return $this->successResponse(
                OfferResource::collection($offers)->response()->getData(true),
                'Offers retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OfferController@index', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve offers.');
        }
    }

    // =========================================================
    // STORE
    // =========================================================

    /**
     * Create a new offer with optional banner image upload.
     * Admin only. Accepts multipart/form-data.
     */
    public function store(StoreOfferRequest $request): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can create offers.');
            }

            $bannerPath = null;
            if ($request->hasFile('banner_image')) {
                $ext        = $request->file('banner_image')->getClientOriginalExtension();
                $filename   = Str::uuid() . '.' . strtolower($ext);
                Storage::disk('public')->putFileAs(self::STORAGE_DIR, $request->file('banner_image'), $filename);
                $bannerPath = self::STORAGE_DIR . '/' . $filename;
            }

            $offer = Offer::create([
                'title'               => $request->title,
                'description'         => $request->description,
                'offer_type'          => $request->offer_type,
                'discount_value'      => $request->discount_value,
                'max_discount_amount' => $request->max_discount_amount,
                'min_booking_amount'  => $request->min_booking_amount,
                'applicable_to'       => $request->applicable_to,
                'parking_id'          => $request->applicable_to === 'specific_parking'
                                            ? $request->parking_id
                                            : null,
                'banner_image'        => $bannerPath,
                'valid_from'          => $request->valid_from,
                'valid_until'         => $request->valid_until,
                'is_active'           => $request->boolean('is_active', true),
            ]);

            $offer->load('parking:id,name');

            return $this->createdResponse(
                new OfferResource($offer),
                'Offer created successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OfferController@store', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to create offer.');
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

    /**
     * Get a single offer detail.
     * Admin sees any offer; users see only active/valid ones.
     */
    public function show(Request $request, Offer $offer): JsonResponse
    {
        try {
            $isAdmin = $request->user()->hasRole('admin');

            if (! $isAdmin) {
                // Non-admin: enforce active + validity window
                $isValid = $offer->is_active
                    && (! $offer->valid_from  || now()->gte($offer->valid_from))
                    && (! $offer->valid_until || now()->lte($offer->valid_until));

                if (! $isValid) {
                    return $this->notFoundResponse('Offer not found or no longer available.');
                }
            }

            $offer->load('parking:id,name');

            return $this->successResponse(
                new OfferResource($offer),
                'Offer retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OfferController@show', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve offer.');
        }
    }

    // =========================================================
    // UPDATE
    // =========================================================

    /**
     * Update an existing offer. Admin only.
     * Supports partial updates and optional banner image replacement.
     */
    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can update offers.');
            }

            $updateData = array_filter(
                $request->only([
                    'title', 'description', 'offer_type', 'discount_value',
                    'max_discount_amount', 'min_booking_amount', 'applicable_to',
                    'parking_id', 'valid_from', 'valid_until',
                ]),
                fn ($v) => ! is_null($v)
            );

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            // Clear parking_id if applicable_to is no longer specific_parking
            if (isset($updateData['applicable_to']) && $updateData['applicable_to'] !== 'specific_parking') {
                $updateData['parking_id'] = null;
            }

            // Replace banner image if new one uploaded
            if ($request->hasFile('banner_image')) {
                // Delete old file
                if ($offer->banner_image && Storage::disk('public')->exists($offer->banner_image)) {
                    Storage::disk('public')->delete($offer->banner_image);
                }

                $ext      = $request->file('banner_image')->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . strtolower($ext);
                Storage::disk('public')->putFileAs(self::STORAGE_DIR, $request->file('banner_image'), $filename);
                $updateData['banner_image'] = self::STORAGE_DIR . '/' . $filename;
            }

            $offer->update($updateData);
            $offer->load('parking:id,name');

            return $this->successResponse(
                new OfferResource($offer->fresh()->load('parking:id,name')),
                'Offer updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OfferController@update', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to update offer.');
        }
    }

    // =========================================================
    // DESTROY
    // =========================================================

    /**
     * Soft-delete an offer. Admin only.
     * Physical banner image file is NOT deleted on soft-delete
     * (preserved in case of accidental deletion / restore needed).
     */
    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        try {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('Only administrators can delete offers.');
            }

            $offer->delete();

            return $this->successResponse(null, 'Offer deleted successfully.');

        } catch (Throwable $e) {
            Log::error('OfferController@destroy', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to delete offer.');
        }
    }
}