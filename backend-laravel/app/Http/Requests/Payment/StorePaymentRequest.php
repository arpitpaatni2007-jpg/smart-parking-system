<?php

namespace App\Http\Requests\Payment;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * ============================================================
 * StorePaymentRequest
 * ============================================================
 *
 * Validates the request to INITIATE a payment for a booking.
 *
 * WHAT THIS REQUEST DOES:
 * When a user taps "Pay Now" in the Flutter app, it calls
 * POST /api/v1/payments/initiate with the booking_id and chosen
 * payment method. This request validates that:
 *   1. The booking exists and belongs to this user
 *   2. The booking is in a payable state (pending + unpaid)
 *   3. The payment method is supported
 *   4. No existing successful payment already exists
 *
 * WHAT HAPPENS NEXT (in the controller):
 * Once validated, the controller creates a Razorpay order and
 * returns the order_id + amount to the Flutter app. Flutter
 * then opens Razorpay's checkout SDK. After the user pays,
 * Flutter calls VerifyPaymentRequest to confirm.
 *
 * FUTURE SCALABILITY:
 *   - Add `promo_code` field when discount/coupon system is added.
 *   - Add `wallet_amount` when partial wallet payment is supported.
 *   - Add `emi_duration` when EMI payment option is added.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The booking to pay for.
            // Must be a real, approved booking (not a fake ID).
            'booking_id' => [
                'required',
                'integer',
                Rule::exists('bookings', 'id'),
            ],

            // The payment method the user chose.
            // These values are fed to Razorpay's preferred_method.
            // "wallet" covers Paytm, PhonePe, etc. via Razorpay.
            'payment_method' => [
                'required',
                'string',
                Rule::in(['upi', 'card', 'net_banking', 'wallet']),
            ],

            // The currency for the transaction.
            // Currently only INR is supported. Added for future
            // international expansion.
            'currency' => [
                'nullable',
                'string',
                Rule::in(['INR']), // Extend when going international
            ],
        ];
    }

    /**
     * Additional business-rule validation after field rules pass.
     * These require DB queries, so we run them here in withValidator()
     * rather than as inline rule closures.
     *
     * @param  Validator  $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {

            if ($v->errors()->any()) {
                return; // Don't run DB checks if basic rules already failed
            }

            $bookingId = $this->input('booking_id');
            $userId    = $this->user()->id;

            // ── Rule 1: Booking must belong to the requesting user ─
            $booking = Booking::find($bookingId);

            if (!$booking) {
                $v->errors()->add('booking_id', 'Booking not found.');
                return;
            }

            if ($booking->user_id !== $userId) {
                $v->errors()->add('booking_id', 'This booking does not belong to your account.');
                return;
            }

            // ── Rule 2: Booking must be in payable state ───────────
            // Only "pending" bookings with "unpaid" payment status
            // should be allowed to initiate payment.
            if ($booking->booking_status !== 'pending') {
                $statusMessages = [
                    'confirmed'  => 'This booking has already been paid.',
                    'checked_in' => 'This booking has already been checked in.',
                    'completed'  => 'This booking has already been completed.',
                    'cancelled'  => 'Cannot pay for a cancelled booking.',
                    'no_show'    => 'This booking has been marked as no-show.',
                ];
                $v->errors()->add(
                    'booking_id',
                    $statusMessages[$booking->booking_status]
                        ?? "Booking is in '{$booking->booking_status}' status and cannot be paid."
                );
                return;
            }

            if ($booking->payment_status !== 'unpaid') {
                $v->errors()->add(
                    'booking_id',
                    "Payment has already been attempted for this booking (status: {$booking->payment_status})."
                );
                return;
            }

            // ── Rule 3: No existing successful payment ─────────────
            // Prevent double-payment if the user somehow triggers two
            // simultaneous payment requests for the same booking.
            $existingSuccessfulPayment = \App\Models\Payment::where('booking_id', $bookingId)
                ->where('status', 'success')
                ->exists();

            if ($existingSuccessfulPayment) {
                $v->errors()->add(
                    'booking_id',
                    'A successful payment already exists for this booking.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'booking_id.required'      => 'Please provide the booking ID to pay for.',
            'booking_id.integer'       => 'Invalid booking ID format.',
            'booking_id.exists'        => 'The specified booking does not exist.',
            'payment_method.required'  => 'Please select a payment method.',
            'payment_method.in'        => 'Invalid payment method. Choose from: upi, card, net_banking, wallet.',
            'currency.in'              => 'Only INR currency is currently supported.',
        ];
    }
}