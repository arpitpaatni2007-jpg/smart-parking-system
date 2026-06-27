<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * EarningsReportRequest
 * ============================================================
 *
 * Validates query parameters for the earnings report endpoint.
 *
 * ENDPOINT: GET /api/v1/reports/earnings
 *
 * WHO USES THIS:
 *   - Admin / Super Admin → sees platform-wide earnings with all filters
 *   - Parking Owner       → sees only their own earnings (parking_id
 *                           is auto-scoped in the controller, but they
 *                           may still filter by their own parking_id)
 *
 * ALL PARAMETERS ARE OPTIONAL:
 * If not provided, the controller defaults to the current month
 * matching the Admin Panel's default view.
 *
 * FUTURE SCALABILITY:
 *   - Add `group_by` parameter (day | week | month | year) to control
 *     chart data granularity when the chart module is enhanced.
 *   - Add `currency` parameter when multi-currency is supported.
 *   - Add `parking_type` filter (open | covered | multi_level) for
 *     revenue breakdown by parking type.
 */
class EarningsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role-based data scoping is handled inside the controller.
        // Both admins and owners can access this endpoint.
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Date Range ────────────────────────────────────────
            // Both dates are optional. Controller defaults to the
            // current month if neither is provided.
            'date_from' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:date_to', // Can't start after end
            ],
            'date_to' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
                'before_or_equal:today',   // Can't request future data
            ],

            // ── Scope Filters ─────────────────────────────────────
            // Admins can filter by specific parking or owner.
            // Owners can only filter by their own parkings (enforced in controller).
            'parking_id' => [
                'nullable',
                'integer',
                Rule::exists('parkings', 'id'),
            ],
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],

            // ── Payment Method Filter ─────────────────────────────
            // See breakdown by how customers paid.
            'payment_method' => [
                'nullable',
                'string',
                Rule::in(['upi', 'card', 'net_banking', 'wallet']),
            ],

            // ── Grouping ──────────────────────────────────────────
            // Controls how chart data is bucketed.
            // daily → one data point per day
            // monthly → one data point per month (for wide date ranges)
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
            'date_from.date_format'       => 'date_from must be in Y-m-d format (e.g. 2026-06-01).',
            'date_to.date_format'         => 'date_to must be in Y-m-d format (e.g. 2026-06-30).',
            'date_from.before_or_equal'   => 'date_from must be before or equal to date_to.',
            'date_to.after_or_equal'      => 'date_to must be after or equal to date_from.',
            'date_to.before_or_equal'     => 'date_to cannot be a future date.',
            'parking_id.exists'           => 'The selected parking does not exist.',
            'owner_id.exists'             => 'The selected owner does not exist.',
            'payment_method.in'           => 'payment_method must be one of: upi, card, net_banking, wallet.',
            'group_by.in'                 => 'group_by must be daily or monthly.',
            'per_page.max'                => 'per_page cannot exceed 100.',
        ];
    }

    /**
     * Apply sensible defaults after validation passes.
     * These defaults match the Admin Panel's initial load state.
     */
    protected function prepareForValidation(): void
    {
        // Default group_by to daily if not specified.
        if (!$this->filled('group_by')) {
            $this->merge(['group_by' => 'daily']);
        }

        // Default per_page to 15.
        if (!$this->filled('per_page')) {
            $this->merge(['per_page' => 15]);
        }
    }
}