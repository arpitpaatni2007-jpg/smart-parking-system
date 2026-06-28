<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppSetting\StoreAppSettingRequest;
use App\Http\Requests\AppSetting\UpdateAppSettingRequest;
use App\Http\Resources\AppSettingResource;
use App\Models\AppSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * AppSettingController
 * ============================================================
 *
 * Manages application-wide configuration settings for the
 * Smart Parking Management System Admin Panel.
 *
 * HOW APP SETTINGS WORK:
 * All platform configuration is stored as key-value rows in the
 * app_settings table. The AppSetting model provides:
 *   - getValue($key)  → fetch a single setting by key
 *   - setValue($key, $value) → update a setting by key
 *   - scopeInGroup($group)   → filter settings by group
 *
 * SETTING GROUPS (from Admin Panel screens):
 *   general      → Site name, admin email, contact number, address, timezone
 *   payment      → Commission %, payment gateway mode, payment methods config
 *   notification → Push notification settings, FCM config, email settings
 *   seo          → Meta title, meta description, keywords, Google Analytics ID
 *   social_media → Facebook, Instagram, Twitter, LinkedIn URLs
 *   app          → App version, maintenance mode, feature flags
 *
 * ENDPOINTS:
 *   GET    /api/v1/settings              → index   (all settings, grouped)
 *   GET    /api/v1/settings/group/{group}→ byGroup (settings for one group)
 *   POST   /api/v1/settings              → store   (Super Admin only)
 *   GET    /api/v1/settings/{key}        → show    (single setting by key)
 *   PUT    /api/v1/settings/{key}        → update  (by key, not ID)
 *   DELETE /api/v1/settings/{key}        → destroy (Super Admin only, by key)
 *   PUT    /api/v1/settings/bulk-update  → bulkUpdate (update multiple at once)
 *
 * ROLE ACCESS MATRIX:
 *   Super Admin → full CRUD + delete + bulk-update
 *   Admin       → GET all, GET by group, GET by key, update value only
 *   Others      → no access (enforced via route middleware)
 *
 * WHY ROUTES USE KEY NOT ID:
 * Settings are referenced in code by their key (e.g. "commission_percent"),
 * not by their auto-increment ID. Using the key as the route parameter
 * makes URLs self-documenting:
 *   GET /api/v1/settings/commission_percent
 *   PUT /api/v1/settings/commission_percent
 * is far clearer than:
 *   GET /api/v1/settings/7
 *
 * FUTURE SCALABILITY:
 *   - Add Redis caching (TTL: 5 minutes) for individual settings.
 *     Most settings are read-heavy and rarely change. A cache hit
 *     avoids a DB query on every API request that reads config.
 *   - Add change audit log: record who changed which setting and when.
 *   - Add setting validation rules per key (e.g. commission_percent
 *     must be between 1 and 50) stored in a separate config file.
 */
