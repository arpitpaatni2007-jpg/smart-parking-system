<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ============================================================
 * Payment Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * When a user confirms a parking booking, they pay for it.
 * This model records that payment event — how much was paid,
 * through which method, when, and what the current status is.
 *
 * RELATIONSHIP TO BOOKING:
 *   Booking → Payment → PaymentTransaction(s)
 *
 *   One Booking has ONE Payment record (one charge per booking).
 *   One Payment has ONE OR MORE PaymentTransactions — because:
 *     - The first attempt might fail (card declined) → one transaction
 *     - User retries → a second transaction
 *     - Final success → third transaction
 *   All attempts are logged in PaymentTransaction.
 *   Only the successful one matters for the Payment status.
 *
 * PAYMENT FLOW EXAMPLE:
 *   1. User taps "Pay ₹120" in Flutter app
 *   2. Payment record created: status = 'pending', amount = 120
 *   3. Razorpay gateway is called → PaymentTransaction #1 created
 *   4. If gateway succeeds:
 *        Payment: status → 'paid', paid_at → NOW()
 *        Booking: payment_status → 'paid', booking_status → 'confirmed'
 *   5. If user cancels later:
 *        Refund record created
 *        Payment: status → 'refunded'
 *        Booking: payment_status → 'refunded'
 *
 * PAYMENT REFERENCE:
 *   payment_reference is the unique identifier returned by the
 *   payment gateway after a successful transaction.
 *   Examples:
 *     Razorpay: "pay_OFj22lmMfLkH4d"
 *     PayU:     "8472938742"
 *     UPI:      "UPI/CR/123456789012"
 *   This is used for reconciliation, refund processing, and
 *   customer support queries ("my payment went through but booking didn't confirm").
 *
 * FUTURE SCALABILITY:
 *   - Add `currency` CHAR(3) for multi-currency support (e.g. 'INR', 'USD')
 *   - Add `tax_amount` DECIMAL for GST tracking
 *   - Add `convenience_fee` DECIMAL for gateway surcharges
 *   - Add `coupon_id` FK for discount/coupon tracking
 *   - Add `gateway_order_id` string for gateways that use pre-created orders (Razorpay)
 *   - Add `initiated_at` datetime for payment funnel timing analytics
 *   - Add `failure_reason` text to store declined/failed reasons
 *   - Add `ip_address` for fraud detection
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int         $user_id
 * @property float       $amount
 * @property int         $payment_method_id
 * @property string      $payment_status
 * @property string|null $payment_reference
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'payments';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'booking_id',         // FK → bookings.id — which booking this payment is for
        'user_id',            // FK → users.id — who is paying (denormalized from booking for fast queries)
        'amount',             // Total amount charged in platform currency (e.g. INR)
        'payment_method_id',  // FK → payment_methods.id — UPI, card, net banking, etc.
        'payment_status',     // 'pending'|'paid'|'failed'|'refunded'|'partially_refunded'
        'payment_reference',  // Gateway-issued unique transaction reference (e.g. "pay_OFj22lmM...")
        'paid_at',            // Timestamp when payment was confirmed as successful
    ];

    /**
     * Automatic type casts.
     *
     * IMPORTANT: amount is DECIMAL in the DB and cast to 'decimal:2' here.
     * This means PHP always gets a string like "120.00" — not a float.
     * Use (float) or bcmath functions for arithmetic to avoid precision loss.
     */
    protected $casts = [
        'amount'  => 'decimal:2',   // Financial value — always 2 decimal places
        'paid_at' => 'datetime',    // Carbon — enables $payment->paid_at->format(...)
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants — Payment Status
    |--------------------------------------------------------------------
    |
    | LIFECYCLE:
    |   pending → paid           (successful payment)
    |   pending → failed         (gateway rejected / user cancelled)
    |   paid    → refunded       (full cancellation refund)
    |   paid    → partially_refunded (partial overstay waiver, etc.)
    |
    |--------------------------------------------------------------------
    */

    /** Payment initiated, awaiting gateway confirmation */
    public const STATUS_PENDING            = 'pending';

    /** Payment successfully received and confirmed by gateway */
    public const STATUS_PAID               = 'paid';

    /** Gateway rejected, user cancelled, or session timed out */
    public const STATUS_FAILED             = 'failed';

    /** Full amount refunded (e.g. booking cancelled before use) */
    public const STATUS_REFUNDED           = 'refunded';

    /** Partial amount refunded (e.g. early checkout partial refund) */
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A Payment BELONGS TO one Booking.
     *
     * The booking this payment is settling.
     * One booking = one payment (one charge per reservation).
     *
     * Usage:
     *   $payment->booking->booking_number   → "BK-20250115-0042"
     *   $payment->booking->parking->name    → "Green Valley Parking"
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * A Payment BELONGS TO one User.
     *
     * The user who made this payment.
     * Denormalized from booking.user_id for faster payment queries
     * without needing to JOIN bookings → users every time.
     *
     * Example use case: "Show all payments by user X" — no join needed.
     *
     * Usage:
     *   $payment->user->name    → "Arpit Sharma"
     *   $payment->user->email   → "arpit@example.com"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A Payment BELONGS TO one PaymentMethod.
     *
     * The payment channel used: UPI, credit card, debit card,
     * net banking, wallet, etc.
     * Payment methods are managed in the PaymentMethod master table.
     *
     * Usage:
     *   $payment->paymentMethod->name   → "UPI"
     *   $payment->paymentMethod->type   → "upi"
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * A Payment HAS MANY PaymentTransactions.
     *
     * Each gateway API call (attempt) creates one transaction log.
     * Multiple transactions exist when:
     *   - First attempt failed → user retried
     *   - Network timeout → system auto-retried
     *
     * Usage:
     *   $payment->transactions                → all attempts
     *   $payment->transactions()->successful()->first() → the one that worked
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_id');
    }

    /**
     * A Payment HAS ONE Refund.
     *
     * If a booking is cancelled and the user is eligible for a refund,
     * one Refund record is created linked to this payment.
     *
     * NULL = no refund has been initiated for this payment.
     *
     * Usage:
     *   $payment->refund                → Refund model or null
     *   $payment->refund->refund_amount → how much was refunded
     */
    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'payment_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only successful (paid) payments.
     * Used in revenue reports and settlement calculations.
     *
     * Usage: Payment::paid()->sum('amount')
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::STATUS_PAID);
    }

    /**
     * Scope: only pending payments.
     * Used to find payments that are still awaiting gateway response.
     * A scheduled job can auto-expire stale pending payments.
     *
     * Usage: Payment::pending()->where('created_at', '<', now()->subMinutes(15))->get()
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    /**
     * Scope: only failed payments.
     * Used for retry logic and failure analytics.
     *
     * Usage: Payment::failed()->forUser($userId)->get()
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', self::STATUS_FAILED);
    }

    /**
     * Scope: payments for a specific user.
     * Used in "Payment History" screen in the Flutter app.
     *
     * Usage: Payment::forUser(auth()->id())->paid()->latest('paid_at')->get()
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: payments within a date range.
     * Used in financial reports and owner settlement calculations.
     *
     * Usage: Payment::paid()->inDateRange('2025-01-01', '2025-01-31')->sum('amount')
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $from  Start date (inclusive)
     * @param  string $to    End date (inclusive)
     */
    public function scopeInDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('paid_at', [
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
     * Check if this payment was successfully completed.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    /**
     * Check if this payment is still waiting for gateway confirmation.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    /**
     * Check if this payment failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->payment_status === self::STATUS_FAILED;
    }

    /**
     * Check if this payment has been refunded (fully or partially).
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return in_array($this->payment_status, [
            self::STATUS_REFUNDED,
            self::STATUS_PARTIALLY_REFUNDED,
        ]);
    }

    /**
     * Get the net amount retained after any refund.
     * Used in owner payout calculations.
     *
     * Formula: paid amount − refunded amount (if any)
     *
     * @return float
     */
    public function netAmount(): float
    {
        $refundedAmount = $this->refund
            ? (float) $this->refund->refund_amount
            : 0.0;

        return round((float) $this->amount - $refundedAmount, 2);
    }
}