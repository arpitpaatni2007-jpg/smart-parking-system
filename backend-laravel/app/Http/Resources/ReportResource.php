<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================
 * ReportResource
 * ============================================================
 *
 * Transforms a paginated or summarised report data structure
 * into a clean, consistent JSON response.
 *
 * WHY THIS RESOURCE EXISTS:
 * All report endpoints (earnings, bookings, users, parkings,
 * commission, owner-earnings) return the same outer envelope
 * shape — a summary block + paginated rows + metadata. This
 * single resource handles all of them cleanly.
 *
 * The inner `rows` content varies per report type, but the
 * envelope is always identical, which makes the Admin Panel
 * and Owner App easier to build (one generic report renderer).
 *
 * ENVELOPE SHAPE:
 * {
 *   "report_type":    "earnings",
 *   "period":         { "from": "2026-06-01", "to": "2026-06-30" },
 *   "summary":        { "total_earnings": "24,65,890.00", ... },
 *   "rows":           { paginated data },
 *   "generated_at":   "2026-06-23 10:30:00"
 * }
 *
 * USAGE IN CONTROLLER:
 *   return new ReportResource([
 *       'report_type' => 'earnings',
 *       'period'      => ['from' => $from, 'to' => $to],
 *       'summary'     => $summary,
 *       'rows'        => $paginatedRows,
 *   ]);
 *
 * FUTURE SCALABILITY:
 *   - Add `export_url` field pointing to a signed S3 URL once
 *     the async export job has completed.
 *   - Add `filters_applied` block to echo back which filters
 *     were active, helping users understand what they're seeing.
 *   - Add `chart_data` block for inline sparklines per report.
 */
class ReportResource extends JsonResource
{
    /**
     * Transform the report data into a JSON-friendly array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            // ── Report Identity ───────────────────────────────────
            // Tells the frontend which report this response belongs to.
            // Values: earnings | bookings | users | parkings |
            //         owner_earnings | commission
            'report_type'  => $data['report_type'] ?? null,

            // ── Date Range ────────────────────────────────────────
            // The from/to range the data covers.
            // { "from": "2026-06-01", "to": "2026-06-30" }
            'period'       => $data['period'] ?? [],

            // ── Filters Applied ───────────────────────────────────
            // Echo back active filters so the UI can display them.
            // e.g. { "parking_id": 5, "payment_method": "upi" }
            'filters'      => $data['filters'] ?? [],

            // ── Summary Block ─────────────────────────────────────
            // Aggregated totals for the report period.
            // For earnings: { total, commission, owner_share, net }
            // For bookings: { total, confirmed, completed, cancelled }
            // For users:    { total, new, active, inactive }
            // For parkings: { total, approved, pending, rejected }
            'summary'      => $data['summary'] ?? [],

            // ── Paginated Rows ────────────────────────────────────
            // The actual report rows with Laravel pagination metadata.
            // The controller passes $query->paginate() result here.
            'rows'         => $data['rows'] ?? [],

            // ── Metadata ──────────────────────────────────────────
            'generated_at' => now()->toDateTimeString(),

            // ── Export Info (future) ──────────────────────────────
            // Will contain export_url or job_id when async export
            // is implemented. Null until then — included so the
            // frontend contract is established early.
            'export'       => $data['export'] ?? null,
        ];
    }
}