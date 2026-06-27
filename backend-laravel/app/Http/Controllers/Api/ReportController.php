<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\BookingReportRequest;
use App\Http\Requests\Report\EarningsReportRequest;
use App\Http\Requests\Report\ParkingReportRequest;
use App\Http\Requests\Report\UserReportRequest;
use App\Http\Resources\ReportResource;
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
 * ReportController
 * ============================================================
 *
 * Provides all data-export and analytics report endpoints
 * for the Smart Parking Management System Admin Panel and
 * Parking Owner App.
 *
 * ENDPOINTS:
 *   GET /api/v1/reports/earnings        → Revenue & commission report
 *   GET /api/v1/reports/bookings        → Booking activity report
 *   GET /api/v1/reports/users           → User registration report (admin only)
 *   GET /api/v1/reports/parkings        → Parking performance report
 *   GET /api/v1/reports/owner-earnings  → Owner-specific earnings (owner/admin)
 *   GET /api/v1/reports/commission      → Platform commission breakdown (admin)
 *   GET /api/v1/reports/export/earnings → CSV export of earnings report
 *   GET /api/v1/reports/export/bookings → CSV export of bookings report
 *
 * PERFORMANCE PRINCIPLES:
 *   - All aggregations use DB::table() raw queries or Eloquent
 *     withCount()/withSum() — never loading full collections.
 *   - Pagination is always applied to row-level results.
 *   - Summary blocks are computed with single aggregate queries.
 *   - No N+1 queries — all relationships are eager-loaded.
 *
 * ROLE SCOPING:
 *   - Admin/Super Admin: platform-wide data, all filters available.
 *   - Parking Owner: data scoped to their own parking_ids only.
 *     The owner cannot see other owners' data even if they pass
 *     another owner_id or parking_id in the query params.
 *   - Customer: no access to any report endpoint.
 *
 * FUTURE SCALABILITY:
 *   - Move export logic to a queued job (ReportExportJob) for
 *     large datasets. Return a job_id and let the client poll
 *     GET /reports/export/status/{job_id} for completion.
 *   - Add Redis caching for summary blocks (TTL: 5 minutes)
 *     since they are expensive and don't need real-time accuracy.
 *   - Add `format` query param (?format=json|csv|xlsx) to unify
 *     all export endpoints into the main report endpoints.
 */
