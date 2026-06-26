<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\RefundPaymentRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\QRBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * PaymentController
 * ============================================================
 *
 * Manages the complete payment lifecycle for the Smart Parking
 * Management System.
 *
 * PAYMENT FLOW OVERVIEW:
 *
 *   STEP 1 — initiate()
 *     Customer taps "Pay Now" in Flutter app.
 *     We create a Razorpay order and a local Payment record (status: pending).
 *     Return the Razorpay order_id and amount to Flutter.
 *
 *   STEP 2 — Flutter (client-side)
 *     Flutter opens Razorpay's native checkout UI.
 *     User completes payment via UPI/card/net banking.
 *     Razorpay returns payment_id + order_id + signature to Flutter.
 *
 *   STEP 3 — verify()
 *     Flutter sends all three Razorpay values to our server.
 *     We verify the HMAC signature to confirm authenticity.
 *     On success: update Payment to "success", Booking to "confirmed",
 *     generate QR code, log the transaction.
 *
 *   STEP 4 — refund() (when needed)
 *     User cancels booking or admin initiates refund.
 *     We call Razorpay's refund API.
 *     On success: update Payment to "refunded", store refund_id.
 *
 *   ADDITIONAL:
 *     history()   → User's payment list (My Bookings screen)
 *     show()      → Single payment detail (receipt screen)
 *     webhook()   → Razorpay sends async events (payment captured, failed)
 *
 * RAZORPAY INTEGRATION NOTE:
 * All Razorpay API calls are wrapped in placeholder methods at the
 * bottom of this controller (createRazorpayOrder, verifySignature,
 * initiateRazorpayRefund). These will be implemented when the
 * Razorpay SDK package is installed:
 *   composer require razorpay/razorpay
 *
 * SECURITY NOTES:
 *   - The razorpay_signature is verified server-side ONLY.
 *     Never trust client-provided payment status.
 *   - The Razorpay secret key is in .env, never in code.
 *   - Webhook calls are verified using Razorpay's webhook secret.
 *   - All payment operations are wrapped in DB transactions.
 *
 * FUTURE SCALABILITY:
 *   - Add PaymentGatewayInterface for multi-gateway support (PayU, Stripe).
 *   - Move heavy processing to queued jobs (PaymentVerificationJob).
 *   - Add idempotency keys to prevent duplicate payment processing.
 *   - Add payment retry logic with exponential backoff.
 */
