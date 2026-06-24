<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * CheckIn Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * A CheckIn record captures the exact moment a customer
 * physically enters a parking location by having their QR code
 * scanned at the entry point.
 *
 * We separate this from the Booking table for several reasons:
 *
 *   1. SINGLE RESPONSIBILITY:
 *      A booking is a reservation. A check-in is a physical
 *      event. They are different things that happen at different
 *      times — sometimes hours apart.
 *
 *   2. RICH CHECK-IN DATA:
 *      We store WHO performed the scan (checked_in_by) and any
 *      notes. This wouldn't cleanly fit on the bookings table
 *      without cluttering it.
 *
 *   3. ONE-TO-ONE GUARANTEE:
 *      The unique constraint on booking_id in the migration
 *      ensures a booking can only ever have ONE check-in record.
 *      This prevents double check-ins from race conditions or
 *      accidental double-scans.
 *
 *   4. AUDIT TRAIL:
 *      If there's ever a dispute ("I checked in but the system
 *      shows I didn't"), this record is the ground truth —
 *      it includes a precise timestamp and the manager's ID.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   OWNER APP — SCAN QR (CHECK-IN):
 *     Manager opens the QR scanner in the Owner App.
 *     Scans the customer's QR code.
 *     API validates the QR, creates a CheckIn record,
 *     and updates booking_status to "checked_in".
 *     Owner App shows: "Check-In Successful ✅"
 *
 *   BOOKING DETAIL SCREEN (ADMIN):
 *     Shows: "Checked in at 10:45 AM by Manager Arjun Mehta"
 *     Data comes from: checkIn.checkin_time + checkIn->checkedInBy->name
 *
 *   ACTIVE PARKING SCREEN (OWNER APP):
 *     After check-in, the Owner App shows the "Active Parking"
 *     screen with:
 *       Check-in Time: 10:45 AM  ← from this record
 *       Booked Duration: 2 hours
 *       Time Elapsed: 1 hr 20 min  ← calculated from checkin_time
 *
 *   ACTUAL HOURS CALCULATION:
 *     At checkout, the service computes:
 *       actual_hours = checkout_time - checkIn->checkin_time
 *     Then determines if extra_amount is owed.
 *
 * FUTURE SCALABILITY:
 *   - Add `location_lat` / `location_lng` columns to record
 *     the GPS coordinates of where the scan happened (useful
 *     for verifying the manager was physically at the parking).
 *   - Add `scan_method` column: "qr_scan" | "manual" | "anpr"
 *     (for when we add ANPR camera-based check-in in Phase 3).
 *   - Add `device_id` to track which device/tablet performed
 *     the scan (useful for multi-gate parking lots).
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int|null    $checked_in_by
 * @property \Carbon\Carbon $checkin_time
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CheckIn extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'check_ins';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'booking_id',
        'checked_in_by',
        'checkin_time',
        'notes',
    ];

    /**
     * Type-cast database columns to proper PHP types.
     */
    protected $casts = [
        'booking_id'     => 'integer',
        'checked_in_by'  => 'integer',
        'checkin_time'   => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A CheckIn BELONGS TO one Booking.
     *
     * This is the parent record. CheckIn is meaningless without
     * a corresponding Booking.
     *
     * Usage:
     *   $checkIn->booking->booking_number  // "BK20260623123456"
     *   $checkIn->booking->user->name      // "Rahul Sharma"
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * The User (Manager or Owner) who performed the check-in scan.
     *
     * The `checked_in_by` column stores the user_id of whoever
     * scanned the QR code in the Owner App. This could be:
     *   - The parking_owner themselves
     *   - A parking_manager assigned to that location
     *
     * Nullable because in future we may support ANPR auto-check-in
     * where no human scans the QR.
     *
     * Usage:
     *   $checkIn->checkedInBy->name  // "Arjun Mehta"
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Calculate how long ago (in minutes) the check-in happened.
     * Used on the "Active Parking" screen in the Owner App to show
     * "Time Elapsed: 1 hr 20 min".
     *
     * Usage:
     *   $checkIn->minutesElapsed()  // 80 (for 1 hr 20 min)
     */
    public function minutesElapsed(): int
    {
        return (int) $this->checkin_time->diffInMinutes(now());
    }

    /**
     * Format elapsed time as a human-readable string.
     * Used for display on the Owner App's Active Parking screen.
     *
     * Usage:
     *   $checkIn->elapsedLabel()  // "1 hr 20 min"
     */
    public function elapsedLabel(): string
    {
        $minutes = $this->minutesElapsed();
        $hours   = intdiv($minutes, 60);
        $mins    = $minutes % 60;

        if ($hours === 0) {
            return "{$mins} min";
        }

        if ($mins === 0) {
            return "{$hours} hr";
        }

        return "{$hours} hr {$mins} min";
    }
}