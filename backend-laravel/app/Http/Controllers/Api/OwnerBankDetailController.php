<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreOwnerBankDetailRequest;
use App\Http\Requests\Parking\UpdateOwnerBankDetailRequest;
use App\Http\Resources\OwnerBankDetailResource;
use App\Models\OwnerBankDetail;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ============================================================
 * OwnerBankDetailController
 * ============================================================
 *
 * Manages bank account details for parking owners.
 * Used by the finance module to process payouts.
 *
 * ENDPOINTS:
 *   GET    /api/v1/owner/bank-detail          → index  (get own bank detail)
 *   POST   /api/v1/owner/bank-detail          → store  (submit bank details)
 *   GET    /api/v1/owner/bank-detail/{detail} → show
 *   PUT    /api/v1/owner/bank-detail/{detail} → update
 *   DELETE /api/v1/owner/bank-detail/{detail} → destroy
 *
 * ONE-TO-ONE DESIGN:
 *   Each owner has at most one OwnerBankDetail record.
 *   If they try to create a second one, return the existing one
 *   and prompt them to update instead.
 *
 * SECURITY PRINCIPLES:
 *   - account_number is NEVER returned in responses (masked only)
 *   - Only the owner who created the record can access it
 *   - Admin access to bank details should go through admin panel (not this API)
 *   - Updating bank details resets status to 'pending_verification'
 *
 * SENSITIVE DATA HANDLING:
 *   account_number is encrypted before storing:
 *     encrypt($request->account_number)
 *   And decrypted when needed internally:
 *     decrypt($bankDetail->account_number)
 *   API responses always use masked_account_number accessor.
 */
class OwnerBankDetailController extends Controller
{
    use ApiResponse;

    // =========================================================
    // INDEX — Get Own Bank Detail
    // =========================================================