class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------
    | PUBLIC API METHODS
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/payments/history
     *
     * Return a paginated payment history for the authenticated user.
     *
     * USED BY:
     *   - User App: "Payment History" in Profile screen
     *   - Admin Panel: All payments list with filters
     *
     * QUERY PARAMETERS:
     *   ?status=success         → filter by payment status
     *   ?payment_method=upi     → filter by method
     *   ?date_from=2026-01-01   → payments from date
     *   ?date_to=2026-01-31     → payments up to date
     *   ?search=BK20260623      → search by booking number
     *   ?per_page=15
     */
    public function history(Request $request): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        $query = Payment::query()
            ->with([
                'booking:id,booking_number,booking_status,booking_start_time,parking_id',
                'booking.parking:id,name',
                'user:id,name,email,phone',
            ]);

        // ── Role-Based Scoping ─────────────────────────────────────
        // Non-admins only see their own payment records.
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Admins can filter by specific user.
        if ($isAdmin && $request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // ── Filters ───────────────────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // ── Search by Booking Number ──────────────────────────────
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('booking', fn ($q) =>
                $q->where('booking_number', 'LIKE', "%{$search}%")
            );
        }

        $query->orderBy('created_at', 'desc');

        $perPage  = min((int) $request->input('per_page', 15), 100);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Payment history retrieved successfully.',
            'data'    => PaymentResource::collection($payments)->response()->getData(true),
        ], 200);
    }

    /**
     * GET /api/v1/payments/{payment}
     *
     * Return full detail of a single payment record.
     *
     * USED BY:
     *   - User App: Receipt screen after successful payment
     *   - Admin Panel: Payment detail view
     *
     * Loads the booking, user, and all transaction logs
     * (individual gateway call records).
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        // Non-admins can only view their own payments.
        if (!$isAdmin && $payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found.',
                'data'    => null,
            ], 404);
        }

        $payment->load([
            'user:id,name,email,phone',
            'booking',
            'booking.parking:id,name,address',
            'booking.parkingSlot:id,slot_number,floor',
            'booking.vehicle:id,vehicle_number,vehicle_type',
            'transactions',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment details retrieved successfully.',
            'data'    => new PaymentResource($payment),
        ], 200);
    }

    /**
     * POST /api/v1/payments/initiate
     *
     * Initiate a payment for a booking.
     *
     * PROCESS:
     *   1. Validate booking + method (StorePaymentRequest)
     *   2. Create a Razorpay order via their API
     *   3. Create a local Payment record (status: pending)
     *   4. Return the Razorpay order_id + amount to Flutter
     *   5. Flutter opens Razorpay SDK with these details
     *
     * RESPONSE RETURNED TO FLUTTER:
     * {
     *   "payment_id": 42,                     ← our internal ID
     *   "razorpay_order_id": "order_xyz123",  ← pass to Razorpay SDK
     *   "amount": 16000,                      ← in paise (₹160 = 16000)
     *   "currency": "INR",
     *   "key_id": "rzp_test_xxx"              ← Razorpay public key
     * }
     *
     * Flutter uses razorpay_order_id + amount + key_id to open checkout.
     *
     * RAZORPAY NOTE:
     * Amount must be in the SMALLEST currency unit.
     * For INR: ₹160.00 → 16000 paise.
     */
    public function initiate(StorePaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $booking       = Booking::findOrFail($request->input('booking_id'));
            $paymentMethod = $request->input('payment_method');
            $currency      = $request->input('currency', 'INR');

            // ── Amount in Paise ───────────────────────────────────
            // Razorpay requires amount in the smallest unit.
            // ₹160.50 → 16050 paise.
            $amountInPaise = (int) round((float) $booking->amount * 100);

            // ── Create Razorpay Order ─────────────────────────────
            // PLACEHOLDER: Replace with actual Razorpay SDK call.
            $razorpayOrderData = $this->createRazorpayOrder(
                amount: $amountInPaise,
                currency: $currency,
                receiptId: 'bk_' . $booking->id . '_' . time(),
                notes: [
                    'booking_id'     => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'user_id'        => $booking->user_id,
                ]
            );

            // ── Create Local Payment Record ───────────────────────
            $payment = Payment::create([
                'booking_id'         => $booking->id,
                'user_id'            => $booking->user_id,
                'amount'             => $booking->amount,
                'currency'           => $currency,
                'payment_method'     => $paymentMethod,
                'payment_gateway'    => 'razorpay',
                'razorpay_order_id'  => $razorpayOrderData['id'],
                'status'             => 'pending',
            ]);

            // ── Log the Initiation Transaction ────────────────────
            $this->logTransaction(
                paymentId: $payment->id,
                type: 'initiation',
                amount: $booking->amount,
                status: 'pending',
                gatewayRef: $razorpayOrderData['id'],
                responseCode: 'ORDER_CREATED'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment order created. Please complete the payment.',
                'data'    => [
                    // Our internal payment ID — Flutter sends this back in verify()
                    'payment_id'       => $payment->id,

                    // Pass these to Razorpay SDK in Flutter
                    'razorpay_order_id'=> $razorpayOrderData['id'],
                    'amount'           => $amountInPaise,  // in paise for Razorpay SDK
                    'amount_display'   => $booking->amount, // human-readable in rupees
                    'currency'         => $currency,

                    // Flutter needs the public key to initialize Razorpay SDK
                    'key_id'           => config('services.razorpay.key_id'),

                    // Prefill data for Razorpay's checkout form
                    'prefill'          => [
                        'name'    => $request->user()->name,
                        'email'   => $request->user()->email,
                        'contact' => $request->user()->phone,
                    ],

                    // Description shown in Razorpay's payment screen
                    'description'      => "Parking booking #{$booking->booking_number}",
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment initiation failed', [
                'booking_id' => $request->input('booking_id'),
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/payments/verify
     *
     * Verify a payment after Razorpay checkout completes.
     *
     * CRITICAL SECURITY STEP:
     * We verify the HMAC SHA256 signature that Razorpay provides.
     * This proves the payment genuinely happened on Razorpay's
     * servers and wasn't faked by the client.
     *
     * ON SUCCESSFUL VERIFICATION:
     *   1. Payment status → "success"
     *   2. Booking status → "confirmed"
     *   3. Booking payment_status → "paid"
     *   4. QR code generated for the booking
     *   5. Transaction log updated
     *   6. Status history recorded
     *
     * ON FAILED VERIFICATION:
     *   1. Payment status → "failed"
     *   2. Transaction log records the failure
     *   3. Slot reverts to "available" (booking remains pending)
     */
    public function verify(VerifyPaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payment = Payment::with('booking.parkingSlot')->findOrFail(
                $request->input('payment_id')
            );

            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpayOrderId   = $request->input('razorpay_order_id');
            $razorpaySignature = $request->input('razorpay_signature');

            // ── Verify Razorpay Signature ─────────────────────────
            // PLACEHOLDER: Replace with actual signature verification.
            $isSignatureValid = $this->verifyRazorpaySignature(
                orderId: $razorpayOrderId,
                paymentId: $razorpayPaymentId,
                signature: $razorpaySignature
            );

            if (!$isSignatureValid) {
                // Signature mismatch = payment is fake or tampered.
                $payment->update([
                    'status'              => 'failed',
                    'razorpay_payment_id' => $razorpayPaymentId,
                ]);

                $this->logTransaction(
                    paymentId: $payment->id,
                    type: 'verification',
                    amount: $payment->amount,
                    status: 'failed',
                    gatewayRef: $razorpayPaymentId,
                    responseCode: 'SIGNATURE_MISMATCH'
                );

                DB::commit();

                Log::warning('Payment signature verification failed', [
                    'payment_id'          => $payment->id,
                    'razorpay_payment_id' => $razorpayPaymentId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed. If money was deducted, it will be refunded within 5-7 business days.',
                    'data'    => null,
                ], 422);
            }

            // ── Signature Valid: Confirm Payment ───────────────────
            $payment->update([
                'status'              => 'success',
                'razorpay_payment_id' => $razorpayPaymentId,
                'transaction_id'      => $razorpayPaymentId,
                'paid_at'             => now(),
            ]);

            // ── Confirm the Booking ───────────────────────────────
            $booking = $payment->booking;
            $booking->update([
                'booking_status' => Booking::STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_PAID,
            ]);

            // ── Generate QR Code for Check-In ─────────────────────
            // The customer will show this QR at the parking entry gate.
            QRBooking::generateForBooking($booking);

            // ── Log Status Change ─────────────────────────────────
            BookingStatusHistory::record(
                booking: $booking,
                newStatus: Booking::STATUS_CONFIRMED,
                remarks: "Payment confirmed. Razorpay Payment ID: {$razorpayPaymentId}",
                changedBy: 'payment_gateway'
            );

            // ── Log the Transaction ───────────────────────────────
            $this->logTransaction(
                paymentId: $payment->id,
                type: 'charge',
                amount: $payment->amount,
                status: 'success',
                gatewayRef: $razorpayPaymentId,
                responseCode: 'PAYMENT_CAPTURED'
            );

            DB::commit();

            // Load full payment details for the receipt response.
            $payment->refresh()->load([
                'booking.parking:id,name,address',
                'booking.parkingSlot:id,slot_number,floor',
                'booking.vehicle:id,vehicle_number',
                'booking.qrBooking',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your parking slot is confirmed.',
                'data'    => new PaymentResource($payment),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment verification exception', [
                'payment_id' => $request->input('payment_id'),
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification encountered an error. Please contact support with your booking number.',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/payments/refund
     *
     * Initiate a refund for a payment.
     *
     * WHO CAN REFUND:
     *   - Customer: can refund their own payment if booking is
     *               not yet checked in (subject to policy)
     *   - Admin: can refund any payment at any stage
     *
     * REFUND TIMING:
     *   Razorpay processes refunds within 5-7 business days.
     *   We mark the payment as "refunded" immediately in our DB,
     *   and store the Razorpay refund_id for tracking.
     *
     * PARTIAL REFUNDS:
     *   Supported — e.g. 50% cancellation fee means we refund
     *   only 50% of the original payment amount.
     */
    public function refund(RefundPaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payment = Payment::with('booking')->findOrFail(
                $request->input('payment_id')
            );

            $user    = $request->user();
            $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

            // Determine refund amount (full refund if not specified).
            $refundAmount = $request->input('refund_amount')
                ? (float) $request->input('refund_amount')
                : (float) $payment->amount;

            // ── Call Razorpay Refund API ──────────────────────────
            // PLACEHOLDER: Replace with actual Razorpay refund call.
            $refundAmountInPaise = (int) round($refundAmount * 100);

            $razorpayRefundData = $this->initiateRazorpayRefund(
                razorpayPaymentId: $payment->razorpay_payment_id,
                amountInPaise: $refundAmountInPaise,
                notes: [
                    'booking_id'     => $payment->booking_id,
                    'booking_number' => $payment->booking?->booking_number,
                    'reason'         => $request->input('reason', 'Customer requested refund'),
                    'initiated_by'   => $isAdmin ? 'admin' : 'customer',
                ]
            );

            // ── Update Payment Record ─────────────────────────────
            $payment->update([
                'status'        => 'refunded',
                'refund_id'     => $razorpayRefundData['id'],
                'refund_amount' => $refundAmount,
                'refunded_at'   => now(),
            ]);

            // ── Update Booking Payment Status ─────────────────────
            if ($payment->booking) {
                $payment->booking->update([
                    'payment_status' => Booking::PAYMENT_REFUNDED,
                ]);
            }

            // ── Log the Refund Transaction ────────────────────────
            $this->logTransaction(
                paymentId: $payment->id,
                type: 'refund',
                amount: $refundAmount,
                status: 'success',
                gatewayRef: $razorpayRefundData['id'],
                responseCode: 'REFUND_INITIATED'
            );

            DB::commit();

            Log::info('Refund initiated', [
                'payment_id'    => $payment->id,
                'refund_amount' => $refundAmount,
                'razorpay_ref'  => $razorpayRefundData['id'],
                'initiated_by'  => $user->id,
            ]);

            $payment->refresh()->load(['booking', 'transactions']);

            return response()->json([
                'success' => true,
                'message' => "Refund of ₹{$refundAmount} initiated successfully. "
                           . "Amount will be credited within 5-7 business days.",
                'data'    => new PaymentResource($payment),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund failed', [
                'payment_id' => $request->input('payment_id'),
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refund could not be processed at this time. Please try again or contact support.',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/payments/webhook
     *
     * Handle asynchronous payment events from Razorpay.
     *
     * Razorpay sends webhook events to this endpoint for:
     *   - payment.captured  → payment was successfully captured
     *   - payment.failed    → payment failed on Razorpay's end
     *   - refund.created    → refund was processed
     *   - order.paid        → entire order was paid
     *
     * WHY A WEBHOOK?
     * Sometimes the user's internet drops after paying but before
     * our verify() endpoint is called. The webhook ensures we
     * still get notified by Razorpay and can confirm the booking.
     *
     * SECURITY: Webhook signature must be verified using the
     * X-Razorpay-Signature header and our webhook secret.
     * This endpoint is PUBLIC (no auth:sanctum) — Razorpay
     * calls it directly, not the user's app.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // ── Verify Webhook Signature ──────────────────────────
            // PLACEHOLDER: Implement Razorpay webhook signature check.
            $webhookSecret    = config('services.razorpay.webhook_secret');
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $payload          = $request->getContent();

            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

            if (!hash_equals($expectedSignature, $webhookSignature ?? '')) {
                Log::warning('Razorpay webhook: Invalid signature', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['message' => 'Invalid signature'], 400);
            }

            // ── Parse Webhook Event ───────────────────────────────
            $event = $request->input('event');
            $entity = $request->input('payload.payment.entity')
                ?? $request->input('payload.refund.entity')
                ?? [];

            Log::info('Razorpay webhook received', ['event' => $event]);

            // PLACEHOLDER: Implement event handlers.
            // These will be filled in during Razorpay integration phase.
            match ($event) {
                'payment.captured' => $this->handlePaymentCaptured($entity),
                'payment.failed'   => $this->handlePaymentFailed($entity),
                'refund.created'   => $this->handleRefundCreated($entity),
                default            => Log::info("Unhandled Razorpay event: {$event}"),
            };

            // Always return 200 to Razorpay to acknowledge receipt.
            // If we return anything else, Razorpay retries the webhook.
            return response()->json(['message' => 'Webhook received'], 200);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook error', ['error' => $e->getMessage()]);
            // Still return 200 to prevent Razorpay from retrying.
            return response()->json(['message' => 'Webhook acknowledged'], 200);
        }
    }

    /*
    |--------------------------------------------------------------------
    | RAZORPAY PLACEHOLDER METHODS
    |--------------------------------------------------------------------
    |
    | These methods define the integration contract with Razorpay.
    | Implement them when the Razorpay SDK is installed:
    |   composer require razorpay/razorpay
    |
    | Each method documents exactly what it should do, what it
    | receives, and what it should return — so implementation
    | is straightforward.
    */

    /**
     * Create a Razorpay order and return the order data.
     *
     * WILL BE IMPLEMENTED AS:
     *   $api = new \Razorpay\Api\Api(
     *       config('services.razorpay.key_id'),
     *       config('services.razorpay.key_secret')
     *   );
     *   return $api->order->create([
     *       'amount'   => $amount,      // in paise
     *       'currency' => $currency,
     *       'receipt'  => $receiptId,
     *       'notes'    => $notes,
     *   ]);
     *
     * RETURNS ARRAY:
     * [
     *   'id'       => 'order_AbCdEfGhIjKl',  ← razorpay_order_id
     *   'amount'   => 16000,                  ← in paise
     *   'currency' => 'INR',
     *   'status'   => 'created',
     * ]
     *
     * @param  int     $amount      Amount in paise (₹160 = 16000)
     * @param  string  $currency    Currency code (INR)
     * @param  string  $receiptId   Our internal receipt reference
     * @param  array   $notes       Key-value metadata for the order
     * @return array                Razorpay order data
     */
    protected function createRazorpayOrder(
        int $amount,
        string $currency,
        string $receiptId,
        array $notes = []
    ): array {
        // PLACEHOLDER — replace with actual Razorpay SDK call.
        // This stub allows the controller to function in development
        // before the Razorpay account and SDK are configured.

        Log::debug('Razorpay createOrder placeholder called', [
            'amount'    => $amount,
            'currency'  => $currency,
            'receipt'   => $receiptId,
        ]);

        // Return a fake order object that matches the real Razorpay response shape.
        // In production, this entire method body is replaced by the SDK call above.
        return [
            'id'       => 'order_' . strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 14)),
            'amount'   => $amount,
            'currency' => $currency,
            'receipt'  => $receiptId,
            'status'   => 'created',
            'notes'    => $notes,
        ];
    }

    /**
     * Verify the Razorpay payment signature.
     *
     * WILL BE IMPLEMENTED AS:
     *   $expectedSignature = hash_hmac(
     *       'sha256',
     *       $orderId . '|' . $paymentId,
     *       config('services.razorpay.key_secret')
     *   );
     *   return hash_equals($expectedSignature, $signature);
     *
     * OR using the Razorpay SDK utility:
     *   $api = new \Razorpay\Api\Api(key_id, key_secret);
     *   $api->utility->verifyPaymentSignature([
     *       'razorpay_order_id'   => $orderId,
     *       'razorpay_payment_id' => $paymentId,
     *       'razorpay_signature'  => $signature,
     *   ]);
     *   // Throws SignatureVerificationError if invalid
     *
     * @param  string  $orderId    Razorpay order ID
     * @param  string  $paymentId  Razorpay payment ID
     * @param  string  $signature  Signature from Flutter/Razorpay SDK
     * @return bool                True if signature is valid
     */
    protected function verifyRazorpaySignature(
        string $orderId,
        string $paymentId,
        string $signature
    ): bool {
        // PLACEHOLDER — replace with actual signature verification.
        // In development (APP_ENV=local), we skip verification
        // so the payment flow can be tested end-to-end.
        if (app()->environment('local', 'testing')) {
            Log::debug('Razorpay signature verification skipped in local/testing environment.');
            return true;
        }

        // Production implementation:
        $generatedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            config('services.razorpay.key_secret')
        );

        return hash_equals($generatedSignature, $signature);
    }

    /**
     * Initiate a refund via Razorpay.
     *
     * WILL BE IMPLEMENTED AS:
     *   $api = new \Razorpay\Api\Api(key_id, key_secret);
     *   return $api->payment->fetch($razorpayPaymentId)->refund([
     *       'amount' => $amountInPaise,
     *       'notes'  => $notes,
     *   ]);
     *
     * RETURNS ARRAY:
     * [
     *   'id'         => 'rfnd_AbCdEf123',  ← refund_id to store
     *   'amount'     => 16000,              ← refunded in paise
     *   'payment_id' => 'pay_xyz',
     *   'status'     => 'processed',
     * ]
     *
     * @param  string  $razorpayPaymentId  The payment to refund
     * @param  int     $amountInPaise      Refund amount in paise
     * @param  array   $notes              Metadata for the refund
     * @return array                        Razorpay refund data
     */
    protected function initiateRazorpayRefund(
        string $razorpayPaymentId,
        int $amountInPaise,
        array $notes = []
    ): array {
        // PLACEHOLDER — replace with actual Razorpay SDK refund call.

        Log::debug('Razorpay initiateRefund placeholder called', [
            'payment_id'    => $razorpayPaymentId,
            'amount_paise'  => $amountInPaise,
        ]);

        return [
            'id'         => 'rfnd_' . strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 14)),
            'payment_id' => $razorpayPaymentId,
            'amount'     => $amountInPaise,
            'status'     => 'processed',
            'notes'      => $notes,
        ];
    }

    /*
    |--------------------------------------------------------------------
    | WEBHOOK EVENT HANDLERS (Placeholders)
    |--------------------------------------------------------------------
    */

    /**
     * Handle the payment.captured webhook event from Razorpay.
     *
     * Triggered when Razorpay confirms a payment was captured.
     * Used as a fallback for cases where verify() was not called
     * (e.g. user's internet dropped after paying).
     *
     * WILL IMPLEMENT:
     *   - Find Payment by razorpay_order_id
     *   - If still "pending" → run the same confirmation logic as verify()
     *   - This prevents bookings being stuck in "pending" forever
     *
     * @param  array  $entity  Razorpay payment entity data
     */
    protected function handlePaymentCaptured(array $entity): void
    {
        // PLACEHOLDER — implement during Razorpay integration phase.
        Log::info('Webhook: payment.captured — implement in Razorpay integration phase', [
            'razorpay_payment_id' => $entity['id'] ?? null,
            'order_id'            => $entity['order_id'] ?? null,
        ]);
    }

    /**
     * Handle the payment.failed webhook event from Razorpay.
     *
     * Triggered when a payment attempt definitively fails on
     * Razorpay's end (insufficient funds, card declined, etc.)
     *
     * WILL IMPLEMENT:
     *   - Find Payment by razorpay_order_id
     *   - Update status to "failed"
     *   - Release the reserved parking slot back to "available"
     *   - Notify the customer to retry payment
     *
     * @param  array  $entity  Razorpay payment entity data
     */
    protected function handlePaymentFailed(array $entity): void
    {
        // PLACEHOLDER — implement during Razorpay integration phase.
        Log::info('Webhook: payment.failed — implement in Razorpay integration phase', [
            'razorpay_payment_id' => $entity['id'] ?? null,
            'error_code'          => $entity['error_code'] ?? null,
        ]);
    }

    /**
     * Handle the refund.created webhook event from Razorpay.
     *
     * Triggered when Razorpay confirms a refund has been queued.
     *
     * WILL IMPLEMENT:
     *   - Update refund status in our database
     *   - Send the customer a "refund processed" notification
     *
     * @param  array  $entity  Razorpay refund entity data
     */
    protected function handleRefundCreated(array $entity): void
    {
        // PLACEHOLDER — implement during Razorpay integration phase.
        Log::info('Webhook: refund.created — implement in Razorpay integration phase', [
            'refund_id'  => $entity['id'] ?? null,
            'payment_id' => $entity['payment_id'] ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | PRIVATE UTILITY METHODS
    |--------------------------------------------------------------------
    */

    /**
     * Log a payment transaction to the payment_transactions table.
     *
     * Every significant gateway call is logged here:
     *   - Order creation (initiation)
     *   - Payment verification (charge)
     *   - Refund initiation
     *
     * This gives a complete audit trail for every rupee that
     * flows through the system. Essential for dispute resolution
     * and financial reconciliation.
     *
     * @param  int         $paymentId    Our internal Payment ID
     * @param  string      $type         initiation | charge | refund | verification
     * @param  float       $amount       Amount involved in this transaction
     * @param  string      $status       pending | success | failed
     * @param  string|null $gatewayRef   Razorpay's ID for this event
     * @param  string|null $responseCode Our internal code for this event
     */
    private function logTransaction(
        int $paymentId,
        string $type,
        float $amount,
        string $status,
        ?string $gatewayRef = null,
        ?string $responseCode = null
    ): void {
        try {
            PaymentTransaction::create([
                'payment_id'    => $paymentId,
                'type'          => $type,
                'amount'        => $amount,
                'status'        => $status,
                'gateway_ref'   => $gatewayRef,
                'response_code' => $responseCode,
            ]);
        } catch (\Exception $e) {
            // Logging failure should NEVER cause the main payment flow to fail.
            // Log the error and move on.
            Log::error('Failed to log payment transaction', [
                'payment_id' => $paymentId,
                'type'       => $type,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}