<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * BookingStatusHistory Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * The `bookings` table only ever shows the CURRENT booking status.
 * But in real operations, we need to know the FULL HISTORY:
 *
 *   "This booking went from pending → confirmed at 10:02 AM by
 *    the payment webhook, then checked_in at 10:45 AM by Manager
 *    Arjun, then completed at 12:35 PM by the same manager."
 *
 * Without this table, that timeline is completely invisible.
 * We'd have no way to:
 *   - Debug why a booking is stuck in "pending"
 *   - Answer a user's question: "When was my booking confirmed?"
 *   - Audit whether a manager's check-in time is suspicious
 *   - Build the booking timeline UI in the Admin Panel
 *
 * This is essentially an append-only audit log for booking status
 * changes. Every time booking_status on a Booking record changes,
 * we insert a new row here — we never update or delete from this table.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   ADMIN PANEL — BOOKING DETAIL:
 *     The Booking Detail screen shows a timeline of status changes:
 *     ● Booking Created          → 10:00 AM (pending)
 *     ● Payment Received         → 10:02 AM (confirmed)
 *     ● Customer Checked In      → 10:45 AM (checked_in)
 *     ● Customer Checked Out     → 12:35 PM (completed)
 *     This is built by reading all rows for a booking_id.
 *
 *   DEBUGGING AND SUPPORT:
 *     When a user says "my booking shows wrong status", support
 *     staff can look at this table to see exactly what happened
 *     and when — including who triggered each change.
 *
 *   AUTOMATED SERVICES:
 *     A Laravel scheduled job marks "no_show" on bookings that
 *     are still "confirmed" 30 min after booking_start_time.
 *     That change is also recorded here with changed_by = "system".
 *
 * HOW STATUS CHANGES ARE RECORDED:
 *   This should NOT be done manually in controllers. Instead, a
 *   BookingService (built later) will handle all status transitions
 *   and always call:
 *     BookingStatusHistory::create([
 *         'booking_id' => $booking->id,
 *         'old_status' => $booking->booking_status,     // BEFORE
 *         'new_status' => Booking::STATUS_CONFIRMED,    // AFTER
 *         'remarks'    => 'Payment verified via Razorpay',
 *         'changed_by' => $userId,  // or "system" for automated
 *     ]);
 *   Then separately: $booking->update(['booking_status' => ...])
 *
 * FUTURE SCALABILITY:
 *   - Add `ip_address` column for security auditing — know where
 *     the status change request came from.
 *   - Add `source` column: "api" | "admin_panel" | "system" | "webhook"
 *     to know which part of the code triggered the change.
 *   - Add `metadata` JSON column for extra context per status type.
 *     Example for cancelled: {"refund_id": "rfnd_xyz", "amount": 160}
 *
 * @property int         $id
 * @property int         $booking_id
 * @property string|null $old_status
 * @property string      $new_status
 * @property string|null $remarks
 * @property string|null $changed_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BookingStatusHistory extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'booking_status_histories';

    /**
     * Fields allowed for mass assignment.
     *
     * NOTE: `booking_id` IS in fillable here because this record
     * is always created programmatically by the BookingService —
     * never from raw user input. The service always provides the
     * correct booking_id.
     */
    protected $fillable = [
        'booking_id',
        'old_status',
        'new_status',
        'remarks',
        'changed_by',
    ];

    /**
     * Type-cast database columns to proper PHP types.
     */
    protected $casts = [
        'booking_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * Each status history record BELONGS TO one Booking.
     *
     * Usage:
     *   $history->booking->booking_number  // "BK20260623123456"
     *   $history->booking->user->name      // "Rahul Sharma"
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: get history for a specific booking.
     * Returns records in chronological order (oldest first)
     * which is what a timeline display needs.
     *
     * Usage:
     *   BookingStatusHistory::forBooking($id)->get()
     */
    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId)
                     ->orderBy('created_at', 'asc');
    }

    /**
     * Scope: get all history records where a status was reached.
     * Useful for analytics: "How many bookings ever reached checked_in?"
     *
     * Usage:
     *   BookingStatusHistory::withNewStatus(Booking::STATUS_COMPLETED)->count()
     */
    public function scopeWithNewStatus($query, string $status)
    {
        return $query->where('new_status', $status);
    }

    /*
    |--------------------------------------------------------------------
    | Static Helper
    |--------------------------------------------------------------------
    */

    /**
     * Record a status change for a booking in one clean call.
     *
     * This helper keeps the BookingService tidy. Instead of
     * calling create([...]) with all keys every time, just call:
     *
     *   BookingStatusHistory::record(
     *       booking: $booking,
     *       newStatus: Booking::STATUS_CONFIRMED,
     *       remarks: 'Payment confirmed via Razorpay webhook',
     *       changedBy: 'system'
     *   );
     *
     * @param  Booking     $booking    The booking being updated.
     * @param  string      $newStatus  The new status being set.
     * @param  string|null $remarks    Optional note about why it changed.
     * @param  string|null $changedBy  User ID or "system" or "webhook".
     * @return static                  The created history record.
     */
    public static function record(
        Booking $booking,
        string $newStatus,
        ?string $remarks = null,
        ?string $changedBy = null
    ): static {
        return static::create([
            'booking_id' => $booking->id,
            'old_status' => $booking->booking_status,
            'new_status' => $newStatus,
            'remarks'    => $remarks,
            'changed_by' => $changedBy,
        ]);
    }
}