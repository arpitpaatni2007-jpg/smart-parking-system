<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * City Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Cities are the second level of our location hierarchy, sitting
 * between States and Parking Locations:
 *
 *   State → City → Parking Location → Parking Slot
 *
 * When a Parking Owner adds a new parking location, they pick
 * a city from a dropdown. That city_id is then stored on the
 * `parkings` table. When a Customer searches for parking, they
 * can search by city name or let GPS find nearby spots.
 *
 * Having cities as a separate master table (rather than just
 * typing the city name as a string on the parking record)
 * ensures consistent spelling, enables proper filtering, and
 * makes city-level analytics possible.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *   - Owner App → Add Parking: "State" dropdown → then "City"
 *     dropdown filters to only show cities in that state.
 *   - User App → Search: Users pick a city to search parking in,
 *     or GPS auto-detects their current city.
 *   - Admin Panel → Reports: "Revenue by city" breakdowns.
 *   - Admin Panel → Parkings: Filter all parkings by city.
 *   - The `parkings` table will store a `city_id` FK.
 *
 * FUTURE SCALABILITY:
 *   - If we add delivery zones, surge pricing areas, or smart
 *     city integrations, the city_id FK on `parkings` is the
 *     anchor for all those features.
 *   - `status` lets us launch in cities gradually. Set a city
 *     "inactive" before we have any parking owners onboarded —
 *     keeps the user-facing dropdown clean.
 *   - We could add `latitude` / `longitude` to this table later
 *     for city-center-based proximity searches.
 *
 * @property int         $id
 * @property int         $state_id
 * @property string      $name
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class City extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'cities';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'state_id',
        'name',
        'status',
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'state_id'   => 'integer',
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
     * A City BELONGS TO one State.
     *
     * Example: "Mumbai" belongs to "Maharashtra".
     *
     * Usage:
     *   $city->state->name  // returns "Maharashtra"
     *
     * This will be used in the Admin Panel and Owner App when
     * displaying a parking location's full address:
     *   "Connaught Place, New Delhi, Delhi"
     *                              ↑city   ↑state (via city->state)
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * A City HAS MANY Parking Locations.
     *
     * Example: "Bengaluru" city has many parking spots:
     *   MG Road Parking, Koramangala Parking, Indiranagar Parking...
     *
     * This relationship will be used for:
     *   - City-level parking searches
     *   - Admin reports: "How many parkings are in Mumbai?"
     *   - Dashboard stats: active parking count by city
     *
     * NOTE: The `Parking` model and `parkings` table are part of
     * a later module. This relationship is defined here so it's
     * ready to use once that module is built.
     */
    public function parkings(): HasMany
    {
        return $this->hasMany(Parking::class, 'city_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active cities.
     * Used when building city dropdowns in the apps.
     *
     * Usage:
     *   City::active()->where('state_id', $stateId)->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: filter cities by a specific state.
     * Makes the "state-dependent city dropdown" query clean.
     *
     * Usage:
     *   City::active()->forState($stateId)->orderBy('name')->get()
     */
    public function scopeForState($query, int $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this city is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Return a formatted label combining city and state name.
     * Useful for search results, dropdowns, and address display.
     *
     * Example: "Mumbai, Maharashtra"
     *
     * Requires the `state` relationship to be loaded:
     *   $city->load('state')->fullLabel()
     */
    public function fullLabel(): string
    {
        $stateName = $this->relationLoaded('state') && $this->state
            ? ', ' . $this->state->name
            : '';

        return $this->name . $stateName;
    }
}