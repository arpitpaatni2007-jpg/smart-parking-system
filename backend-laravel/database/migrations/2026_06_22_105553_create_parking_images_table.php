<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_parking_images_table
 *
 * Stores image file paths for parking location gallery photos.
 * One parking can have many images; one image is marked primary
 * for use as the listing thumbnail.
 *
 * STORAGE STRATEGY:
 *   Only file paths are stored here (not binary blobs).
 *   Actual files live in storage/app/public/ or S3.
 *   This keeps the DB fast and lets you switch storage backends
 *   by changing one .env variable.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parking_images', function (Blueprint $table) {

            $table->id();

            /**
             * The parking this image belongs to.
             * CASCADE on delete: when a parking is (hard) deleted,
             * its images are also removed from the DB automatically.
             * Your service layer should also delete the actual files
             * from storage when this happens.
             */
            $table->unsignedBigInteger('parking_id')
                  ->comment('FK → parkings.id');
            $table->foreign('parking_id')
                  ->references('id')->on('parkings')
                  ->onDelete('cascade');

            /**
             * Relative path to the image file.
             * Stored relative to the storage disk root.
             * e.g. "parking-images/abc123.jpg"
             *
             * Use Storage::url($path) to get the full public URL.
             * Keeping it relative means the URL base can change
             * (local → S3) without a DB migration.
             */
            $table->string('image_path')
                  ->comment('Relative storage path e.g. parking-images/abc123.jpg');

            /**
             * Marks one image as the primary thumbnail.
             * This image is shown in search result cards.
             *
             * CONSTRAINT NOTE: Only one image per parking should
             * have is_primary = true. Enforced in application logic
             * via ParkingImage::makePrimary() — not at DB level,
             * because UNIQUE partial indexes aren't supported in
             * all MySQL versions without conditional workarounds.
             *
             * FUTURE: Use a DB trigger or unique filtered index
             * if you want DB-level enforcement.
             */
            $table->boolean('is_primary')->default(false)
                  ->comment('true = this image is the listing thumbnail; only one per parking should be true');

            // No soft deletes: image records are cheap to delete and recreate.
            // If you need an audit trail, add softDeletes() here.

            $table->timestamps();

            // ── INDEXES ───────────────────────────────────────────────────
            // Loading all images for one parking (gallery view)
            $table->index('parking_id', 'idx_parking_images_parking');

            // Fast lookup for the primary image (thumbnail fetch)
            $table->index(['parking_id', 'is_primary'], 'idx_parking_images_primary');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_images');
    }
};