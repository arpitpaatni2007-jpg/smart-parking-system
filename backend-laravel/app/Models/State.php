<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================
 * State Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Our Smart Parking System will operate across multiple cities
 * in India. Every city belongs to a state. Before we can let
 * a Parking Owner register a parking location with an address,
 * we need a clean list of states so the address data stays
 * consistent and searchable.
 *
 * Without a States table, parking owners might type "Delhi",
 * "New Delhi", "NCT Delhi" — all meaning the same thing. Having
 * a dropdown backed by this table means every address uses the
 * exact same state name, making search and filtering reliable.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *   - The "Add Parking" screen (Owner App) will show a dropdown
 *     of states, then filter cities based on the selected state.
 *   - The "Search Parking" screen (User App) will let users
 *     browse/filter parking by state and city.
 *   - Admin Panel will use states for report filtering
 *     ("Show me revenue from Maharashtra" etc.)
 *   - The `parkings` table will reference city_id, which in
 *     turn references state_id — giving us a full location
 *     hierarchy: Parking → City → State.
 *
 * FUTURE SCALABILITY:
 *   - The `code` field (e.g. "MH" for Maharashtra, "DL" for
 *     Delhi) is useful for short display labels in the UI,
 *     SMS templates, or external API integrations (like
 *     government parking APIs that use state codes).
 *   - If we expand internationally, `code` could store ISO
 *     country subdivision codes. The `status` field lets us
 *     silently disable a state (e.g. if we're not yet live
 *     in a particular state) without deleting any data.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $code
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class State extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     * Explicit for clarity even though Laravel would guess "states".
     */
    protected $table = 'states';

    /**
     * Fields allowed for mass assignment.
     * Listed explicitly — a Laravel security best practice
     * to prevent unexpected fields from being saved.
     */
    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    /**
     * Type-cast database values to proper PHP types
     * when we read them from the model.
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
    | Using constants instead of raw strings prevents typos and makes
    | code like `State::STATUS_ACTIVE` easy to search for later.
    */

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A State has MANY Cities.
     *
     * Example: "Maharashtra" state has cities like
     * "Mumbai", "Pune", "Nagpur", "Nashik"...
     *
     * This relationship will be used when:
     *   - Loading the city dropdown after a state is selected
     *     in the "Add Parking" form.
     *   - Eager loading city counts for admin reports.
     *   - Deleting a state should cascade to cities — but we
     *     handle that at the DB level in the migration.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    | Query scopes let us write clean, readable queries elsewhere
    | in the code without repeating the same where() conditions.
    |
    | Usage example:
    |   State::active()->orderBy('name')->get()
    */

    /**
     * Scope: only return active states.
     * Used when populating dropdowns — we don't want to show
     * states where the platform isn't operating yet.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
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
     * Check if this state is currently active.
     * Useful in views and conditions without repeating the string.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}