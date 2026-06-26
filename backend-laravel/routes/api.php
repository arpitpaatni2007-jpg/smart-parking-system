<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ParkingFacilityController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\VehicleTypeController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\ParkingSlotController;
use App\Http\Controllers\Api\ParkingImageController;
use App\Http\Controllers\Api\OwnerBankDetailController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\CheckOutController;
use App\Http\Controllers\Api\QRBookingController;
use App\Http\Controllers\Api\BookingStatusHistoryController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Smart Parking Management System
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api (configured in bootstrap/app.php
| or RouteServiceProvider depending on your Laravel 12 setup).
|
| AUTHENTICATION STRATEGY:
|   Public routes  → No middleware — anyone can call these
|   Protected routes → auth:sanctum middleware — requires Bearer token
|
| HOW SANCTUM TOKEN AUTH WORKS:
|   1. Client calls POST /api/auth/login with credentials
|   2. Server returns a plain text token e.g. "3|abc123xyz..."
|   3. Client stores token and sends it in every subsequent request:
|      Header: Authorization: Bearer 3|abc123xyz...
|   4. auth:sanctum middleware validates the token on protected routes
|   5. $request->user() returns the authenticated User model
|
| FLUTTER APP USAGE:
|   Store token in flutter_secure_storage after login.
|   Attach to every API call via Dio's BaseOptions headers:
|     'Authorization': 'Bearer $token'
|
*/

// ============================================================
// API VERSION PREFIX
// ============================================================
// Grouping under /api/v1/ allows future versioning.
// When you need breaking changes, add /api/v2/ routes
// without removing v1 — existing app users won't break.
// ============================================================

