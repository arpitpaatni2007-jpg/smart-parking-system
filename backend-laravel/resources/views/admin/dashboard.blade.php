{{-- ============================================================
     Dashboard
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Main dashboard — stat cards, overview panels,
               recent bookings table, top parking owners list.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── Stat cards ──────────────────────────────────────────── */
    .stat-card {
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        background:    #fff;
        padding:       1.35rem 1.5rem;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        transition:    box-shadow .18s ease, transform .18s ease;
    }
    .stat-card:hover {
        box-shadow:  0 6px 22px rgba(15,61,86,.11);
        transform:   translateY(-2px);
    }
    .stat-icon {
        width:         48px;
        height:        48px;
        border-radius: 12px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1.3rem;
        flex-shrink:   0;
    }
    .stat-value {
        font-size:   1.65rem;
        font-weight: 700;
        color:       #0D1B2A;
        line-height: 1.1;
    }
    .stat-label {
        font-size: .8rem;
        color:     #5A6A7A;
        margin-top: .2rem;
    }
    .badge-growth {
        font-size:     .72rem;
        font-weight:   600;
        padding:       .25em .55em;
        border-radius: 6px;
    }
    .badge-up   { background: rgba(46,204,113,.13); color:#1aaa5a; }
    .badge-down { background: rgba(231,76,60,.12);  color:#c0392b; }

    /* ── Overview cards ─────────────────────────────────────── */
    .overview-card {
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        background:    #fff;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .overview-header {
        padding:       1.1rem 1.4rem .6rem;
        border-bottom: 1px solid #f0f3f7;
    }
    .overview-body {
        padding: 1.25rem 1.4rem;
    }

    /* ── Faux bar chart ─────────────────────────────────────── */
    .bar-chart {
        display:     flex;
        align-items: flex-end;
        gap:         8px;
        height:      110px;
    }
    .bar-chart-col {
        flex: 1;
        display:       flex;
        flex-direction: column;
        align-items:   center;
        gap:           4px;
        height:        100%;
        justify-content: flex-end;
    }
    .bar-chart-bar {
        width:         100%;
        border-radius: 5px 5px 0 0;
        background:    #0F3D56;
        transition:    background .18s;
        cursor:        default;
    }
    .bar-chart-bar:hover { background: #2ECC71; }
    .bar-chart-bar.accent { background: #2ECC71; }
    .bar-chart-label {
        font-size:  .68rem;
        color:      #8899aa;
        white-space: nowrap;
    }

    /* ── Parking slot grid ───────────────────────────────────── */
    .slot-grid {
        display:               grid;
        grid-template-columns: repeat(4, 1fr);
        gap:                   8px;
    }
    .slot-box {
        border-radius: 8px;
        padding:       .55rem .4rem;
        text-align:    center;
        font-size:     .72rem;
        font-weight:   600;
    }
    .slot-box.occupied  { background: rgba(15,61,86,.1);  color: #0F3D56; }
    .slot-box.available { background: rgba(46,204,113,.14); color:#1aaa5a; }
    .slot-box.reserved  { background: rgba(255,165,0,.13); color:#c47d00; }

    /* ── Table ───────────────────────────────────────────────── */
    .dash-table {
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        background:    #fff;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .dash-table-header {
        padding:       1.1rem 1.4rem .7rem;
        border-bottom: 1px solid #f0f3f7;
    }
    .dash-table table {
        margin-bottom: 0;
    }
    .dash-table thead th {
        font-size:      .75rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom:  1px solid #f0f3f7 !important;
        padding:        .65rem 1rem;
        background:     #fafbfc;
    }
    .dash-table tbody td {
        font-size:    .855rem;
        padding:      .75rem 1rem;
        color:        #0D1B2A;
        border-bottom: 1px solid #f5f7f9;
        vertical-align: middle;
    }
    .dash-table tbody tr:last-child td { border-bottom: none; }
    .dash-table tbody tr:hover td { background: #fafcff; }

    /* ── Status badges ───────────────────────────────────────── */
    .status-pill {
        display:       inline-block;
        padding:       .25em .7em;
        border-radius: 20px;
        font-size:     .72rem;
        font-weight:   600;
    }
    .status-confirmed { background:rgba(46,204,113,.15); color:#1aaa5a; }
    .status-pending   { background:rgba(255,165,0,.15);  color:#c47d00; }
    .status-cancelled { background:rgba(231,76,60,.12);  color:#c0392b; }
    .status-completed { background:rgba(15,61,86,.1);    color:#0F3D56; }

    /* ── Owner list ──────────────────────────────────────────── */
    .owner-list {
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        background:    #fff;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .owner-list-header {
        padding:       1.1rem 1.4rem .7rem;
        border-bottom: 1px solid #f0f3f7;
    }
    .owner-row {
        display:       flex;
        align-items:   center;
        gap:           .85rem;
        padding:       .85rem 1.25rem;
        border-bottom: 1px solid #f5f7f9;
        transition:    background .15s;
    }
    .owner-row:last-child { border-bottom: none; }
    .owner-row:hover      { background: #fafcff; }
    .owner-avatar {
        width:         40px;
        height:        40px;
        border-radius: 10px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .95rem;
        font-weight:   700;
        color:         #fff;
        flex-shrink:   0;
    }
    .owner-info { flex: 1; min-width: 0; }
    .owner-name {
        font-size:   .875rem;
        font-weight: 600;
        color:       #0D1B2A;
        white-space: nowrap;
        overflow:    hidden;
        text-overflow: ellipsis;
    }
    .owner-meta {
        font-size: .75rem;
        color:     #5A6A7A;
        margin-top: .1rem;
    }
    .owner-revenue {
        font-size:   .875rem;
        font-weight: 700;
        color:       #0F3D56;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading ───────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Dashboard
            </h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Welcome back, Admin. Here's what's happening today.
            </p>
        </div>
        <span style="font-size:.8rem; color:#8899aa;">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->format('d M Y') }}
        </span>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ROW 1 — Stat cards
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Total Users --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(15,61,86,.1);">
                    <i class="bi bi-people-fill" style="color:#0F3D56;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="stat-value">4,820</span>
                        <span class="badge-growth badge-up">
                            <i class="bi bi-arrow-up-short"></i>12.4%
                        </span>
                    </div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>

        {{-- Parking Owners --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(46,204,113,.12);">
                    <i class="bi bi-person-badge-fill" style="color:#2ECC71;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="stat-value">318</span>
                        <span class="badge-growth badge-up">
                            <i class="bi bi-arrow-up-short"></i>8.1%
                        </span>
                    </div>
                    <div class="stat-label">Parking Owners</div>
                </div>
            </div>
        </div>

        {{-- Total Parkings --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(52,144,220,.12);">
                    <i class="bi bi-p-square-fill" style="color:#3490dc;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="stat-value">1,074</span>
                        <span class="badge-growth badge-up">
                            <i class="bi bi-arrow-up-short"></i>5.3%
                        </span>
                    </div>
                    <div class="stat-label">Total Parkings</div>
                </div>
            </div>
        </div>

        {{-- Total Bookings --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(255,140,0,.12);">
                    <i class="bi bi-calendar2-check-fill" style="color:#f59e0b;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="stat-value">9,251</span>
                        <span class="badge-growth badge-down">
                            <i class="bi bi-arrow-down-short"></i>2.7%
                        </span>
                    </div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
        </div>

    </div>{{-- /row 1 --}}


    {{-- ══════════════════════════════════════════════════════════
         ROW 2 — Revenue Overview + Parking Overview
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Revenue Overview ──────────────────────────────────── --}}
        <div class="col-12 col-lg-7">
            <div class="overview-card h-100">
                <div class="overview-header d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-700" style="font-weight:700;color:#0D1B2A;">
                            Revenue Overview
                        </h6>
                        <p class="mb-0" style="font-size:.78rem;color:#5A6A7A;">
                            Monthly earnings — current year
                        </p>
                    </div>
                    <span class="badge-growth badge-up px-2 py-1" style="font-size:.78rem;">
                        <i class="bi bi-arrow-up-short"></i> ₹ 2.4L this month
                    </span>
                </div>
                <div class="overview-body">

                    {{-- Summary figures --}}
                    <div class="row g-3 mb-3">
                        <div class="col-4 text-center">
                            <div style="font-size:1.25rem;font-weight:700;color:#0D1B2A;">₹ 18.6L</div>
                            <div style="font-size:.73rem;color:#5A6A7A;">YTD Revenue</div>
                        </div>
                        <div class="col-4 text-center" style="border-left:1px solid #f0f3f7;border-right:1px solid #f0f3f7;">
                            <div style="font-size:1.25rem;font-weight:700;color:#2ECC71;">₹ 2.4L</div>
                            <div style="font-size:.73rem;color:#5A6A7A;">This Month</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size:1.25rem;font-weight:700;color:#f59e0b;">₹ 1.9L</div>
                            <div style="font-size:.73rem;color:#5A6A7A;">Last Month</div>
                        </div>
                    </div>

                    {{-- Faux bar chart --}}
                    <div class="bar-chart">
                        @php
                            $months = [
                                ['Jan', 48], ['Feb', 62], ['Mar', 55], ['Apr', 71],
                                ['May', 66], ['Jun', 83], ['Jul', 75], ['Aug', 90],
                                ['Sep', 78], ['Oct', 68], ['Nov', 85], ['Dec', 92],
                            ];
                        @endphp
                        @foreach ($months as [$month, $pct])
                            <div class="bar-chart-col">
                                <div
                                    class="bar-chart-bar {{ $month === 'Dec' ? 'accent' : '' }}"
                                    style="height:{{ $pct }}%;"
                                    title="{{ $month }}: {{ $pct }}%"
                                ></div>
                                <span class="bar-chart-label">{{ $month }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- Parking Overview ──────────────────────────────────── --}}
        <div class="col-12 col-lg-5">
            <div class="overview-card h-100">
                <div class="overview-header d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-700" style="font-weight:700;color:#0D1B2A;">
                            Parking Overview
                        </h6>
                        <p class="mb-0" style="font-size:.78rem;color:#5A6A7A;">
                            Current slot utilisation
                        </p>
                    </div>
                </div>
                <div class="overview-body">

                    {{-- Utilisation bars --}}
                    @php
                        $utilisation = [
                            ['Occupied',  68, 'var(--sidebar-bg)',  '#0F3D56'],
                            ['Available', 22, 'var(--sidebar-accent)', '#2ECC71'],
                            ['Reserved',  10, '#f59e0b',            '#f59e0b'],
                        ];
                    @endphp
                    <div class="mb-3">
                        @foreach ($utilisation as [$label, $pct, $bg, $color])
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span style="font-size:.8rem;font-weight:600;color:#0D1B2A;">{{ $label }}</span>
                                <span style="font-size:.8rem;color:#5A6A7A;">{{ $pct }}%</span>
                            </div>
                            <div class="progress mb-3" style="height:8px;border-radius:6px;background:#f0f3f7;">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width:{{ $pct }}%; background:{{ $color }}; border-radius:6px;"
                                    aria-valuenow="{{ $pct }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Slot grid --}}
                    <p style="font-size:.75rem;font-weight:600;color:#8899aa;text-transform:uppercase;letter-spacing:.05em;" class="mb-2">
                        Sample Slot Map — Zone A
                    </p>
                    <div class="slot-grid">
                        @php
                            $slots = [
                                'A1' => 'occupied',  'A2' => 'available', 'A3' => 'occupied',  'A4' => 'reserved',
                                'B1' => 'available', 'B2' => 'occupied',  'B3' => 'occupied',  'B4' => 'available',
                                'C1' => 'reserved',  'C2' => 'occupied',  'C3' => 'available', 'C4' => 'occupied',
                                'D1' => 'occupied',  'D2' => 'available', 'D3' => 'reserved',  'D4' => 'occupied',
                            ];
                        @endphp
                        @foreach ($slots as $id => $state)
                            <div class="slot-box {{ $state }}">{{ $id }}</div>
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div class="d-flex gap-3 mt-3">
                        <span style="font-size:.72rem;color:#5A6A7A;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#0F3D56;margin-right:4px;"></span>Occupied
                        </span>
                        <span style="font-size:.72rem;color:#5A6A7A;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#2ECC71;margin-right:4px;"></span>Available
                        </span>
                        <span style="font-size:.72rem;color:#5A6A7A;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#f59e0b;margin-right:4px;"></span>Reserved
                        </span>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /row 2 --}}


    {{-- ══════════════════════════════════════════════════════════
         ROW 3 — Recent Bookings + Top Parking Owners
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3">

        {{-- Recent Bookings ───────────────────────────────────── --}}
        <div class="col-12 col-xl-7">
            <div class="dash-table h-100">
                <div class="dash-table-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-700" style="font-weight:700;color:#0D1B2A;">
                        Recent Bookings
                    </h6>
                    <a href="#" class="btn btn-sm" style="font-size:.78rem;color:#0F3D56;font-weight:600;">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>User</th>
                                <th>Parking</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $bookings = [
                                    ['#10281', 'Riya Sharma',   'Green Park, Delhi',     '28 Jul 2025', '₹ 120', 'confirmed'],
                                    ['#10280', 'Aman Verma',    'Metro Station, Mumbai', '28 Jul 2025', '₹ 85',  'pending'],
                                    ['#10279', 'Sneha Patel',   'City Mall, Ahmedabad',  '27 Jul 2025', '₹ 200', 'completed'],
                                    ['#10278', 'Rohan Mehta',   'Sector 18, Noida',      '27 Jul 2025', '₹ 150', 'cancelled'],
                                    ['#10277', 'Priya Singh',   'MG Road, Bengaluru',    '26 Jul 2025', '₹ 95',  'confirmed'],
                                    ['#10276', 'Karan Kapoor',  'Saket, New Delhi',      '26 Jul 2025', '₹ 175', 'completed'],
                                    ['#10275', 'Divya Nair',    'Andheri West, Mumbai',  '25 Jul 2025', '₹ 110', 'confirmed'],
                                ];
                            @endphp
                            @foreach ($bookings as [$id, $user, $parking, $date, $amount, $status])
                                <tr>
                                    <td style="font-weight:600;color:#0F3D56;font-size:.8rem;">{{ $id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="
                                                    width:30px;height:30px;border-radius:8px;
                                                    background:rgba(15,61,86,.1);color:#0F3D56;
                                                    display:flex;align-items:center;justify-content:center;
                                                    font-size:.75rem;font-weight:700;flex-shrink:0;
                                                "
                                            >
                                                {{ strtoupper(substr($user,0,1)) }}
                                            </div>
                                            <span style="font-size:.855rem;font-weight:500;">{{ $user }}</span>
                                        </div>
                                    </td>
                                    <td style="color:#5A6A7A;font-size:.82rem;">{{ $parking }}</td>
                                    <td style="color:#5A6A7A;font-size:.82rem;white-space:nowrap;">{{ $date }}</td>
                                    <td style="font-weight:600;">{{ $amount }}</td>
                                    <td>
                                        <span class="status-pill status-{{ $status }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Parking Owners ─────────────────────────────────── --}}
        <div class="col-12 col-xl-5">
            <div class="owner-list h-100">
                <div class="owner-list-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-700" style="font-weight:700;color:#0D1B2A;">
                        Top Parking Owners
                    </h6>
                    <a href="#" style="font-size:.78rem;color:#0F3D56;font-weight:600;text-decoration:none;">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                @php
                    $owners = [
                        ['Vikram Joshi',   'Delhi NCR',    '24 parkings',  '₹ 58,400', '0F3D56'],
                        ['Meena Reddy',    'Hyderabad',    '18 parkings',  '₹ 44,120', '1a7a50'],
                        ['Sanjay Gupta',   'Mumbai',       '15 parkings',  '₹ 39,750', '2d6a8f'],
                        ['Pooja Iyer',     'Bengaluru',    '12 parkings',  '₹ 31,200', '8a4d9e'],
                        ['Harish Rao',     'Chennai',      '10 parkings',  '₹ 26,880', 'c0392b'],
                        ['Anjali Bose',    'Kolkata',      '9 parkings',   '₹ 22,500', 'd35400'],
                        ['Rahul Trivedi',  'Pune',         '8 parkings',   '₹ 19,300', '27ae60'],
                    ];
                @endphp

                @foreach ($owners as $index => [$name, $city, $lots, $revenue, $avatarBg])
                    <div class="owner-row">
                        {{-- Rank --}}
                        <span style="font-size:.75rem;font-weight:700;color:#8899aa;width:18px;text-align:center;flex-shrink:0;">
                            {{ $index + 1 }}
                        </span>
                        {{-- Avatar --}}
                        <div class="owner-avatar" style="background:#{{ $avatarBg }};">
                            {{ strtoupper(substr($name, 0, 1)) }}
                        </div>
                        {{-- Info --}}
                        <div class="owner-info">
                            <div class="owner-name">{{ $name }}</div>
                            <div class="owner-meta">
                                <i class="bi bi-geo-alt me-1" style="font-size:.7rem;"></i>{{ $city }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-p-square me-1" style="font-size:.7rem;"></i>{{ $lots }}
                            </div>
                        </div>
                        {{-- Revenue --}}
                        <div class="owner-revenue">{{ $revenue }}</div>
                    </div>
                @endforeach

            </div>
        </div>

    </div>{{-- /row 3 --}}

@endsection