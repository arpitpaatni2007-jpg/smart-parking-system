<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleDocumentRequest;
use App\Http\Requests\Vehicle\UpdateVehicleDocumentRequest;
use App\Http\Resources\VehicleDocumentResource;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * ============================================================
 * VehicleDocumentController
 * ============================================================
 *
 * Manages document uploads for a user's vehicles.
 * Documents include: RC, Insurance, PUC, Fitness Certificate, Permit.
 *
 * ENDPOINTS (nested under vehicles):
 *   GET    /api/v1/vehicles/{vehicle}/documents               → List all documents
 *   POST   /api/v1/vehicles/{vehicle}/documents               → Upload a document
 *   GET    /api/v1/vehicles/{vehicle}/documents/{document}    → Document detail
 *   PUT    /api/v1/vehicles/{vehicle}/documents/{document}    → Update / replace file
 *   DELETE /api/v1/vehicles/{vehicle}/documents/{document}    → Delete document
 *
 * FILE STORAGE STRATEGY:
 *   Files are stored in: storage/app/public/vehicle-documents/
 *   Public URL via: Storage::url($path) → /storage/vehicle-documents/uuid.pdf
 *   Run: php artisan storage:link to create the symlink.
 *
 *   File names are UUID-based to prevent:
 *     - Collisions (two users upload a file named "insurance.pdf")
 *     - Path traversal attacks (no user-provided filenames ever used)
 *     - Guessable URLs that might expose sensitive documents
 *
 *   FUTURE: Set FILESYSTEM_DISK=s3 to move to S3. No code change needed.
 *
 * DOCUMENT STATUS FLOW:
 *   pending → [admin verifies] → active
 *   active  → [expiry_date passes] → expired  (via scheduled job)
 *   pending → [admin rejects] → rejected
 *
 * ADMIN ACTIONS:
 *   Admins can update the `status` field directly via PUT.
 *   Regular users can only update the file and expiry_date
 *   while the document is still in 'pending' status.
 *
 * FUTURE SCALABILITY:
 *   - Add scheduled job to auto-expire documents past their expiry_date
 *   - Add push notification 30/7/1 days before expiry
 *   - Add OCR extraction to auto-fill expiry_date from uploaded images
 *   - Add admin bulk-verify endpoint for the verification queue
 */
class VehicleDocumentController extends Controller
{
    use ApiResponse;

    /** Directory within the 'public' disk where documents are stored. */
    private const STORAGE_DIR = 'vehicle-documents';

    // =========================================================
    // INDEX — List Documents for a Vehicle
    // =========================================================

    /**
     * Return all documents for a given vehicle.
     *
     * QUERY PARAMETERS:
     *   ?document_type=insurance  → filter by type
     *   ?status=active            → filter by verification status
     *
     * ORDERING:
     *   Latest uploaded first. Within the same type, the newest
     *   document is the most relevant (e.g. most recent insurance).
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Vehicle      $vehicle  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            // ── Authorization: must own the vehicle or be admin ────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You do not have access to this vehicle.');
            }

            $query = $vehicle->documents();

            // ── Filter by document type ────────────────────────────────────
            if ($request->filled('document_type')) {
                $query->ofType($request->document_type);
            }

            // ── Filter by status ───────────────────────────────────────────
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $documents = $query->latest()->get();

            return $this->successResponse(
                VehicleDocumentResource::collection($documents),
                'Documents retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleDocumentController@index failed', [
                'vehicle_id' => $vehicle->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to retrieve documents.');
        }
    }

    // =========================================================
    // STORE — Upload a New Document
    // =========================================================

    /**
     * Upload a new document for a vehicle.
     *
     * FLOW:
     *   1. StoreVehicleDocumentRequest validates type, file, expiry
     *   2. Authorization: must own the vehicle
     *   3. Generate UUID-based filename
     *   4. Store file to disk via Storage facade
     *   5. Create VehicleDocument DB record (status: 'pending')
     *   6. Return the new document resource
     *
     * PENDING BY DEFAULT:
     *   All newly uploaded documents start as 'pending'.
     *   An admin must verify them before they count as 'active'.
     *   This prevents users from faking document compliance.
     *
     * MULTIPLE DOCUMENTS OF SAME TYPE:
     *   Allowed — insurance is renewed annually so there can be
     *   an 'expired' and a 'pending' insurance at the same time.
     *   The application shows the most recent active one.
     *
     * @param  \App\Http\Requests\Vehicle\StoreVehicleDocumentRequest $request
     * @param  \App\Models\Vehicle                                     $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVehicleDocumentRequest $request, Vehicle $vehicle): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if ($vehicle->user_id !== $request->user()->id && ! $request->user()->hasRole('admin')) {
                return $this->forbiddenResponse('You can only upload documents for your own vehicles.');
            }

            $document = DB::transaction(function () use ($request, $vehicle) {

                // ── Generate UUID-based filename ───────────────────────────
                /**
                 * Never use the user-provided filename — it could contain:
                 *   - Path traversal: "../../../etc/passwd"
                 *   - Collisions: two users with "insurance.pdf"
                 *   - Special chars that break storage paths
                 *
                 * UUID + original extension is safe and unique.
                 * e.g. "a3f1c9d2-5b8e-4c7a-9d1f.pdf"
                 */
                $extension = $request->file('document')->getClientOriginalExtension();
                $filename  = Str::uuid() . '.' . strtolower($extension);
                $path      = self::STORAGE_DIR . '/' . $filename;