Route::prefix('v1')->group(function () {

    // ========================================================
    // AUTH ROUTES — PUBLIC (no token required)
    // ========================================================

    Route::prefix('auth')->name('auth.')->group(function () {

        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');

        // ── Protected Auth Routes ──────────────────────────────────────
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout',          [AuthController::class, 'logout'])->name('logout');
            Route::get('profile',          [AuthController::class, 'profile'])->name('profile');
            Route::put('profile',          [AuthController::class, 'updateProfile'])->name('profile.update');
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('password.change');
        });

    }); // end /auth prefix

    // ========================================================
    // MASTER MANAGEMENT ROUTES — PROTECTED
    // ========================================================
    //
    // All master management routes sit under /api/v1/master/
    // and require a valid Sanctum token.
    //
    // VERIFY IN TERMINAL:
    //   php artisan route:list --path=api/v1/master
    //
    // ========================================================

    Route::middleware('auth:sanctum')
        ->prefix('master')
        ->name('master.')
        ->group(function () {

            // ── STATES ────────────────────────────────────────────────────
            // GET    /api/v1/master/states
            // POST   /api/v1/master/states
            // GET    /api/v1/master/states/{state}
            // PUT    /api/v1/master/states/{state}
            // DELETE /api/v1/master/states/{state}
            Route::apiResource('states', StateController::class)->names('states');

            // PATCH /api/v1/master/states/{state}/toggle-status
            Route::patch(
                'states/{state}/toggle-status',
                [StateController::class, 'toggleStatus']
            )->name('states.toggle-status');

            // ── CITIES ────────────────────────────────────────────────────
            // GET    /api/v1/master/cities
            // POST   /api/v1/master/cities
            // GET    /api/v1/master/cities/{city}
            // PUT    /api/v1/master/cities/{city}
            // DELETE /api/v1/master/cities/{city}
            Route::apiResource('cities', CityController::class)->names('cities');

            // PATCH /api/v1/master/cities/{city}/toggle-status
            Route::patch(
                'cities/{city}/toggle-status',
                [CityController::class, 'toggleStatus']
            )->name('cities.toggle-status');

            // GET /api/v1/master/states/{state}/cities
            // Cascaded dropdown: all active cities for a given state.
            Route::get(
                'states/{state}/cities',
                [CityController::class, 'byState']
            )->name('states.cities');

            // ── VEHICLE TYPES ─────────────────────────────────────────────
            // GET    /api/v1/master/vehicle-types
            // POST   /api/v1/master/vehicle-types
            // GET    /api/v1/master/vehicle-types/{vehicle_type}
            // PUT    /api/v1/master/vehicle-types/{vehicle_type}
            // DELETE /api/v1/master/vehicle-types/{vehicle_type}
            Route::apiResource('vehicle-types', VehicleTypeController::class)
                ->parameters(['vehicle-types' => 'vehicle_type'])
                ->names('vehicle-types');

            // PATCH /api/v1/master/vehicle-types/{vehicle_type}/toggle-status
            Route::patch(
                'vehicle-types/{vehicle_type}/toggle-status',
                [VehicleTypeController::class, 'toggleStatus']
            )->name('vehicle-types.toggle-status');

            // ── PARKING FACILITIES ────────────────────────────────────────
            // GET    /api/v1/master/parking-facilities
            // POST   /api/v1/master/parking-facilities
            // GET    /api/v1/master/parking-facilities/{parking_facility}
            // PUT    /api/v1/master/parking-facilities/{parking_facility}
            // DELETE /api/v1/master/parking-facilities/{parking_facility}
            Route::apiResource('parking-facilities', ParkingFacilityController::class)
                ->parameters(['parking-facilities' => 'parking_facility'])
                ->names('parking-facilities');

            // PATCH /api/v1/master/parking-facilities/{parking_facility}/toggle-status
            Route::patch(
                'parking-facilities/{parking_facility}/toggle-status',
                [ParkingFacilityController::class, 'toggleStatus']
            )->name('parking-facilities.toggle-status');

        }); // end master group

    // ========================================================
    // PARKING MANAGEMENT ROUTES — PROTECTED
    // ========================================================
    //
    // All routes require a valid Sanctum Bearer token.
    //
    // ┌────────────────────────────────────────────────────────────────────────────┐
    // │ Verb   │ URI                                              │ Action          │
    // ├────────────────────────────────────────────────────────────────────────────┤
    // │ GET    │ /api/v1/parkings                                 │ index           │
    // │ POST   │ /api/v1/parkings                                 │ store           │
    // │ GET    │ /api/v1/parkings/{parking}                       │ show            │
    // │ PUT    │ /api/v1/parkings/{parking}                       │ update          │
    // │ DELETE │ /api/v1/parkings/{parking}                       │ destroy         │
    // ├────────────────────────────────────────────────────────────────────────────┤
    // │ GET    │ /api/v1/parkings/{parking}/slots                 │ index           │
    // │ POST   │ /api/v1/parkings/{parking}/slots                 │ store           │
    // │ GET    │ /api/v1/parkings/{parking}/slots/{slot}          │ show            │
    // │ PUT    │ /api/v1/parkings/{parking}/slots/{slot}          │ update          │
    // │ DELETE │ /api/v1/parkings/{parking}/slots/{slot}          │ destroy         │
    // ├────────────────────────────────────────────────────────────────────────────┤
    // │ GET    │ /api/v1/parkings/{parking}/images                │ index           │
    // │ POST   │ /api/v1/parkings/{parking}/images                │ store (upload)  │
    // │ GET    │ /api/v1/parkings/{parking}/images/{image}        │ show            │
    // │ DELETE │ /api/v1/parkings/{parking}/images/{image}        │ destroy         │
    // │ PATCH  │ /api/v1/parkings/{parking}/images/{image}/primary│ setPrimary      │
    // ├────────────────────────────────────────────────────────────────────────────┤
    // │ GET    │ /api/v1/owner/bank-detail                        │ index           │
    // │ POST   │ /api/v1/owner/bank-detail                        │ store           │
    // │ GET    │ /api/v1/owner/bank-detail/{detail}               │ show            │
    // │ PUT    │ /api/v1/owner/bank-detail/{detail}               │ update          │
    // │ DELETE │ /api/v1/owner/bank-detail/{detail}               │ destroy         │
    // └────────────────────────────────────────────────────────────────────────────┘
    //
    // VERIFY IN TERMINAL:
    //   php artisan route:list --path=api/v1/parkings
    //   php artisan route:list --path=api/v1/owner
    //
    // ========================================================

    Route::middleware('auth:sanctum')->group(function () {

        // ──────────────────────────────────────────────────────────────
        // PARKING LOCATIONS
        // ──────────────────────────────────────────────────────────────
        //
        // GET    /api/v1/parkings
        //   → List/search parkings (paginated).
        //   → Query params: ?search=name &city_id=3 &state_id=1
        //                   &status=active &lat=28.61 &lng=77.20 &radius=10
        //                   &per_page=15
        //   → Users see only active parkings.
        //   → Owners see only their own parkings (any status).
        //
        // POST   /api/v1/parkings
        //   → Owner: register a new parking location.
        //   → Status defaults to 'pending' (requires admin approval).
        //   → Accepts facility_ids[] for many-to-many facility sync.
        //
        // GET    /api/v1/parkings/{parking}
        //   → Full detail: state, city, owner, images (gallery),
        //     facilities, slots with vehicle types.
        //
        // PUT    /api/v1/parkings/{parking}
        //   → Owner: partial update (only sent fields are changed).
        //   → Accepts facility_ids[] to replace all facility links.
        //
        // DELETE /api/v1/parkings/{parking}
        //   → Owner: soft-delete.
        //   → Returns 409 Conflict if parking has active bookings.
        //
        // ──────────────────────────────────────────────────────────────
        Route::apiResource('parkings', ParkingController::class);

        // ──────────────────────────────────────────────────────────────
        // PARKING SLOTS (nested under parkings)
        // ──────────────────────────────────────────────────────────────
        //
        // GET    /api/v1/parkings/{parking}/slots
        //   → List all slots for this parking.
        //   → Query params: ?vehicle_type_id=2 &slot_type=ev &status=available
        //   → Users see only available slots; owners see all.
        //
        // POST   /api/v1/parkings/{parking}/slots
        //   → Owner: add a new slot.
        //   → Validates slot_number uniqueness within this parking only.
        //   → Auto-increments parking.total_slots.
        //
        // GET    /api/v1/parkings/{parking}/slots/{slot}
        //   → Single slot detail with vehicle type.
        //   → Validates {slot} belongs to {parking} (scope check).
        //
        // PUT    /api/v1/parkings/{parking}/slots/{slot}
        //   → Owner: update slot details or toggle maintenance status.
        //   → Returns 409 Conflict if slot status is 'booked'.
        //
        // DELETE /api/v1/parkings/{parking}/slots/{slot}
        //   → Owner: soft-delete slot.
        //   → Returns 409 Conflict if slot is currently booked.
        //   → Auto-decrements parking.total_slots.
        //
        // ──────────────────────────────────────────────────────────────
        Route::apiResource('parkings.slots', ParkingSlotController::class);

        // ──────────────────────────────────────────────────────────────
        // PARKING IMAGES (nested under parkings)
        // ──────────────────────────────────────────────────────────────
        //
        // GET    /api/v1/parkings/{parking}/images
        //   → List all images for this parking (primary image first).
        //
        // POST   /api/v1/parkings/{parking}/images
        //   → Owner: upload a new image (multipart/form-data).
        //   → Accepts: image (file, max 5MB, jpeg/jpg/png/webp), is_primary (bool).
        //   → Filename stored as UUID (prevents collisions & path traversal).
        //   → If first image for this parking → auto-set as primary.
        //   → If is_primary=true → demotes any existing primary image.
        //
        // GET    /api/v1/parkings/{parking}/images/{image}
        //   → Single image detail. Validates {image} belongs to {parking}.
        //
        // DELETE /api/v1/parkings/{parking}/images/{image}
        //   → Owner: delete image record + physical file from storage.
        //   → If deleted image was primary → auto-promotes the next oldest image.
        //
        // NOTE: No PUT/PATCH on the resource — updating an image means
        // delete + re-upload. The only "update" is toggling primary,
        // handled by the dedicated route below.
        //
        // ──────────────────────────────────────────────────────────────
        Route::apiResource('parkings.images', ParkingImageController::class)
             ->only(['index', 'store', 'show', 'destroy']);

        // PATCH /api/v1/parkings/{parking}/images/{image}/primary
        //   → Owner: promote this image to be the primary thumbnail.
        //   → Automatically demotes the current primary image.
        //   → Validates {image} belongs to {parking}.
        Route::patch(
            'parkings/{parking}/images/{image}/primary',
            [ParkingImageController::class, 'setPrimary']
        )->name('parkings.images.primary');

        // ──────────────────────────────────────────────────────────────
        // OWNER BANK DETAILS
        // ──────────────────────────────────────────────────────────────
        //
        // Prefixed under /owner/ to make intent clear in the URL.
        // Only users with the 'owner' role can access these routes.
        // Role enforcement is done inside the controller methods.
        //
        // GET    /api/v1/owner/bank-detail
        //   → Get the authenticated owner's bank detail record.
        //   → Returns null data (not 404) if not yet submitted.
        //
        // POST   /api/v1/owner/bank-detail
        //   → Submit bank details for payout setup.
        //   → account_number is encrypted before storage (AES-256-CBC).
        //   → Status defaults to 'pending_verification' (admin must approve).
        //   → Returns 409 Conflict if owner already has bank details on file.
        //
        // GET    /api/v1/owner/bank-detail/{detail}
        //   → Get a specific record by ID.
        //   → Owner can only access their own record (forbidden otherwise).
        //
        // PUT    /api/v1/owner/bank-detail/{detail}
        //   → Update bank details (partial update supported).
        //   → SECURITY: ANY update resets status → 'pending_verification'.
        //     This forces admin re-verification before payouts resume.
        //   → account_number is re-encrypted if provided.
        //   → Response always returns masked_account_number (last 4 digits only).
        //
        // DELETE /api/v1/owner/bank-detail/{detail}
        //   → Soft-delete (preserves audit trail for historical payouts).
        //   → Owner cannot receive payouts until new details are submitted.
        //
        // ──────────────────────────────────────────────────────────────
        Route::prefix('owner')->name('owner.')->group(function () {
            Route::apiResource('bank-detail', OwnerBankDetailController::class)
                 ->parameters(['bank-detail' => 'detail']);
        });

    }); // end auth:sanctum (parking management)

    // ====================================================================
    // BOOKING MANAGEMENT ROUTES
    // ====================================================================

    Route::middleware('auth:sanctum')->group(function () {

        // QR Verification
        Route::post('bookings/verify-qr', [QRBookingController::class, 'verifyQR'])
            ->name('bookings.qr.verify');

        // Booking CRUD
        Route::apiResource('bookings', BookingController::class);

        // Cancel Booking
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->name('bookings.cancel');

        // Check In
        Route::post('bookings/{booking}/checkin', [CheckInController::class, 'store'])
            ->name('bookings.checkin.store');

        Route::get('bookings/{booking}/checkin', [CheckInController::class, 'show'])
            ->name('bookings.checkin.show');

        // Check Out
        Route::post('bookings/{booking}/checkout', [CheckOutController::class, 'store'])
            ->name('bookings.checkout.store');

        Route::get('bookings/{booking}/checkout', [CheckOutController::class, 'show'])
            ->name('bookings.checkout.show');

        // QR Code
        Route::get('bookings/{booking}/qr', [QRBookingController::class, 'show'])
            ->name('bookings.qr.show');

        Route::post('bookings/{booking}/qr', [QRBookingController::class, 'store'])
            ->name('bookings.qr.regenerate');

        // Booking History
        Route::get('bookings/{booking}/history', [BookingStatusHistoryController::class, 'index'])
            ->name('bookings.history.index');

        Route::get('bookings/{booking}/history/{id}', [BookingStatusHistoryController::class, 'show'])
            ->name('bookings.history.show');

    }); // end auth:sanctum (booking management)

    // ====================================================================
    // PAYMENT MANAGEMENT ROUTES
    // ====================================================================
    //
    // ┌──────────┬──────────────────────────────────────────┬───────────────────────────┐
    // │ Verb     │ URI                                       │ Action                    │
    // ├──────────┼──────────────────────────────────────────┼───────────────────────────┤
    // │ GET      │ /api/v1/payments/history                  │ PaymentController@history  │
    // │ POST     │ /api/v1/payments/initiate                 │ PaymentController@initiate │
    // │ POST     │ /api/v1/payments/verify                   │ PaymentController@verify   │
    // │ POST     │ /api/v1/payments/refund                   │ PaymentController@refund   │
    // │ GET      │ /api/v1/payments/{payment}                │ PaymentController@show     │
    // ├──────────┼──────────────────────────────────────────┼───────────────────────────┤
    // │ POST     │ /api/v1/payments/webhook  (PUBLIC)        │ PaymentController@webhook  │
    // └──────────┴──────────────────────────────────────────┴───────────────────────────┘
    //
    // WHY IS /webhook PUBLIC?
    //   Razorpay calls this endpoint directly from their servers.
    //   It cannot send a Bearer token. Security is handled inside
    //   webhook() by verifying the X-Razorpay-Signature header.
    //
    // VERIFY IN TERMINAL:
    //   php artisan route:list --path=api/v1/payments
    //
    // ====================================================================

    // ── Webhook — PUBLIC (no auth:sanctum) ────────────────────────────
    // Registered BEFORE the auth group so Razorpay can reach it
    // without a Bearer token.
    Route::post(
        'payments/webhook',
        [PaymentController::class, 'webhook']
    )->name('payments.webhook');

    // ── Payment Routes — PROTECTED ─────────────────────────────────────
    Route::middleware('auth:sanctum')
        ->prefix('payments')
        ->name('payments.')
        ->group(function () {

            // GET /api/v1/payments/history
            // Paginated payment history for the authenticated user.
            // Query params: ?status=success &payment_method=upi
            //               &date_from=2026-01-01 &date_to=2026-01-31
            //               &user_id=5 (admin only) &search=BK20260623
            //               &per_page=15
            // Named BEFORE {payment} so "history" is not matched as a route param.
            Route::get('history', [PaymentController::class, 'history'])
                ->name('history');

            // POST /api/v1/payments/initiate
            // Step 1: Create a Razorpay order. Returns order_id + amount to Flutter.
            // Body: { booking_id, payment_method, currency? }
            Route::post('initiate', [PaymentController::class, 'initiate'])
                ->name('initiate');

            // POST /api/v1/payments/verify
            // Step 2: Verify Razorpay signature after Flutter checkout completes.
            // Body: { payment_id, razorpay_payment_id, razorpay_order_id, razorpay_signature }
            // On success → booking: confirmed, QR code generated.
            Route::post('verify', [PaymentController::class, 'verify'])
                ->name('verify');

            // POST /api/v1/payments/refund
            // Initiate a full or partial refund via Razorpay.
            // Body: { payment_id, refund_amount?, reason?, notify_customer? }
            Route::post('refund', [PaymentController::class, 'refund'])
                ->name('refund');

            // GET /api/v1/payments/{payment}
            // Single payment detail — receipt screen, admin panel view.
            // Registered LAST so static named segments above match first.
            Route::get('{payment}', [PaymentController::class, 'show'])
                ->name('show');

        }); // end payments group

}); // end /v1 prefix

// ============================================================
// API HEALTH CHECK — PUBLIC
// ============================================================

Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Smart Parking API is running.',
        'data' => [
            'version'     => 'v1',
            'environment' => app()->environment(),
            'timestamp'   => now()->toISOString(),
        ],
    ]);
})->name('health');