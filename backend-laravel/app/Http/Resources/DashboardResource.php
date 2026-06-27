<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * DashboardResource
 * ============================================================
 *
 * Transforms the dashboard analytics data array into a clean,
 * structured JSON response.
 *
 * WHY THIS RESOURCE EXISTS:
 * The DashboardController builds a raw associative array of
 * platform statistics from multiple aggregation queries. This
 * resource shapes that raw data into a consistent, documented
 * response format so the Admin Panel and Owner App always
 * receive the same structure regardless of internal changes.
 *
 * DESIGN NOTE:
 * Unlike most resources that wrap an Eloquent model, this one
 * wraps a plain PHP array/object — a common Laravel pattern for
 * summary/analytics endpoints. We use `$this->resource` to
 * access the raw data passed in from the controller.
 *
 * USAGE IN CONTROLLER:
 *   return new DashboardResource([
 *       'stats'          => [...],
 *       'recent_bookings'=> [...],
 *       'chart_data'     => [...],
 *       'role'           => 'admin',
 *   ]);
 *
 * FUTURE SCALABILITY:
 *   - Add `alerts` array for system warnings (e.g. pending approvals
 *     exceeding SLA, failed payments needing attention).
 *   - Add `quick_links` computed from the user's role for a
 *     personalised dashboard experience.
 *   - Add `comparison` block for period-over-period changes
 *     (this week vs last week, this month vs last month).
 */
class DashboardResource extends JsonResource
{
    /**
     * Transform the dashboard data into a JSON-friendly array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            // ── Who is viewing this dashboard ─────────────────────
            // The role determines which cards and sections are shown.
            'role'            => $data['role'] ?? null,
            'generated_at'    => now()->toDateTimeString(),

            // ── Summary Stats Cards ───────────────────────────────
            // These power the top-row stats cards in the Admin/Owner
            // dashboard. Each card has a value and an optional
            // percentage change from the previous period.
            'stats'           => $data['stats'] ?? [],

            // ── Revenue Summary ───────────────────────────────────
            // Monetary values formatted as strings ("24,65,890.00")
            // to avoid floating-point issues in JSON.
            'revenue'         => $data['revenue'] ?? [],

            // ── Booking Breakdown ─────────────────────────────────
            // Count of bookings by status for the current period.
            // Powers the booking summary section of the dashboard.
            'booking_summary' => $data['booking_summary'] ?? [],

            // ── Chart Data ────────────────────────────────────────
            // Time-series data for the earnings/bookings trend chart.
            // Each entry: { date: "2026-06-01", amount: 4500.00, bookings: 45 }
            'chart_data'      => $data['chart_data'] ?? [],

            // ── Recent Activity ───────────────────────────────────
            // Last N bookings for the "Recent Bookings" table.
            'recent_bookings' => $data['recent_bookings'] ?? [],

            // ── Top Performers ────────────────────────────────────
            // Top parking locations by revenue (admin view).
            // Or: top earning days (owner view).
            'top_performers'  => $data['top_performers'] ?? [],

            // ── Pending Actions ───────────────────────────────────
            // Items needing admin attention:
            //   { parking_approvals: 3, support_tickets: 5, settlements: 2 }
            'pending_actions' => $data['pending_actions'] ?? [],

            // ── Date Range Used ───────────────────────────────────
            // Tells the frontend which period the stats cover.
            'period'          => $data['period'] ?? [],
        ];
    }
}