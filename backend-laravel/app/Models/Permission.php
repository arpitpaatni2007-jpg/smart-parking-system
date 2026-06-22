<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ============================================================
 * Permission Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * A "Role" tells us WHO a user is (e.g. Admin, Customer).
 * A "Permission" tells us WHAT that user is allowed to DO
 * (e.g. "approve_parking", "manage_bookings", "view_reports").
 *
 * Splitting Roles and Permissions into two separate tables
 * (instead of just hardcoding access per role) is the standard
 * RBAC approach. It means one Permission can be shared across
 * multiple Roles, and we can fine-tune access without creating
 * a brand new role every time.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 * - Each Permission represents one specific action in the system.
 *   Example permissions we'll likely create later:
 *     "manage_users", "approve_parking", "manage_bookings",
 *     "manage_commission", "view_earnings", "scan_qr_checkin"
 * - The `module` field groups permissions together so the Admin
 *   Panel's "Roles & Permissions" screen can show them neatly
 *   under headings like "User Management", "Parking Management",
 *   "Booking Management", etc.
 * - Permissions get attached to Roles using the role_permissions
 *   pivot table (built separately). A user's actual access is
 *   then: User -> Role -> Permissions.
 *
 * FUTURE SCALABILITY:
 * - New features almost always need new permissions. Since this
 *   is just a database row, we don't need to touch the codebase
 *   structure — we add a permission, attach it to the right
 *   role(s), and we're done.
 * - The `module` grouping also makes it easy to build a clean
 *   permission-matrix UI in the admin panel later (one column
 *   per role, one row per permission, grouped by module).
 */
class Permission extends Model
{
    use HasFactory;

    /**
     * The database table this model talks to.
     * Written explicitly for clarity, same reasoning as the
     * Role model.
     */
    protected $table = 'permissions';

    /**
     * Fields allowed for mass assignment.
     * Kept explicit (instead of $guarded = []) as a safety net —
     * this is a Laravel best practice.
     */
    protected $fillable = [
        'name',
        'module',
        'description',
    ];

    /**
     * Automatically cast these fields to proper PHP types.
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
     * A Permission can belong to MANY Roles, and a Role can have
     * MANY Permissions. Same many-to-many relationship as defined
     * on the Role model, just viewed from the other side.
     *
     * Example: "view_reports" permission might be given to both
     * "Admin" and "Super Admin" roles.
     *
     * NOTE: The `role_permissions` pivot table is created in a
     * later step, not part of this task.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }
}