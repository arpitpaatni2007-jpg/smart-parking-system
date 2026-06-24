<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ============================================================
 * Booking Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * The Booking is the core transaction record of the entire Smart
 * Parking System. Every time a customer successfully reserves a
 * parking slot, one row is created here. Everything else in the
 * system — payments, QR codes, check-ins, check-outs, earnings,
 * commissions, reviews — revolves around a Booking.
 *
 * Think of it as the "order" in an e-commerce system. Just as an
 * order ties together a customer, product, price, and delivery —
 * a Booking ties together a user, parking slot, vehicle, time
 * window, and payment.
 *
 * HOW IT WILL BE USED IN SMART PARKING SYSTEM:
 *
 *   USER APP:
 *     → User searches parking → selects slot → fills booking form
 *     → Booking is created with status "pending"
 *     → After payment → status changes to "confirmed"
 *     → QR code is generated (linked to this booking)
 *     → User shows QR at entry → "checked_in"
 *     → User shows QR at exit  → "checked_out" / "completed"
 *
 *   OWNER APP:
 *     → Owner dashboard shows today's bookings for their parking
 *     → Owner scans QR → system looks up booking by QR token
 *     → Check-in / Check-out records are created linking here
 *
 *   ADMIN PANEL:
 *     → Bookings list with filters (date, status, parking, user)
 *     → Booking detail shows full timeline of status changes
 *     → Revenue reports aggregate booking amounts here
 *
 *   PAYMENTS:
 *     → Payment record links to booking_id
 *     → Commission record links to booking_id
 *     → Both are created after booking payment is confirmed
 *
 * BOOKING LIFECYCLE (booking_status flow):
 *   pending → confirmed → checked_in → completed
 *                      ↓
 *                  cancelled
 *                      ↓
 *                  no_show  (user never arrived)
 *
 * PAYMENT STATUS FLOW (payment_status):
 *   unpaid → paid → refunded (if cancelled after payment)
 *         → failed (if payment attempt failed)
 *
 * FUTURE SCALABILITY:
 *   - Add `promo_code_id` FK for discount/coupon support.
 *   - Add `discount_amount` for tracking promotions per booking.
 *   - Add `booking_type` (instant | advance | recurring) for
 *     Phase 2 monthly subscription parking passes.
 *   - Add `cancelled_by` and `cancellation_reason` columns
 *     for cleaner cancellation tracking.
 *   - Add `reviewed` boolean to know if user left a review,
 *     avoiding duplicate review prompts.
 *   - Partitioning the `bookings` table by month will be needed
 *     once data grows large (millions of bookings per month).
 *
 * @property int         $id
 * @property string      $booking_number
 * @property int         $user_id
 * @property int         $parking_id
 * @property int         $parking_slot_id
 * @property int         $vehicle_id
 * @property \Carbon\Carbon $booking_start_time
 * @property \Carbon\Carbon $booking_end_time
 * @property \Carbon\Carbon|null $actual_checkin_time
 * @property \Carbon\Carbon|null $actual_checkout_time
 * @property float       $duration_hours
 * @property float       $amount
 * @property string      $booking_status
 * @property string      $payment_status
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'bookings';

    /**
     * Fields allowed for mass assignment.
     *
     * SECURITY NOTE: We intentionally exclude `booking_number`
     * from fillable. It is generated automatically in the boot()
     * method below — never set by user input.
     */
    protected $fillable = [
        'user_id',
        'parking_id',
        'parking_slot_id',
        'vehicle_id',
        'booking_start_time',
        'booking_end_time',
        'actual_checkin_time',
        'actual_checkout_time',
        'duration_hours',
        'amount',
        'booking_status',
        'payment_status',
        'notes',
    ];

    /**
     * Cast database columns to proper PHP types.
     *
     * WHY DECIMAL CAST FOR AMOUNTS?
     * Using 'decimal:2' returns a string like "160.00" rather than
     * a raw float. This is important for financial values because
     * PHP floats have precision issues:
     *   (0.1 + 0.2) === 0.3  // false in PHP with raw floats
     * With decimal strings, we control precision explicitly.
     *
     * WHY DATETIME CAST FOR TIMES?
     * Casting to 'datetime' gives us Carbon instances, so we can
     * do: $booking->booking_start_time->format('d M Y, h:i A')
     * or: $booking->booking_start_time->diffInHours(now())
     */
    protected $casts = [
        'user_id'              => 'integer',
        'parking_id'           => 'integer',
        'parking_slot_id'      => 'integer',
        'vehicle_id'           => 'integer',
        'booking_start_time'   => 'datetime',
        'booking_end_time'     => 'datetime',
        'actual_checkin_time'  => 'datetime',
        'actual_checkout_time' => 'datetime',
        'duration_hours'       => 'decimal:2',
        'amount'               => 'decimal:2',
        'booking_status'       => 'string',
        'payment_status'       => 'string',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Booking Status Constants
    |--------------------------------------------------------------------
    |
    | Every possible value for the `booking_status` column.
    | Using constants prevents typos like "Confirmed" vs "confirmed"
    | and makes status checks readable:
    |
    |   if ($booking->booking_status === Booking::STATUS_CONFIRMED)
    |
    | instead of:
    |
    |   if ($booking->booking_status === 'confirmed')  // fragile
    */

    /** Booking created, waiting for payment */
    public const STATUS_PENDING    = 'pending';

    /** Payment received, booking is active */
    public const STATUS_CONFIRMED  = 'confirmed';

    /** Customer scanned QR and entered the parking */
    public const STATUS_CHECKED_IN = 'checked_in';

    /** Customer has exited, booking fully complete */
    public const STATUS_COMPLETED  = 'completed';

    /** Booking was cancelled before check-in */
    public const STATUS_CANCELLED  = 'cancelled';

    /** Customer never showed up after confirmed booking */
    public const STATUS_NO_SHOW    = 'no_show';

    /*
    |--------------------------------------------------------------------
    | Payment Status Constants
    |--------------------------------------------------------------------
    */

    /** Payment not yet made */
    public const PAYMENT_UNPAID   = 'unpaid';

    /** Payment successfully received */
    public const PAYMENT_PAID     = 'paid';

    /** Payment attempt was made but failed */
    public const PAYMENT_FAILED   = 'failed';

    /** Amount refunded after cancellation */
    public const PAYMENT_REFUNDED = 'refunded';

    /*
    |--------------------------------------------------------------------
    | Model Boot — Auto-generate Booking Number
    |--------------------------------------------------------------------
    |
    | The `boot()` method runs automatically when the model is loaded.
    | We use it to hook into the "creating" event — the moment just
    | before a new Booking row is saved to the database.
    |
    | WHY AUTO-GENERATE HERE instead of in the controller/service?
    |   - It's impossible to forget — the number is ALWAYS generated.
    |   - It's consistent — same format every time, everywhere.
    |   - The controller/service stays clean.
    */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            // Only generate if not already set (safety check).
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber();
            }
        });
    }

    /**
     * Generate a unique booking number.
     *
     * FORMAT: BK + YYYYMMDD + 6-digit random number
     * EXAMPLE: BK2026062312345678
     *
     * The date prefix makes booking numbers human-readable and
     * helps support teams instantly know when a booking was made
     * just from the reference number.
     *
     * The random suffix makes collisions extremely unlikely, but
     * we also have a UNIQUE constraint on the column in the DB
     * as a final safety net.
     *
     * @return string
     */
    protected static function generateBookingNumber(): string
    {
        do {
            // Format: BK + today's date (YYYYMMDD) + random 6 digits
            $number = 'BK' . date('Ymd') . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('booking_number', $number)->exists());
        // Loop re-runs only on the extremely rare chance of a collision.

        return $number;
    }

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * The Customer who made this booking.
     *
     * Usage:
     *   $booking->user->name       // "Rahul Sharma"
     *   $booking->user->mobile     // "9876543210"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The Parking Location this booking is for.
     *
     * Usage:
     *   $booking->parking->name     // "Connaught Place Parking"
     *   $booking->parking->address  // "New Delhi, 110001"
     */
    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class, 'parking_id');
    }

    /**
     * The specific Parking Slot that was reserved.
     *
     * Usage:
     *   $booking->parkingSlot->slot_number  // "A1"
     *   $booking->parkingSlot->floor        // "Ground"
     */
    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

    /**
     * The Vehicle that will use this booking.
     *
     * Usage:
     *   $booking->vehicle->vehicle_number  // "DL 01 AB 1234"
     *   $booking->vehicle->vehicle_type    // "car"
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Full status change history for this booking.
     * Every time booking_status changes, a new row is added here.
     *
     * Usage:
     *   $booking->statusHistory        // collection of all changes
     *   $booking->statusHistory()->latest()->first()  // most recent
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id');
    }

    /**
     * The Check-In record for this booking.
     * Created when the owner/manager scans the entry QR code.
     *
     * Usage:
     *   $booking->checkIn              // null if not checked in yet
     *   $booking->checkIn->checkin_time
     */
    public function checkIn(): HasOne
    {
        return $this->hasOne(CheckIn::class, 'booking_id');
    }

    /**
     * The Check-Out record for this booking.
     * Created when the owner/manager scans the exit QR code.
     *
     * Usage:
     *   $booking->checkOut                  // null if not checked out
     *   $booking->checkOut->extra_amount    // overtime charge if any
     */
    public function checkOut(): HasOne
    {
        return $this->hasOne(CheckOut::class, 'booking_id');
    }

    /**
     * The QR Code generated for this booking.
     * Created after payment is confirmed.
     *
     * Usage:
     *   $booking->qrBooking->qr_code    // the token string
     *   $booking->qrBooking->status     // "active" | "used" | "expired"
     */
    public function qrBooking(): HasOne
    {
        return $this->hasOne(QRBooking::class, 'booking_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    |
    | Query scopes make common filters reusable and readable.
    | Instead of writing: ->where('booking_status', 'confirmed')
    | everywhere, we write:  ->confirmed()
    */

    /** Only return confirmed bookings. */
    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', self::STATUS_CONFIRMED);
    }

    /** Only return pending (awaiting payment) bookings. */
    public function scopePending($query)
    {
        return $query->where('booking_status', self::STATUS_PENDING);
    }

    /** Only return completed bookings. */
    public function scopeCompleted($query)
    {
        return $query->where('booking_status', self::STATUS_COMPLETED);
    }

    /** Only return currently active bookings (confirmed or checked in). */
    public function scopeActive($query)
    {
        return $query->whereIn('booking_status', [
            self::STATUS_CONFIRMED,
            self::STATUS_CHECKED_IN,
        ]);
    }

    /** Only return cancelled bookings. */
    public function scopeCancelled($query)
    {
        return $query->where('booking_status', self::STATUS_CANCELLED);
    }

    /**
     * Bookings for a specific user.
     * Used in the "My Bookings" screen of the User App.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Bookings for a specific parking location.
     * Used in the Owner App dashboard.
     */
    public function scopeForParking($query, int $parkingId)
    {
        return $query->where('parking_id', $parkingId);
    }

    /**
     * Bookings for a specific date (by start time date).
     * Used for "Today's Bookings" in Owner dashboard.
     *
     * Usage:
     *   Booking::forParking($id)->forDate(today())->get()
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('booking_start_time', $date);
    }

    /**
     * Paid bookings only.
     * Used in revenue reports to exclude unpaid/cancelled.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    /*
    |--------------------------------------------------------------------
    | Helper / Accessor Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if the booking is currently in "pending" status.
     * Pending = created but payment not yet received.
     */
    public function isPending(): bool
    {
        return $this->booking_status === self::STATUS_PENDING;
    }

    /**
     * Check if the booking has been paid and confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->booking_status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the customer has already checked in.
     */
    public function isCheckedIn(): bool
    {
        return $this->booking_status === self::STATUS_CHECKED_IN;
    }

    /**
     * Check if the booking has been fully completed.
     */
    public function isCompleted(): bool
    {
        return $this->booking_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this booking was cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->booking_status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment has been received for this booking.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Calculate how many hours were pre-booked.
     * Returns the difference between booking_end_time and
     * booking_start_time as a decimal number of hours.
     *
     * Usage:
     *   $booking->getBookedHours()  // 2.5 (for 2 hours 30 mins)
     */
    public function getBookedHours(): float
    {
        return round(
            $this->booking_start_time->diffInMinutes($this->booking_end_time) / 60,
            2
        );
    }

    /**
     * Calculate actual hours spent (from check-in to check-out).
     * Returns null if the booking hasn't been checked out yet.
     *
     * This is used at checkout to calculate extra_amount if the
     * customer stayed longer than booked.
     */
    public function getActualHours(): ?float
    {
        if (! $this->actual_checkin_time || ! $this->actual_checkout_time) {
            return null;
        }

        return round(
            $this->actual_checkin_time->diffInMinutes($this->actual_checkout_time) / 60,
            2
        );
    }
}