<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\Booking;
use App\Models\Parking;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * DashboardController
 * ============================================================
 *
 * Provides role-aware analytics data for the dashboard screens.
 *
 * ROLE-BASED BEHAVIOR:
 *
 *   ADMIN / SUPER ADMIN → Platform-wide view
 *     - Total users, owners, parkings, bookings
 *     - Total revenue, platform commission, net earnings
 *     - Pending approval actions (parkings, settlements, support)
 *     - Top performing parking locations
 *     - Recent bookings from any user
 *
 *   PARKING OWNER → Scoped to their own parkings
 *     - Today's bookings and earnings for their parkings
 *     - Their slot availability overview
 *     - Their earnings trend for the month
 *     - Recent bookings at their parking locations
 *
 * PERFORMANCE APPROACH:
 * All statistics use DB aggregation queries (COUNT, SUM, GROUP BY)
 * rather than loading Eloquent collections into PHP memory.
 * This keeps the dashboard fast even with millions of records.
 *
 * QUERY PARAMETERS:
 *   ?period=today          → today's data only
 *   ?period=this_week      → current week (Mon–Sun)
 *   ?period=this_month     → current calendar month (default)
 *   ?period=last_month     → previous calendar month
 *   ?period=this_year      → current calendar year
 *   ?date_from=2026-01-01  → custom start (overrides period)
 *   ?date_to=2026-06-30    → custom end   (overrides period)
 *
 * FUTURE SCALABILITY:
 *   - Cache dashboard results for 60 seconds per role/user
 *     using Laravel Cache to avoid hammering the DB on every
 *     page refresh from the Admin Panel.
 *   - Move heavy aggregation queries into a scheduled job that
 *     pre-computes stats and stores them in a `dashboard_snapshots`
 *     table, then serve from there instead of live queries.
 *   - Add WebSocket support (Laravel Reverb) to push live updates
 *     to the Admin Panel without polling.
 */
