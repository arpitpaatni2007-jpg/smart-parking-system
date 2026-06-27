<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * ParkingReportRequest
 * ============================================================
 *
 * Validates query parameters for the parking performance
 * report endpoint.
 *
 * ENDPOINT: GET /api/v1/reports/parkings
 *
 * WHO USES THIS:
 *   - Admin / Super Admin → all parkings across the platform
 *   - Parking Owner       → their own parkings only
 *                           (auto-scoped in the controller)
 *
 * REPORT PROVIDES:
 *   - Total parkings by status (approved, pending, rejected)
 *   - Occupancy rate per parking (bookings / total_slots)
 *   - Revenue per parking location
 *   - Top performing parkings by revenue / booking count
 *   - Paginated parking list with key metrics
 *   - City/state breakdown for admin view
 *
 * FUTURE SCALABILITY:
 *   - Add `occupancy_rate_min` / `occupancy_rate_max` range filters
 *     for finding under-utilised or over-demanded parkings.
 *   - Add `has_ev_slots` boolean filter when EV analytics are needed.
 *   - Add `facility_id` filter to see performance of parkings with
 *     specific amenities (e.g. "how do CCTV parkings perform?").
 */
class ParkingReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Both admins and parking owners can access this report.
        // Data scoping is enforced inside the controller.
        $role = $this->user()?->role?->name;
        return in_array($role, ['super_admin', 'admin', 'parking_owner']);
    }

    public function rules(): array
    {
        return [
            // ── Date Range ────────────────────────────────────────
            // Used to filter bookings that fall within this period.
            // (Parking records themselves don't have a date range —
            // we're reporting on their activity within this window.)
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

            // ── Approval Status Filter ────────────────────────────
            'status' => [
                'nullable',
                'string',
                Rule::in(['approved', 'pending', 'rejected', 'inactive']),
            ],

            // ── Geographic Filters ────────────────────────────────
            'city_id' => [
                'nullable',
                'integer',
                Rule::exists('cities', 'id'),
            ],
            'state_id' => [
                'nullable',
                'integer',
                Rule::exists('states', 'id'),
            ],

            // ── Owner Filter (admin only) ─────────────────────────
            // Admins can view the report scoped to a specific owner.
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],

            // ── Parking Type Filter ───────────────────────────────
            'parking_type' => [
                'nullable',
                'string',
                Rule::in(['open', 'covered', 'multi_level', 'basement']),
            ],

            // ── Sorting ───────────────────────────────────────────
            // How to sort the paginated rows.
            'sort_by' => [
                'nullable',
                'string',
                Rule::in([
                    'revenue',        // highest revenue first
                    'bookings',       // most bookings first
                    'occupancy_rate', // highest occupancy first
                    'name',           // alphabetical
                    'created_at',     // newest first
                ]),
            ],
            'sort_direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],

            // ── Search ────────────────────────────────────────────
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
     * Fail authorization with a JSON response, not a redirect.
     */
    public function failedAuthorization(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to access parking reports.',
            'data'    => null,
        ], 403);
    }

    public function messages(): array
    {
        return [
            'date_from.date_format'     => 'date_from must be in Y-m-d format.',
            'date_to.date_format'       => 'date_to must be in Y-m-d format.',
            'date_from.before_or_equal' => 'date_from must be before or equal to date_to.',
            'date_to.after_or_equal'    => 'date_to must be after or equal to date_from.',
            'date_to.before_or_equal'   => 'date_to cannot be a future date.',
            'status.in'                 => 'status must be one of: approved, pending, rejected, inactive.',
            'city_id.exists'            => 'The selected city does not exist.',
            'state_id.exists'           => 'The selected state does not exist.',
            'owner_id.exists'           => 'The selected owner does not exist.',
            'parking_type.in'           => 'Invalid parking type.',
            'sort_by.in'                => 'sort_by must be one of: revenue, bookings, occupancy_rate, name, created_at.',
            'sort_direction.in'         => 'sort_direction must be asc or desc.',
            'search.max'                => 'Search term must not exceed 100 characters.',
            'per_page.max'              => 'per_page cannot exceed 100.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('sort_by')) {
            $this->merge(['sort_by' => 'revenue']);
        }
        if (!$this->filled('sort_direction')) {
            $this->merge(['sort_direction' => 'desc']);
        }
        if (!$this->filled('per_page')) {
            $this->merge(['per_page' => 15]);
        }
    }
}