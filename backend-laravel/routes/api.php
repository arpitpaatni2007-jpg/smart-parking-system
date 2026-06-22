<?php

use App\Http\Controllers\Api\AuthController;
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
    // These endpoints are accessible to everyone.
    // No auth:sanctum middleware — users don't have a token yet.
    // ========================================================

    Route::prefix('auth')->name('auth.')->group(function () {

        /**
         * POST /api/v1/auth/register
         *
         * Register a new user or owner account.
         *
         * Request Body (JSON):
         *   {
         *     "name": "Arpit Sharma",
         *     "email": "arpit@example.com",
         *     "phone": "9876543210",
         *     "password": "SecurePass@123",
         *     "password_confirmation": "SecurePass@123",
         *     "role_id": 2
         *   }
         *
         * Success Response (201):
         *   {
         *     "success": true,
         *     "message": "Account created successfully.",
         *     "data": {
         *       "user": { ... },
         *       "token": "3|abc123...",
         *       "token_type": "Bearer"
         *     }
         *   }
         */
        Route::post('register', [AuthController::class, 'register'])->name('register');

        /**
         * POST /api/v1/auth/login
         *
         * Authenticate with email + password. Returns a Sanctum Bearer token.
         *
         * Request Body (JSON):
         *   {
         *     "email": "arpit@example.com",
         *     "password": "SecurePass@123"
         *   }
         *
         * Success Response (200):
         *   {
         *     "success": true,
         *     "message": "Login successful.",
         *     "data": {
         *       "user": { ... },
         *       "token": "4|xyz789...",
         *       "token_type": "Bearer"
         *     }
         *   }
         *
         * Error Response (401):
         *   {
         *     "success": false,
         *     "message": "Invalid email or password."
         *   }
         */
        Route::post('login', [AuthController::class, 'login'])->name('login');

        // ========================================================
        // AUTH ROUTES — PROTECTED (Sanctum token required)
        // ========================================================
        // These routes require a valid Bearer token in the header.
        // auth:sanctum middleware validates the token automatically.
        // If token is missing or invalid → 401 Unauthorized.
        // ========================================================

        Route::middleware('auth:sanctum')->group(function () {

            /**
             * POST /api/v1/auth/logout
             *
             * Revoke the current device's Sanctum token.
             * The user remains logged in on other devices.
             *
             * Request Header:
             *   Authorization: Bearer {token}
             *
             * Success Response (200):
             *   {
             *     "success": true,
             *     "message": "Logged out successfully.",
             *     "data": null
             *   }
             */
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');

            /**
             * GET /api/v1/auth/profile
             *
             * Retrieve the authenticated user's profile and role information.
             * Also used by the Flutter app as a token validity check on startup.
             *
             * Request Header:
             *   Authorization: Bearer {token}
             *
             * Success Response (200):
             *   {
             *     "success": true,
             *     "message": "Profile retrieved successfully.",
             *     "data": {
             *       "id": 1,
             *       "name": "Arpit Sharma",
             *       "email": "arpit@example.com",
             *       "phone": "9876543210",
             *       "role": {
             *         "id": 2,
             *         "name": "owner",
             *         "display_name": "Parking Owner"
             *       },
             *       "is_active": true,
             *       "email_verified": false,
             *       "created_at": "2025-01-15T10:30:00.000000Z"
             *     }
             *   }
             */
            Route::get('profile', [AuthController::class, 'profile'])->name('profile');

            /**
             * PUT /api/v1/auth/profile
             *
             * Update the authenticated user's profile details.
             *
             * Request Header:
             *   Authorization: Bearer {token}
             *
             * Request Body (JSON) — all fields optional:
             *   {
             *     "name": "Arpit Kumar",
             *     "phone": "9876500000"
             *   }
             *
             * Success Response (200):
             *   {
             *     "success": true,
             *     "message": "Profile updated successfully.",
             *     "data": { ... updated user ... }
             *   }
             */
            Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile.update');

            /**
             * POST /api/v1/auth/change-password
             *
             * Change the authenticated user's password.
             *
             * Request Header:
             *   Authorization: Bearer {token}
             *
             * Request Body (JSON):
             *   {
             *     "current_password": "OldPass@123",
             *     "password": "NewPass@456",
             *     "password_confirmation": "NewPass@456"
             *   }
             *
             * Success Response (200):
             *   {
             *     "success": true,
             *     "message": "Password changed successfully.",
             *     "data": null
             *   }
             *
             * Error Response (422):
             *   {
             *     "success": false,
             *     "message": "Current password is incorrect."
             *   }
             */
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('password.change');

        }); // end auth:sanctum group

    }); // end /auth prefix

    // ========================================================
    // FUTURE ROUTE GROUPS (to be added in next phases)
    // ========================================================

    /*
    |----------------------------------------------------------
    | PARKING ROUTES (Parking Module — Phase Next)
    |----------------------------------------------------------
    | Route::middleware('auth:sanctum')->prefix('parkings')
    |   ->name('parkings.')->group(function () {
    |     Route::get('/', [ParkingController::class, 'index']);
    |     Route::post('/', [ParkingController::class, 'store']);
    |     Route::get('{parking}', [ParkingController::class, 'show']);
    |     Route::put('{parking}', [ParkingController::class, 'update']);
    |     Route::delete('{parking}', [ParkingController::class, 'destroy']);
    | });
    |
    |----------------------------------------------------------
    | BOOKING ROUTES (Booking Module)
    |----------------------------------------------------------
    | Route::middleware('auth:sanctum')->prefix('bookings')
    |   ->name('bookings.')->group(function () {
    |     Route::get('/', [BookingController::class, 'index']);
    |     Route::post('/', [BookingController::class, 'store']);
    |     Route::get('{booking}', [BookingController::class, 'show']);
    |     Route::post('{booking}/cancel', [BookingController::class, 'cancel']);
    | });
    |
    |----------------------------------------------------------
    | PAYMENT ROUTES (Payment Module)
    |----------------------------------------------------------
    | Route::middleware('auth:sanctum')->prefix('payments')
    |   ->name('payments.')->group(function () {
    |     Route::post('initiate', [PaymentController::class, 'initiate']);
    |     Route::post('verify', [PaymentController::class, 'verify']);
    |     Route::get('history', [PaymentController::class, 'history']);
    | });
    */

}); // end /v1 prefix


// ============================================================
// API HEALTH CHECK — PUBLIC
// ============================================================
// Simple ping endpoint to confirm the API server is running.
// Used by monitoring tools and the Flutter app on first launch.
// ============================================================

Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Smart Parking API is running.',
        'data'    => [
            'version'     => 'v1',
            'environment' => app()->environment(),
            'timestamp'   => now()->toISOString(),
        ],
    ]);
})->name('health');