                // ── Store the file to the 'public' disk ────────────────────
                /**
                 * Storage::putFileAs() stores the uploaded file at the given path.
                 * 'public' disk = storage/app/public/ (symlinked to public/storage/)
                 *
                 * To switch to S3: FILESYSTEM_DISK=s3 in .env
                 * No code change needed — Storage facade handles both.
                 */
                Storage::disk('public')->putFileAs(
                    self::STORAGE_DIR,
                    $request->file('document'),
                    $filename
                );

                // ── Create the DB record ───────────────────────────────────
                return VehicleDocument::create([
                    'vehicle_id'    => $vehicle->id,
                    'document_type' => $request->document_type,
                    'document_path' => $path,
                    'expiry_date'   => $request->expiry_date,
                    'status'        => VehicleDocument::STATUS_PENDING, // Always pending on upload
                ]);
            });

            return $this->createdResponse(
                new VehicleDocumentResource($document),
                'Document uploaded successfully. It will be reviewed by our team and verified within 2-3 business days.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleDocumentController@store failed', [
                'vehicle_id' => $vehicle->id,
                'user_id'    => $request->user()->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to upload document. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Single Document Detail
    // =========================================================

    /**
     * Return full details for a single vehicle document.
     * Verifies the document belongs to the given vehicle (scope check).
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \App\Models\Vehicle         $vehicle
     * @param  \App\Models\VehicleDocument $document  (route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        try {
            // ── Scope check: document must belong to this vehicle ──────────
            /**
             * Route model binding resolves {document} to any VehicleDocument.
             * Without this check, user could request:
             *   GET /vehicles/1/documents/999 (doc 999 belongs to vehicle 5!)
             */
            if ($document->vehicle_id !== $vehicle->id) {
                return $this->notFoundResponse('Document not found for this vehicle.');
            }

            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You do not have access to this document.');
            }

            $document->load('vehicle');

            return $this->successResponse(
                new VehicleDocumentResource($document),
                'Document retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleDocumentController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve document.');
        }
    }

    // =========================================================
    // UPDATE — Update a Document
    // =========================================================

    /**
     * Update an existing vehicle document.
     *
     * USER CAN UPDATE:
     *   - Replace the file (e.g. uploaded wrong file)
     *   - Correct the expiry_date
     *   - Only while the document is still 'pending' (not yet verified)
     *
     * ADMIN CAN ADDITIONALLY UPDATE:
     *   - status: 'active' (verify), 'rejected', 'expired'
     *   - Any document regardless of current status
     *
     * FILE REPLACEMENT:
     *   If a new file is uploaded, the OLD file is deleted from storage
     *   to prevent orphaned files accumulating on disk.
     *
     * @param  \App\Http\Requests\Vehicle\UpdateVehicleDocumentRequest $request
     * @param  \App\Models\Vehicle                                      $vehicle
     * @param  \App\Models\VehicleDocument                              $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateVehicleDocumentRequest $request, Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        try {
            // ── Scope check ────────────────────────────────────────────────
            if ($document->vehicle_id !== $vehicle->id) {
                return $this->notFoundResponse('Document not found for this vehicle.');
            }

            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You do not have access to this document.');
            }

            $isAdmin = $request->user()->hasRole('admin');

            // ── Non-admins can only update pending documents ───────────────
            /**
             * Once a document is verified (active), users cannot replace it.
             * They must upload a new document instead.
             * This prevents tampering with verified documents.
             */
            if (! $isAdmin && $document->status !== VehicleDocument::STATUS_PENDING) {
                return $this->errorResponse(
                    'Only pending documents can be updated. To replace a verified document, please upload a new one.',
                    409
                );
            }

            // ── Non-admins cannot change status ────────────────────────────
            if (! $isAdmin && $request->has('status')) {
                return $this->forbiddenResponse('You are not authorized to change the document status.');
            }

            $updateData = DB::transaction(function () use ($request, $document, $isAdmin) {

                $updateData = [];

                // ── Replace the file if a new one was uploaded ─────────────
                if ($request->hasFile('document')) {

                    // Delete the old file from storage to free up space
                    if (Storage::disk('public')->exists($document->document_path)) {
                        Storage::disk('public')->delete($document->document_path);
                    }

                    // Store the new file with a fresh UUID name
                    $extension = $request->file('document')->getClientOriginalExtension();
                    $filename  = Str::uuid() . '.' . strtolower($extension);
                    $newPath   = self::STORAGE_DIR . '/' . $filename;

                    Storage::disk('public')->putFileAs(
                        self::STORAGE_DIR,
                        $request->file('document'),
                        $filename
                    );

                    $updateData['document_path'] = $newPath;

                    // File replacement by user resets status to pending for re-verification
                    if (! $isAdmin) {
                        $updateData['status'] = VehicleDocument::STATUS_PENDING;
                    }
                }

                // ── Update expiry date if provided ─────────────────────────
                if ($request->has('expiry_date')) {
                    $updateData['expiry_date'] = $request->expiry_date;
                }

                // ── Admin: update status ───────────────────────────────────
                if ($isAdmin && $request->filled('status')) {
                    $updateData['status'] = $request->status;
                }

                return $updateData;
            });

            if (! empty($updateData)) {
                $document->update($updateData);
            }

            return $this->successResponse(
                new VehicleDocumentResource($document->fresh()),
                'Document updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('VehicleDocumentController@update failed', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to update document.');
        }
    }

    // =========================================================
    // DESTROY — Delete a Document
    // =========================================================

    /**
     * Delete a vehicle document record and its physical file.
     *
     * FILE CLEANUP:
     *   Always delete the physical file when removing the DB record.
     *   Orphaned files consume storage and cannot be cleaned up easily.
     *
     * ADMIN NOTE:
     *   Admins can delete any document.
     *   Users can only delete their own pending/rejected documents.
     *   Active (verified) documents cannot be deleted by users —
     *   they must upload a new one (the old becomes 'expired' naturally).
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \App\Models\Vehicle         $vehicle
     * @param  \App\Models\VehicleDocument $document
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        try {
            // ── Scope check ────────────────────────────────────────────────
            if ($document->vehicle_id !== $vehicle->id) {
                return $this->notFoundResponse('Document not found for this vehicle.');
            }

            // ── Authorization ──────────────────────────────────────────────
            if (! $this->canAccessVehicle($request->user(), $vehicle)) {
                return $this->forbiddenResponse('You do not have access to this document.');
            }

            $isAdmin = $request->user()->hasRole('admin');

            // ── Users cannot delete verified (active) documents ────────────
            if (! $isAdmin && $document->status === VehicleDocument::STATUS_ACTIVE) {
                return $this->errorResponse(
                    'Verified documents cannot be deleted. To replace this document, upload a new one.',
                    409
                );
            }

            DB::transaction(function () use ($document) {
                // ── Delete the physical file from storage ──────────────────
                if (Storage::disk('public')->exists($document->document_path)) {
                    Storage::disk('public')->delete($document->document_path);
                }

                // ── Soft-delete the DB record ──────────────────────────────
                /**
                 * Soft delete preserves the record for audit purposes.
                 * The file is physically deleted but the DB record remains
                 * with deleted_at set, preserving the document submission history.
                 */
                $document->delete();
            });

            return $this->successResponse(null, 'Document deleted successfully.');

        } catch (Throwable $e) {
            Log::error('VehicleDocumentController@destroy failed', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete document.');
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Check if the authenticated user can access a given vehicle.
     *
     * @param  \App\Models\User    $user
     * @param  \App\Models\Vehicle $vehicle
     * @return bool
     */
    private function canAccessVehicle($user, Vehicle $vehicle): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $vehicle->user_id === $user->id;
    }
}