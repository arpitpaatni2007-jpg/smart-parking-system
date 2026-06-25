<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingImageRequest;
use App\Http\Resources\ParkingImageResource;
use App\Models\Parking;
use App\Models\ParkingImage;
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
 * ParkingImageController
 * ============================================================
 *
 * Handles image uploads and management for parking locations.
 *
 * ENDPOINTS:
 *   GET    /api/v1/parkings/{parking}/images          → index
 *   POST   /api/v1/parkings/{parking}/images          → store (upload)
 *   GET    /api/v1/parkings/{parking}/images/{image}  → show
 *   DELETE /api/v1/parkings/{parking}/images/{image}  → destroy
 *
 * NOTE: No update() endpoint — updating an image means deleting the
 * old one and uploading a new file. The only "update" that makes sense
 * is toggling is_primary — which is done via a dedicated endpoint
 * in the routes (PATCH .../images/{image}/primary).
 *
 * FILE STORAGE:
 *   Images are stored in storage/app/public/parking-images/
 *   Public URL: /storage/parking-images/filename.webp
 *   Run: php artisan storage:link to create the public symlink.
 *
 *   FUTURE: Set FILESYSTEM_DISK=s3 in .env to switch to S3 storage.
 *   The Storage facade handles both transparently.
 *
 * FILE NAMING:
 *   Files are stored with a UUID-based name to prevent:
 *   - Collisions between multiple owners
 *   - Path traversal attacks (no user-provided filenames)
 *   - Guessable filenames that expose sensitive images
 */
class ParkingImageController extends Controller
{
    use ApiResponse;

    /** Directory where parking images are stored */
    private const STORAGE_DIR = 'parking-images';

    // =========================================================
    // INDEX — List Images for a Parking
    // =========================================================

    /**
     * Return all images for a parking location.
     * Primary image is listed first.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Parking $parking): JsonResponse
    {
        try {
            $images = $parking->images()
                ->orderByDesc('is_primary') // Primary first
                ->orderBy('created_at')
                ->get();

            return $this->successResponse(
                ParkingImageResource::collection($images),
                'Parking images retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingImageController@index failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve parking images.');
        }
    }

    // =========================================================
    // STORE — Upload a New Image
    // =========================================================

    /**
     * Upload a new image for a parking location.
     *
     * FLOW:
     *   1. Validate: file exists, is image, max 5MB
     *   2. Authorization: must be the parking owner
     *   3. Generate a unique safe filename (UUID-based)
     *   4. Store file to disk via Storage facade
     *   5. If is_primary: demote any existing primary image
     *   6. Create ParkingImage DB record with the file path
     *   7. Return the new image resource
     *
     * AUTO PRIMARY:
     *   If this is the FIRST image for this parking, it automatically
     *   becomes the primary image regardless of the is_primary flag.
     *
     * @param  \App\Http\Requests\Parking\StoreParkingImageRequest $request
     * @param  \App\Models\Parking                                 $parking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreParkingImageRequest $request, Parking $parking): JsonResponse
    {
        try {
            // ── Authorization ──────────────────────────────────────────────
            if ($parking->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only upload images to your own parking locations.');
            }

            $imageModel = DB::transaction(function () use ($request, $parking) {
                // ── Generate unique filename ───────────────────────────────
                /**
                 * UUID ensures no two files share a name.
                 * We use the original file extension for browser compatibility.
                 * e.g. "a3f1c9d2-5b8e-4c7a-9d1f-2e3b4c5a6d7e.jpg"
                 */
                $extension = $request->file('image')->getClientOriginalExtension();
                $filename  = Str::uuid() . '.' . strtolower($extension);
                $path      = self::STORAGE_DIR . '/' . $filename;

                // ── Store the file ─────────────────────────────────────────
                /**
                 * Storage::putFileAs() stores the file at the given path.
                 * 'public' disk = storage/app/public/ (symlinked to public/storage/)
                 *
                 * FUTURE: change 'public' to 's3' for cloud storage.
                 */
                Storage::disk('public')->putFileAs(
                    self::STORAGE_DIR,
                    $request->file('image'),
                    $filename
                );

