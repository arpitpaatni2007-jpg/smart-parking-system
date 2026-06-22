<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================
 * OwnerBankDetail Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Parking owners earn revenue from bookings made at their locations.
 * To process payouts (weekly/monthly transfers), the platform needs
 * the owner's bank account details on file.
 *
 * This model stores the KYC (Know Your Customer) banking information
 * for each owner so the finance module can initiate bank transfers.
 *
 * ONE-TO-ONE RELATIONSHIP:
 *   Each owner (User) has at most ONE bank detail record.
 *   This is enforced by a UNIQUE constraint on `owner_id` in the DB.
 *
 * SECURITY CONSIDERATIONS:
 *   Bank account details are sensitive PII (Personally Identifiable
 *   Information). In production you should:
 *     1. Encrypt `account_number` at rest using Laravel's encryption:
 *        Store: encrypt($accountNumber)
 *        Read:  decrypt($this->account_number)
 *     2. Mask account numbers in API responses (show only last 4 digits)
 *     3. Log all access to this table for audit purposes
 *     4. Never expose IFSC + account number together in a single API response
 *
 * FUTURE SCALABILITY:
 *   - Add `upi_id` for UPI-based payouts (common in India)
 *   - Add `verified_at` timestamp for admin verification workflow
 *   - Add `verified_by` (admin user id) for audit trail
 *   - Add `pan_number` for tax compliance / TDS deduction
 *   - Add `gstin` for GST-registered parking owners
 *
 * @property int         $id
 * @property int         $owner_id
 * @property string      $account_holder_name
 * @property string      $bank_name
 * @property string      $account_number
 * @property string      $ifsc_code
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class OwnerBankDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'owner_bank_details';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'owner_id',              // FK → users.id (must have 'owner' role)
        'account_holder_name',   // Full legal name as per bank records
        'bank_name',             // e.g. "State Bank of India", "HDFC Bank"
        'account_number',        // Bank account number (sensitive — encrypt in production)
        'ifsc_code',             // 11-character Indian Financial System Code
        'status',                // 'active' | 'inactive' | 'pending_verification'
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Fields that should NEVER appear in JSON/array output.
     *
     * SECURITY: account_number is hidden from all API responses by default.
     * Access it explicitly: $bankDetail->account_number (only in trusted contexts).
     *
     * Add 'ifsc_code' here too if you want extra caution.
     */
    protected $hidden = [
        'account_number',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------
    */

    public const STATUS_ACTIVE               = 'active';
    public const STATUS_INACTIVE             = 'inactive';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * OwnerBankDetail BELONGS TO one Owner (User).
     *
     * Usage: $bankDetail->owner->name
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only verified/active bank details.
     * Used by the payout service to fetch valid payout destinations.
     *
     * Usage: OwnerBankDetail::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: only records pending admin verification.
     * Used in admin panel to show newly submitted bank details.
     *
     * Usage: OwnerBankDetail::pendingVerification()->get()
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', self::STATUS_PENDING_VERIFICATION);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods / Accessors
    |--------------------------------------------------------------------
    */

    /**
     * Get a masked version of the account number for safe display.
     * Shows only the last 4 digits; rest replaced with asterisks.
     *
     * Example: "12345678901" → "***********8901" — no, cleaned:
     *          "12345678901" → "*******8901"
     *
     * Usage: $bankDetail->masked_account_number
     *        Safe to include in API responses and UI display.
     *
     * @return string
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $account = $this->account_number;
        $length  = strlen($account);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($account, -4);
    }

    /**
     * Check if IFSC code is in valid format.
     * Indian IFSC codes are always 11 characters:
     *   First 4: Bank code (alpha)   e.g. "SBIN"
     *   5th:     Always '0' (zero)
     *   Last 6:  Branch code         e.g. "001234"
     *
     * @return bool
     */
    public function isValidIfsc(): bool
    {
        return (bool) preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', strtoupper($this->ifsc_code));
    }

    /**
     * Check if this bank detail is verified and ready for payouts.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}