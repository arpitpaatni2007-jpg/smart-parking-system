<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ============================================================
 * Parking Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * This is the core entity of the entire Smart Parking system.
 * A "Parking" record represents one physical parking location
 * owned and operated by a parking facility owner.
 *
 * Each Parking location:
 *   - Belongs to one owner (User with role 'owner')
 *   - Is located in a specific State and City
 *   - Has a precise GPS coordinate (latitude/longitude) for map display
 *   - Contains multiple slots (ParkingSlot) for different vehicle types
 *   - Can offer multiple facilities (CCTV, EV Charging, etc.)
 *   - Has photos (ParkingImage) for the app listing
 *
 * HOW IT FITS IN THE SYSTEM:
 *
 *   OWNER APP:
 *     Owners register their parking location here.
 *     They add slot configurations, images, and facilities.
 *
 *   USER APP:
 *     Users search for parking near their location.
 *     Results are filtered from this table using lat/lng proximity.
 *     The app shows name, address, available slots, price, and facilities.
 *
 *   BOOKING:
 *     When a user books, the booking record references parking_id
 *     and a specific slot_id from ParkingSlot.
 *
 * FUTURE SCALABILITY:
 *   - Add `rating` (decimal) for aggregated review score
 *   - Add `opening_time` / `closing_time` for operational hours
 *   - Add `is_24_hours` boolean flag
 *   - Add `cancellation_policy` for booking flexibility rules
 *   - Add `search_vector` (FULLTEXT index) for name/address search
 *
 * @property int         $id
 * @property int         $owner_id
 * @property int         $state_id
 * @property int         $city_id
 * @property string      $name
 * @property string|null $description
 * @property string      $address
 * @property float       $latitude
 * @property float       $longitude
 * @property int         $total_slots
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Parking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'parkings';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'owner_id',      // FK → users.id (the parking owner)
        'state_id',      // FK → states.id
        'city_id',       // FK → cities.id
        'name',          // Display name e.g. "Green Valley Parking"
        'description',   // Optional detailed description
        'address',       // Full street address
        'latitude',      // GPS latitude for map pin
        'longitude',     // GPS longitude for map pin
        'total_slots',   // Total physical slot count (denormalized for quick display)
        'status',        // 'active' | 'inactive' | 'pending'
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'total_slots' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------
    */

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PENDING  = 'pending';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A Parking BELONGS TO one Owner (User).
     *
     * The owner is the person who registered and manages this
     * parking location. They use the Owner App to manage slots,
     * view bookings, and track earnings.
     *
     * Usage: $parking->owner->name
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * A Parking BELONGS TO one State.
     *
     * Used for location-based filtering — e.g. "show all
     * parkings in Maharashtra".
     *
     * Usage: $parking->state->name
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * A Parking BELONGS TO one City.
     *
     * Used for city-level search — e.g. "find parking in Mumbai".
     *
     * Usage: $parking->city->name
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * A Parking HAS MANY ParkingImages.
     *
     * Multiple photos can be uploaded for one parking location.
     * One image is marked as primary (is_primary = true) and
     * shown as the thumbnail in search results.
     *
     * Usage:
     *   $parking->images                    → all images
     *   $parking->images()->where('is_primary', true)->first() → thumbnail
     */
    public function images(): HasMany
    {
        return $this->hasMany(ParkingImage::class, 'parking_id');
    }

    /**
     * A Parking HAS MANY ParkingSlots.
     *
     * Slots are the individual bookable units within a parking.
     * E.g. Slot A1, A2 for cars; Slot B1, B2 for bikes.
     *
     * Usage:
     *   $parking->slots                           → all slots
     *   $parking->slots()->where('status', 'available')->count() → free slots
     */
    public function slots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class, 'parking_id');
    }

    /**
     * A Parking BELONGS TO MANY ParkingFacilities (via pivot table).
     *
     * Facilities are amenities like CCTV, EV Charging, Covered Parking, etc.
     * This is a many-to-many: one parking can have many facilities,
     * and one facility type can be offered by many parkings.
     *
     * Pivot table: parking_facility_mappings
     *
     * Usage:
     *   $parking->facilities                    → Collection of ParkingFacility
     *   $parking->facilities->pluck('name')    → ['CCTV', 'EV Charging']
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(
            ParkingFacility::class,
            'parking_facility_mappings', // pivot table name
            'parking_id',                // FK on pivot pointing to this model
            'parking_facility_id'        // FK on pivot pointing to related model
        );
    }

    /**
     * A Parking's Owner HAS ONE OwnerBankDetail.
     *
     * Used when processing payouts to the parking owner.
     * We navigate: Parking → Owner → BankDetail.
     *
     * This is a convenience "has-one-through" style shortcut
     * defined directly here for ease of access in billing flows.
     *
     * Usage: $parking->ownerBankDetail->account_number
     *
     * NOTE: Technically this is hasOneThrough(OwnerBankDetail, User).
     * But defined as a direct query here for simplicity.
     * Refactor to hasOneThrough() if needed.
     */
    public function ownerBankDetail(): HasOne
    {
        return $this->hasOne(OwnerBankDetail::class, 'owner_id', 'owner_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active parkings.
     * Used when showing listings to users in the app.
     *
     * Usage: Parking::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: filter by city.
     * Used for city-level parking search.
     *
     * Usage: Parking::inCity(3)->active()->get()
     */
    public function scopeInCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope: filter by state.
     *
     * Usage: Parking::inState(1)->get()
     */
    public function scopeInState($query, int $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Scope: filter parkings near a GPS coordinate using Haversine formula.
     * Returns parkings within a given radius (in kilometers).
     *
     * Usage: Parking::nearLocation(28.6139, 77.2090, 5)->active()->get()
     *
     * @param  float $lat     User's latitude
     * @param  float $lng     User's longitude
     * @param  float $radius  Search radius in kilometers (default 10km)
     */
    public function scopeNearLocation($query, float $lat, float $lng, float $radius = 10)
    {
        // Haversine formula calculates distance between two GPS points
        return $query->selectRaw("
                *,
                ( 6371 * ACOS(
                    COS( RADIANS(?) ) *
                    COS( RADIANS(latitude) ) *
                    COS( RADIANS(longitude) - RADIANS(?) ) +
                    SIN( RADIANS(?) ) *
                    SIN( RADIANS(latitude) )
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this parking location is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the primary (thumbnail) image for this parking.
     * Returns null if no images have been uploaded yet.
     *
     * @return ParkingImage|null
     */
    public function primaryImage(): ?ParkingImage
    {
        return $this->images()->where('is_primary', true)->first();
    }

    /**
     * Count available (unbooked) slots for this parking.
     * Useful for showing "X slots available" in the app listing.
     *
     * @return int
     */
    public function availableSlotCount(): int
    {
        return $this->slots()->where('status', 'available')->count();
    }
}