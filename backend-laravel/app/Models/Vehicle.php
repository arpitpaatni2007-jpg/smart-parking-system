<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * Vehicle Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * In the Smart Parking system, users register their vehicles so that:
 *   1. They don't have to re-enter vehicle details every time they book
 *   2. The parking gate system can match the arriving vehicle to a booking
 *   3. Vehicle documents (RC, insurance, PUC) can be stored and verified
 *   4. Different vehicle types (bike, car, truck) get different slot assignments
 *
 * REAL-WORLD ANALOGY:
 * Think of this like "Saved Cards" on a payment app — users register
 * their vehicles once and pick from the list when making a booking.
 *
 * HOW IT FITS IN THE SYSTEM:
 *
 *   USER APP — MY VEHICLES:
 *     Users add their car/bike registration here.
 *     When booking, they select from their saved vehicles.
 *     The booking records the vehicle_id for gate verification.
 *
 *   GATE VERIFICATION:
 *     When a vehicle arrives, the attendant can verify:
 *     vehicle_number (number plate) matches the booking's vehicle.
 *
 *   SLOT ASSIGNMENT:
 *     vehicle.vehicle_type_id → PricingRule → correct slot type
 *     A car can only be assigned to a car slot, not a bike slot.
 *
 *   DOCUMENT COMPLIANCE:
 *     hasMany VehicleDocuments tracks RC book, insurance, PUC.
 *     Useful for facilities that require document verification
 *     before allowing parking (government facilities, etc.)
 *
 * VEHICLE NUMBER FORMAT (India):
 *   Standard format: "DL 01 AB 1234" or "HR26DQ8849"
 *   Stored as uppercase, spaces stripped by accessor for consistency.
 *   Example: "HR26DQ8849"
 *
 * STATUS MEANINGS:
 *   active   → Vehicle is usable for bookings
 *   inactive → User deactivated it (e.g., sold the vehicle)
 *
 * FUTURE SCALABILITY:
 *   - Add `is_default` boolean — user's primary vehicle for quick booking
 *   - Add `fuel_type` enum ('petrol','diesel','electric','cng','hybrid')
 *     for EV slot preference and emission-based parking zones
 *   - Add `seating_capacity` integer for future carpooling features
 *   - Add `vehicle_year` smallInteger for age-based restrictions
 *   - Add `insurance_expiry` date for quick compliance checks
 *   - Add `verified_at` + `verified_by` for admin-verified vehicles
 *   - Add `fasttag_id` string for FASTag-integrated auto-payment
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $vehicle_type_id
 * @property string      $vehicle_number
 * @property string      $vehicle_name
 * @property string      $vehicle_brand
 * @property string      $vehicle_color
 * @property string      $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     * Explicitly declared to avoid confusion — Laravel would
     * auto-detect "vehicles" but we write it for clarity.
     */
    protected $table = 'vehicles';

    /**
     * Fields allowed for mass assignment.
     *
     * These are the only fields that can be set via:
     *   Vehicle::create([...])  or  $vehicle->fill([...])
     *
     * Protects against mass-assignment vulnerabilities where an
     * attacker might pass extra fields (e.g. user_id) in a POST body.
     */
    protected $fillable = [
        'user_id',          // FK → users.id — who owns this vehicle
        'vehicle_type_id',  // FK → vehicle_types.id — car, bike, truck, etc.
        'vehicle_number',   // Registration plate e.g. "HR26DQ8849"
        'vehicle_name',     // User-given nickname e.g. "My Swift", "Office Car"
        'vehicle_brand',    // Manufacturer e.g. "Maruti Suzuki", "Honda", "Royal Enfield"
        'vehicle_color',    // Color e.g. "White", "Midnight Black"
        'status',           // 'active' | 'inactive'
    ];

    /**
     * Automatic type casts.
     * Laravel converts these DB values to/from the specified PHP types.
     *
     * Without casts:
     *   $vehicle->status returns "active" (string from DB)
     * With casts:
     *   $vehicle->status returns "active" (same here, but typed)
     *   $vehicle->created_at returns a Carbon object (not a plain string)
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------
    | Use these instead of hardcoding strings like 'active' or 'inactive'
    | throughout your codebase. If you ever rename a status, you only
    | change it here — not in 20 different files.
    |--------------------------------------------------------------------
    */

    /** Vehicle is usable for bookings */
    public const STATUS_ACTIVE   = 'active';

    /** User deactivated this vehicle (e.g., sold it) */
    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A Vehicle BELONGS TO one User.
     *
     * Every vehicle must have an owner — the user who registered it.
     * Used to load all vehicles belonging to the logged-in user.
     *
     * Usage:
     *   $vehicle->user->name        → "Arpit Sharma"
     *   $vehicle->user->email       → "arpit@example.com"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A Vehicle BELONGS TO one VehicleType.
     *
     * VehicleType is the master category: Car, Bike, Truck, EV, etc.
     * This determines:
     *   - Which parking slot type the vehicle can use
     *   - Which pricing rule applies for booking
     *
     * Usage:
     *   $vehicle->vehicleType->name        → "Car"
     *   $vehicle->vehicleType->pricingRules → pricing for this type
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /**
     * A Vehicle HAS MANY VehicleDocuments.
     *
     * A single vehicle can have multiple documents:
     *   - Registration Certificate (RC Book)
     *   - Insurance Certificate
     *   - Pollution Under Control (PUC) Certificate
     *   - Fitness Certificate (for commercial vehicles)
     *
     * Usage:
     *   $vehicle->documents                      → all documents
     *   $vehicle->documents()->active()->get()   → only valid docs
     *   $vehicle->documents()->ofType('rc')->first() → RC document
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'vehicle_id');
    }

    /**
     * A Vehicle HAS MANY Bookings.
     *
     * A vehicle can be used for multiple bookings over its lifetime.
     * Used to show "Parking History" for a specific vehicle.
     *
     * NOTE: The Booking model (from Booking Management module) uses
     * vehicle_id FK. This relationship is the inverse of Booking::vehicle().
     *
     * Usage:
     *   $vehicle->bookings()->latest()->get()    → booking history
     *   $vehicle->bookings()->active()->count()  → currently parked?
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes — Reusable Query Filters
    |--------------------------------------------------------------------
    | Scopes let you write clean, chainable queries without repeating
    | WHERE conditions everywhere.
    |
    | Usage: Vehicle::active()->forUser(1)->get()
    | Instead of: Vehicle::where('status','active')->where('user_id',1)->get()
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active vehicles.
     * Used in booking flow — only active vehicles shown for selection.
     *
     * Usage: Vehicle::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: filter vehicles belonging to a specific user.
     * Used in "My Vehicles" screen to load the current user's vehicles.
     *
     * Usage: Vehicle::forUser(auth()->id())->active()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $userId
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter vehicles by vehicle type.
     * Used when searching for bikes vs cars vs trucks.
     *
     * Usage: Vehicle::ofType(2)->active()->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $vehicleTypeId
     */
    public function scopeOfType($query, int $vehicleTypeId)
    {
        return $query->where('vehicle_type_id', $vehicleTypeId);
    }

    /*
    |--------------------------------------------------------------------
    | Mutators & Accessors
    |--------------------------------------------------------------------
    */

    /**
     * Mutator: automatically uppercase and strip spaces from vehicle_number.
     *
     * WHY:
     *   Users enter number plates in many formats:
     *     "hr 26 dq 8849", "Hr26Dq8849", "HR26 DQ 8849"
     *   We normalize all of these to: "HR26DQ8849"
     *   This ensures consistent lookups and avoids "HR26DQ8849" ≠ "HR 26 DQ 8849"
     *
     * This runs automatically when you set $vehicle->vehicle_number = "hr 26 dq 8849"
     * It saves "HR26DQ8849" to the database.
     *
     * @param  string $value  Raw input from user
     */
    public function setVehicleNumberAttribute(string $value): void
    {
        // strtoupper → uppercase, str_replace → remove spaces
        $this->attributes['vehicle_number'] = strtoupper(str_replace(' ', '', $value));
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this vehicle is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get a formatted display label for this vehicle.
     * Used in dropdowns and booking summaries.
     *
     * Example: "My Swift (HR26DQ8849)" or "Honda Activa — White"
     *
     * @return string
     */
    public function displayLabel(): string
    {
        return "{$this->vehicle_name} ({$this->vehicle_number})";
    }

    /**
     * Check if all required documents are present and valid (not expired).
     *
     * FUTURE: Define what "required documents" means per vehicle type.
     * For now, checks that at least one active, non-expired document exists.
     *
     * @return bool
     */
    public function hasValidDocuments(): bool
    {
        return $this->documents()
                    ->where('status', VehicleDocument::STATUS_ACTIVE)
                    ->where(function ($query) {
                        $query->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->exists();
    }
}