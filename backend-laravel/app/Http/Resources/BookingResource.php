<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * BookingResource
 * ============================================================
 *
 * Transforms a Booking model into a clean JSON response.
 *
 * RESPONSE STRATEGY:
 *   - List view (index): minimal data — no nested relationships
 *   - Detail view (show): full data — all relationships loaded
 *   - whenLoaded() handles both: returns data only if eager-loaded,
 *     so the same resource works for both contexts without N+1 risk.
 *
 * SENSITIVE FIELDS NEVER EXPOSED:
 *   - Internal DB IDs of related records beyond what's needed
 *   - Raw payment reference (masked if needed in PaymentResource)
 */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── CORE IDENTITY ──────────────────────────────────────────────
            'id'             => $this->id,
            'booking_number' => $this->booking_number,

            // ── STATUS ─────────────────────────────────────────────────────
            'booking_status' => $this->booking_status,
            'payment_status' => $this->payment_status,

            // ── FINANCIALS ─────────────────────────────────────────────────
            'amount'         => (float) $this->amount,
            'total_bill'     => $this->totalBill(), // base + overstay (from model helper)

            // ── TIMING ─────────────────────────────────────────────────────
            'booking_start_time'   => $this->booking_start_time?->toISOString(),
            'booking_end_time'     => $this->booking_end_time?->toISOString(),
            'actual_checkin_time'  => $this->actual_checkin_time?->toISOString(),
            'actual_checkout_time' => $this->actual_checkout_time?->toISOString(),
            'duration_hours'       => $this->duration_hours ? (float) $this->duration_hours : null,

            // ── NOTES ──────────────────────────────────────────────────────
            'notes' => $this->notes,

            // ── CONVENIENCE FLAGS ──────────────────────────────────────────
            // Pre-computed booleans let Flutter avoid status string comparisons
            'is_cancellable'  => $this->isCancellable(),
            'is_checked_in'   => $this->isCheckedIn(),
            'is_completed'    => $this->isCompleted(),

            // ── USER ───────────────────────────────────────────────────────
            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            // ── PARKING ────────────────────────────────────────────────────
            'parking' => $this->whenLoaded('parking', fn () => [
                'id'      => $this->parking->id,
                'name'    => $this->parking->name,
                'address' => $this->parking->address,
            ]),

            // ── SLOT ───────────────────────────────────────────────────────
            'parking_slot' => $this->whenLoaded('parkingSlot', fn () => [
                'id'          => $this->parkingSlot->id,
                'slot_number' => $this->parkingSlot->slot_number,
                'slot_type'   => $this->parkingSlot->slot_type,
            ]),

            // ── VEHICLE ────────────────────────────────────────────────────
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'id'             => $this->vehicle->id,
                'vehicle_number' => $this->vehicle->vehicle_number,
                'vehicle_name'   => $this->vehicle->vehicle_name,
                'vehicle_brand'  => $this->vehicle->vehicle_brand,
                'vehicle_color'  => $this->vehicle->vehicle_color,
            ]),

            // ── CHECK-IN (null until checked in) ───────────────────────────
            'check_in' => $this->whenLoaded('checkIn', fn () =>
                $this->checkIn ? new CheckInResource($this->checkIn) : null
            ),

            // ── CHECK-OUT (null until checked out) ─────────────────────────
            'check_out' => $this->whenLoaded('checkOut', fn () =>
                $this->checkOut ? new CheckOutResource($this->checkOut) : null
            ),

            // ── QR CODE ────────────────────────────────────────────────────
            'qr_booking' => $this->whenLoaded('qrBooking', fn () =>
                $this->qrBooking ? new QRBookingResource($this->qrBooking) : null
            ),

            // ── STATUS HISTORY ─────────────────────────────────────────────
            'status_history' => $this->whenLoaded(
                'statusHistory',
                fn () => BookingStatusHistoryResource::collection($this->statusHistory)
            ),

            // ── TIMESTAMPS ─────────────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}