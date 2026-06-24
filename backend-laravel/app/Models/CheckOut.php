<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * CheckOut Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * A CheckOut record captures the exact moment a customer exits
 * a parking location — the manager scans their QR code at the
 * exit gate, and this record is created.
 *
 * CheckOut is the most financially significant event after
 * the initial payment because it:
 *   1. Triggers overtime calculation (extra_hours × extra_hour_price)
 *   2. Collects any extra payment directly from the customer
 *   3. Marks the booking as "completed"
 *   4. Frees up the parking slot for the next booking
 *   5. Triggers the owner's earnings update
 *
 * WHY SEPARATE FROM CHECK-IN?
 * Check-in and check-out are distinct events that happen at
 * different times, carry different data (check-out has extra_hours
 * and extra_amount that check-in doesn't), and have separate
 * business logic. Keeping them in separate tables is cleaner
 * and easier to query independently.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   OWNER APP — ACTIVE PARKING SCREEN:
 *     After check-in, the owner sees the Active Parking screen
 *     showing: booked duration, time elapsed, total payable.
 *     When the customer wants to leave, the owner taps
 *     "Check-out & Collect Payment" → scans exit QR.
 *
 *   OWNER APP — SCAN QR (CHECK-OUT):
 *     API receives the QR scan.
 *     Calculates extra_hours = actual_time - booked_end_time (if any).
 *     Calculates extra_amount = extra_hours × extra_hour_price.
 *     Creates this CheckOut record.
 *     Shows owner how much to collect: base_amount + extra_amount.
 *
 *   BOOKING COMPLETION:
 *     After CheckOut is created:
 *       - booking_status → "completed"
 *       - actual_checkout_time on Booking is updated
 *       - parking_slot status → "available" (slot is freed)
 *       - owner's wallet earnings are updated
 *       - BookingStatusHistory record is created
 *
 *   EARNINGS DASHBOARD (OWNER APP):
 *     Owner's total earning per booking =
 *       (booking.amount + checkout.extra_amount) × owner_share_percent
 *     CheckOut.extra_amount contributes to total earnings.
 *
 *   ADMIN REPORTS:
 *     Extra revenue report: total extra_amount across all checkouts
 *     shows how much overtime revenue the platform generated.
 *
 * EXTRA HOURS LOGIC:
 *   extra_hours = max(0, actual_hours - booked_hours)
 *   extra_amount = extra_hours × pricing_rule.extra_hour_price
 *
 *   If customer left BEFORE end_time: extra_hours = 0, extra_amount = 0.
 *   No refund for early checkout (pre-booked hours are committed).
 *
 * FUTURE SCALABILITY:
 *   - Add `extra_payment_method` to track how the extra was paid
 *     (cash at gate, UPI, or added to original payment).
 *   - Add `extra_payment_status` to track whether the extra
 *     amount was actually collected (cash can't be auto-confirmed).
 *   - Add `scan_method` ("qr_scan" | "manual" | "anpr") for
 *     future ANPR-based auto-checkout.
 *   - Add `vehicle_damage_noted` boolean for parking lots that
 *     do damage inspection at exit.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int|null    $checked_out_by
 * @property \Carbon\Carbon $checkout_time
 * @property float       $extra_hours
 * @property float       $extra_amount
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CheckOut extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'check_outs';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'booking_id',
        'checked_out_by',
        'checkout_time',
        'extra_hours',
        'extra_amount',
        'notes',
    ];

    /**
     * Type-cast database columns to proper PHP types.
     *
     * Casting extra_hours as 'decimal:2' allows values like 1.50
     * (1 hour 30 minutes).
     *
     * Casting extra_amount as 'decimal:2' keeps financial
     * precision consistent.
     */
    protected $casts = [
        'booking_id'      => 'integer',
        'checked_out_by'  => 'integer',
        'checkout_time'   => 'datetime',
        'extra_hours'     => 'decimal:2',
        'extra_amount'    => 'decimal:2',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A CheckOut BELONGS TO one Booking.
     *
     * Usage:
     *   $checkOut->booking->booking_number   // "BK20260623123456"
     *   $checkOut->booking->amount           // ₹160 (pre-booked)
     *   $checkOut->extra_amount              // ₹80  (overtime)
     *   // Total the customer paid = ₹240
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * The User (Manager or Owner) who performed the check-out scan.
     *
     * Nullable because future ANPR/IoT systems may check out
     * vehicles automatically without a human scanning.
     *
     * Usage:
     *   $checkOut->checkedOutBy->name  // "Arjun Mehta"
     */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check whether this checkout involved any overtime charges.
     *
     * Usage:
     *   if ($checkOut->hasExtraCharges()) {
     *       // Notify customer of additional payment required
     *   }
     */
    public function hasExtraCharges(): bool
    {
        return (float) $this->extra_hours > 0;
    }

    /**
     * Calculate the total amount due at checkout.
     * This is the pre-booked amount PLUS any overtime charges.
     *
     * Usage:
     *   $total = $checkOut->totalDue();
     *   // Booking amount ₹160 + Extra ₹80 = ₹240
     *
     * Note: This requires the 'booking' relationship to be loaded.
     *   $checkOut->load('booking')->totalDue()
     */
    public function totalDue(): float
    {
        $baseAmount  = (float) ($this->booking->amount ?? 0);
        $extraAmount = (float) $this->extra_amount;

        return round($baseAmount + $extraAmount, 2);
    }
}