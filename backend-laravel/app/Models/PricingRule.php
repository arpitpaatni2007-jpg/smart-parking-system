<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PricingRule Model
 *
 * Defines how much a vehicle is charged for parking based on its type.
 * Each rule is tied to a VehicleType (e.g. bike, car, truck) and supports
 * multiple pricing strategies:
 *
 *   - hourly  → Charge per hour (base_price = first hour, extra_hour_price = each after)
 *   - daily   → Flat rate per calendar day
 *   - monthly → Fixed monthly subscription rate
 *
 * EXAMPLES:
 *   Car  | hourly | base_price: 30 | extra: 20  → ₹30 first hour, ₹20/hr after
 *   Bike | daily  | base_price: 80 | extra: 0   → ₹80/day flat
 *
 * FUTURE SCALABILITY:
 *   - Add `parking_facility_id` FK to allow per-facility custom pricing
 *   - Add `valid_from` / `valid_to` for seasonal or event-based pricing
 *   - Add `peak_multiplier` for dynamic surge pricing
 *   - Add `min_charge` / `max_charge` to cap the bill range
 */
class PricingRule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Explicit table name.
     */
    protected $table = 'pricing_rules';

    /**
     * Mass-assignable fields.
     */
    protected $fillable = [
        'vehicle_type_id',   // FK → vehicle_types.id
        'pricing_type',      // 'hourly' | 'daily' | 'monthly'
        'base_price',        // Base/first-unit charge (in INR or platform currency)
        'extra_hour_price',  // Charge per additional hour/unit beyond the base
        'status',            // 'active' | 'inactive'
    ];

    /**
     * Automatic type casts.
     * DECIMAL columns come back from MySQL as strings — cast to float for math.
     */
    protected $casts = [
        'base_price'       => 'decimal:2',
        'extra_hour_price' => 'decimal:2',
    ];

    // ─────────────────────────────────────────────
    // CONSTANTS
    // ─────────────────────────────────────────────

    const PRICING_HOURLY  = 'hourly';
    const PRICING_DAILY   = 'daily';
    const PRICING_MONTHLY = 'monthly';

    // ─────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────

    /**
     * A pricing rule belongs to one VehicleType.
     *
     * This means: to find the price for a Car, you load the PricingRule
     * where vehicle_type_id = (Car's ID).
     *
     * Usage: $pricingRule->vehicleType->name
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    /**
     * Scope: only active pricing rules.
     * Usage: PricingRule::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: filter by pricing type.
     * Usage: PricingRule::ofType('hourly')->get()
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('pricing_type', $type);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    /**
     * Calculate the total charge for a given number of hours.
     * Only meaningful for 'hourly' pricing type.
     *
     * Logic:
     *   - If hours ≤ 1 → charge base_price only
     *   - If hours > 1 → base_price + (extra hours × extra_hour_price)
     *
     * @param  float $hours  Duration in hours (can be decimal, e.g. 1.5)
     * @return float         Total amount to charge
     */
    public function calculateCharge(float $hours): float
    {
        if ($this->pricing_type !== self::PRICING_HOURLY) {
            // For daily/monthly, just return base_price as-is
            return (float) $this->base_price;
        }

        if ($hours <= 1) {
            return (float) $this->base_price;
        }

        $extraHours = ceil($hours - 1); // Round up partial hours
        return (float) $this->base_price + ($extraHours * (float) $this->extra_hour_price);
    }
}