                // ── Determine if this should be primary ────────────────────
                $isPrimary = $request->boolean('is_primary', false);

                // Auto-promote to primary if it's the first image
                $hasExistingImages = $parking->images()->exists();
                if (! $hasExistingImages) {
                    $isPrimary = true;
                }

                // ── Demote existing primary if needed ──────────────────────
                if ($isPrimary) {
                    $parking->images()
                            ->where('is_primary', true)
                            ->update(['is_primary' => false]);
                }

                // ── Create the DB record ───────────────────────────────────
                return ParkingImage::create([
                    'parking_id' => $parking->id,
                    'image_path' => $path,
                    'is_primary' => $isPrimary,
                ]);
            });

            return $this->createdResponse(
                new ParkingImageResource($imageModel),
                'Image uploaded successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingImageController@store failed', [
                'parking_id' => $parking->id,
                'error'      => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to upload image. Please try again.');
        }
    }

    // =========================================================
    // SHOW — Get Single Image
    // =========================================================

    /**
     * Return details for a single parking image.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @param  \App\Models\ParkingImage $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Parking $parking, ParkingImage $image): JsonResponse
    {
        try {
            // Verify the image belongs to this parking
            if ($image->parking_id !== $parking->id) {
                return $this->notFoundResponse('Image not found for this parking location.');
            }

            return $this->successResponse(
                new ParkingImageResource($image),
                'Image retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingImageController@show failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to retrieve image.');
        }
    }

    // =========================================================
    // DESTROY — Delete an Image
    // =========================================================

    /**
     * Delete a parking image.
     *
     * FLOW:
     *   1. Verify image belongs to this parking
     *   2. Authorization: must be parking owner
     *   3. Delete the physical file from storage
     *   4. Delete the DB record
     *   5. If the deleted image was primary, auto-promote the next image
     *
     * FILE CLEANUP:
     *   Always delete the physical file when removing the DB record.
     *   Orphaned files waste storage and cannot be cleaned up easily later.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @param  \App\Models\ParkingImage $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Parking $parking, ParkingImage $image): JsonResponse
    {
        try {
            if ($image->parking_id !== $parking->id) {
                return $this->notFoundResponse('Image not found for this parking location.');
            }

            if ($parking->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only delete images from your own parking locations.');
            }

            DB::transaction(function () use ($parking, $image) {
                $wasPrimary = $image->is_primary;

                // ── Delete the physical file from storage ──────────────────
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }

                // ── Delete the DB record ───────────────────────────────────
                $image->delete();

                // ── Auto-promote next image if this was primary ────────────
                /**
                 * If the deleted image was the primary thumbnail, promote
                 * the next oldest image to be the new primary automatically.
                 * This prevents the parking from having no primary image.
                 */
                if ($wasPrimary) {
                    $nextImage = $parking->images()->oldest()->first();
                    $nextImage?->update(['is_primary' => true]);
                }
            });

            return $this->successResponse(null, 'Image deleted successfully.');

        } catch (Throwable $e) {
            Log::error('ParkingImageController@destroy failed', [
                'image_id' => $image->id,
                'error'    => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to delete image.');
        }
    }

    // =========================================================
    // SET PRIMARY — Promote an Image to Primary
    // =========================================================

    /**
     * Set a specific image as the primary thumbnail for the parking.
     * Called via: PATCH /api/v1/parkings/{parking}/images/{image}/primary
     *
     * Automatically demotes any previously primary image.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Parking      $parking
     * @param  \App\Models\ParkingImage $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPrimary(Request $request, Parking $parking, ParkingImage $image): JsonResponse
    {
        try {
            if ($image->parking_id !== $parking->id) {
                return $this->notFoundResponse('Image not found for this parking location.');
            }

            if ($parking->owner_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only update images for your own parking locations.');
            }

            // Use the model's makePrimary() helper (handles demotion internally)
            $image->makePrimary();

            return $this->successResponse(
                new ParkingImageResource($image->fresh()),
                'Primary image updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('ParkingImageController@setPrimary failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Failed to update primary image.');
        }
    }
}