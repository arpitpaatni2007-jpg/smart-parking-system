<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * ============================================================
 * AuthController
 * ============================================================
 *
 * Handles all authentication-related API endpoints:
 *
 *   POST   /api/v1/auth/register          → Create new user account
 *   POST   /api/v1/auth/login             → Authenticate + issue Sanctum token
 *   POST   /api/v1/auth/logout            → Revoke current Sanctum token
 *   GET    /api/v1/auth/profile           → Get authenticated user profile
 *   PUT    /api/v1/auth/profile           → Update authenticated user profile
 *   POST   /api/v1/auth/change-password   → Change authenticated user password
 *
 * DEPENDENCIES:
 *   - Laravel Sanctum     → Token-based API authentication
 *   - Spatie Permission   → Role assignment on register
 *   - App\Traits\ApiResponse       → Consistent JSON response envelope
 *   - App\Http\Requests\Auth\*     → Form request validation
 *   - App\Http\Resources\UserResource → Response transformation (hides password etc.)
 *
 * SECURITY PRINCIPLES APPLIED:
 *   1. Passwords are NEVER returned in any response
 *   2. Generic error message on login failure — prevents user enumeration attacks
 *   3. DB::transaction() on register — prevents partial inserts (user without role)
 *   4. Only current token revoked on logout — preserves other device sessions
 *   5. Role whitelist — admins cannot self-register via public API
 *   6. current_password verified before allowing password change
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Roles allowed to self-register via the public API.
     *
     * 'admin' is intentionally excluded — admin accounts must be
     * created via seeders or a protected internal command.
     * Extend this array as new public roles are added.
     */
    private const ALLOWED_SELF_REGISTER_ROLES = ['user', 'owner'];

    // =========================================================
    // REGISTER
    // =========================================================

    /**
     * Register a new user account.
     *
     * FLOW:
     *   1. RegisterRequest validates all fields (auto-runs before this method)
     *   2. Role whitelist check — block admin self-registration
     *   3. DB transaction: create User + assign Role atomically
     *   4. Issue Sanctum token (user is logged in immediately after registration)
     *   5. Return UserResource + token
     *
     * HTTP STATUS:
     *   201 Created       → Registration successful
     *   403 Forbidden     → Tried to register as a restricted role
     *   500 Server Error  → Unexpected DB/system failure
     *
     * @param  \App\Http\Requests\Auth\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // ── Verify the requested role is publicly registerable ───────────
            $role = Role::findById($request->role_id, 'sanctum');

            if (! in_array($role->name, self::ALLOWED_SELF_REGISTER_ROLES)) {
                return $this->forbiddenResponse(
                    'You cannot self-register with the selected role.'
                );
            }

            // ── Create User + assign Role inside a single DB transaction ─────
            // If role assignment fails, the user row is also rolled back.
            // This prevents orphaned users with no role in the database.
            $user = DB::transaction(function () use ($request, $role) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'phone'    => $request->phone,
                    'password' => Hash::make($request->password), // ALWAYS hash — never store plain text
                    'status'   => 'active',
                ]);

                // Spatie Permission — inserts into model_has_roles pivot table
                $user->assignRole($role);

                return $user;
            });

            // ── Issue Sanctum token — user is logged in post-registration ────
            $tokenName  = $role->name . '-auth-token';
            $plainToken = $user->createToken($tokenName)->plainTextToken;

            // ── Eager-load roles so UserResource can include role data ────────
            $user->load('roles');

            return $this->createdResponse(
                [
                    'user'       => new UserResource($user),
                    'token'      => $plainToken,
                    'token_type' => 'Bearer',
                ],
                'Account created successfully. Welcome to Smart Parking!'
            );

        } catch (Throwable $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->serverErrorResponse(
                'Registration failed. Please try again later.'
            );
        }
    }

    // =========================================================
    // LOGIN
    // =========================================================

    /**
     * Authenticate a user and issue a Sanctum API token.
     *
     * FLOW:
     *   1. LoginRequest validates fields are present and properly formatted
     *   2. Find user by email
     *   3. Verify password with Hash::check() (constant-time bcrypt compare)
     *   4. Check account status is 'active'
     *   5. Issue new Sanctum token
     *   6. Return UserResource + token
     *
     * SECURITY — USER ENUMERATION PREVENTION:
     *   We return the same error message whether the email doesn't exist
     *   OR the password is wrong. This prevents an attacker from probing
     *   which emails are registered in your database.
     *
     * HTTP STATUS:
     *   200 OK            → Login successful
     *   401 Unauthorized  → Invalid credentials OR inactive account
     *   500 Server Error  → Unexpected failure
     *
     * @param  \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // ── Find user by email ───────────────────────────────────────────
            $user = User::where('email', $request->email)->first();

            // ── Verify credentials — same message for both failure modes ─────
            // "!$user" covers email not found.
            // "!Hash::check()" covers wrong password.
            // Both return 401 with identical message — no enumeration clue.
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return $this->unauthorizedResponse(
                    'Invalid email or password. Please check your credentials.'
                );
            }

            // ── Verify account is active ─────────────────────────────────────
            if ($user->status !== 'active') {
                return $this->unauthorizedResponse(
                    'Your account has been deactivated. Please contact support.'
                );
            }

            // ── Issue new Sanctum token ──────────────────────────────────────
            // Each device login creates a separate token — they can be
            // individually revoked (e.g. "logout this device only").
            $plainToken = $user->createToken('auth-token')->plainTextToken;

            // ── Load roles for UserResource ──────────────────────────────────
            $user->load('roles');

            return $this->successResponse(
                [
                    'user'       => new UserResource($user),
                    'token'      => $plainToken,
                    'token_type' => 'Bearer',
                ],
                'Login successful. Welcome back!'
            );

        } catch (Throwable $e) {
            Log::error('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Login failed. Please try again later.'
            );
        }
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    /**
     * Revoke the current device's Sanctum token (logout).
     *
     * DESIGN DECISION — SINGLE TOKEN REVOCATION:
     *   We revoke ONLY the token used in this HTTP request.
     *   If the user is logged into 3 devices (phone, tablet, web),
     *   only their current device is logged out.
     *
     *   To log out from ALL devices simultaneously, replace with:
     *     $request->user()->tokens()->delete();
     *
     * HOW IT WORKS:
     *   currentAccessToken() returns the Laravel\Sanctum\PersonalAccessToken
     *   model that matched the Bearer token in the Authorization header.
     *   Calling delete() removes it from personal_access_tokens table.
     *   Subsequent requests with that token will get 401 Unauthorized.
     *
     * HTTP STATUS:
     *   200 OK            → Logout successful
     *   500 Server Error  → Unexpected failure
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(
                null,
                'Logged out successfully.'
            );

        } catch (Throwable $e) {
            Log::error('Logout failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->serverErrorResponse('Logout failed. Please try again.');
        }
    }

    // =========================================================
    // GET PROFILE
    // =========================================================

    /**
     * Return the authenticated user's profile + role information.
     *
     * USES:
     *   1. Flutter app Profile screen — display user details
     *   2. App startup token validation — call this to verify token is still valid
     *   3. Role re-check — confirm user's role after app resumes from background
     *
     * auth:sanctum middleware already verified the Bearer token.
     * $request->user() returns the authenticated User model.
     *
     * HTTP STATUS:
     *   200 OK           → Profile returned
     *   401 Unauthorized → No token / invalid token (handled by middleware)
     *   500 Server Error → Unexpected failure
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            // Eager-load roles to avoid N+1 query inside UserResource
            $user = $request->user()->load('roles');

            return $this->successResponse(
                new UserResource($user),
                'Profile retrieved successfully.'
            );

        } catch (Throwable $e) {
            Log::error('Profile fetch failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to retrieve profile. Please try again.'
            );
        }
    }

    // =========================================================
    // UPDATE PROFILE
    // =========================================================

    /**
     * Update the authenticated user's profile details.
     *
     * PARTIAL UPDATE SUPPORT:
     *   All fields are optional — the user only sends what they want to change.
     *   We use $request->only([...]) to extract only the fields that were
     *   actually sent, then filter out null values before updating.
     *
     *   Example: sending only {"name": "New Name"} updates ONLY the name.
     *   Email and phone remain unchanged.
     *
     * UNIQUE FIELD HANDLING:
     *   UpdateProfileRequest uses Rule::unique()->ignore($userId) so the
     *   user can submit their own email/phone without a "already taken" error.
     *
     * HTTP STATUS:
     *   200 OK            → Profile updated
     *   422 Unprocessable → Validation failed (handled by UpdateProfileRequest)
     *   500 Server Error  → Unexpected failure
     *
     * @param  \App\Http\Requests\Auth\UpdateProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Extract only the fields that were actually sent in the request.
            // array_filter removes null/empty values so absent fields
            // don't overwrite existing data with null.
            $updateData = array_filter(
                $request->only(['name', 'email', 'phone']),
                fn ($value) => ! is_null($value)
            );

            if (empty($updateData)) {
                return $this->errorResponse(
                    'No update data provided. Please send at least one field to update.',
                    400
                );
            }

            $user->update($updateData);

            // Reload the user + roles to return fresh data
            $user->load('roles');

            return $this->successResponse(
                new UserResource($user),
                'Profile updated successfully.'
            );

        } catch (Throwable $e) {
            Log::error('Profile update failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->serverErrorResponse(
                'Failed to update profile. Please try again.'
            );
        }
    }

    // =========================================================
    // CHANGE PASSWORD
    // =========================================================

    /**
     * Change the authenticated user's password.
     *
     * FLOW:
     *   1. ChangePasswordRequest validates field formats
     *   2. Verify current_password matches what's stored (Hash::check)
     *   3. Hash and save the new password
     *   4. Revoke ALL tokens — forces re-login on all devices
     *      (this is a security best practice after password change)
     *   5. Issue a fresh token for the current device
     *   6. Return new token so the app doesn't have to log in again
     *
     * WHY REVOKE ALL TOKENS ON PASSWORD CHANGE?
     *   If an attacker obtained a stolen token, changing the password
     *   should invalidate that token. Revoking all tokens ensures the
     *   password change effectively "logs out" any unauthorized sessions.
     *
     * HTTP STATUS:
     *   200 OK            → Password changed, new token returned
     *   422 Unprocessable → Current password incorrect (business rule)
     *   500 Server Error  → Unexpected failure
     *
     * @param  \App\Http\Requests\Auth\ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // ── Verify the current password is correct ───────────────────────
            // This is a 422 (unprocessable), not 401, because the user IS
            // authenticated — they just provided the wrong current password.
            if (! Hash::check($request->current_password, $user->password)) {
                return $this->validationErrorResponse(
                    ['current_password' => ['The current password you entered is incorrect.']],
                    'Current password is incorrect.'
                );
            }

            // ── Save the new hashed password ─────────────────────────────────
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // ── Revoke ALL tokens — security measure post-password-change ────
            $user->tokens()->delete();

            // ── Issue a fresh token for the current device ───────────────────
            // So the user doesn't have to log in again right after changing their password
            $plainToken = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse(
                [
                    'token'      => $plainToken,
                    'token_type' => 'Bearer',
                ],
                'Password changed successfully. Please use your new password to login on other devices.'
            );

        } catch (Throwable $e) {
            Log::error('Password change failed', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage(),
            ]);

                return $this->serverErrorResponse(
                    'Failed to change password. Please try again.'
                );
            }
        }
    }