<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * ============================================================
 * QRBooking Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * After a customer successfully pays for a booking, they need a
 * way to prove their reservation at the parking location without
 * the manager looking them up manually. That proof is a QR Code.
 *
 * This model stores the QR code token for each confirmed booking.
 * When the customer arrives, the manager scans this QR, the system
 * validates the token against this table, and allows entry.
 *
 * WHY A SEPARATE TABLE (not just a column on bookings)?
 *
 *   1. SECURITY ISOLATION:
 *      QR tokens are sensitive — they grant physical access to a
 *      parking spot. Keeping them in a separate table means:
 *        - We can invalidate/regenerate QRs without touching bookings
 *        - Access control can be applied more granularly
 *        - The QR's status (active/used/expired) is explicit
 *
 *   2. INDEPENDENT LIFECYCLE:
 *      A QR code has its own lifecycle (generated → scanned → expired)
 *      that's separate from the booking's lifecycle. A booking can be
 *      "completed" while the QR record shows it was "used" at
 *      check-in — these are different states of different entities.
 *
 *   3. REGENERATION SUPPORT:
 *      If a customer loses their QR or it expires, we can generate
 *      a new token and update this record without touching the booking.
 *      Or we could keep old tokens for audit purposes.
 *
 *   4. FUTURE MULTI-QR:
 *      Some parking systems use two separate QRs — one for entry,
 *      one for exit. Keeping QR data in its own table makes this
 *      extension trivial (add a `qr_type` column later).
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   GENERATION (after payment confirmed):
 *     BookingService calls QRBooking::generateForBooking($booking)
 *     → Creates a secure random token
 *     → Stores it with qr_expiry = booking_end_time + 2 hours (buffer)
 *     → Status = "active"
 *
 *   USER APP — QR CODE SCREEN:
 *     User opens their booking → taps "View QR Code".
 *     App fetches the QR token from this table (via booking API).
 *     App renders it as a scannable QR image using a Flutter QR package.
 *
 *   OWNER APP — SCAN QR (CHECK-IN):
 *     Manager scans QR → app sends `qr_code` token to API.
 *     API calls: QRBooking::validateToken($token)
 *       → Checks: exists? active? not expired?
 *       → Returns the associated booking
 *     If valid → creates CheckIn record → updates booking status.
 *
 *   OWNER APP — SCAN QR (CHECK-OUT):
 *     Same scan flow at exit gate.
 *     After successful checkout → QR status updated to "used".
 *
 *   EXPIRY:
 *     A Laravel scheduled command runs periodically to find QR records
 *     where qr_expiry < now() AND status = "active" → sets to "expired".
 *
 * SECURITY DESIGN:
 *   The `qr_code` token must be:
 *     - Long enough to be unguessable (128+ random characters)
 *     - Generated using cryptographically secure randomness
 *     - Stored as plain text (no need to hash — it's not a password,
 *       it just needs to be found quickly by exact match)
 *     - Set to expire (prevents old QRs from being reused)
 *
 * FUTURE SCALABILITY:
 *   - Add `qr_type` column: "entry" | "exit" for two-QR systems.
 *   - Add `scan_count` integer to detect if a QR is scanned more
 *     than expected (potential security red flag).
 *   - Add `last_scanned_at` datetime for audit purposes.
 *   - Add `regenerated_from` FK for QR regeneration history.
 *   - Store the rendered QR image path in S3 (`qr_image_path`)
 *     so we can send it directly via notification without
 *     the app having to render it.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property string      $qr_code
 * @property \Carbon\Carbon $qr_expiry
 * @property string      $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class QRBooking extends Model
{
    use HasFactory;

    /**
     * The database table used by this model.
     */
    protected $table = 'qr_bookings';

    /**
     * Fields allowed for mass assignment.
     *
     * SECURITY NOTE: `qr_code` is in fillable because it's always
     * set by our own generateForBooking() method — never from
     * raw user input. The API never accepts qr_code as input;
     * it only validates tokens submitted for scanning.
     */
    protected $fillable = [
        'booking_id',
        'qr_code',
        'qr_expiry',
        'status',
    ];

    /**
     * Type-cast database columns.
     */
    protected $casts = [
        'booking_id' => 'integer',
        'qr_expiry'  => 'datetime',
        'status'     => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------
    */

    /** QR is valid and ready to be scanned */
    public const STATUS_ACTIVE  = 'active';

    /** QR has been scanned and booking completed */
    public const STATUS_USED    = 'used';

    /** QR expiry time has passed without being used */
    public const STATUS_EXPIRED = 'expired';

    /** QR was manually revoked (e.g. booking cancelled) */
    public const STATUS_REVOKED = 'revoked';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A QRBooking BELONGS TO one Booking.
     *
     * Usage:
     *   $qr->booking->user->name      // Who owns this QR
     *   $qr->booking->parking->name   // Which parking it's for
     *   $qr->booking->booking_status  // Current booking state
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

    /** Only active (scannable) QR codes. */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Only expired QR codes. Used by cleanup scheduler. */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /*
    |--------------------------------------------------------------------
    | Static Helpers
    |--------------------------------------------------------------------
    */

    /**
     * Generate and save a new QR code for a confirmed booking.
     *
     * This is the ONLY place QR tokens are created. Calling this
     * method ensures every QR is generated consistently with the
     * right token length and expiry time.
     *
     * Called by: BookingService after payment is confirmed.
     *
     * Usage:
     *   $qr = QRBooking::generateForBooking($booking);
     *   // $qr->qr_code = "a3f9e2d1c7b8..." (128-char random string)
     *
     * Expiry logic:
     *   qr_expiry = booking_end_time + 2 hours
     *   The 2-hour buffer accounts for customers who are running
     *   late or whose booking_end_time passed but they're still
     *   legitimately parked.
     *
     * @param  Booking  $booking  A confirmed booking (payment received).
     * @return static             The newly created QRBooking record.
     */
    public static function generateForBooking(Booking $booking): static
    {
        // 128-character cryptographically random token.
        // This is long enough that brute-force guessing is computationally
        // impossible even with millions of attempts per second.
        $token = Str::random(128);

        return static::create([
            'booking_id' => $booking->id,
            'qr_code'    => $token,
            // Give a 2-hour grace period beyond booking_end_time
            // so the QR doesn't expire while the customer is still parked.
            'qr_expiry'  => $booking->booking_end_time->addHours(2),
            'status'     => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Validate a QR token submitted during check-in or check-out scan.
     *
     * Returns the QRBooking record if valid, or null if:
     *   - Token not found
     *   - Status is not "active"
     *   - Token has expired
     *
     * Called by: QRScanService (or directly in CheckIn/CheckOut service)
     *
     * Usage:
     *   $qr = QRBooking::validateToken($request->qr_code);
     *   if (! $qr) {
     *       return response()->json(['error' => 'Invalid or expired QR code'], 422);
     *   }
     *   $booking = $qr->booking;
     *
     * @param  string      $token  The raw QR token scanned from the user's screen.
     * @return static|null         Valid QRBooking or null if invalid.
     */
    public static function validateToken(string $token): ?static
    {
        return static::where('qr_code', $token)
                     ->where('status', self::STATUS_ACTIVE)
                     ->where('qr_expiry', '>=', now())
                     ->with('booking') // eager load for immediate use
                     ->first();
    }

    /*
    |--------------------------------------------------------------------
    | Instance Helpers
    |--------------------------------------------------------------------
    */

    /**
     * Check if this QR code is still valid and scannable.
     *
     * A QR is valid when:
     *   - Status is "active"
     *   - It hasn't expired yet
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->qr_expiry->isFuture();
    }

    /**
     * Check if this QR has already expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || $this->qr_expiry->isPast();
    }

    /**
     * Mark this QR as used (called after successful check-out).
     * Once used, the QR cannot be scanned again.
     */
    public function markAsUsed(): bool
    {
        return $this->update(['status' => self::STATUS_USED]);
    }

    /**
     * Revoke this QR code (called when a booking is cancelled).
     * Ensures the customer can't use a QR for a cancelled booking.
     */
    public function revoke(): bool
    {
        return $this->update(['status' => self::STATUS_REVOKED]);
    }
}
