<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * Refund Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * When a paid booking is cancelled, the user may be entitled to
 * a refund (full or partial, depending on cancellation policy).
 * This model records that refund event.
 *
 * REFUND SCENARIOS IN SMART PARKING:
 *
 *   FULL REFUND (refund_amount = payment.amount):
 *     - User cancels before check-in
 *     - Parking cancels the booking (e.g. slot damaged)
 *     - Admin overrides and issues full refund
 *
 *   PARTIAL REFUND (refund_amount < payment.amount):
 *     - User cancels less than X hours before booking start
 *       (cancellation fee applies)
 *     - User checks out early and policy allows partial refund
 *       for unused hours
 *
 *   NO REFUND:
 *     - User is a no-show (expired booking)
 *     - Late cancellation beyond policy window
 *     - In this case, no Refund record is created at all.
 *
 * REFUND FLOW:
 *   1. User cancels booking in app
 *   2. BookingService checks cancellation policy
 *   3. Refund record created: status = 'pending', refund_amount = X
 *   4. RefundService calls gateway API to initiate the refund
 *   5. Gateway processes refund (may take 5–10 business days)
 *   6. Webhook/callback arrives → Refund: status → 'processed', refunded_at = NOW()
 *   7. Payment: status → 'refunded' or 'partially_refunded'
 *   8. User gets push notification + email: "Your refund of ₹X is on its way"
 *
 * REFUND STATUS LIFECYCLE:
 *   pending → processing → processed (money sent to bank)
 *                        → failed    (gateway couldn't process — needs retry)
 *
 * ONE PAYMENT — ONE REFUND:
 *   We store one Refund record per Payment.
 *   This covers both full and partial refunds.
 *   If a booking needs multiple partial refunds (rare), extend this
 *   by removing the unique constraint on payment_id (see FUTURE section).
 *
 * FUTURE SCALABILITY:
 *   - Add `refund_reference` string for the gateway's refund transaction ID
 *     (separate from the original payment reference)
 *   - Add `initiated_by` FK → users.id (who triggered: user/admin/system)
 *   - Add `approved_by` FK → users.id (which admin approved)
 *   - Add `gateway_name` string for the refund gateway (may differ from payment)
 *   - Add `gateway_response` JSON for raw refund gateway response
 *   - Remove unique on payment_id + add `refund_number` for multiple-refund support
 *   - Add `cancellation_policy_id` FK to track which policy was applied
 *
 * @property int         $id
 * @property int         $payment_id
 * @property float       $refund_amount
 * @property string      $refund_reason
 * @property string      $refund_status
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Refund extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'refunds';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'payment_id',    // FK → payments.id — which payment is being refunded
        'refund_amount', // How much to refund (may be less than payment.amount for partial)
        'refund_reason', // Why the refund is being issued (for audit and user communication)
        'refund_status', // 'pending' | 'processing' | 'processed' | 'failed'
        'refunded_at',   // Timestamp when refund was confirmed as successfully processed
    ];

    /**
     * Automatic type casts.
     *
     * refund_amount cast to 'decimal:2' — same as payment.amount.
     * ALWAYS use decimal for money: 120.50 stays 120.50, not 120.4999999...
     */
    protected $casts = [
        'refund_amount' => 'decimal:2',   // Financial value — exact precision
        'refunded_at'   => 'datetime',    // Carbon — null until refund is confirmed
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants — Refund Status
    |--------------------------------------------------------------------
    |
    | LIFECYCLE:
    |   pending → processing → processed   (success path)
    |   pending → processing → failed      (gateway error — retry needed)
    |   pending → failed                   (immediate failure — no processing)
    |
    |--------------------------------------------------------------------
    */

    /**
     * Refund request created; not yet sent to gateway.
     * Waiting for: admin approval, policy check, or service trigger.
     */
    public const STATUS_PENDING    = 'pending';

    /**
     * Refund request sent to gateway; awaiting bank processing.
     * Typically takes 5–10 business days in India.
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * Gateway confirmed refund successfully sent to user's bank.
     * refunded_at is set when this status is reached.
     */
    public const STATUS_PROCESSED  = 'processed';

    /**
     * Refund failed at the gateway level.
     * Requires manual investigation and retry.
     * Common causes: account closed, bank rejection, network error.
     */
    public const STATUS_FAILED     = 'failed';

    /*
    |--------------------------------------------------------------------
    | Refund Reasons — Common Constants
    |--------------------------------------------------------------------
    | Standardize refund reasons for consistent reporting.
    | Free-text is allowed but these constants cover 90% of cases.
    |--------------------------------------------------------------------
    */

    /** User cancelled before check-in window */
    public const REASON_USER_CANCELLED      = 'User cancelled booking.';

    /** Booking window expired with no check-in */
    public const REASON_BOOKING_EXPIRED     = 'Booking expired — no check-in recorded.';

    /** Parking facility unavailable at time of booking */
    public const REASON_FACILITY_UNAVAILABLE = 'Parking facility was unavailable.';

    /** Admin manually issued refund */
    public const REASON_ADMIN_OVERRIDE      = 'Admin manually issued refund.';

    /** User checked out early, partial refund for unused hours */
    public const REASON_EARLY_CHECKOUT      = 'Refund for unused hours (early checkout).';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A Refund BELONGS TO one Payment.
     *
     * The original payment being refunded.
     * Through this, you can access: booking, user, amount paid, etc.
     *
     * Usage:
     *   $refund->payment->amount              → original charged amount
     *   $refund->payment->user->name          → "Arpit Sharma"
     *   $refund->payment->booking->booking_number → "BK-20250115-0042"
     *   $refund->refund_amount                → how much is being refunded
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only pending refunds.
     * Used in admin panel to show the refund approval queue.
     *
     * Usage: Refund::pending()->oldest()->get()
     */
    public function scopePending($query)
    {
        return $query->where('refund_status', self::STATUS_PENDING);
    }

    /**
     * Scope: only successfully processed refunds.
     * Used in financial reports — refunds reduce net revenue.
     *
     * Usage: Refund::processed()->sum('refund_amount')
     */
    public function scopeProcessed($query)
    {
        return $query->where('refund_status', self::STATUS_PROCESSED);
    }

    /**
     * Scope: only failed refunds that need retry.
     * Used by admin to manually retry failed refunds.
     *
     * Usage: Refund::failed()->with('payment.user')->get()
     */
    public function scopeFailed($query)
    {
        return $query->where('refund_status', self::STATUS_FAILED);
    }

    /**
     * Scope: refunds processed within a date range.
     * Used for monthly refund reports and financial reconciliation.
     *
     * Usage: Refund::processed()->inDateRange('2025-01-01', '2025-01-31')->get()
     */
    public function scopeInDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('refunded_at', [
            $from . ' 00:00:00',
            $to   . ' 23:59:59',
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this refund has been successfully processed.
     *
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->refund_status === self::STATUS_PROCESSED;
    }

    /**
     * Check if this refund is still waiting to be sent to the gateway.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->refund_status === self::STATUS_PENDING;
    }

    /**
     * Check if this refund failed and needs attention.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->refund_status === self::STATUS_FAILED;
    }

    /**
     * Check if this is a full refund (entire payment amount being returned).
     *
     * Compares refund_amount against the original payment amount.
     * Uses a tolerance of 0.01 to handle floating-point edge cases.
     *
     * @return bool
     */
    public function isFullRefund(): bool
    {
        return abs((float) $this->refund_amount - (float) $this->payment->amount) < 0.01;
    }

    /**
     * Check if this is a partial refund (less than the full payment amount).
     *
     * @return bool
     */
    public function isPartialRefund(): bool
    {
        return ! $this->isFullRefund();
    }

    /**
     * Get the amount NOT being refunded (kept by the platform).
     * Useful for cancellation fee calculations and display.
     *
     * Example:
     *   payment.amount = 120.00
     *   refund.refund_amount = 90.00
     *   retainedAmount() = 30.00  ← cancellation fee kept by platform
     *
     * @return float
     */
    public function retainedAmount(): float
    {
        return round(
            (float) $this->payment->amount - (float) $this->refund_amount,
            2
        );
    }
}