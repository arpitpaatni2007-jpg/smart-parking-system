<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * BookingReportRequest
 * ============================================================
 *
 * Validates query parameters for the bookings report endpoint.
 *
 * ENDPOINT: GET /api/v1/reports/bookings
 *
 * WHO USES THIS:
 *   - Admin / Super Admin → all bookings across the platform
 *   - Parking Owner       → bookings for their parkings only
 *                           (scoped automatically in the controller)
 *
 * REPORT PROVIDES:
 *   - Total bookings in the period
 *   - Breakdown by status (pending, confirmed, completed, cancelled)
 *   - Breakdown by vehicle type
 *   - Paginated list of individual bookings
 *   - Time-series data for booking trend chart
 *
 * FUTURE SCALABILITY:
 *   - Add `vehicle_type` filter for booking breakdown by vehicle category.
 *   - Add `booking_type` filter (instant | advance) when advance booking
 *     is differentiated in Phase 3.
 *   - Add `has_extra_charges` boolean filter for overtime analysis.
 */
class BookingReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Date Range ────────────────────────────────────────
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

            // ── Status Filter ─────────────────────────────────────
            // Filter to only a specific booking status.
            'booking_status' => [
                'nullable',
                'string',
                Rule::in([
                    'pending',
                    'confirmed',
                    'checked_in',
                    'completed',
                    'cancelled',
                    'no_show',
                ]),
            ],

            // ── Payment Status Filter ─────────────────────────────
            'payment_status' => [
                'nullable',
                'string',
                Rule::in(['unpaid', 'paid', 'failed', 'refunded']),
            ],

            // ── Scope Filters ─────────────────────────────────────
            // Admins can filter to a specific parking or city.
            'parking_id' => [
                'nullable',
                'integer',
                Rule::exists('parkings', 'id'),
            ],
            'city_id' => [
                'nullable',
                'integer',
                Rule::exists('cities', 'id'),
            ],

            // ── Vehicle Type Filter ───────────────────────────────
            'vehicle_type' => [
                'nullable',
                'string',
                Rule::in(['two_wheeler', 'four_wheeler', 'taxi', 'ev']),
            ],

            // ── Chart Grouping ────────────────────────────────────
            'group_by' => [
                'nullable',
                'string',
                Rule::in(['daily', 'monthly']),
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

    public function messages(): array
    {
        return [
            'date_from.date_format'     => 'date_from must be in Y-m-d format.',
            'date_to.date_format'       => 'date_to must be in Y-m-d format.',
            'date_from.before_or_equal' => 'date_from must be before or equal to date_to.',
            'date_to.after_or_equal'    => 'date_to must be after or equal to date_from.',
            'date_to.before_or_equal'   => 'date_to cannot be a future date.',
            'booking_status.in'         => 'Invalid booking status. Must be one of: pending, confirmed, checked_in, completed, cancelled, no_show.',
            'payment_status.in'         => 'Invalid payment status. Must be one of: unpaid, paid, failed, refunded.',
            'parking_id.exists'         => 'The selected parking does not exist.',
            'city_id.exists'            => 'The selected city does not exist.',
            'vehicle_type.in'           => 'Invalid vehicle type.',
            'group_by.in'               => 'group_by must be daily or monthly.',
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