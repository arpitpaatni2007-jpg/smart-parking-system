<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ============================================================
 * ParkingFacility Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Not all parking locations offer the same amenities. Some have
 * CCTV cameras, some offer EV charging, some are covered, some
 * have security guards, washrooms, or are open 24/7.
 *
 * These are called "Facilities" — extra features that make a
 * parking location more or less attractive to users.
 *
 * Instead of adding boolean columns like `has_cctv`, `has_ev`,
 * `is_covered` directly on the `parkings` table (which would
 * mean a migration every time we add a new facility), we store
 * facilities in their own table and link them to parkings via
 * a pivot table. This is the correct, scalable approach.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   OWNER APP — ADD PARKING:
 *     Screen 10 in the Owner App shows a "Select Facilities"
 *     checklist. The items in that list come from THIS table.
 *     The owner checks which facilities their parking has.
 *     Those selections are saved to the `parking_facility` pivot.
 *
 *   USER APP — PARKING DETAILS:
 *     The parking detail screen shows facility icons (CCTV icon,
 *     EV icon, Covered icon...) for each parking location.
 *     These are loaded via the pivot relationship.
 *
 *   USER APP — SEARCH/FILTER:
 *     Users can filter parking by facility:
 *     "Show only EV-charging-enabled parkings near me."
 *     This filter queries the `parking_facility` pivot table.
 *
 *   ADMIN PANEL:
 *     Admin can manage the master list of facilities from the
 *     Settings section — add new ones, rename, or disable old ones.
 *
 * FUTURE SCALABILITY:
 *   - Adding a new facility (e.g. "Valet Parking", "Air Pump")
 *     is a new row in this table. No migration or code change
 *     needed elsewhere.
 *   - The `icon` field stores a key/URL so the UI can display
 *     the right icon for each facility without hardcoding it.
 *   - `status` lets us soft-disable facilities. Example: if EV
 *     charging is being rolled out in phases, we can show it
 *     in the list but mark it as coming soon using the description.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $icon
 * @property string|null $description
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ParkingFacility extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'parking_facilities';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'name',
        'icon',
        'description',
        'status',
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'status'     => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------
    */

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A ParkingFacility BELONGS TO MANY Parking Locations.
     *
     * This is a many-to-many relationship:
     *   - One facility (e.g. "CCTV") can exist at many parkings.
     *   - One parking can have many facilities (CCTV + EV + Covered).
     *
     * The pivot table `parking_facility` is the join table that
     * stores these connections. It will be created in the Parking
     * module's migration (not in this task).
     *
     * Usage example (when Parking model is built):
     *   $parking->facilities                    // get all facilities
     *   $parking->facilities()->attach($id)     // add a facility
     *   $parking->facilities()->detach($id)     // remove a facility
     *   $parking->facilities()->sync($ids)      // replace all
     *
     * And from the facility side:
     *   $facility->parkings->count()   // how many parkings have this
     */
    public function parkings(): BelongsToMany
    {
        return $this->belongsToMany(
            Parking::class,
            'parking_facility',     // pivot table name
            'parking_facility_id',  // FK on pivot pointing to THIS model
            'parking_id'            // FK on pivot pointing to Parking
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active facilities.
     * Used when building the "Select Facilities" checklist in the
     * Owner App and the filter panel in the User App.
     *
     * Usage:
     *   ParkingFacility::active()->orderBy('name')->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this facility is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}