class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------
    | EARNINGS REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/earnings
     *
     * Platform-wide earnings and revenue report.
     *
     * SUMMARY RETURNS:
     *   - Total gross revenue (sum of all successful payment amounts)
     *   - Platform commission (sum of platform_fee from commissions table)
     *   - Net owner payouts (sum of owner_amount from commissions table)
     *   - Payment method breakdown (UPI %, card %, etc.)
     *
     * ROWS RETURN:
     *   Each row = one successful payment with booking context.
     */
    public function earnings(EarningsReportRequest $request): JsonResponse
    {
        $user    = $request->user();
        $role    = $user->role?->name;
        $isAdmin = in_array($role, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        // ── Base Query ────────────────────────────────────────────
        $baseQuery = Payment::query()
            ->where('status', 'success')
            ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'booking:id,booking_number,parking_id,user_id,duration_hours',
                'booking.parking:id,name',
                'booking.user:id,name',
                'user:id,name',
            ]);

        // ── Owner Scope ───────────────────────────────────────────
        if (!$isAdmin) {
            $ownerParkingIds = Parking::where('owner_id', $user->id)->pluck('id');
            $baseQuery->whereHas('booking', fn ($q) =>
                $q->whereIn('parking_id', $ownerParkingIds)
            );
        }

        // ── Optional Filters ──────────────────────────────────────
        if ($request->filled('parking_id')) {
            // Owners: validate this parking belongs to them (controller level)
            if (!$isAdmin) {
                $ownerParkingIds = $ownerParkingIds ?? Parking::where('owner_id', $user->id)->pluck('id');
                if (!$ownerParkingIds->contains($request->integer('parking_id'))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this parking location.',
                        'data'    => null,
                    ], 403);
                }
            }
            $baseQuery->whereHas('booking', fn ($q) =>
                $q->where('parking_id', $request->integer('parking_id'))
            );
        }

        if ($request->filled('owner_id') && $isAdmin) {
            $ownerParkingIds = Parking::where('owner_id', $request->integer('owner_id'))->pluck('id');
            $baseQuery->whereHas('booking', fn ($q) =>
                $q->whereIn('parking_id', $ownerParkingIds)
            );
        }

        if ($request->filled('payment_method')) {
            $baseQuery->where('payment_method', $request->input('payment_method'));
        }

        // ── Summary Block ─────────────────────────────────────────
        // Clone the query for summary (before pagination is applied).
        $summaryQuery  = clone $baseQuery;
        $totalRevenue  = $summaryQuery->sum('amount');

        // Commission summary directly from the commissions table.
        $commissionSummaryBase = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->join('payments', 'payments.booking_id', '=', 'bookings.id')
            ->where('payments.status', 'success')
            ->whereBetween('payments.paid_at', [$from->startOfDay(), $to->endOfDay()]);

        if (!$isAdmin) {
            $commissionSummaryBase->whereIn('bookings.parking_id', $ownerParkingIds ?? collect());
        }
        if ($request->filled('parking_id')) {
            $commissionSummaryBase->where('bookings.parking_id', $request->integer('parking_id'));
        }

        $totalCommission  = $commissionSummaryBase->sum('commissions.platform_fee');
        $totalOwnerPayout = (clone $commissionSummaryBase)->sum('commissions.owner_amount');

        // Payment method breakdown.
        $methodBreakdown = (clone $baseQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [
                'method'  => $row->payment_method,
                'count'   => (int) $row->count,
                'total'   => number_format((float) $row->total, 2, '.', ''),
                'percent' => $totalRevenue > 0
                    ? round(($row->total / $totalRevenue) * 100, 1)
                    : 0,
            ]);

        // ── Chart Data ────────────────────────────────────────────
        $groupFormat = $request->input('group_by') === 'monthly'
            ? '%Y-%m'
            : '%Y-%m-%d';

        $chartData = (clone $baseQuery)
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '{$groupFormat}') as period"),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(fn ($row) => [
                'period'  => $row->period,
                'amount'  => number_format((float) $row->total_amount, 2, '.', ''),
                'count'   => (int) $row->payment_count,
            ]);

        // ── Paginated Rows ────────────────────────────────────────
        $perPage = $request->integer('per_page', 15);
        $rows    = $baseQuery->orderBy('paid_at', 'desc')->paginate($perPage);

        $formattedRows = $rows->getCollection()->map(fn ($p) => [
            'payment_id'          => $p->id,
            'booking_number'      => $p->booking?->booking_number,
            'user_name'           => $p->user?->name,
            'parking_name'        => $p->booking?->parking?->name,
            'amount'              => number_format((float) $p->amount, 2, '.', ''),
            'payment_method'      => $p->payment_method,
            'razorpay_payment_id' => $p->razorpay_payment_id,
            'paid_at'             => $p->paid_at?->toDateTimeString(),
            'status'              => $p->status,
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Earnings report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'earnings',
                'period'      => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'filters'     => array_filter($request->only([
                    'parking_id', 'owner_id', 'payment_method', 'group_by',
                ])),
                'summary'     => [
                    'total_revenue'       => number_format((float) $totalRevenue, 2, '.', ''),
                    'platform_commission' => number_format((float) $totalCommission, 2, '.', ''),
                    'owner_payout'        => number_format((float) $totalOwnerPayout, 2, '.', ''),
                    'total_transactions'  => $rows->total(),
                    'payment_methods'     => $methodBreakdown,
                ],
                'chart_data'  => $chartData,
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | BOOKINGS REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/bookings
     *
     * Booking activity report with status and payment breakdowns.
     */
    public function bookings(BookingReportRequest $request): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        // ── Base Query ────────────────────────────────────────────
        $baseQuery = Booking::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'user:id,name,phone',
                'parking:id,name',
                'parkingSlot:id,slot_number',
                'vehicle:id,vehicle_number,vehicle_type',
            ]);

        // ── Owner Scope ───────────────────────────────────────────
        if (!$isAdmin) {
            $ownerParkingIds = Parking::where('owner_id', $user->id)->pluck('id');
            $baseQuery->whereIn('parking_id', $ownerParkingIds);
        }

        // ── Optional Filters ──────────────────────────────────────
        if ($request->filled('booking_status')) {
            $baseQuery->where('booking_status', $request->input('booking_status'));
        }
        if ($request->filled('payment_status')) {
            $baseQuery->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('parking_id')) {
            $baseQuery->where('parking_id', $request->integer('parking_id'));
        }
        if ($request->filled('city_id')) {
            $baseQuery->whereHas('parking', fn ($q) =>
                $q->where('city_id', $request->integer('city_id'))
            );
        }
        if ($request->filled('vehicle_type')) {
            $baseQuery->whereHas('vehicle', fn ($q) =>
                $q->where('vehicle_type', $request->input('vehicle_type'))
            );
        }

        // ── Summary ───────────────────────────────────────────────
        $summaryQuery  = clone $baseQuery;
        $totalBookings = $summaryQuery->count();
        $totalRevenue  = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->sum('amount');
        $avgDuration   = (clone $baseQuery)
            ->where('booking_status', 'completed')
            ->avg('duration_hours');

        $statusBreakdown = (clone $baseQuery)
            ->select('booking_status', DB::raw('COUNT(*) as count'))
            ->groupBy('booking_status')
            ->pluck('count', 'booking_status')
            ->toArray();

        $vehicleTypeBreakdown = (clone $baseQuery)
            ->join('vehicles', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->select('vehicles.vehicle_type', DB::raw('COUNT(*) as count'))
            ->groupBy('vehicles.vehicle_type')
            ->pluck('count', 'vehicles.vehicle_type')
            ->toArray();

        // ── Chart Data ────────────────────────────────────────────
        $groupFormat = $request->input('group_by') === 'monthly'
            ? '%Y-%m'
            : '%Y-%m-%d';

        $chartData = (clone $baseQuery)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as revenue')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(fn ($row) => [
                'period'   => $row->period,
                'bookings' => (int) $row->booking_count,
                'revenue'  => number_format((float) $row->revenue, 2, '.', ''),
            ]);

        // ── Paginated Rows ────────────────────────────────────────
        $perPage = $request->integer('per_page', 15);
        $rows    = $baseQuery->orderBy('created_at', 'desc')->paginate($perPage);

        $formattedRows = $rows->getCollection()->map(fn ($b) => [
            'booking_id'     => $b->id,
            'booking_number' => $b->booking_number,
            'user'           => $b->user?->name,
            'user_phone'     => $b->user?->phone,
            'parking'        => $b->parking?->name,
            'slot'           => $b->parkingSlot?->slot_number,
            'vehicle'        => $b->vehicle?->vehicle_number,
            'vehicle_type'   => $b->vehicle?->vehicle_type,
            'start_time'     => $b->booking_start_time?->toDateTimeString(),
            'end_time'       => $b->booking_end_time?->toDateTimeString(),
            'duration_hours' => $b->duration_hours,
            'amount'         => number_format((float) $b->amount, 2, '.', ''),
            'booking_status' => $b->booking_status,
            'payment_status' => $b->payment_status,
            'created_at'     => $b->created_at?->toDateTimeString(),
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Bookings report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'bookings',
                'period'      => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'filters'     => array_filter($request->only([
                    'booking_status', 'payment_status', 'parking_id', 'city_id', 'vehicle_type',
                ])),
                'summary'     => [
                    'total_bookings'       => $totalBookings,
                    'total_revenue'        => number_format((float) $totalRevenue, 2, '.', ''),
                    'avg_duration_hours'   => round((float) $avgDuration, 2),
                    'status_breakdown'     => $statusBreakdown,
                    'vehicle_type_breakdown' => $vehicleTypeBreakdown,
                ],
                'chart_data'  => $chartData,
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | USERS REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/users
     *
     * User registration and activity report. Admin only.
     * Authorization is enforced in UserReportRequest.
     */
    public function users(UserReportRequest $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        // ── Base Query ────────────────────────────────────────────
        $baseQuery = User::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with('role:id,name');

        // ── Filters ───────────────────────────────────────────────
        if ($request->filled('role')) {
            $baseQuery->whereHas('role', fn ($q) =>
                $q->where('name', $request->input('role'))
            );
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->input('status'));
        }
        if ($request->filled('is_verified')) {
            $baseQuery->where('is_verified', $request->boolean('is_verified'));
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $baseQuery->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%")
                  ->orWhere('phone', 'LIKE', "%{$term}%");
            });
        }

        // ── Summary ───────────────────────────────────────────────
        $totalUsers  = (clone $baseQuery)->count();
        $activeUsers = (clone $baseQuery)->where('status', 'active')->count();
        $verifiedUsers = (clone $baseQuery)->where('is_verified', true)->count();

        $roleBreakdown = (clone $baseQuery)
            ->select('roles.name as role_name', DB::raw('COUNT(users.id) as count'))
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->groupBy('roles.name')
            ->pluck('count', 'role_name')
            ->toArray();

        // ── Chart Data ────────────────────────────────────────────
        $groupFormat = $request->input('group_by') === 'monthly'
            ? '%Y-%m'
            : '%Y-%m-%d';

        $chartData = (clone $baseQuery)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('COUNT(*) as registrations')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(fn ($row) => [
                'period'        => $row->period,
                'registrations' => (int) $row->registrations,
            ]);

        // ── Paginated Rows ────────────────────────────────────────
        $perPage = $request->integer('per_page', 15);
        $rows    = $baseQuery->orderBy('created_at', 'desc')->paginate($perPage);

        $formattedRows = $rows->getCollection()->map(fn ($u) => [
            'user_id'    => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'phone'      => $u->phone,
            'role'       => $u->role?->name,
            'status'     => $u->status,
            'is_verified'=> (bool) $u->is_verified,
            'created_at' => $u->created_at?->toDateTimeString(),
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Users report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'users',
                'period'      => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'filters'     => array_filter($request->only([
                    'role', 'status', 'is_verified', 'search',
                ])),
                'summary'     => [
                    'total_registrations' => $totalUsers,
                    'active_users'        => $activeUsers,
                    'verified_users'      => $verifiedUsers,
                    'role_breakdown'      => $roleBreakdown,
                ],
                'chart_data'  => $chartData,
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | PARKINGS REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/parkings
     *
     * Parking performance report. Admin sees all, owner sees theirs.
     */
    public function parkings(ParkingReportRequest $request): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        // ── Base Query ────────────────────────────────────────────
        $baseQuery = Parking::query()
            ->with(['city:id,name', 'state:id,name', 'owner:id,name'])
            ->withCount('slots');

        // ── Owner Scope ───────────────────────────────────────────
        if (!$isAdmin) {
            $baseQuery->where('owner_id', $user->id);
        }

        // ── Filters ───────────────────────────────────────────────
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->input('status'));
        }
        if ($request->filled('city_id')) {
            $baseQuery->where('city_id', $request->integer('city_id'));
        }
        if ($request->filled('state_id')) {
            $baseQuery->where('state_id', $request->integer('state_id'));
        }
        if ($request->filled('owner_id') && $isAdmin) {
            $baseQuery->where('owner_id', $request->integer('owner_id'));
        }
        if ($request->filled('parking_type')) {
            $baseQuery->where('parking_type', $request->input('parking_type'));
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $baseQuery->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('address', 'LIKE', "%{$term}%");
            });
        }

        // ── Summary ───────────────────────────────────────────────
        $totalParkings   = (clone $baseQuery)->count();
        $approvedCount   = (clone $baseQuery)->where('status', 'approved')->count();
        $pendingCount    = (clone $baseQuery)->where('status', 'pending')->count();
        $totalSlots      = (clone $baseQuery)->join('parking_slots', 'parkings.id', '=', 'parking_slots.parking_id')
            ->count('parking_slots.id');

        // ── Paginated Rows with Revenue ───────────────────────────
        $sortBy        = $request->input('sort_by', 'revenue');
        $sortDirection = $request->input('sort_direction', 'desc');
        $perPage       = $request->integer('per_page', 15);

        // Sub-query to get revenue per parking in the period.
        $revenueSubquery = DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->where('payments.status', 'success')
            ->whereBetween('payments.paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->select(
                'bookings.parking_id',
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(payments.id) as booking_count')
            )
            ->groupBy('bookings.parking_id');

        // Apply revenue sort if needed.
        if ($sortBy === 'revenue') {
            $parkingIds = $baseQuery->pluck('parkings.id');
            $revenueMap = DB::table(DB::raw("({$revenueSubquery->toSql()}) as rev"))
                ->mergeBindings($revenueSubquery)
                ->whereIn('rev.parking_id', $parkingIds)
                ->pluck('total_revenue', 'parking_id');

            // Fetch paginated parkings.
            $rows = $baseQuery->orderBy('parkings.name', 'asc')->paginate($perPage);
        } else {
            $rows = $baseQuery->orderBy(
                $sortBy === 'name' ? 'parkings.name' : 'parkings.' . $sortBy,
                $sortDirection
            )->paginate($perPage);
            $revenueMap = collect();
        }

        // Pull revenue data for the current page's parkings.
        $pageIds     = $rows->getCollection()->pluck('id');
        $pageRevenue = DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->where('payments.status', 'success')
            ->whereBetween('payments.paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->whereIn('bookings.parking_id', $pageIds)
            ->select(
                'bookings.parking_id',
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(payments.id) as booking_count')
            )
            ->groupBy('bookings.parking_id')
            ->get()
            ->keyBy('parking_id');

        $formattedRows = $rows->getCollection()->map(fn ($p) => [
            'parking_id'    => $p->id,
            'name'          => $p->name,
            'status'        => $p->status,
            'parking_type'  => $p->parking_type,
            'city'          => $p->city?->name,
            'state'         => $p->state?->name,
            'owner'         => $isAdmin ? $p->owner?->name : null,
            'total_slots'   => $p->slots_count ?? 0,
            'total_revenue' => number_format(
                (float) ($pageRevenue[$p->id]->total_revenue ?? 0),
                2, '.', ''
            ),
            'booking_count' => (int) ($pageRevenue[$p->id]->booking_count ?? 0),
            'created_at'    => $p->created_at?->toDateTimeString(),
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Parking report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'parkings',
                'period'      => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'filters'     => array_filter($request->only([
                    'status', 'city_id', 'state_id', 'owner_id', 'parking_type', 'search',
                ])),
                'summary'     => [
                    'total_parkings'  => $totalParkings,
                    'approved'        => $approvedCount,
                    'pending'         => $pendingCount,
                    'total_slots'     => $totalSlots,
                ],
                'chart_data'  => [],
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | OWNER EARNINGS REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/owner-earnings
     *
     * Parking owner's earnings breakdown — per booking, per parking.
     * Owners see only their own data. Admins can filter by owner_id.
     */
    public function ownerEarnings(EarningsReportRequest $request): JsonResponse
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        // Determine which owner to scope to.
        $ownerId = $isAdmin && $request->filled('owner_id')
            ? $request->integer('owner_id')
            : $user->id;

        $ownerParkingIds = Parking::where('owner_id', $ownerId)->pluck('id');

        // ── Summary ───────────────────────────────────────────────
        $commissionBase = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereBetween('commissions.created_at', [$from->startOfDay(), $to->endOfDay()]);

        $totalEarnings   = (clone $commissionBase)->sum('commissions.owner_amount');
        $totalGross      = (clone $commissionBase)->sum('commissions.total_amount');
        $totalCommission = (clone $commissionBase)->sum('commissions.platform_fee');
        $totalBookings   = (clone $commissionBase)->count();

        // Per-parking breakdown.
        $perParking = (clone $commissionBase)
            ->join('parkings', 'parkings.id', '=', 'bookings.parking_id')
            ->select(
                'parkings.id',
                'parkings.name',
                DB::raw('SUM(commissions.owner_amount) as earnings'),
                DB::raw('SUM(commissions.total_amount) as gross'),
                DB::raw('COUNT(commissions.id) as bookings')
            )
            ->groupBy('parkings.id', 'parkings.name')
            ->orderByDesc('earnings')
            ->get()
            ->map(fn ($row) => [
                'parking_id'   => $row->id,
                'parking_name' => $row->name,
                'gross'        => number_format((float) $row->gross, 2, '.', ''),
                'earnings'     => number_format((float) $row->earnings, 2, '.', ''),
                'bookings'     => (int) $row->bookings,
            ]);

        // ── Paginated Transaction Rows ────────────────────────────
        $perPage = $request->integer('per_page', 15);
        $rows    = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->join('parkings', 'parkings.id', '=', 'bookings.parking_id')
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->whereIn('bookings.parking_id', $ownerParkingIds)
            ->whereBetween('commissions.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->select(
                'commissions.id',
                'bookings.booking_number',
                'parkings.name as parking_name',
                'users.name as customer_name',
                DB::raw('commissions.total_amount'),
                DB::raw('commissions.platform_fee'),
                DB::raw('commissions.owner_amount'),
                DB::raw('commissions.commission_percent'),
                'commissions.created_at'
            )
            ->orderByDesc('commissions.created_at')
            ->paginate($perPage);

        // Format monetary values.
        $formattedRows = collect($rows->items())->map(fn ($row) => [
            'booking_number'      => $row->booking_number,
            'parking_name'        => $row->parking_name,
            'customer_name'       => $row->customer_name,
            'gross_amount'        => number_format((float) $row->total_amount, 2, '.', ''),
            'platform_commission' => number_format((float) $row->platform_fee, 2, '.', ''),
            'your_earnings'       => number_format((float) $row->owner_amount, 2, '.', ''),
            'commission_percent'  => $row->commission_percent . '%',
            'date'                => Carbon::parse($row->created_at)->toDateTimeString(),
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Owner earnings report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'owner_earnings',
                'period'      => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'filters'     => array_filter($request->only(['owner_id', 'parking_id'])),
                'summary'     => [
                    'total_gross'        => number_format((float) $totalGross, 2, '.', ''),
                    'platform_commission'=> number_format((float) $totalCommission, 2, '.', ''),
                    'your_earnings'      => number_format((float) $totalEarnings, 2, '.', ''),
                    'total_bookings'     => $totalBookings,
                    'per_parking'        => $perParking,
                ],
                'chart_data'  => [],
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | COMMISSION REPORT
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/commission
     *
     * Platform commission breakdown report. Admin only.
     */
    public function commission(EarningsReportRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view commission reports.',
                'data'    => null,
            ], 403);
        }

        [$from, $to] = $this->resolveDateRange($request);

        $baseQuery = DB::table('commissions')
            ->join('bookings', 'bookings.id', '=', 'commissions.booking_id')
            ->join('parkings', 'parkings.id', '=', 'bookings.parking_id')
            ->join('users as owners', 'owners.id', '=', 'parkings.owner_id')
            ->whereBetween('commissions.created_at', [$from->startOfDay(), $to->endOfDay()]);

        // Optional filters.
        if ($request->filled('parking_id')) {
            $baseQuery->where('parkings.id', $request->integer('parking_id'));
        }
        if ($request->filled('owner_id')) {
            $baseQuery->where('parkings.owner_id', $request->integer('owner_id'));
        }

        // Summary.
        $totalGross      = (clone $baseQuery)->sum('commissions.total_amount');
        $totalCommission = (clone $baseQuery)->sum('commissions.platform_fee');
        $totalOwnerPaid  = (clone $baseQuery)->sum('commissions.owner_amount');
        $totalTransact   = (clone $baseQuery)->count();

        // By commission rate breakdown.
        $rateBreakdown = (clone $baseQuery)
            ->select(
                'commissions.commission_percent',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(commissions.platform_fee) as total_fee')
            )
            ->groupBy('commissions.commission_percent')
            ->orderByDesc('total_fee')
            ->get()
            ->map(fn ($row) => [
                'rate'       => $row->commission_percent . '%',
                'count'      => (int) $row->count,
                'total_fee'  => number_format((float) $row->total_fee, 2, '.', ''),
            ]);

        $perPage = $request->integer('per_page', 15);
        $rows = (clone $baseQuery)
            ->select(
                'bookings.booking_number',
                'parkings.name as parking_name',
                'owners.name as owner_name',
                'commissions.total_amount',
                'commissions.commission_percent',
                'commissions.platform_fee',
                'commissions.owner_amount',
                'commissions.settled',
                'commissions.created_at'
            )
            ->orderByDesc('commissions.created_at')
            ->paginate($perPage);

        $formattedRows = collect($rows->items())->map(fn ($row) => [
            'booking_number'      => $row->booking_number,
            'parking_name'        => $row->parking_name,
            'owner_name'          => $row->owner_name,
            'gross_amount'        => number_format((float) $row->total_amount, 2, '.', ''),
            'commission_percent'  => $row->commission_percent . '%',
            'platform_fee'        => number_format((float) $row->platform_fee, 2, '.', ''),
            'owner_amount'        => number_format((float) $row->owner_amount, 2, '.', ''),
            'settled'             => (bool) $row->settled,
            'date'                => Carbon::parse($row->created_at)->toDateTimeString(),
        ]);
        $rows->setCollection($formattedRows);

        return response()->json([
            'success' => true,
            'message' => 'Commission report generated successfully.',
            'data'    => new ReportResource([
                'report_type' => 'commission',
                'period'      => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'filters'     => array_filter($request->only(['parking_id', 'owner_id'])),
                'summary'     => [
                    'total_gross'       => number_format((float) $totalGross, 2, '.', ''),
                    'total_commission'  => number_format((float) $totalCommission, 2, '.', ''),
                    'total_owner_payout'=> number_format((float) $totalOwnerPaid, 2, '.', ''),
                    'total_transactions'=> $totalTransact,
                    'rate_breakdown'    => $rateBreakdown,
                ],
                'chart_data'  => [],
                'rows'        => $rows,
            ]),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------
    | EXPORT ENDPOINTS
    |--------------------------------------------------------------------
    */

    /**
     * GET /api/v1/reports/export/earnings
     *
     * Stream a CSV export of the earnings report.
     *
     * For large datasets (> 5,000 rows), this should be converted to
     * a queued job that generates the file and stores it on S3,
     * then sends a download link via notification.
     * For now it streams directly — suitable for small/medium datasets.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function exportEarnings(EarningsReportRequest $request)
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        $query = Payment::query()
            ->where('status', 'success')
            ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'booking:id,booking_number,parking_id,user_id,duration_hours',
                'booking.parking:id,name',
                'booking.user:id,name',
            ]);

        if (!$isAdmin) {
            $ownerParkingIds = Parking::where('owner_id', $user->id)->pluck('id');
            $query->whereHas('booking', fn ($q) =>
                $q->whereIn('parking_id', $ownerParkingIds)
            );
        }

        $filename = 'earnings_report_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV Header row.
            fputcsv($handle, [
                'Payment ID', 'Booking Number', 'Customer Name',
                'Parking Name', 'Amount (INR)', 'Payment Method',
                'Razorpay Payment ID', 'Paid At', 'Status',
            ]);

            // Stream rows in chunks to avoid memory exhaustion.
            $query->orderBy('paid_at', 'desc')
                ->chunk(500, function ($payments) use ($handle) {
                    foreach ($payments as $p) {
                        fputcsv($handle, [
                            $p->id,
                            $p->booking?->booking_number,
                            $p->booking?->user?->name,
                            $p->booking?->parking?->name,
                            number_format((float) $p->amount, 2, '.', ''),
                            $p->payment_method,
                            $p->razorpay_payment_id,
                            $p->paid_at?->toDateTimeString(),
                            $p->status,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /api/v1/reports/export/bookings
     *
     * Stream a CSV export of the bookings report.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function exportBookings(BookingReportRequest $request)
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role?->name, ['super_admin', 'admin']);

        [$from, $to] = $this->resolveDateRange($request);

        $query = Booking::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'user:id,name,phone',
                'parking:id,name',
                'parkingSlot:id,slot_number',
                'vehicle:id,vehicle_number,vehicle_type',
            ]);

        if (!$isAdmin) {
            $ownerParkingIds = Parking::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('parking_id', $ownerParkingIds);
        }

        // Apply filters from request.
        if ($request->filled('booking_status')) {
            $query->where('booking_status', $request->input('booking_status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $filename = 'bookings_report_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Booking Number', 'Customer Name', 'Customer Phone',
                'Parking Name', 'Slot Number', 'Vehicle Number', 'Vehicle Type',
                'Start Time', 'End Time', 'Duration (hrs)', 'Amount (INR)',
                'Booking Status', 'Payment Status', 'Created At',
            ]);

            $query->orderBy('created_at', 'desc')
                ->chunk(500, function ($bookings) use ($handle) {
                    foreach ($bookings as $b) {
                        fputcsv($handle, [
                            $b->booking_number,
                            $b->user?->name,
                            $b->user?->phone,
                            $b->parking?->name,
                            $b->parkingSlot?->slot_number,
                            $b->vehicle?->vehicle_number,
                            $b->vehicle?->vehicle_type,
                            $b->booking_start_time?->toDateTimeString(),
                            $b->booking_end_time?->toDateTimeString(),
                            $b->duration_hours,
                            number_format((float) $b->amount, 2, '.', ''),
                            $b->booking_status,
                            $b->payment_status,
                            $b->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | PRIVATE HELPER
    |--------------------------------------------------------------------
    */

    /**
     * Resolve the date range from a report request.
     * Defaults to the current calendar month if nothing is specified.
     *
     * @return array{Carbon, Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        return [$from, $to];
    }
}