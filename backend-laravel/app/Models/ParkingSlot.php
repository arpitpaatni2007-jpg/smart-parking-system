<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * ParkingSlot Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * A parking location contains multiple individual slots — the
 * actual bookable units. Each slot has:
 *   - A slot number (e.g. "A1", "B3", "EV-01") for physical identification
 *   - A vehicle type restriction (only bikes, only cars, etc.)
 *   - A slot type (standard, premium, EV-enabled, handicapped, etc.)
 *   - A real-time status (available, booked, reserved, maintenance)
 *
 * This granularity is required because:
 *   - When a user books, they book a SPECIFIC slot (not just "a car space")
 *   - The slot status changes in real time as bookings are created/completed
 *   - Owners can designate premium slots at higher prices
 *   - EV slots need separate tracking for charging infrastructure
 *
 * STATUS LIFECYCLE:
 *   available → [user books] → booked → [booking ends] → available
 *   available → [owner reserves for VIP] → reserved → available
 *   available → [maintenance] → maintenance → available
 *
 * FUTURE SCALABILITY:
 *   - Add `floor_number` for multi-level parking structures
 *   - Add `section` (e.g. "A", "B", "C") for large parking lots
 *   - Add `price_override` decimal to allow per-slot premium pricing
 *   - Add `qr_code` string for physical QR code on the slot sign
 *   - Add `ev_charger_type` enum for EV slot specs
 *
 * @property int         $id
 * @property int         $parking_id
 * @property int         $vehicle_type_id
 * @property string      $slot_number
 * @property string      $slot_type
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class ParkingSlot extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'parking_slots';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'parking_id',       // FK → parkings.id
        'vehicle_type_id',  // FK → vehicle_types.id (what vehicle fits here)
        'slot_number',      // Physical label e.g. "A1", "B3", "EV-01"
        'slot_type',        // 'standard' | 'premium' | 'ev' | 'handicapped'
        'status',           // 'available' | 'booked' | 'reserved' | 'maintenance'
    ];

    /**
     * Type-cast database values to proper PHP types.
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
    */

    // Slot types
    public const TYPE_STANDARD    = 'standard';
    public const TYPE_PREMIUM     = 'premium';
    public const TYPE_EV          = 'ev';
    public const TYPE_HANDICAPPED = 'handicapped';

    // Slot statuses
    public const STATUS_AVAILABLE   = 'available';
    public const STATUS_BOOKED      = 'booked';
    public const STATUS_RESERVED    = 'reserved';
    public const STATUS_MAINTENANCE = 'maintenance';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A ParkingSlot BELONGS TO one Parking.
     *
     * Usage: $slot->parking->name
     */
    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class, 'parking_id');
    }

    /**
     * A ParkingSlot BELONGS TO one VehicleType.
     *
     * This defines which vehicle category can use this slot.
     * e.g. Slot A1 is a Car slot → vehicle_type_id = (Car's ID)
     *
     * Usage: $slot->vehicleType->name
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /**
     * A ParkingSlot HAS MANY Bookings.
     *
     * A slot can be booked many times over its lifetime — one
     * active booking at a time, but a history of past bookings.
     *
     * NOTE: The Booking model is built in the Booking Module (next phase).
     *
     * Usage: $slot->bookings()->latest()->first()
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only available slots.
     * Used when checking real-time availability for a parking location.
     *
     * Usage: ParkingSlot::available()->where('parking_id', $id)->count()
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope: filter by vehicle type.
     * Used when a user selects their vehicle type to see matching slots.
     *
     * Usage: ParkingSlot::forVehicleType(2)->available()->get()
     */
    public function scopeForVehicleType($query, int $vehicleTypeId)
    {
        return $query->where('vehicle_type_id', $vehicleTypeId);
    }

    /**
     * Scope: filter by slot type.
     *
     * Usage: ParkingSlot::ofType('ev')->available()->get()
     */
    public function scopeOfType($query, string $slotType)
    {
        return $query->where('slot_type', $slotType);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this slot is currently available to book.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if this slot is an EV charging slot.
     *
     * @return bool
     */
    public function isEvSlot(): bool
    {
        return $this->slot_type === self::TYPE_EV;
    }

    /**
     * Mark this slot as booked.
     * Called by the Booking service when a booking is confirmed.
     *
     * @return bool
     */
    public function markAsBooked(): bool
    {
        return $this->update(['status' => self::STATUS_BOOKED]);
    }

    /**
     * Mark this slot as available again.
     * Called when a booking ends, is cancelled, or expires.
     *
     * @return bool
     */
    public function markAsAvailable(): bool
    {
        return $this->update(['status' => self::STATUS_AVAILABLE]);
    }
}