<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ============================================================
 * ParkingImage Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * A parking listing in the app should show photos so users can
 * visually verify the location before booking. This model stores
 * the image paths for each parking location.
 *
 * DESIGN DECISIONS:
 *   - One parking can have MULTIPLE images (gallery view).
 *   - Exactly ONE image should be marked as `is_primary = true`.
 *     This is the thumbnail shown in search results and listing cards.
 *   - Images are stored on disk (or S3) and only the path is
 *     saved here — not the raw binary. This is the standard
 *     approach for scalable file storage.
 *
 * STORAGE:
 *   Files are stored in: storage/app/public/parking-images/
 *   Accessible at: /storage/parking-images/filename.jpg
 *   (after running: php artisan storage:link)
 *
 *   FUTURE: Switch to S3 by changing FILESYSTEM_DISK=s3 in .env.
 *   The `image_path` column stores the relative path, which works
 *   with both local and cloud storage via Laravel's Storage facade.
 *
 * FUTURE SCALABILITY:
 *   - Add `sort_order` integer for drag-to-reorder in owner app
 *   - Add `caption` string for accessibility alt text
 *   - Add `width` / `height` for pre-known image dimensions
 *   - Add `thumbnail_path` for pre-generated smaller version
 *
 * @property int         $id
 * @property int         $parking_id
 * @property string      $image_path
 * @property bool        $is_primary
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ParkingImage extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'parking_images';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'parking_id',   // FK → parkings.id
        'image_path',   // Relative path e.g. "parking-images/abc123.jpg"
        'is_primary',   // true = this is the main thumbnail
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'is_primary'  => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A ParkingImage BELONGS TO one Parking.
     *
     * Usage: $image->parking->name
     */
    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class, 'parking_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return the primary image.
     * Used when fetching the thumbnail for search result cards.
     *
     * Usage: ParkingImage::primary()->where('parking_id', $id)->first()
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods / Accessors
    |--------------------------------------------------------------------
    */

    /**
     * Get the full public URL for this image.
     * Works with both local disk and S3 (reads FILESYSTEM_DISK from .env).
     *
     * Usage: $image->url  → "https://yourapp.com/storage/parking-images/abc.jpg"
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    /**
     * Promote this image to be the primary thumbnail.
     * Automatically demotes any previously primary image for the same parking.
     *
     * Usage: $image->makePrimary()
     */
    public function makePrimary(): void
    {
        // First, demote any existing primary for this parking
        static::where('parking_id', $this->parking_id)
              ->where('is_primary', true)
              ->update(['is_primary' => false]);

        // Then promote this one
        $this->update(['is_primary' => true]);
    }
}