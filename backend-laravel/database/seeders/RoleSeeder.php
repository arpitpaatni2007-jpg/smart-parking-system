<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * RoleSeeder
 * ============================================================
 *
 * WHY THIS SEEDER EXISTS:
 * After running migrations, the `roles` table is empty.
 * Before we can create any users or do any testing, we need
 * the 5 core roles to exist in the database.
 *
 * A seeder lets us insert this "starter data" with a single
 * command instead of manually inserting rows through a database
 * tool every time we set up the project on a new machine or
 * reset the database during development.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *   - "super_admin" — Full control over everything. Manages
 *     other admins, configures commission rates, has access
 *     to all screens in the Admin Panel.
 *
 *   - "admin" — Day-to-day platform management. Can approve
 *     parking locations, manage users and owners, handle
 *     support tickets, view reports. Cannot change system-wide
 *     settings or manage other admins.
 *
 *   - "parking_owner" — The person who listed a parking
 *     location. Can manage their own locations, slots, pricing,
 *     view their earnings, and manage their parking managers.
 *
 *   - "parking_manager" — A staff member under an owner.
 *     Can scan QR codes for check-in/check-out and view
 *     today's bookings for their assigned location. Limited
 *     access — cannot see earnings or change pricing.
 *
 *   - "customer" — The end user of the mobile app. Can
 *     search and book parking, make payments, view their
 *     booking history, and write reviews.
 *
 * FUTURE SCALABILITY:
 *   - If a new role is needed later (e.g. "support_agent"
 *     or "finance_manager"), simply add a new entry to the
 *     $roles array below and re-run the seeder.
 *   - Using `updateOrInsert` (instead of plain `insert`)
 *     means re-running the seeder won't crash with a
 *     "duplicate entry" error. It's idempotent — safe to
 *     run multiple times.
 *   - The `name` field is written in snake_case here
 *     (e.g. "super_admin"). The display-friendly version
 *     ("Super Admin") can be generated in the UI from this.
 *     Having the slug form in the DB makes permission checks
 *     in code more reliable and typo-resistant.
 *
 * HOW TO RUN:
 *   php artisan db:seed --class=RoleSeeder
 *   or add it to DatabaseSeeder and run: php artisan db:seed
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // The 5 core roles for our Smart Parking System.
        // Each entry maps exactly to the columns in the `roles`
        // table: name, description, status, created_at, updated_at
        // -------------------------------------------------------
        $roles = [
            [
                // The highest authority in the system.
                // Has access to EVERYTHING — including managing
                // other admins and configuring global settings
                // like commission rates and subscription plans.
                'name'        => 'super_admin',
                'description' => 'Full platform control. Can manage admins, configure system settings, '
                               . 'set commission rates, and access all data across the platform.',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                // Handles day-to-day operations.
                // Can approve/reject parking locations, manage
                // users and owners, handle support tickets, and
                // view platform-wide reports.
                // Cannot change system settings or manage admins.
                'name'        => 'admin',
                'description' => 'Day-to-day platform management. Approves parkings, manages users '
                               . 'and owners, handles support tickets, and views reports.',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                // The person who owns/operates a parking location.
                // Manages their own parking spots, pricing, earnings,
                // and the parking managers working under them.
                // Has access to the Owner mobile app.
                'name'        => 'parking_owner',
                'description' => 'Owns and manages one or more parking locations. Can manage slots, '
                               . 'pricing, view earnings, request settlements, and manage staff.',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                // A staff member assigned to a specific parking location
                // by the Parking Owner. Their job is to scan customer
                // QR codes at entry and exit and handle day-to-day
                // on-ground operations. Very limited system access.
                'name'        => 'parking_manager',
                'description' => 'Staff member under a parking owner. Can scan QR codes for '
                               . 'check-in/check-out and view daily bookings for their location.',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                // The end user who uses the Customer mobile app
                // to search, book, and pay for parking.
                // Has the most limited access — only to their own
                // bookings, profile, vehicles, and payment history.
                'name'        => 'customer',
                'description' => 'End user of the mobile app. Can search and book parking, '
                               . 'make payments, view booking history, and write reviews.',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        // -------------------------------------------------------
        // Loop through each role and upsert it into the database.
        //
        // `updateOrInsert` checks if a row with this `name` already
        // exists. If it does, it UPDATES the row. If not, it INSERTS
        // a new one. This makes the seeder safe to re-run at any
        // time without duplicate key errors.
        // -------------------------------------------------------
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                // Search condition: find by the unique `name` column
                ['name' => $role['name']],

                // Values to insert or update
                [
                    'description' => $role['description'],
                    'status'      => $role['status'],
                    'created_at'  => $role['created_at'],
                    'updated_at'  => $role['updated_at'],
                ]
            );
        }

        // -------------------------------------------------------
        // This line prints a friendly message in the terminal when
        // the seeder runs, so we know it completed successfully.
        // -------------------------------------------------------
        $this->command->info('✅  RoleSeeder: 5 roles seeded successfully.');
        $this->command->line('    → super_admin, admin, parking_owner, parking_manager, customer');
    }
}