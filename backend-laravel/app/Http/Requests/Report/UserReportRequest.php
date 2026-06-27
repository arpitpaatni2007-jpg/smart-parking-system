<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UserReportRequest
 * ============================================================
 *
 * Validates query parameters for the user report endpoint.
 *
 * ENDPOINT: GET /api/v1/reports/users
 *
 * ACCESS: Admin and Super Admin only.
 * This endpoint is intentionally NOT accessible to Parking Owners
 * as it contains platform-wide user data.
 *
 * REPORT PROVIDES:
 *   - Total registered users in the period
 *   - New registrations by day/month (trend chart)
 *   - Breakdown by role (customer, owner, manager)
 *   - Active vs inactive vs blocked users
 *   - Top users by booking count / total spend
 *   - Paginated user list with key metrics
 *
 * FUTURE SCALABILITY:
 *   - Add `has_bookings` boolean filter to find dormant users
 *     (registered but never booked) for re-engagement campaigns.
 *   - Add `city_id` filter when user location data is captured.
 *   - Add `registration_source` filter (app | google | otp) once
 *     auth method tracking is added to the users table.
 */
class UserReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can access user reports.
        // This is enforced again inside the controller, but we
        // do a quick role check here as a first-line guard.
        $role = $this->user()?->role?->name;
        return in_array($role, ['super_admin', 'admin']);
    }

    public function rules(): array
    {
        return [
            // ── Date Range ────────────────────────────────────────
            // Filters by user registration date (created_at).
            'date_from' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:date_to',
            ],
            'date_to' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
                'before_or_equal:today',
            ],

            // ── Role Filter ───────────────────────────────────────
            // Filter by user role to see only customers, only owners, etc.
            'role' => [
                'nullable',
                'string',
                Rule::in(['customer', 'parking_owner', 'parking_manager', 'admin', 'super_admin']),
            ],

            // ── Account Status Filter ─────────────────────────────
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive', 'banned']),
            ],

            // ── Verification Filter ───────────────────────────────
            // Find users who have or have not verified their account.
            'is_verified' => [
                'nullable',
                'boolean',
            ],

            // ── Chart Grouping ────────────────────────────────────
            'group_by' => [
                'nullable',
                'string',
                Rule::in(['daily', 'monthly']),
            ],

            // ── Search ────────────────────────────────────────────
            // Search by name, email, or phone in the user list rows.
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            // ── Pagination ────────────────────────────────────────
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Handle failed authorization — return a proper JSON 403 response
     * instead of Laravel's default redirect or HTML error page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function messages(): array
    {
        return [
            'date_from.date_format'     => 'date_from must be in Y-m-d format.',
            'date_to.date_format'       => 'date_to must be in Y-m-d format.',
            'date_from.before_or_equal' => 'date_from must be before or equal to date_to.',
            'date_to.after_or_equal'    => 'date_to must be after or equal to date_from.',
            'date_to.before_or_equal'   => 'date_to cannot be a future date.',
            'role.in'                   => 'Invalid role filter.',
            'status.in'                 => 'status must be one of: active, inactive, banned.',
            'is_verified.boolean'       => 'is_verified must be true or false.',
            'group_by.in'               => 'group_by must be daily or monthly.',
            'search.max'                => 'Search term must not exceed 100 characters.',
            'per_page.max'              => 'per_page cannot exceed 100.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('group_by')) {
            $this->merge(['group_by' => 'daily']);
        }
        if (!$this->filled('per_page')) {
            $this->merge(['per_page' => 15]);
        }
    }
}