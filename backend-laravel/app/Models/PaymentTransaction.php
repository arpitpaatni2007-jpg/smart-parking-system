<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * PaymentTransaction Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Every time your application calls a payment gateway API,
 * that API call is logged here as a "transaction".
 *
 * THE KEY DISTINCTION:
 *   Payment = the business-level event ("user paid ₹120 for booking")
 *   PaymentTransaction = the technical-level gateway call ("we sent a
 *                         request to Razorpay and got back this response")
 *
 * WHY SEPARATE FROM PAYMENT?
 * A user might attempt to pay multiple times before succeeding:
 *   Attempt 1: Card declined   → PaymentTransaction #1 (failed)
 *   Attempt 2: OTP timeout     → PaymentTransaction #2 (failed)
 *   Attempt 3: Payment success → PaymentTransaction #3 (success)
 *
 * All 3 attempts are under the same Payment record (same booking, same amount).
 * This table logs each attempt separately for:
 *   - DEBUGGING: "Why did payment fail 3 times?"
 *   - SUPPORT: "The gateway shows success but booking isn't confirmed"
 *   - RECONCILIATION: Match your DB records with gateway settlement report
 *   - FRAUD DETECTION: Too many failed attempts = suspicious pattern
 *
 * WHAT IS gateway_response?
 *   The full raw JSON body returned by the payment gateway.
 *   Examples (stored as JSON string):
 *
 *   Razorpay success response:
 *   {
 *     "id": "pay_OFj22lmMfLkH4d",
 *     "entity": "payment",
 *     "amount": 12000,   ← in paise (120.00 INR)
 *     "currency": "INR",
 *     "status": "captured",
 *     "method": "upi",
 *     "email": "arpit@example.com"
 *   }
 *
 *   Razorpay failure response:
 *   {
 *     "error": { "code": "BAD_REQUEST_ERROR",
 *                "description": "Your payment has been declined..." }
 *   }
 *
 *   Storing the full raw response means you always have the original
 *   data for debugging — even if the gateway changes their API.
 *
 * IMMUTABILITY:
 *   Transaction records are NEVER updated after creation.
 *   They are append-only logs — like accounting ledger entries.
 *   No soft deletes (they're immutable audit records).
 *
 * FUTURE SCALABILITY:
 *   - Add `request_payload` JSON to store what WE sent to the gateway
 *     (for full request/response debugging)
 *   - Add `response_time_ms` integer for gateway performance monitoring
 *   - Add `ip_address` for fraud analysis
 *   - Add `device_fingerprint` for multi-device fraud detection
 *   - Add `error_code` string (extracted from gateway_response) for
 *     easier failure analytics without parsing JSON
 *   - Add `currency` CHAR(3) for multi-currency transaction logging
 *   - Move to a time-series DB or data warehouse for high-volume analytics
 *
 * @property int         $id
 * @property int         $payment_id
 * @property string      $transaction_id
 * @property string      $gateway_name
 * @property array|null  $gateway_response
 * @property string      $transaction_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'payment_transactions';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'payment_id',          // FK → payments.id
        'transaction_id',      // Gateway's own unique ID for this attempt
        'gateway_name',        // 'razorpay' | 'payu' | 'paytm' | 'stripe' | etc.
        'gateway_response',    // Full raw JSON response body from the gateway
        'transaction_status',  // 'initiated' | 'success' | 'failed' | 'pending'
    ];

    /**
     * Automatic type casts.
     *
     * gateway_response is cast to 'array':
     *   - When reading:  DB string → PHP array (JSON decoded automatically)
     *   - When writing:  PHP array → JSON string (encoded automatically)
     *
     * This means you can do:
     *   $txn->gateway_response['status']  → "captured"
     * Instead of:
     *   json_decode($txn->gateway_response, true)['status']
     */
    protected $casts = [
        'gateway_response' => 'array',    // Auto JSON encode/decode
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants — Transaction Status
    |--------------------------------------------------------------------
    |
    | These represent the state of the gateway API call itself,
    | not the business-level payment status.
    |
    | 'initiated' → We sent the request; awaiting gateway response
    | 'success'   → Gateway confirmed the money was received
    | 'failed'    → Gateway explicitly rejected (declined, insufficient funds)
    | 'pending'   → Gateway said "processing" — we're waiting for webhook
    |
    |--------------------------------------------------------------------
    */

    /** API request sent to gateway; awaiting response */
    public const STATUS_INITIATED = 'initiated';

    /** Gateway confirmed successful payment */
    public const STATUS_SUCCESS   = 'success';

    /** Gateway confirmed failure (declined, insufficient funds, etc.) */
    public const STATUS_FAILED    = 'failed';

    /**
     * Gateway is still processing (async confirmation pending).
     * Common with UPI and net banking where bank confirmation is async.
     * A webhook will arrive later to update this to 'success' or 'failed'.
     */
    public const STATUS_PENDING   = 'pending';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A PaymentTransaction BELONGS TO one Payment.
     *
     * Multiple transactions can exist under one Payment —
     * each represents one gateway API call attempt.
     *
     * Usage:
     *   $transaction->payment->amount            → ₹120.00
     *   $transaction->payment->booking->booking_number → "BK-20250115-0042"
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
     * Scope: only successful transactions.
     * Used when looking for the transaction that actually worked.
     *
     * Usage: $payment->transactions()->successful()->first()
     */
    public function scopeSuccessful($query)
    {
        return $query->where('transaction_status', self::STATUS_SUCCESS);
    }

    /**
     * Scope: only failed transactions.
     * Used for failure analytics and retry logic.
     *
     * Usage: PaymentTransaction::failed()->where('created_at', '>', now()->subHour())->count()
     */
    public function scopeFailed($query)
    {
        return $query->where('transaction_status', self::STATUS_FAILED);
    }

    /**
     * Scope: filter by gateway name.
     * Used for gateway-specific reporting and reconciliation.
     *
     * Usage: PaymentTransaction::viaGateway('razorpay')->successful()->get()
     */
    public function scopeViaGateway($query, string $gateway)
    {
        return $query->where('gateway_name', $gateway);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this transaction was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->transaction_status === self::STATUS_SUCCESS;
    }

    /**
     * Check if this transaction failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->transaction_status === self::STATUS_FAILED;
    }

    /**
     * Safely extract a value from the gateway_response JSON.
     * Prevents errors when gateway_response is null or the key doesn't exist.
     *
     * Usage:
     *   $txn->getResponseValue('status')       → "captured"
     *   $txn->getResponseValue('error.code')   → "BAD_REQUEST_ERROR"
     *   $txn->getResponseValue('missing_key')  → null
     *
     * Supports dot-notation for nested keys using Laravel's data_get().
     *
     * @param  string $key      Dot-notation key path
     * @param  mixed  $default  Value if key not found
     * @return mixed
     */
    public function getResponseValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->gateway_response, $key, $default);
    }
}