    /**
     * Return the authenticated owner's bank detail record.
     *
     * Returns null data (not 404) if the owner hasn't submitted
     * bank details yet — this is a valid "not filled" state.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Role check: only owners have bank details
            if (! $request->user()->hasRole('owner')) {
                return $this->forbiddenResponse('Only parking owners can access bank details.');
            }

            $bankDetail = OwnerBankDetail::where('owner_id', $request->user()->id)->first();

            if (! $bankDetail) {
                return $this->successResponse(
                    null,
                    'No bank details submitted yet. Please add your bank account for payouts.'
                );
            }

            $bankDetail->load('owner');

            return $this->successResponse(
                new OwnerBankDetailResource($bankDetail),
                'Bank details retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OwnerBankDetailController@index failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve bank details.');
        }
    }

    // =========================================================
    // STORE — Submit Bank Details
    // =========================================================

    /**
     * Submit bank details for payout processing.
     *
     * ONE-PER-OWNER ENFORCEMENT:
     *   If an owner already has bank details, return a 409 Conflict
     *   and direct them to use the update endpoint instead.
     *
     * ENCRYPTION:
     *   account_number is encrypted before storing.
     *   Only the last 4 digits are exposed in responses.
     *
     * STATUS:
     *   Newly submitted bank details start as 'pending_verification'.
     *   Admin must verify before payouts are enabled.
     *
     * @param  \App\Http\Requests\Parking\StoreOwnerBankDetailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOwnerBankDetailRequest $request): JsonResponse
    {
        try {
            // ── Role check ─────────────────────────────────────────────────
            if (! $request->user()->hasRole('owner')) {
                return $this->forbiddenResponse('Only parking owners can submit bank details.');
            }

            // ── Enforce one-per-owner ──────────────────────────────────────
            $existing = OwnerBankDetail::where('owner_id', $request->user()->id)->first();

            if ($existing) {
                return $this->errorResponse(
                    'You already have bank details on file. Please use the update endpoint to make changes.',
                    409 // 409 Conflict
                );
            }

            $bankDetail = OwnerBankDetail::create([
                'owner_id'             => $request->user()->id,
                'account_holder_name'  => $request->account_holder_name,
                'bank_name'            => $request->bank_name,
                /**
                 * ENCRYPTION: account_number is encrypted before storage.
                 * Laravel's encrypt() uses AES-256-CBC.
                 * Decrypt with: decrypt($bankDetail->getRawOriginal('account_number'))
                 */
                'account_number'       => encrypt($request->account_number),
                'ifsc_code'            => $request->ifsc_code,
                'status'               => OwnerBankDetail::STATUS_PENDING_VERIFICATION,
            ]);

            $bankDetail->load('owner');

            return $this->createdResponse(
                new OwnerBankDetailResource($bankDetail),
                'Bank details submitted successfully. They will be verified by our team within 2-3 business days.'
            );

        } catch (Throwable $e) {
            Log::error('OwnerBankDetailController@store failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to save bank details.');
        }
    }

    // =========================================================
    // SHOW — Get Specific Bank Detail Record
    // =========================================================

    /**
     * Return a specific bank detail record.
     *
     * Route model binding resolves {detail} → OwnerBankDetail.
     * We must verify it belongs to the requesting user.
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \App\Models\OwnerBankDetail $detail
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, OwnerBankDetail $detail): JsonResponse
    {
        try {
            // ── Authorization: owner can only see their own record ─────────
            if ($detail->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You do not have access to this bank detail record.');
            }

            $detail->load('owner');

            return $this->successResponse(
                new OwnerBankDetailResource($detail),
                'Bank details retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('OwnerBankDetailController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve bank details.');
        }
    }

    // =========================================================
    // UPDATE — Update Bank Details
    // =========================================================

    /**
     * Update existing bank details.
     *
     * IMPORTANT SECURITY RULE:
     *   Updating bank details ALWAYS resets status to 'pending_verification'.
     *   This forces admin re-verification before the new account can receive
     *   payouts. This prevents an owner from swapping accounts to a fraudulent
     *   one without admin knowledge.
     *
     * @param  \App\Http\Requests\Parking\UpdateOwnerBankDetailRequest $request
     * @param  \App\Models\OwnerBankDetail                             $detail
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateOwnerBankDetailRequest $request, OwnerBankDetail $detail): JsonResponse
    {
        try {
            if ($detail->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only update your own bank details.');
            }

            // ── Build update data ──────────────────────────────────────────
            $updateData = $request->only([
                'account_holder_name', 'bank_name', 'ifsc_code',
            ]);

            // ── Encrypt account_number if being updated ────────────────────
            if ($request->filled('account_number')) {
                $updateData['account_number'] = encrypt($request->account_number);
            }

            // ── SECURITY: Reset to pending verification ────────────────────
            /**
             * ANY update to bank details requires re-verification.
             * This is a mandatory security control — do not remove.
             */
            $updateData['status'] = OwnerBankDetail::STATUS_PENDING_VERIFICATION;

            $detail->update($updateData);

            return $this->successResponse(
                new OwnerBankDetailResource($detail->fresh()->load('owner')),
                'Bank details updated successfully. Our team will re-verify your account within 2-3 business days.'
            );

        } catch (Throwable $e) {
            Log::error('OwnerBankDetailController@update failed', [
                'detail_id' => $detail->id,
                'error'     => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update bank details.');
        }
    }

    // =========================================================
    // DESTROY — Delete Bank Details
    // =========================================================

    /**
     * Delete the owner's bank detail record.
     *
     * Soft-deletes the record (preserves for payout audit history).
     *
     * NOTE: Deleting bank details means the owner cannot receive
     * payouts until new details are submitted and verified.
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \App\Models\OwnerBankDetail $detail
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, OwnerBankDetail $detail): JsonResponse
    {
        try {
            if ($detail->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only delete your own bank details.');
            }

            $detail->delete(); // Soft delete — preserves audit trail

            return $this->successResponse(
                null,
                'Bank details removed. You will not be able to receive payouts until you add new bank details.'
            );

        } catch (Throwable $e) {
            Log::error('OwnerBankDetailController@destroy failed', [
                'detail_id' => $detail->id,
                'error'     => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to remove bank details.');
        }
    }
}