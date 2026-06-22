<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * ParkingFacilityMapping Model (Pivot Table Model)
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * This is the pivot (junction) table that connects Parkings
 * to ParkingFacilities in a many-to-many relationship.
 *
 * THE PROBLEM IT SOLVES:
 *   A parking can offer multiple facilities:
 *     "Green Valley Parking" → [CCTV, EV Charging, Covered]
 *
 *   A facility can be offered by many parkings:
 *     "CCTV" → [Green Valley Parking, City Centre Parking, ...]
 *
 *   This many-to-many link is stored here — one row per
 *   (parking, facility) combination.
 *
 * DO YOU NEED THIS AS A MODEL?
 *   Usually, Laravel pivot tables don't need an explicit Model —
 *   the BelongsToMany relationship handles them automatically.
 *   We create this Model explicitly because:
 *     1. It gives you a place to add pivot attributes later
 *        (e.g. `is_verified`, `added_at`)
 *     2. It lets you query the pivot table directly if needed
 *        without going through the parent relationship
 *     3. It follows the project's consistent structure
 *
 * FUTURE SCALABILITY:
 *   - Add `is_featured` boolean to highlight top facilities per parking
 *   - Add `added_by` (admin user id) to track who verified the facility
 *   - Add `verified_at` timestamp for facility verification workflows
 *
 * @property int $id
 * @property int $parking_id
 * @property int $parking_facility_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ParkingFacilityMapping extends Model
{
    /**
     * The database table used by this model.
     */
    protected $table = 'parking_facility_mappings';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'parking_id',           // FK → parkings.id
        'parking_facility_id',  // FK → parking_facilities.id
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * This mapping record BELONGS TO one Parking.
     *
     * Usage: $mapping->parking->name
     */
    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class, 'parking_id');
    }

    /**
     * This mapping record BELONGS TO one ParkingFacility.
     *
     * Usage: $mapping->facility->name
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(ParkingFacility::class, 'parking_facility_id');
    }
}