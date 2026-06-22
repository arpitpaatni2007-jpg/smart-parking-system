<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CommissionSetting Model
 *
 * Stores the platform's commission/revenue-sharing configuration.
 * In a parking marketplace, the platform typically takes a commission
 * from each booking, and the parking facility owner gets the remainder.
 *
 * Example:
 *   commission_percent  = 20  → Platform keeps 20%
 *   owner_share_percent = 80  → Parking owner receives 80%
 *
 * FUTURE SCALABILITY:
 *   - You can extend this to support per-facility or per-city commission overrides
 *   - Add a `valid_from` / `valid_to` date range for time-bound commission rules
 *   - Link to a specific ParkingFacility via nullable FK for custom rates
 */
class CommissionSetting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     * Laravel auto-detects "commission_settings" but we define it explicitly
     * for clarity and to avoid surprises if you rename the class.
     */
    protected $table = 'commission_settings';

    /**
     * Mass-assignable fields.
     * Only fields listed here can be set via create() or fill().
     * This protects against mass-assignment vulnerabilities.
     */
    protected $fillable = [
        'commission_percent',    // Platform's cut (e.g. 20 = 20%)
        'owner_share_percent',   // Facility owner's cut (e.g. 80 = 80%)
        'status',                // 'active' | 'inactive'
    ];

    /**
     * Cast these columns to native PHP types automatically.
     * 'decimal:2' ensures calculations stay precise (no floating-point drift).
     */
    protected $casts = [
        'commission_percent'   => 'decimal:2',
        'owner_share_percent'  => 'decimal:2',
    ];

    // ─────────────────────────────────────────────
    // SCOPES — reusable query filters
    // ─────────────────────────────────────────────

    /**
     * Scope: only return active commission settings.
     * Usage: CommissionSetting::active()->first()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    /**
     * Quickly verify that commission + owner share add up to 100%.
     * Call this before saving to catch configuration errors early.
     *
     * @return bool
     */
    public function isBalanced(): bool
    {
        return ($this->commission_percent + $this->owner_share_percent) == 100;
    }
}