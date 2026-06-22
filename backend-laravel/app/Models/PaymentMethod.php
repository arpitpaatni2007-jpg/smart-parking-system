<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================
 * PaymentMethod Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * When a customer pays for parking in the User App, they see
 * a list of payment options: UPI, Credit/Debit Card, Net Banking,
 * Wallet. These are "Payment Methods."
 *
 * Rather than hardcoding these options in the Flutter app or
 * the API response, we store them in this table. The Admin Panel
 * can then enable/disable payment methods without requiring a
 * new app release or code deployment. This is a common and
 * important real-world requirement.
 *
 * Example scenario:
 *   The business decides to temporarily disable "Net Banking"
 *   due to high failure rates. An admin flips the status to
 *   "inactive" in the Admin Panel → the payment screen in the
 *   app automatically stops showing it. No code change needed.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   USER APP — PAYMENT SCREEN:
 *     The Payment screen shows a list of payment methods with
 *     icons. This list is fetched from the API which queries
 *     this table (active methods only). The user selects one,
 *     and that method's `name` (e.g. "upi") is passed to the
 *     Razorpay integration.
 *
 *   PAYMENTS TABLE:
 *     The `payments` table will store a `payment_method` string
 *     or a `payment_method_id` FK referencing this table, so
 *     every payment record knows which method was used.
 *
 *   ADMIN PANEL — SETTINGS:
 *     The "Payment Methods" section in Admin Settings (visible
 *     in the screenshots) reads from and writes to this table.
 *     Admin can toggle, rename, or reorder methods.
 *
 *   REPORTS:
 *     Payment method breakdown reports ("60% of users pay via UPI")
 *     become possible because every payment is linked to a method.
 *
 * FUTURE SCALABILITY:
 *   - Adding a new payment method (e.g. "BNPL", "Crypto") is a
 *     new row — no code or schema changes needed.
 *   - A `sort_order` column can be added to control the display
 *     order of methods in the app without redeploying.
 *   - If we integrate multiple payment gateways in future
 *     (e.g. PayU in addition to Razorpay), a `gateway` column
 *     can be added here to route payments correctly.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $icon
 * @property string|null $description
 * @property string      $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'payment_methods';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'name',
        'icon',
        'description',
        'status',
    ];

    /**
     * Type-cast database values to proper PHP types.
     */
    protected $casts = [
        'status'     => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------
    */

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only return active payment methods.
     *
     * This is the most commonly used query — when the payment
     * screen in the app loads, it calls the API which runs:
     *   PaymentMethod::active()->orderBy('name')->get()
     *
     * Only active methods are shown to users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this payment method is currently active.
     * Useful when validating a payment request — make sure the
     * method the user selected hasn't been disabled since they
     * loaded the payment screen.
     *
     * Usage in future Payment service:
     *   $method = PaymentMethod::find($id);
     *   if (! $method->isActive()) {
     *       return response()->json(['error' => 'Payment method unavailable'], 422);
     *   }
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}