<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ============================================================
 * VehicleDocument Model
 * ============================================================
 *
 * WHY THIS MODEL EXISTS:
 * Vehicles in India require several legal documents to operate.
 * Some parking facilities — especially government complexes,
 * corporate campuses, and gated communities — require proof of
 * valid documents before granting parking access.
 *
 * This model stores uploaded copies of vehicle documents:
 *
 *   RC (Registration Certificate):
 *     Proof of ownership. Shows vehicle number, owner name,
 *     engine/chassis number. Issued by RTO. No expiry in most states.
 *
 *   Insurance:
 *     Mandatory by law (Motor Vehicles Act). Must be valid to drive.
 *     Expires annually. Third-party insurance is minimum requirement.
 *
 *   PUC (Pollution Under Control Certificate):
 *     Emission compliance certificate. Expires every 6 months.
 *     Required at traffic checkpoints and some parking facilities.
 *
 *   FITNESS (for commercial vehicles):
 *     Roadworthiness certificate for transport vehicles.
 *     Required for trucks, buses, taxi cabs.
 *
 * HOW IT WORKS:
 *   1. User uploads a photo/PDF of their document via the Flutter app
 *   2. File is stored in storage/app/public/vehicle-documents/
 *   3. document_path stores the relative path
 *   4. Admin/system verifies and marks status as 'active'
 *   5. A scheduled job watches expiry_date and auto-marks expired documents
 *
 * DOCUMENT STATUS MEANINGS:
 *   pending  → Uploaded by user, awaiting admin verification
 *   active   → Verified and valid (expiry_date is in the future)
 *   expired  → expiry_date has passed (or admin manually flagged)
 *   rejected → Admin rejected (blurry image, wrong document, mismatch)
 *
 * ONE VEHICLE — MULTIPLE DOCUMENTS:
 *   A single vehicle has multiple document records:
 *     vehicle_id=1, type='rc'        → RC Book uploaded
 *     vehicle_id=1, type='insurance' → Insurance uploaded
 *     vehicle_id=1, type='puc'       → PUC uploaded
 *
 *   If a document (e.g. insurance) is renewed, a new record is created
 *   with the new expiry_date. The old record becomes 'expired'.
 *   This gives you a full document renewal history.
 *
 * FUTURE SCALABILITY:
 *   - Add `verified_by` FK → users.id (admin who verified)
 *   - Add `verified_at` timestamp for audit
 *   - Add `rejection_reason` text for feedback to user on rejection
 *   - Add `document_number` string (e.g. insurance policy number, PUC cert number)
 *   - Add `issuing_authority` string (e.g. "HDFC Ergo", "Delhi RTO")
 *   - Add `reminder_sent_at` datetime for expiry reminder notification tracking
 *   - Add `is_primary` boolean if multiple docs of same type are allowed
 *
 * @property int         $id
 * @property int         $vehicle_id
 * @property string      $document_type
 * @property string      $document_path
 * @property string|null $expiry_date
 * @property string      $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class VehicleDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by this model.
     */
    protected $table = 'vehicle_documents';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'vehicle_id',     // FK → vehicles.id
        'document_type',  // 'rc' | 'insurance' | 'puc' | 'fitness' | 'permit'
        'document_path',  // Relative file path e.g. "vehicle-documents/rc_abc123.jpg"
        'expiry_date',    // Date the document expires (null for RC which rarely expires)
        'status',         // 'pending' | 'active' | 'expired' | 'rejected'
    ];

    /**
     * Automatic type casts.
     *
     * 'expiry_date' is cast to 'date' (not 'datetime') because it's a
     * calendar date only — we don't track the exact time of expiry.
     * This gives you: $document->expiry_date->format('d M Y')
     * and: $document->expiry_date->isPast()  → true if expired
     */
    protected $casts = [
        'expiry_date' => 'date',      // Carbon date (no time component)
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Constants — Document Types
    |--------------------------------------------------------------------
    */

    /** Registration Certificate — proof of vehicle ownership */
    public const TYPE_RC        = 'rc';

    /** Insurance Certificate — mandatory by Motor Vehicles Act */
    public const TYPE_INSURANCE = 'insurance';

    /** Pollution Under Control — emission compliance */
    public const TYPE_PUC       = 'puc';

    /**
     * Fitness Certificate — commercial vehicle roadworthiness.
     * Required for trucks, buses, taxi/cab vehicles.
     */
    public const TYPE_FITNESS   = 'fitness';

    /**
     * Transport Permit — required for goods/passenger transport vehicles.
     * e.g. All-India permit for long-haul trucks.
     */
    public const TYPE_PERMIT    = 'permit';

    /*
    |--------------------------------------------------------------------
    | Constants — Document Status
    |--------------------------------------------------------------------
    */

    /** Uploaded by user, awaiting admin review */
    public const STATUS_PENDING  = 'pending';

    /** Verified by admin, currently valid */
    public const STATUS_ACTIVE   = 'active';

    /** Expiry date has passed (auto-flagged by scheduled job) */
    public const STATUS_EXPIRED  = 'expired';

    /** Admin rejected — wrong doc, blurry, tampered, mismatch */
    public const STATUS_REJECTED = 'rejected';

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    /**
     * A VehicleDocument BELONGS TO one Vehicle.
     *
     * Usage:
     *   $document->vehicle->vehicle_number   → "HR26DQ8849"
     *   $document->vehicle->user->name       → "Arpit Sharma"
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    /**
     * Scope: only active (verified and valid) documents.
     * Used when checking document compliance before a booking.
     *
     * Usage: $vehicle->documents()->active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: only pending documents (awaiting admin review).
     * Used in admin panel to show the verification queue.
     *
     * Usage: VehicleDocument::pending()->latest()->get()
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: filter by document type.
     * Used to find a specific document for a vehicle.
     *
     * Usage: $vehicle->documents()->ofType('insurance')->first()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $type  Use VehicleDocument::TYPE_* constants
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope: documents that are expiring soon (within given days).
     * Used by a scheduled job to send expiry reminder notifications.
     *
     * Usage: VehicleDocument::expiringSoon(30)->get()
     *        → documents expiring within the next 30 days
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $days  Number of days ahead to check
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereNotNull('expiry_date')
                     ->whereBetween('expiry_date', [
                         now()->toDateString(),
                         now()->addDays($days)->toDateString(),
                     ]);
    }

    /**
     * Scope: documents that have already expired (past expiry_date).
     * Used by a scheduled job to auto-mark documents as expired.
     *
     * Usage: VehicleDocument::alreadyExpired()->get()
     */
    public function scopeAlreadyExpired($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now()->toDateString());
    }

    /*
    |--------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------
    */

    /**
     * Get the full public URL for this document file.
     * Works with both local disk and S3 storage.
     *
     * Usage: $document->url → "https://yourapp.com/storage/vehicle-docs/rc_abc.jpg"
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->document_path);
    }

    /**
     * Get a human-readable label for the document type.
     * Used in UI display and notification messages.
     *
     * Usage: $document->type_label → "Insurance Certificate"
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            self::TYPE_RC        => 'Registration Certificate (RC)',
            self::TYPE_INSURANCE => 'Insurance Certificate',
            self::TYPE_PUC       => 'Pollution Under Control (PUC)',
            self::TYPE_FITNESS   => 'Fitness Certificate',
            self::TYPE_PERMIT    => 'Transport Permit',
            default              => ucfirst($this->document_type),
        };
    }

    /*
    |--------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------
    */

    /**
     * Check if this document is currently valid.
     * A document is valid if: status is 'active' AND not expired.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        // Documents with no expiry_date (e.g. RC in most states) are always valid
        if (is_null($this->expiry_date)) {
            return true;
        }

        // Check if expiry date is today or in the future
        return $this->expiry_date->isFuture() || $this->expiry_date->isToday();
    }

    /**
     * Check if this document is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (is_null($this->expiry_date)) {
            return false; // No expiry = never expires (e.g. RC)
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check how many days remain until this document expires.
     * Returns null if no expiry date.
     * Returns 0 or negative if already expired.
     *
     * Usage:
     *   $doc->daysUntilExpiry() → 45  (expires in 45 days)
     *   $doc->daysUntilExpiry() → -3  (expired 3 days ago)
     *   $doc->daysUntilExpiry() → null (no expiry, e.g. RC)
     *
     * @return int|null
     */
    public function daysUntilExpiry(): ?int
    {
        if (is_null($this->expiry_date)) {
            return null;
        }

        // diffInDays with false preserves negative value for past dates
        return (int) now()->diffInDays($this->expiry_date, false);
    }
}