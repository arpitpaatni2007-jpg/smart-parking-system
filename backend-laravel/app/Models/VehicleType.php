<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * VehicleType Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Not all parking slots are the same. A motorcycle slot is
 * physically different from a car slot, and an EV charging
 * slot is different again. Similarly, pricing differs by
 * vehicle type — a two-wheeler pays less than a four-wheeler.
 *
 * VehicleType is the master list that defines all supported
 * vehicle categories in the system. Instead of hardcoding
 * "bike", "car", "taxi" as magic strings throughout the code,
 * we store them here as proper records. This makes the system
 * flexible and consistent.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   PARKING SLOTS:
 *     Each slot in `parking_slots` will have a `vehicle_type_id`
 *     so we know what kind of vehicle can use that slot.
 *     Example: Slot A1 → Car only, Slot B3 → Bike only.
 *
 *   PRICING:
 *     The `parkings` table (or a separate `parking_prices` table
 *     later) will store price_per_hour per vehicle type.
 *     A parking might charge ₹40/hr for bikes and ₹80/hr for cars.
 *
 *   USER APP — BOOKING:
 *     When a user books parking, they select their vehicle type.
 *     The app then filters available slots for that type and
 *     shows the correct price.
 *
 *   USER APP — MY VEHICLES:
 *     Each saved vehicle in `vehicles` table will have a
 *     `vehicle_type_id` linking here, so the app can auto-select
 *     the correct vehicle type when booking.
 *
 *   OWNER APP — SLOT MANAGEMENT:
 *     When an owner adds slots, they assign a vehicle type to each.
 *
 * FUTURE SCALABILITY:
 *   - Adding "EV Car", "Heavy Vehicle", "Auto Rickshaw" is just
 *     a new row — no code changes needed.
 *   - The `icon` field (URL or icon class name) lets the UI show
 *     a visual icon next to each vehicle type in dropdowns and
 *     on the booking screen without hardcoding SVGs per type.
 *   - `status` lets us soft-disable a vehicle type. Example: if
 *     we stop supporting taxi bookings in a city, we mark
 *     that type inactive without deleting any historical data.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $icon
 * @property string|null $description
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class VehicleType extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     * Laravel would guess "vehicle_types" (snake_case plural)
     * from "VehicleType" — written explicitly for clarity.
     */
    protected $table = 'vehicle_types';

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
     * A VehicleType HAS MANY Parking Slots.
     *
     * Example: The "Car" vehicle type has many Car-compatible
     * slots across all parking locations.
     *
     * Useful for:
     *   - Counting total available car slots across the platform.
     *   - Filtering slots when a user selects "Car" in the app.
     *
     * NOTE: The `ParkingSlot` model is part of the Parking module
     * (built later). This relationship is defined now so it's
     * ready when that module is connected.
     */
    public function parkingSlots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class, 'vehicle_type_id');
    }

    /**
     * A VehicleType HAS MANY saved Vehicles.
     *
     * The `vehicles` table (a user's saved vehicles) will store
     * a `vehicle_type_id`. This relationship lets us find all
     * saved bikes, all saved cars, etc.
     *
     * NOTE: The `Vehicle` model is built with the User module.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active vehicle types.
     * Used when building dropdowns in the Booking screen and
     * Owner's Slot Management screen.
     *
     * Usage:
     *   VehicleType::active()->orderBy('name')->get()
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
     * Check if this vehicle type is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}