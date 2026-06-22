<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * PermissionSeeder
 * ============================================================
 *
 * WHY THIS SEEDER EXISTS:
 * After roles are seeded, we need to define WHAT each role
 * can actually DO. This seeder inserts all the permissions
 * into the `permissions` table, then wires them to the right
 * roles in the `role_permissions` pivot table.
 *
 * Running this seeder gives us a working RBAC system from day one.
 * No manual database work needed — just run the seeder and
 * permission checks in middleware will start working.
 *
 * HOW IT WORKS (in order):
 *   1. Define all permissions grouped by module.
 *   2. Insert them into the `permissions` table.
 *   3. Look up which role IDs exist in the `roles` table.
 *   4. Define which permissions each role should have.
 *   5. Insert those mappings into `role_permissions`.
 *
 * HOW IT CONNECTS WITH SMART PARKING SYSTEM:
 *   - The Admin Panel uses these permissions to show/hide menus.
 *     Example: only roles with "manage_users" permission can see
 *     the "Users" section in the sidebar.
 *   - Laravel middleware will call something like:
 *       $user->role->permissions->contains('name', 'approve_parking')
 *     to decide whether to allow or block a request.
 *   - The `module` field groups permissions so the Admin Panel's
 *     Roles & Permissions screen can display them as a neat matrix
 *     (rows = modules, columns = roles, cells = on/off toggle).
 *
 * FUTURE SCALABILITY:
 *   - New features = new permission rows in `$permissions` below.
 *   - New roles = extend the `$rolePermissions` mapping below.
 *   - This seeder is safe to re-run thanks to `updateOrInsert`
 *     and `insertOrIgnore` — no duplicate errors.
 *
 * HOW TO RUN:
 *   php artisan db:seed --class=PermissionSeeder
 *
 * NOTE: RoleSeeder must be run BEFORE this seeder because we
 * look up role IDs from the `roles` table here.
 */
class PermissionSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════
        // STEP 1 — DEFINE ALL PERMISSIONS
        // ═══════════════════════════════════════════════════════
        //
        // Each entry has:
        //   `name`   — a snake_case slug used in code checks
        //   `module` — the feature group it belongs to
        //              (matches Admin Panel sidebar sections)
        //   `description` — plain English explanation for
        //                   whoever manages roles in the Admin Panel
        //
        // We use a consistent naming pattern for `name`:
        //   verb_noun  → manage_users, approve_parking, view_reports
        // This makes middleware checks read like sentences:
        //   "Can this user manage_users? Yes/No."
        // ═══════════════════════════════════════════════════════

        $permissions = [

            // ---------------------------------------------------
            // MODULE: User Management
            // Who uses this: Admin, Super Admin
            // Screens: Users List, User Details, Block/Unblock
            // ---------------------------------------------------
            [
                'name'        => 'view_users',
                'module'      => 'User Management',
                'description' => 'View the list of all registered customers on the platform.',
            ],
            [
                'name'        => 'create_users',
                'module'      => 'User Management',
                'description' => 'Create a new customer account from the admin panel.',
            ],
            [
                'name'        => 'edit_users',
                'module'      => 'User Management',
                'description' => 'Edit an existing customer\'s profile information.',
            ],
            [
                'name'        => 'delete_users',
                'module'      => 'User Management',
                'description' => 'Permanently delete a customer account from the platform.',
            ],
            [
                'name'        => 'block_users',
                'module'      => 'User Management',
                'description' => 'Block or unblock a customer account to restrict platform access.',
            ],

            // ---------------------------------------------------
            // MODULE: Role Management
            // Who uses this: Super Admin only
            // Screens: Roles List, Roles & Permissions Matrix
            // ---------------------------------------------------
            [
                'name'        => 'view_roles',
                'module'      => 'Role Management',
                'description' => 'View all roles and their assigned permissions.',
            ],
            [
                'name'        => 'create_roles',
                'module'      => 'Role Management',
                'description' => 'Create a new role in the system.',
            ],
            [
                'name'        => 'edit_roles',
                'module'      => 'Role Management',
                'description' => 'Edit an existing role\'s name, description, or status.',
            ],
            [
                'name'        => 'delete_roles',
                'module'      => 'Role Management',
                'description' => 'Delete a role from the system (only if no users are assigned to it).',
            ],
            [
                'name'        => 'assign_permissions',
                'module'      => 'Role Management',
                'description' => 'Assign or remove permissions from a role using the permissions matrix.',
            ],

            // ---------------------------------------------------
            // MODULE: Parking Management
            // Who uses this: Admin, Super Admin (approval side)
            //                Parking Owner (their own locations)
            // Screens: Parkings List, Add/Edit Parking, Approve/Reject
            // ---------------------------------------------------
            [
                'name'        => 'view_parkings',
                'module'      => 'Parking Management',
                'description' => 'View the list of all parking locations on the platform.',
            ],
            [
                'name'        => 'create_parkings',
                'module'      => 'Parking Management',
                'description' => 'Submit a new parking location for admin approval.',
            ],
            [
                'name'        => 'edit_parkings',
                'module'      => 'Parking Management',
                'description' => 'Edit parking location details such as name, address, pricing, or facilities.',
            ],
            [
                'name'        => 'delete_parkings',
                'module'      => 'Parking Management',
                'description' => 'Remove a parking location from the platform.',
            ],
            [
                'name'        => 'approve_parkings',
                'module'      => 'Parking Management',
                'description' => 'Approve a parking location submitted by an owner so it appears in search results.',
            ],
            [
                'name'        => 'reject_parkings',
                'module'      => 'Parking Management',
                'description' => 'Reject a parking location submission with notes explaining why.',
            ],
            [
                'name'        => 'manage_parking_slots',
                'module'      => 'Parking Management',
                'description' => 'Add, edit, or update the status of individual slots within a parking location.',
            ],

            // ---------------------------------------------------
            // MODULE: Vehicle Management
            // Who uses this: Customer (their own vehicles)
            //                Admin (view/moderate if needed)
            // Screens: My Vehicles (app), Vehicles list (admin)
            // ---------------------------------------------------
            [
                'name'        => 'view_vehicles',
                'module'      => 'Vehicle Management',
                'description' => 'View saved vehicle details (admins see all, customers see their own).',
            ],
            [
                'name'        => 'create_vehicles',
                'module'      => 'Vehicle Management',
                'description' => 'Add a new vehicle (plate number and type) to an account.',
            ],
            [
                'name'        => 'edit_vehicles',
                'module'      => 'Vehicle Management',
                'description' => 'Update details of a saved vehicle.',
            ],
            [
                'name'        => 'delete_vehicles',
                'module'      => 'Vehicle Management',
                'description' => 'Remove a saved vehicle from an account.',
            ],

            // ---------------------------------------------------
            // MODULE: Booking Management
            // Who uses this: Customer (their own bookings)
            //                Admin (all bookings)
            //                Parking Manager (QR scan check-in/out)
            // Screens: My Bookings (app), Bookings List (admin)
            // ---------------------------------------------------
            [
                'name'        => 'view_bookings',
                'module'      => 'Booking Management',
                'description' => 'View booking records (admins see all, customers see their own).',
            ],
            [
                'name'        => 'create_bookings',
                'module'      => 'Booking Management',
                'description' => 'Make a new parking booking from the mobile app.',
            ],
            [
                'name'        => 'cancel_bookings',
                'module'      => 'Booking Management',
                'description' => 'Cancel an existing booking (subject to cancellation policy).',
            ],
            [
                'name'        => 'checkin_bookings',
                'module'      => 'Booking Management',
                'description' => 'Scan a customer\'s QR code to mark them as checked in at the parking.',
            ],
            [
                'name'        => 'checkout_bookings',
                'module'      => 'Booking Management',
                'description' => 'Scan a customer\'s QR code to mark them as checked out and collect any extra charges.',
            ],

            // ---------------------------------------------------
            // MODULE: Payment Management
            // Who uses this: Customer (make payments)
            //                Admin (view all payments, process refunds)
            //                Parking Owner (view their earnings, request settlements)
            // Screens: Payments List, Earnings, Settlements
            // ---------------------------------------------------
            [
                'name'        => 'view_payments',
                'module'      => 'Payment Management',
                'description' => 'View payment records (admins see all, customers see their own).',
            ],
            [
                'name'        => 'make_payments',
                'module'      => 'Payment Management',
                'description' => 'Initiate a payment for a parking booking via Razorpay.',
            ],
            [
                'name'        => 'refund_payments',
                'module'      => 'Payment Management',
                'description' => 'Process a refund for a cancelled or failed booking.',
            ],
            [
                'name'        => 'view_earnings',
                'module'      => 'Payment Management',
                'description' => 'View the earnings dashboard and transaction history (for parking owners).',
            ],
            [
                'name'        => 'manage_settlements',
                'module'      => 'Payment Management',
                'description' => 'Process and mark settlement payouts from the platform to parking owners.',
            ],
            [
                'name'        => 'manage_commission',
                'module'      => 'Payment Management',
                'description' => 'Configure the platform\'s commission percentage (global or per-parking).',
            ],

            // ---------------------------------------------------
            // MODULE: Reports
            // Who uses this: Admin, Super Admin
            // Screens: Earnings Report, Bookings Report, Owner Report, User Report
            // ---------------------------------------------------
            [
                'name'        => 'view_reports',
                'module'      => 'Reports',
                'description' => 'Access the reports section and view analytics charts and tables.',
            ],
            [
                'name'        => 'export_reports',
                'module'      => 'Reports',
                'description' => 'Export report data as CSV or PDF for offline use.',
            ],

            // ---------------------------------------------------
            // MODULE: Notifications
            // Who uses this: Admin (send bulk notifications)
            //                All users (receive notifications)
            // Screens: Notification Management (admin panel)
            //          Notification list (mobile app)
            // ---------------------------------------------------
            [
                'name'        => 'view_notifications',
                'module'      => 'Notifications',
                'description' => 'View the notification inbox (in-app notification list).',
            ],
            [
                'name'        => 'send_notifications',
                'module'      => 'Notifications',
                'description' => 'Send bulk push notifications to users or owners from the admin panel.',
            ],
            [
                'name'        => 'manage_notification_templates',
                'module'      => 'Notifications',
                'description' => 'Create and edit notification templates used by automated system events.',
            ],

            // ---------------------------------------------------
            // MODULE: CMS Management
            // Who uses this: Super Admin, Admin
            // Screens: CMS Pages (Terms, Privacy Policy, FAQs, About)
            // ---------------------------------------------------
            [
                'name'        => 'view_cms',
                'module'      => 'CMS Management',
                'description' => 'View static CMS pages like Terms & Conditions and Privacy Policy.',
            ],
            [
                'name'        => 'edit_cms',
                'module'      => 'CMS Management',
                'description' => 'Edit the content of static CMS pages from the admin panel.',
            ],

        ]; // end $permissions array

        // ═══════════════════════════════════════════════════════
        // STEP 2 — INSERT PERMISSIONS INTO THE DATABASE
        // ═══════════════════════════════════════════════════════
        //
        // We add timestamps to each entry manually because we're
        // using DB::table() directly (which doesn't auto-fill
        // timestamps like an Eloquent model would).
        //
        // `updateOrInsert` means: if a row with this `name` already
        // exists, update it. If not, insert it. Safe to re-run.
        // ═══════════════════════════════════════════════════════

        $this->command->info('  → Inserting permissions...');

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'module'      => $permission['module'],
                    'description' => $permission['description'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        $this->command->info('  ✅ ' . count($permissions) . ' permissions inserted.');

        // ═══════════════════════════════════════════════════════
        // STEP 3 — LOAD ROLE IDs FROM THE DATABASE
        // ═══════════════════════════════════════════════════════
        //
        // We DON'T hardcode role IDs (1, 2, 3...) because those
        // can change if the database is reset or roles are added
        // in different orders. Instead, we look them up by `name`.
        // ═══════════════════════════════════════════════════════

        $roles = DB::table('roles')->pluck('id', 'name');

        // Quick safety check — if RoleSeeder wasn't run first,
        // we warn the developer and stop gracefully.
        if ($roles->isEmpty()) {
            $this->command->error(
                '❌  No roles found. Please run RoleSeeder before PermissionSeeder.'
            );
            return;
        }

        // ═══════════════════════════════════════════════════════
        // STEP 4 — LOAD PERMISSION IDs FROM THE DATABASE
        // ═══════════════════════════════════════════════════════
        //
        // Same approach — look up by `name`, don't assume IDs.
        // ═══════════════════════════════════════════════════════

        $perms = DB::table('permissions')->pluck('id', 'name');

        // ═══════════════════════════════════════════════════════
        // STEP 5 — DEFINE WHICH ROLES GET WHICH PERMISSIONS
        // ═══════════════════════════════════════════════════════
        //
        // This is the permission matrix for our Smart Parking System.
        // Each key is a role `name`, and the value is an array of
        // permission `name` strings that role should have.
        //
        // DESIGN DECISION:
        // Super Admin has ALL permissions (we list them explicitly
        // rather than using a wildcard, so the pivot table is always
        // accurate and queryable).
        // ═══════════════════════════════════════════════════════

        $rolePermissions = [

            // -------------------------------------------------------
            // SUPER ADMIN — Has every permission.
            // This role is the system owner/developer level.
            // -------------------------------------------------------
            'super_admin' => [
                // User Management
                'view_users', 'create_users', 'edit_users', 'delete_users', 'block_users',
                // Role Management
                'view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'assign_permissions',
                // Parking Management
                'view_parkings', 'create_parkings', 'edit_parkings', 'delete_parkings',
                'approve_parkings', 'reject_parkings', 'manage_parking_slots',
                // Vehicle Management
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                // Booking Management
                'view_bookings', 'create_bookings', 'cancel_bookings',
                'checkin_bookings', 'checkout_bookings',
                // Payment Management
                'view_payments', 'make_payments', 'refund_payments',
                'view_earnings', 'manage_settlements', 'manage_commission',
                // Reports
                'view_reports', 'export_reports',
                // Notifications
                'view_notifications', 'send_notifications', 'manage_notification_templates',
                // CMS
                'view_cms', 'edit_cms',
            ],

            // -------------------------------------------------------
            // ADMIN — Can manage the platform day-to-day.
            // Cannot manage roles or change commission settings
            // (those are Super Admin only).
            // -------------------------------------------------------
            'admin' => [
                // User Management
                'view_users', 'edit_users', 'block_users',
                // Role Management (read only — cannot create or delete roles)
                'view_roles',
                // Parking Management (full, including approval)
                'view_parkings', 'create_parkings', 'edit_parkings',
                'approve_parkings', 'reject_parkings', 'manage_parking_slots',
                // Vehicle Management (read only — for support purposes)
                'view_vehicles',
                // Booking Management (read + cancel on behalf of user)
                'view_bookings', 'cancel_bookings',
                // Payment Management (view + refunds, but not commission config)
                'view_payments', 'refund_payments', 'manage_settlements',
                // Reports (full access)
                'view_reports', 'export_reports',
                // Notifications
                'view_notifications', 'send_notifications', 'manage_notification_templates',
                // CMS
                'view_cms', 'edit_cms',
            ],

            // -------------------------------------------------------
            // PARKING OWNER — Manages their own parking business.
            // Can only see/edit their own parkings, not others'.
            // API layer will enforce the "own data only" restriction —
            // the permission just unlocks the feature category.
            // -------------------------------------------------------
            'parking_owner' => [
                // Parking Management (their own locations)
                'view_parkings', 'create_parkings', 'edit_parkings', 'manage_parking_slots',
                // Vehicle Management (view customer vehicles on bookings)
                'view_vehicles',
                // Booking Management (view their location's bookings, scan QR)
                'view_bookings', 'checkin_bookings', 'checkout_bookings',
                // Payment Management (view earnings, request settlement)
                'view_payments', 'view_earnings',
                // Notifications (receive notifications)
                'view_notifications',
            ],

            // -------------------------------------------------------
            // PARKING MANAGER — On-ground staff, very limited access.
            // Can only scan QR codes and view today's bookings for
            // their assigned parking location.
            // -------------------------------------------------------
            'parking_manager' => [
                // Parking Management (view only — so they can see the parking they manage)
                'view_parkings',
                // Booking Management (view + QR scan only)
                'view_bookings', 'checkin_bookings', 'checkout_bookings',
                // Notifications (receive notifications from owner or admin)
                'view_notifications',
            ],

            // -------------------------------------------------------
            // CUSTOMER — The mobile app end user.
            // Can only access their own data — API layer enforces this.
            // Permissions here just unlock the feature category.
            // -------------------------------------------------------
            'customer' => [
                // Parking Management (search and view only)
                'view_parkings',
                // Vehicle Management (their own vehicles)
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                // Booking Management (their own bookings)
                'view_bookings', 'create_bookings', 'cancel_bookings',
                // Payment Management (make payments, view their own payment history)
                'view_payments', 'make_payments',
                // Notifications (receive their own notifications)
                'view_notifications',
            ],

        ]; // end $rolePermissions

        // ═══════════════════════════════════════════════════════
        // STEP 6 — INSERT INTO role_permissions PIVOT TABLE
        // ═══════════════════════════════════════════════════════

        $this->command->info('  → Assigning permissions to roles...');

        $pivotRows = [];
        $now       = now();

        foreach ($rolePermissions as $roleName => $permissionNames) {
            // Get the ID for this role. Skip if role doesn't exist.
            $roleId = $roles[$roleName] ?? null;

            if (! $roleId) {
                $this->command->warn("  ⚠  Role '{$roleName}' not found in DB — skipping.");
                continue;
            }

            foreach ($permissionNames as $permName) {
                // Get the ID for this permission. Skip if not found.
                $permId = $perms[$permName] ?? null;

                if (! $permId) {
                    $this->command->warn("  ⚠  Permission '{$permName}' not found in DB — skipping.");
                    continue;
                }

                // Build the pivot row.
                // We collect all rows first, then bulk-insert below
                // for better performance than inserting one by one.
                $pivotRows[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        // -------------------------------------------------------
        // `insertOrIgnore` skips rows that already exist
        // (based on the composite primary key: role_id + permission_id).
        // This makes the seeder safe to re-run without errors.
        //
        // We chunk the insert into batches of 50 to avoid hitting
        // any database packet size limits on large permission sets.
        // -------------------------------------------------------
        foreach (array_chunk($pivotRows, 50) as $chunk) {
            DB::table('role_permissions')->insertOrIgnore($chunk);
        }

        // ═══════════════════════════════════════════════════════
        // SUMMARY OUTPUT
        // ═══════════════════════════════════════════════════════

        $this->command->newLine();
        $this->command->info('✅  PermissionSeeder completed successfully.');
        $this->command->line('    → ' . count($permissions) . ' permissions created');
        $this->command->line('    → ' . count($pivotRows)   . ' role-permission mappings assigned');
        $this->command->newLine();

        // Print a quick role summary so the developer can
        // verify at a glance that everything looks right.
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $this->command->line(
                '    ' . str_pad($roleName, 20) . ' → ' . count($permissionNames) . ' permissions'
            );
        }
    }
}