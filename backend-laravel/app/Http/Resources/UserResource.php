<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * UserResource — API Resource
 * ============================================================
 *
 * WHY API RESOURCES?
 * An API Resource is a transformation layer between your
 * Eloquent Model and the JSON response sent to the client.
 *
 * Instead of returning the raw User model (which might expose
 * sensitive fields like `password`, internal timestamps, or
 * pivot data), the Resource lets you explicitly control:
 *   - WHAT fields are included
 *   - HOW they are formatted
 *   - WHAT relationships are included (and in what shape)
 *
 * USAGE IN CONTROLLER:
 *   return new UserResource($user);           // single user
 *   return UserResource::collection($users);  // list of users
 *
 * EXAMPLE OUTPUT:
 * {
 *   "id": 1,
 *   "name": "Arpit Sharma",
 *   "email": "arpit@example.com",
 *   "phone": "9876543210",
 *   "role": {
 *     "id": 2,
 *     "name": "owner",
 *     "display_name": "Parking Owner"
 *   },
 *   "created_at": "2025-01-15T10:30:00.000000Z"
 * }
 *
 * NOTICE: `password`, `remember_token`, `updated_at` are NOT included.
 * This is intentional — never send sensitive fields to the client.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * This array becomes the JSON body of the response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ── IDENTITY ──────────────────────────────────────────────────
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,

            // ── ROLE INFORMATION ──────────────────────────────────────────
            /**
             * Include role details so the Flutter app knows what
             * screens/features to show after login.
             *
             * whenLoaded(): only include role data if it was eager-loaded
             * in the controller (e.g. $user->load('roles')).
             * If not loaded, this key is omitted from the response entirely
             * (prevents N+1 queries if forgotten).
             *
             * NOTE: Using Spatie's roles (many-to-many), so we grab the first
             * role. If your system supports multiple roles per user, return
             * the full collection instead of ->first().
             */
            'role' => $this->whenLoaded('roles', function () {
                $role = $this->roles->first();
                return $role ? [
                    'id'           => $role->id,
                    'name'         => $role->name,
                    'display_name' => $role->display_name ?? ucfirst($role->name),
                ] : null;
            }),

            // ── STATUS ────────────────────────────────────────────────────
            /**
             * Whether the account is active.
             * FUTURE: Add email_verified_at check here too.
             */
            'is_active'        => $this->status === 'active',
            'email_verified'   => ! is_null($this->email_verified_at),

            // ── TIMESTAMPS ────────────────────────────────────────────────
            /**
             * ISO 8601 format — works universally across all clients.
             * The Flutter app can parse this with DateTime.parse().
             */
            'created_at' => $this->created_at?->toISOString(),

            // Deliberately NOT including:
            //   - password (never send to client)
            //   - remember_token (internal Laravel field)
            //   - updated_at (not needed by client in most cases)
            //   - pivot data (raw DB internals)
        ];
    }
}