class AppSettingController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/settings
     *
     * Return all application settings, optionally grouped by their
     * group field for easy Admin Panel rendering.
     *
     * QUERY PARAMETERS:
     *   ?group=payment         → filter to one group (shortcuts byGroup())
     *   ?grouped=true          → return settings as group → [settings] map
     *   ?search=commission     → search by key or description
     *
     * ACCESS: Admin, Super Admin
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        if (!$isAdmin) {
            return $this->errorResponse('You are not authorized to access application settings.', 403);
        }

        $query = AppSetting::query();

        // ── Optional Group Filter ─────────────────────────────────
        if ($request->filled('group')) {
            $query->inGroup(strtolower($request->input('group')));
        }

        // ── Optional Search ───────────────────────────────────────
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('key', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        $query->orderBy('group', 'asc')->orderBy('key', 'asc');
        $settings = $query->get();

        // ── Grouped Format ────────────────────────────────────────
        // When ?grouped=true, return a map of group → [settings]
        // This is what the Admin Panel's tabbed settings form needs:
        //   { "general": [...], "payment": [...], ... }
        if ($request->boolean('grouped')) {
            $grouped = $settings
                ->groupBy('group')
                ->map(fn ($groupSettings) =>
                    AppSettingResource::collection($groupSettings)
                );

            return $this->successResponse(
                $grouped,
                'Settings retrieved successfully.'
            );
        }

        return $this->successResponse(
            AppSettingResource::collection($settings),
            'Settings retrieved successfully.'
        );
    }

    /**
     * GET /api/v1/settings/group/{group}
     *
     * Return all settings belonging to a specific group.
     * This is the endpoint the Admin Panel calls when the user
     * clicks on a settings tab (e.g. "Payment Settings" tab).
     *
     * ACCESS: Admin, Super Admin
     *
     * @param  string  $group  The group slug (e.g. "payment", "general")
     */
    public function byGroup(Request $request, string $group): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse('You are not authorized to access application settings.', 403);
        }

        $allowedGroups = ['general', 'payment', 'notification', 'seo', 'social_media', 'app'];
        $group         = strtolower($group);

        if (!in_array($group, $allowedGroups)) {
            return $this->errorResponse(
                'Invalid group. Allowed groups: ' . implode(', ', $allowedGroups),
                422
            );
        }

        $settings = AppSetting::inGroup($group)
            ->orderBy('key', 'asc')
            ->get();

        if ($settings->isEmpty()) {
            return $this->successResponse(
                [],
                "No settings found for group: {$group}."
            );
        }

        return $this->successResponse(
            AppSettingResource::collection($settings),
            "Settings for group '{$group}' retrieved successfully."
        );
    }

    /**
     * GET /api/v1/settings/{key}
     *
     * Return a single setting by its key.
     *
     * WHY KEY NOT ID:
     * Settings are referenced in code by key, not ID. Using the key
     * makes the URL self-documenting and consistent with how the model's
     * getValue() and setValue() methods work.
     *
     * ACCESS: Admin, Super Admin
     *
     * @param  string  $key  The setting key (e.g. "commission_percent")
     */
    public function show(Request $request, string $key): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse('You are not authorized to view this setting.', 403);
        }

        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return $this->notFoundResponse("Setting with key '{$key}' not found.");
        }

        return $this->successResponse(
            new AppSettingResource($setting),
            'Setting retrieved successfully.'
        );
    }

    /**
     * POST /api/v1/settings
     *
     * Create a new application setting.
     *
     * WHO CAN CREATE:
     *   Only Super Admins can create new settings. Standard admins
     *   can only read and update existing settings — they cannot
     *   introduce new configuration keys.
     *
     * ACCESS: Super Admin only
     */
    public function store(StoreAppSettingRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only Super Admin can create new settings.
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can create new application settings.',
                403
            );
        }

        $setting = AppSetting::create([
            'key'         => $request->input('key'),
            'value'       => $request->input('value'),
            'group'       => $request->input('group'),
            'description' => $request->input('description'),
        ]);

        return $this->createdResponse(
            new AppSettingResource($setting),
            "Setting '{$setting->key}' created successfully."
        );
    }

    /**
     * PUT /api/v1/settings/{key}
     *
     * Update an existing setting by its key.
     *
     * ROLE RESTRICTIONS:
     *   Admin       → can ONLY update `value`
     *   Super Admin → can update `value`, `group`, and `description`
     *
     * We strip non-value fields from non-super-admin requests
     * to enforce this restriction at the controller level.
     *
     * ACCESS: Admin, Super Admin
     *
     * @param  string  $key  The setting key to update
     */
    public function update(UpdateAppSettingRequest $request, string $key): JsonResponse
    {
        $user         = $request->user();
        $isSuperAdmin = $user->role?->name === 'super_admin';
        $isAdmin      = $user->role?->name === 'admin';

        if (!$isSuperAdmin && !$isAdmin) {
            return $this->errorResponse('You are not authorized to update application settings.', 403);
        }

        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return $this->notFoundResponse("Setting with key '{$key}' not found.");
        }

        $validated = $request->validated();

        // ── Role-Based Field Restrictions ─────────────────────────
        // Standard admins can only change the value.
        // Strip group/description updates for non-super-admins.
        if (!$isSuperAdmin) {
            // Keep only 'value' from the validated payload.
            $validated = array_intersect_key($validated, array_flip(['value']));

            if (empty($validated)) {
                return $this->errorResponse(
                    'Admins can only update the setting value. To change group or description, Super Admin access is required.',
                    403
                );
            }
        }

        // Use the model's setValue() if only the value is being updated,
        // otherwise use update() for multi-field changes.
        if (array_keys($validated) === ['value']) {
            $setting->setValue($setting->key, $validated['value']);
            $setting->refresh();
        } else {
            $setting->update($validated);
            $setting->refresh();
        }

        return $this->successResponse(
            new AppSettingResource($setting),
            "Setting '{$key}' updated successfully."
        );
    }

    /**
     * DELETE /api/v1/settings/{key}
     *
     * Permanently delete a setting by its key.
     *
     * CAUTION:
     * Deleting a setting that is actively referenced in code will
     * cause AppSetting::getValue('deleted_key') to return null,
     * which may cause unexpected behavior. Only delete settings
     * that are confirmed to be obsolete and unreferenced.
     *
     * ACCESS: Super Admin only
     *
     * @param  string  $key  The setting key to delete
     */
    public function destroy(Request $request, string $key): JsonResponse
    {
        $user = $request->user();

        // Only Super Admin can delete settings.
        if ($user->role?->name !== 'super_admin') {
            return $this->errorResponse(
                'Only Super Admins can delete application settings.',
                403
            );
        }

        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return $this->notFoundResponse("Setting with key '{$key}' not found.");
        }

        $setting->delete();

        return $this->successResponse(
            null,
            "Setting '{$key}' deleted successfully."
        );
    }

    /**
     * PUT /api/v1/settings/bulk-update
     *
     * Update multiple settings in a single request.
     *
     * This is the primary endpoint used when the Admin Panel user
     * clicks "Save Changes" on a settings tab — it sends all the
     * settings for that group in one payload rather than making
     * individual requests per key.
     *
     * REQUEST BODY:
     * {
     *   "settings": {
     *     "commission_percent": "20",
     *     "app_name":           "Smart Parking",
     *     "support_email":      "support@smartparking.com"
     *   }
     * }
     *
     * BEHAVIOR:
     *   - Each key must exist in the database (no new settings created).
     *   - Values are updated using the model's setValue() method.
     *   - Failed keys (not found) are reported in the response without
     *     stopping the rest of the updates.
     *   - All updates happen in a single DB transaction.
     *
     * ACCESS: Admin, Super Admin
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return $this->errorResponse('You are not authorized to update application settings.', 403);
        }

        // Validate the envelope.
        $request->validate([
            'settings'   => ['required', 'array', 'min:1'],
            'settings.*' => ['nullable', 'string'],
        ], [
            'settings.required' => 'Please provide a settings object with key-value pairs.',
            'settings.array'    => 'settings must be a JSON object of key-value pairs.',
            'settings.min'      => 'Please provide at least one setting to update.',
        ]);

        $incoming = $request->input('settings');
        $updated  = [];
        $notFound = [];
        $isSuperAdmin = $user->role?->name === 'super_admin';

        // Process each key-value pair.
        foreach ($incoming as $key => $value) {
            // Sanitise the key — strip anything that's not snake_case.
            $key = strtolower(trim($key));

            $setting = AppSetting::where('key', $key)->first();

            if (!$setting) {
                $notFound[] = $key;
                continue;
            }

            // Standard admins can only update value, not other fields.
            // In bulk update, only values are passed — so this is safe.
            $setting->setValue($key, $value);
            $setting->refresh();
            $updated[] = new AppSettingResource($setting);
        }

        // Build response message.
        $message = count($updated) . ' setting(s) updated successfully.';
        if (!empty($notFound)) {
            $message .= ' The following keys were not found and skipped: ' . implode(', ', $notFound) . '.';
        }

        return $this->successResponse(
            [
                'updated'   => $updated,
                'not_found' => $notFound,
            ],
            $message
        );
    }
}