class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard
     *
     * Return role-aware dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->name;

        // ── Determine Date Range ──────────────────────────────────
        [$from, $to, $periodLabel] = $this->resolveDateRange($request);

        // ── Dispatch to Role-Specific Builder ─────────────────────
        if (in_array($role, ['super_admin', 'admin'])) {
            $data = $this->buildAdminDashboard($from, $to, $periodLabel, $user);
        } elseif ($role === 'parking_owner') {
            $data = $this->buildOwnerDashboard($from, $to, $periodLabel, $user);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard is not available for your role.',
                'data'    => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully.',
            'data'    => new DashboardResource($data),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | ADMIN DASHBOARD BUILDER
    |--------------------------------------------------------------------
    */

    /**
     * Build the platform-wide admin dashboard data.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  string  $periodLabel
     * @param  User    $user
     * @return array<string, mixed>
     */
    private function buildAdminDashboard(
        Carbon $from,
        Carbon $to,
        string $periodLabel,
        $user
    ): array {
        // ── Platform Stats Cards ──────────────────────────────────
        $totalUsers    = User::count();
        $totalOwners   = User::whereHas('role', fn ($q) => $q->where('name', 'parking_owner'))->count();
        $totalParkings = Parking::where('status', 'approved')->count();
        $totalBookings = Booking::whereBetween('created_at', [$from, $to])->count();

        // ── New Registrations This Period ─────────────────────────
        $newUsersThisPeriod = User::whereBetween('created_at', [$from, $to])->count();

        // ── Revenue Stats ─────────────────────────────────────────
        // We compute revenue from completed/confirmed paid bookings.
        $revenueQuery = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$from, $to]);

        $totalRevenue    = $revenueQuery->sum('amount');
        $todaysRevenue   = Payment::where('status', 'success')
            ->whereDate('paid_at', today())
            ->sum('amount');

        // Commission from the commissions table.
        $totalCommission = DB::table('commissions')
            ->whereBetween('created_at', [$from, $to])
            ->sum('platform_fee');

        $totalOwnerPayout = DB::table('commissions')
            ->whereBetween('created_at', [$from, $to])
            ->sum('owner_amount');

        // ── Booking Summary Breakdown ─────────────────────────────
        $bookingBreakdown = Booking::whereBetween('created_at', [$from, $to])
            ->select('booking_status', DB::raw('count(*) as count'))
            ->groupBy('booking_status')
            ->pluck('count', 'booking_status')
            ->toArray();

        // ── Pending Actions (admin to-do items) ───────────────────
        $pendingParkingApprovals = Parking::where('status', 'pending')->count();
        $pendingSettlements      = DB::table('settlements')->where('status', 'pending')->count();
        $openSupportTickets      = DB::table('support_tickets')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        // ── Earnings Chart Data (daily aggregation) ───────────────
        $chartData = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$from, $to])
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as bookings_count')
            )
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn ($row) => [
                'date'     => $row->date,
                'amount'   => number_format((float) $row->total_amount, 2, '.', ''),
                'bookings' => (int) $row->bookings_count,
            ]);

        // ── Recent Bookings Table (latest 5) ─────────────────────
        $recentBookings = Booking::with([
                'user:id,name,phone',
                'parking:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'booking_number' => $b->booking_number,
                'user'           => $b->user?->name,
                'parking'        => $b->parking?->name,
                'date'           => $b->booking_start_time?->format('d M Y'),
                'amount'         => number_format((float) $b->amount, 2, '.', ''),
                'status'         => $b->booking_status,
            ]);

        // ── Top Parking Locations by Revenue ─────────────────────
        $topPerformers = DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('parkings', 'parkings.id', '=', 'bookings.parking_id')
            ->where('payments.status', 'success')
            ->whereBetween('payments.paid_at', [$from, $to])
            ->select(
                'parkings.id',
                'parkings.name',
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(bookings.id) as booking_count')
            )
            ->groupBy('parkings.id', 'parkings.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'parking_id'    => $p->id,
                'parking_name'  => $p->name,
                'total_revenue' => number_format((float) $p->total_revenue, 2, '.', ''),
                'booking_count' => (int) $p->booking_count,
            ]);

        return [
            'role'           => 'admin',
            'period'         => [
                'label'     => $periodLabel,
                'from'      => $from->toDateString(),
                'to'        => $to->toDateString(),
            ],
            'stats'          => [
                'total_users'       => $totalUsers,
                'total_owners'      => $totalOwners,
                'total_parkings'    => $totalParkings,
                'total_bookings'    => $totalBookings,
                'new_users'         => $newUsersThisPeriod,
            ],
            'revenue'        => [
                'total_revenue'     => number_format((float) $totalRevenue, 2, '.', ''),
                'todays_revenue'    => number_format((float) $todaysRevenue, 2, '.', ''),
                'platform_commission' => number_format((float) $totalCommission, 2, '.', ''),
                'owner_payout'      => number_format((float) $totalOwnerPayout, 2, '.', ''),
            ],
            'booking_summary'   => $bookingBreakdown,
            'chart_data'        => $chartData,
            'recent_bookings'   => $recentBookings,
            'top_performers'    => $topPerformers,
            'pending_actions'   => [
                'parking_approvals' => $pendingParkingApprovals,
                'settlements'       => $pendingSettlements,
                'support_tickets'   => $openSupportTickets,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------
    | OWNER DASHBOARD BUILDER
    |--------------------------------------------------------------------
    */

    /**
     * Build the parking owner's scoped dashboard data.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  string  $periodLabel
     * @param  \App\Models\User  $user
     * @return array<string, mixed>
     */
    private function buildOwnerDashboard(
        Carbon $from,
        Carbon $to,
        string $periodLabel,
        $user
    ): array {
        // Get all parking IDs belonging to this owner.
        $ownerParkingIds = Parking::where('owner_id', $user->id)
            ->pluck('id');

        // ── Booking Stats ─────────────────────────────────────────
        $totalBookings     = Booking::whereIn('parking_id', $ownerParkingIds)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $pendingCheckins   = Booking::whereIn('parking_id', $ownerParkingIds)
            ->where('booking_status', 'confirmed')
            ->whereDate('booking_start_time', today())
            ->count();

        $completedToday    = Booking::whereIn('parking_id', $ownerParkingIds)
            ->where('booking_status', 'completed')
            ->whereDate('actual_checkout_time', today())
            ->count();

        // ── Earnings Stats ────────────────────────────────────────
        // Owner earnings come from the commissions table (owner_amount).
        $totalEarnings = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereBetween('commissions.created_at', [$from, $to])
            ->sum('commissions.owner_amount');

        $todaysEarnings = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereDate('commissions.created_at', today())
            ->sum('commissions.owner_amount');

        $thisWeekEarnings = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereBetween('commissions.created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->sum('commissions.owner_amount');

        // ── Slot Availability Summary ─────────────────────────────
        $slotSummary = DB::table('parking_slots')
            ->whereIn('parking_id', $ownerParkingIds)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Earnings Chart ────────────────────────────────────────
        $chartData = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereBetween('commissions.created_at', [$from, $to])
            ->select(
                DB::raw('DATE(commissions.created_at) as date'),
                DB::raw('SUM(commissions.owner_amount) as total_amount'),
                DB::raw('COUNT(*) as bookings_count')
            )
            ->groupBy(DB::raw('DATE(commissions.created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn ($row) => [
                'date'     => $row->date,
                'amount'   => number_format((float) $row->total_amount, 2, '.', ''),
                'bookings' => (int) $row->bookings_count,
            ]);

        // ── Recent Bookings ───────────────────────────────────────
        $recentBookings = Booking::with([
                'user:id,name,phone',
                'parking:id,name',
                'parkingSlot:id,slot_number',
            ])
            ->whereIn('parking_id', $ownerParkingIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'booking_number' => $b->booking_number,
                'user'           => $b->user?->name,
                'parking'        => $b->parking?->name,
                'slot'           => $b->parkingSlot?->slot_number,
                'date'           => $b->booking_start_time?->format('d M Y'),
                'amount'         => number_format((float) $b->amount, 2, '.', ''),
                'status'         => $b->booking_status,
            ]);

        return [
            'role'           => 'parking_owner',
            'period'         => [
                'label' => $periodLabel,
                'from'  => $from->toDateString(),
                'to'    => $to->toDateString(),
            ],
            'stats'          => [
                'total_bookings'    => $totalBookings,
                'pending_checkins'  => $pendingCheckins,
                'completed_today'   => $completedToday,
                'total_parkings'    => count($ownerParkingIds),
            ],
            'revenue'        => [
                'total_earnings'    => number_format((float) $totalEarnings, 2, '.', ''),
                'todays_earnings'   => number_format((float) $todaysEarnings, 2, '.', ''),
                'this_week_earnings'=> number_format((float) $thisWeekEarnings, 2, '.', ''),
            ],
            'booking_summary'  => Booking::whereIn('parking_id', $ownerParkingIds)
                ->whereBetween('created_at', [$from, $to])
                ->select('booking_status', DB::raw('count(*) as count'))
                ->groupBy('booking_status')
                ->pluck('count', 'booking_status')
                ->toArray(),
            'slot_summary'     => $slotSummary,
            'chart_data'       => $chartData,
            'recent_bookings'  => $recentBookings,
            'top_performers'   => [],   // Not applicable for owner view
            'pending_actions'  => [
                'pending_checkins' => $pendingCheckins,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------
    | DATE RANGE RESOLVER
    |--------------------------------------------------------------------
    */

    /**
     * Resolve the date range from request parameters.
     *
     * Supports:
     *   - Named periods: today, this_week, this_month (default),
     *                    last_month, this_year
     *   - Custom range:  date_from + date_to query parameters
     *
     * Returns: [$from (Carbon), $to (Carbon), $label (string)]
     *
     * @param  Request  $request
     * @return array{Carbon, Carbon, string}
     */
    private function resolveDateRange(Request $request): array
    {
        // If explicit dates are provided, use them.
        if ($request->filled('date_from') && $request->filled('date_to')) {
            return [
                Carbon::parse($request->input('date_from'))->startOfDay(),
                Carbon::parse($request->input('date_to'))->endOfDay(),
                'custom',
            ];
        }

        $period = $request->input('period', 'this_month');

        return match ($period) {
            'today'      => [
                now()->startOfDay(),
                now()->endOfDay(),
                'Today',
            ],
            'this_week'  => [
                now()->startOfWeek(),
                now()->endOfWeek(),
                'This Week',
            ],
            'last_month' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
                'Last Month',
            ],
            'this_year'  => [
                now()->startOfYear(),
                now()->endOfYear(),
                'This Year',
            ],
            default      => [ // 'this_month'
                now()->startOfMonth(),
                now()->endOfMonth(),
                'This Month',
            ],
        };
    }
}