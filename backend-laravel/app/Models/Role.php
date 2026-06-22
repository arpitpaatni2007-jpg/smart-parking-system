<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ============================================================
 * Role Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Our Smart Parking System has 5 different types of users —
 * Super Admin, Admin, Parking Owner, Parking Manager, and Customer.
 * Each of them is allowed to do different things in the app.
 *
 * Instead of hardcoding "if user is admin do this, if user is
 * owner do that" everywhere in our code, we store roles as data
 * in the database. This is the core idea behind RBAC
 * (Role Based Access Control).
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 * - Every user (in the `users` table) will be linked to one Role.
 * - When a user logs in, we check their role to decide what
 *   screens/menus/actions they can access.
 *   Example: Only "Admin" and "Super Admin" roles can approve
 *   a new parking location. A "Customer" role can only book parking.
 * - Each Role will have a set of Permissions attached to it
 *   (via the role_permissions pivot table — created separately).
 *
 * FUTURE SCALABILITY:
 * - If tomorrow we need a new role like "Support Agent" or
 *   "Finance Manager", we just add a new row in the database —
 *   no code changes needed in most places.
 * - The `status` field lets us disable a role temporarily
 *   (e.g., disable "Parking Manager" role if that feature
 *   is paused) without deleting any data.
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The database table this model talks to.
     * Laravel would guess "roles" automatically since our class
     * is "Role", but we write it explicitly so it's clear for
     * anyone new reading this code.
     */
    protected $table = 'roles';

    /**
     * Fields that are allowed to be filled using mass assignment
     * (e.g. Role::create([...])).
     *
     * We list these explicitly instead of using $guarded = []
     * to stay safe — this is a Laravel best practice so random
     * extra fields can't sneak into our database by mistake.
     */
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Automatically cast these fields to proper PHP types
     * whenever we read them from the database.
     */
    protected $casts = [
        'status'     => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------
    | Using constants instead of typing "active" / "inactive" as plain
    | strings everywhere helps avoid typos and makes the code easier
    | to read. Example usage: Role::STATUS_ACTIVE
    */
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A Role can have MANY Permissions, and a Permission can belong
     * to MANY Roles. This is a classic many-to-many relationship,
     * connected through a pivot table called `role_permissions`.
     *
     * Example: The "Admin" role might have permissions like
     * "approve_parking", "manage_users", "view_reports".
     *
     * NOTE: The `role_permissions` pivot table is NOT part of this
     * task — it will be created in a later step once both the
     * Role and Permission tables exist.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * A Role can be assigned to MANY Users.
     * (One Super Admin role -> many super admin users, etc.)
     *
     * This assumes the `users` table has a `role_id` column.
     * That column setup is also outside the scope of this task,
     * but the relationship is defined here so it's ready to use
     * once that foreign key exists.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    | Small, readable helper methods so the rest of the app doesn't
    | need to know how "active" is stored internally.
    */

    /**
     * Quick check to see if this role is currently active.
     * Useful before allowing login or permission checks.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}