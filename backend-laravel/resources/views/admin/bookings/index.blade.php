{{-- ============================================================
     Booking Management — Index
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  List all bookings with summary cards, search,
               filters, status badges and CRUD action buttons.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Booking Management')
@section('page-title', 'Booking Management')

@push('styles')
<style>
    /* ── Summary cards ───────────────────────────────────────── */
    .stat-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        padding:       1.2rem 1.4rem;
        display:       flex;
        align-items:   center;
        gap:           1rem;
        height:        100%;
    }
    .stat-icon {
        width:           52px;
        height:          52px;
        border-radius:   13px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1.4rem;
        flex-shrink:     0;
    }
    .stat-value {
        font-size:   1.7rem;
        font-weight: 800;
        color:       #0D1B2A;
        line-height: 1;
        margin-bottom: .2rem;
    }
    .stat-label {
        font-size:   .78rem;
        font-weight: 600;
        color:       #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .stat-trend {
        font-size:   .76rem;
        font-weight: 600;
        margin-top:  .3rem;
    }

    /* ── Page card shell ─────────────────────────────────────── */
    .page-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .page-card-header {
        padding:       1rem 1.4rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
    }

    /* ── Filter panel ────────────────────────────────────────── */
    .filter-label {
        font-size:   .76rem;
        font-weight: 600;
        color:       #5A6A7A;
        margin-bottom: .3rem;
        display:     block;
    }
    .filter-control {
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        font-size:     .845rem;
        height:        36px;
        color:         #0D1B2A;
        width:         100%;
        padding:       0 .75rem;
        outline:       none;
        background:    #fff;
        transition:    border-color .18s;
    }
    .filter-control:focus { border-color: #0F3D56; box-shadow: none; }

    /* ── Search ──────────────────────────────────────────────── */
    .search-wrap {
        position: relative;
    }
    .search-wrap .bi-search {
        position:       absolute;
        left:           .8rem;
        top:            50%;
        transform:      translateY(-50%);
        color:          #8899aa;
        font-size:      .82rem;
        pointer-events: none;
    }
    .search-wrap input {
        padding-left: 2.1rem;
    }

    /* ── Toolbar buttons ─────────────────────────────────────── */
    .btn-filter {
        height:        36px;
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        background:    #fff;
        color:         #0D1B2A;
        font-size:     .845rem;
        font-weight:   600;
        padding:       0 .9rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        white-space:   nowrap;
        cursor:        pointer;
        transition:    background .15s, border-color .15s;
    }
    .btn-filter:hover { background: #f0f3f7; border-color: #c8d2dc; }

    .btn-export {
        height:        36px;
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        background:    #fff;
        color:         #0D1B2A;
        font-size:     .845rem;
        font-weight:   600;
        padding:       0 .9rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        white-space:   nowrap;
        cursor:        pointer;
        transition:    background .15s;
        text-decoration: none;
    }
    .btn-export:hover { background: #f0f3f7; color: #0D1B2A; }

    /* ── Table ───────────────────────────────────────────────── */
    .bookings-table thead th {
        font-size:      .74rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom:  1px solid #f0f3f7 !important;
        border-top:     none !important;
        background:     #fafbfc;
        padding:        .7rem 1rem;
        white-space:    nowrap;
    }
    .bookings-table tbody td {
        font-size:      .855rem;
        padding:        .8rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .bookings-table tbody tr:last-child td { border-bottom: none; }
    .bookings-table tbody tr:hover td      { background: #fafcff; }

    /* ── Avatar ──────────────────────────────────────────────── */
    .user-avatar {
        width:           32px;
        height:          32px;
        border-radius:   8px;
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        font-size:       .75rem;
        font-weight:     700;
        color:           #fff;
        flex-shrink:     0;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .status-badge {
        display:        inline-flex;
        align-items:    center;
        gap:            .3rem;
        padding:        .28em .75em;
        border-radius:  20px;
        font-size:      .72rem;
        font-weight:    600;
        letter-spacing: .02em;
        white-space:    nowrap;
    }
    .badge-confirmed  { background: rgba(46,204,113,.14);  color: #1aaa5a; }
    .badge-active     { background: rgba(2,136,209,.12);   color: #0277bd; }
    .badge-completed  { background: rgba(15,61,86,.1);     color: #0F3D56; }
    .badge-cancelled  { background: rgba(231,76,60,.12);   color: #c0392b; }
    .badge-pending    { background: rgba(245,158,11,.14);  color: #b45309; }
    .badge-expired    { background: rgba(143,163,180,.15); color: #5A6A7A; }

    /* ── Action buttons ──────────────────────────────────────── */
    .action-btn {
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        width:           28px;
        height:          28px;
        border-radius:   7px;
        border:          1px solid transparent;
        font-size:       .8rem;
        cursor:          pointer;
        transition:      background .15s, border-color .15s, color .15s;
        text-decoration: none;
    }
    .action-btn-view         { background: rgba(15,61,86,.08);  color: #0F3D56; }
    .action-btn-view:hover   { background: #0F3D56; color: #fff; border-color: #0F3D56; }
    .action-btn-edit         { background: rgba(52,144,220,.1); color: #3490dc; }
    .action-btn-edit:hover   { background: #3490dc; color: #fff; border-color: #3490dc; }
    .action-btn-cancel       { background: rgba(231,76,60,.1);  color: #e74c3c; }
    .action-btn-cancel:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination .page-link {
        border-radius: 7px !important;
        margin:        0 2px;
        font-size:     .82rem;
        color:         #0F3D56;
        border:        1px solid #e2e8ee;
        padding:       .35rem .65rem;
        transition:    background .15s, color .15s;
    }
    .pagination .page-link:hover { background: #f0f3f7; border-color: #c8d2dc; color: #0F3D56; }
    .pagination .page-item.active .page-link {
        background:   #0F3D56;
        border-color: #0F3D56;
        color:        #fff;
    }
    .pagination .page-item.disabled .page-link { color: #c0c8d0; }

    /* ── Amount ──────────────────────────────────────────────── */
    .amount-val {
        font-weight: 700;
        color:       #0D1B2A;
        font-size:   .875rem;
    }

    /* ── Vehicle chip ────────────────────────────────────────── */
    .vehicle-chip {
        display:        inline-flex;
        align-items:    center;
        gap:            .3rem;
        padding:        .2em .6em;
        background:     #f0f3f7;
        border-radius:  6px;
        font-size:      .75rem;
        font-weight:    500;
        color:          #5A6A7A;
        white-space:    nowrap;
    }

    /* ── Dot indicator ───────────────────────────────────────── */
    .dot {
        width:         7px;
        height:        7px;
        border-radius: 50%;
        display:       inline-block;
        flex-shrink:   0;
    }
</style>
@endpush

@section('content')

    @php
        /* ── Dummy booking data ───────────────────────────────── */
        $bookings = [
            [
                'id'          => 'BK-20250001',
                'user_name'   => 'Arpit Sharma',
                'user_color'  => '0F3D56',
                'parking'     => 'Cyber Hub Parking',
                'vehicle_no'  => 'HR26DQ8849',
                'vehicle_type'=> 'Car',
                'entry_time'  => '15 Jul 2025, 09:00 AM',
                'exit_time'   => '15 Jul 2025, 11:00 AM',
                'duration'    => '2 hrs',
                'amount'      => '₹160',
                'status'      => 'completed',
            ],
            [
                'id'          => 'BK-20250002',
                'user_name'   => 'Priya Nair',
                'user_color'  => '8a4d9e',
                'parking'     => 'Saket Mall Parking',
                'vehicle_no'  => 'DL01AB1234',
                'vehicle_type'=> 'Motorcycle',
                'entry_time'  => '15 Jul 2025, 10:30 AM',
                'exit_time'   => '15 Jul 2025, 12:30 PM',
                'duration'    => '2 hrs',
                'amount'      => '₹60',
                'status'      => 'active',
            ],
            [
                'id'          => 'BK-20250003',
                'user_name'   => 'Rohit Verma',
                'user_color'  => '27ae60',
                'parking'     => 'Connaught Place Parking',
                'vehicle_no'  => 'UP32GH5678',
                'vehicle_type'=> 'SUV',
                'entry_time'  => '15 Jul 2025, 11:00 AM',
                'exit_time'   => '15 Jul 2025, 02:00 PM',
                'duration'    => '3 hrs',
                'amount'      => '₹300',
                'status'      => 'confirmed',
            ],
            [
                'id'          => 'BK-20250004',
                'user_name'   => 'Sneha Kapoor',
                'user_color'  => 'd35400',
                'parking'     => 'Karol Bagh Parking',
                'vehicle_no'  => 'MH02CD3456',
                'vehicle_type'=> 'Car',
                'entry_time'  => '14 Jul 2025, 08:00 AM',
                'exit_time'   => '14 Jul 2025, 10:00 AM',
                'duration'    => '2 hrs',
                'amount'      => '₹160',
                'status'      => 'cancelled',
            ],
            [
                'id'          => 'BK-20250005',
                'user_name'   => 'Karan Mehta',
                'user_color'  => '2980B2',
                'parking'     => 'Dwarka Sector 10',
                'vehicle_no'  => 'GJ05EF7890',
                'vehicle_type'=> 'EV',
                'entry_time'  => '14 Jul 2025, 03:00 PM',
                'exit_time'   => '14 Jul 2025, 05:30 PM',
                'duration'    => '2.5 hrs',
                'amount'      => '₹250',
                'status'      => 'completed',
            ],
            [
                'id'          => 'BK-20250006',
                'user_name'   => 'Anjali Bose',
                'user_color'  => '1a7a50',
                'parking'     => 'Lajpat Nagar Parking',
                'vehicle_no'  => 'KA03GH1122',
                'vehicle_type'=> 'Motorcycle',
                'entry_time'  => '13 Jul 2025, 06:00 PM',
                'exit_time'   => '13 Jul 2025, 07:00 PM',
                'duration'    => '1 hr',
                'amount'      => '₹30',
                'status'      => 'completed',
            ],
            [
                'id'          => 'BK-20250007',
                'user_name'   => 'Divya Iyer',
                'user_color'  => 'c0392b',
                'parking'     => 'Cyber Hub Parking',
                'vehicle_no'  => 'TN09IJ3344',
                'vehicle_type'=> 'Car',
                'entry_time'  => '16 Jul 2025, 07:30 AM',
                'exit_time'   => '16 Jul 2025, 09:30 AM',
                'duration'    => '2 hrs',
                'amount'      => '₹160',
                'status'      => 'pending',
            ],
            [
                'id'          => 'BK-20250008',
                'user_name'   => 'Vikram Joshi',
                'user_color'  => '2e86ab',
                'parking'     => 'Saket Mall Parking',
                'vehicle_no'  => 'RJ14KL5566',
                'vehicle_type'=> 'SUV',
                'entry_time'  => '12 Jul 2025, 01:00 PM',
                'exit_time'   => '12 Jul 2025, 04:00 PM',
                'duration'    => '3 hrs',
                'amount'      => '₹300',
                'status'      => 'expired',
            ],
            [
                'id'          => 'BK-20250009',
                'user_name'   => 'Meena Reddy',
                'user_color'  => '8e44ad',
                'parking'     => 'Connaught Place Parking',
                'vehicle_no'  => 'AP28MN7788',
                'vehicle_type'=> 'EV',
                'entry_time'  => '16 Jul 2025, 10:00 AM',
                'exit_time'   => '16 Jul 2025, 12:00 PM',
                'duration'    => '2 hrs',
                'amount'      => '₹200',
                'status'      => 'active',
            ],
            [
                'id'          => 'BK-20250010',
                'user_name'   => 'Harish Rao',
                'user_color'  => '1a6b8a',
                'parking'     => 'Dwarka Sector 10',
                'vehicle_no'  => 'HR26PQ9900',
                'vehicle_type'=> 'Car',
                'entry_time'  => '11 Jul 2025, 09:00 AM',
                'exit_time'   => '11 Jul 2025, 11:00 AM',
                'duration'    => '2 hrs',
                'amount'      => '₹160',
                'status'      => 'cancelled',
            ],
        ];

        $statusConfig = [
            'confirmed' => ['label' => 'Confirmed', 'class' => 'badge-confirmed', 'dot' => '#2ECC71'],
            'active'    => ['label' => 'Active',    'class' => 'badge-active',    'dot' => '#0288D1'],
            'completed' => ['label' => 'Completed', 'class' => 'badge-completed', 'dot' => '#0F3D56'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'badge-cancelled', 'dot' => '#e74c3c'],
            'pending'   => ['label' => 'Pending',   'class' => 'badge-pending',   'dot' => '#F59E0B'],
            'expired'   => ['label' => 'Expired',   'class' => 'badge-expired',   'dot' => '#8FA3B4'],
        ];

        $vehicleIcons = [
            'Car'        => 'bi-car-front',
            'Motorcycle' => 'bi-scooter',
            'SUV'        => 'bi-truck',
            'EV'         => 'bi-lightning-charge',
        ];
    @endphp

    {{-- ── Page heading ─────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">Booking Management</h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Monitor and manage all parking bookings across the platform.
            </p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                <li class="breadcrumb-item">
                    <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                </li>
                <li class="breadcrumb-item active" style="color:#8899aa;">Booking Management</li>
            </ol>
        </nav>
    </div>

    {{-- ════════════════════════════════════════════════════════
         SUMMARY CARDS
    ════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Total Bookings --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(15,61,86,.1);">
                    <i class="bi bi-calendar2-check" style="color:#0F3D56;"></i>
                </div>
                <div>
                    <div class="stat-value">1,284</div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-trend" style="color:#2ECC71;">
                        <i class="bi bi-arrow-up-short"></i> 12% this month
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Bookings --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(2,136,209,.1);">
                    <i class="bi bi-play-circle" style="color:#0288D1;"></i>
                </div>
                <div>
                    <div class="stat-value" style="color:#0288D1;">48</div>
                    <div class="stat-label">Active Now</div>
                    <div class="stat-trend" style="color:#5A6A7A;">
                        <i class="bi bi-dot"></i> Live right now
                    </div>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(46,204,113,.12);">
                    <i class="bi bi-check-circle" style="color:#1aaa5a;"></i>
                </div>
                <div>
                    <div class="stat-value" style="color:#1aaa5a;">1,091</div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-trend" style="color:#2ECC71;">
                        <i class="bi bi-arrow-up-short"></i> 8% this month
                    </div>
                </div>
            </div>
        </div>

        {{-- Cancelled --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(231,76,60,.1);">
                    <i class="bi bi-x-circle" style="color:#e74c3c;"></i>
                </div>
                <div>
                    <div class="stat-value" style="color:#e74c3c;">145</div>
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-trend" style="color:#e74c3c;">
                        <i class="bi bi-arrow-down-short"></i> 3% this month
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════
         MAIN TABLE CARD
    ════════════════════════════════════════════════════════ --}}
    <div class="page-card">

        {{-- ── Toolbar ─────────────────────────────────────────── --}}
        <div class="page-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">

            {{-- Left: Search + Filter toggle --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">

                {{-- Search --}}
                <div class="search-wrap" style="min-width:220px; max-width:260px; width:100%;">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        class="filter-control"
                        placeholder="Search booking ID or user…"
                        aria-label="Search bookings"
                    >
                </div>

                {{-- Filter toggle --}}
                <button
                    class="btn-filter"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterPanel"
                    aria-expanded="false"
                    aria-controls="filterPanel"
                >
                    <i class="bi bi-funnel"></i> Filters
                </button>

            </div>

            {{-- Right: Export --}}
            <div class="d-flex align-items-center gap-2">
                <a href="#" class="btn-export">
                    <i class="bi bi-download" style="font-size:.8rem;"></i> Export
                </a>
            </div>

        </div>{{-- /toolbar --}}

        {{-- ── Filter Panel ────────────────────────────────────── --}}
        <div class="collapse" id="filterPanel">
            <div class="px-4 py-3" style="background:#f8f9fa; border-bottom:1px solid #f0f3f7;">
                <div class="row g-3 align-items-end">

                    {{-- Booking ID --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Booking ID</label>
                        <input type="text" class="filter-control" placeholder="BK-20250001">
                    </div>

                    {{-- User Name --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">User Name</label>
                        <input type="text" class="filter-control" placeholder="e.g. Arpit">
                    </div>

                    {{-- Parking Name --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Parking Name</label>
                        <select class="filter-control">
                            <option value="">All Parkings</option>
                            <option>Cyber Hub Parking</option>
                            <option>Saket Mall Parking</option>
                            <option>Connaught Place Parking</option>
                            <option>Karol Bagh Parking</option>
                            <option>Dwarka Sector 10</option>
                            <option>Lajpat Nagar Parking</option>
                        </select>
                    </div>

                    {{-- Vehicle Type --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Vehicle Type</label>
                        <select class="filter-control">
                            <option value="">All Types</option>
                            <option>Car</option>
                            <option>Motorcycle</option>
                            <option>SUV</option>
                            <option>EV</option>
                        </select>
                    </div>

                    {{-- Booking Status --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Status</label>
                        <select class="filter-control">
                            <option value="">All Statuses</option>
                            <option>Confirmed</option>
                            <option>Active</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                            <option>Pending</option>
                            <option>Expired</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Date From</label>
                        <input type="date" class="filter-control">
                    </div>

                    {{-- Date To --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">Date To</label>
                        <input type="date" class="filter-control">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="filter-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                style="height:36px; padding:0 1rem; background:#0F3D56; color:#fff; border:none; border-radius:8px; font-size:.83rem; font-weight:600; cursor:pointer; white-space:nowrap;"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                style="height:36px; padding:0 .85rem; background:#fff; color:#5A6A7A; border:1px solid #e2e8ee; border-radius:8px; font-size:.83rem; cursor:pointer;"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>{{-- /filter panel --}}

        {{-- ── Table ───────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table bookings-table mb-0">
                <thead>
                    <tr>
                        <th style="width:130px;">Booking ID</th>
                        <th>User</th>
                        <th>Parking</th>
                        <th>Vehicle</th>
                        <th style="white-space:nowrap;">Entry Time</th>
                        <th style="white-space:nowrap;">Exit Time</th>
                        <th style="width:90px;">Amount</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:100px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $b)
                        @php
                            $sc  = $statusConfig[$b['status']];
                            $vic = $vehicleIcons[$b['vehicle_type']] ?? 'bi-car-front';
                        @endphp
                        <tr>

                            {{-- Booking ID --}}
                            <td>
                                <span style="
                                    font-size:   .78rem;
                                    font-weight: 700;
                                    color:       #0F3D56;
                                    font-family: monospace;
                                    letter-spacing: .02em;
                                ">
                                    {{ $b['id'] }}
                                </span>
                                <div style="font-size:.72rem; color:#8899aa; margin-top:.15rem;">
                                    {{ $b['duration'] }}
                                </div>
                            </td>

                            {{-- User --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        class="user-avatar"
                                        style="background:#{{ $b['user_color'] }};"
                                    >
                                        {{ strtoupper(substr($b['user_name'], 0, 1)) }}
                                    </div>
                                    <span style="font-weight:600; white-space:nowrap;">
                                        {{ $b['user_name'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Parking --}}
                            <td style="color:#5A6A7A;">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-geo-alt" style="font-size:.75rem; color:#8899aa; flex-shrink:0;"></i>
                                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; display:inline-block;">
                                        {{ $b['parking'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Vehicle --}}
                            <td>
                                <div style="font-weight:600; font-size:.83rem; color:#0D1B2A; margin-bottom:.2rem; font-family:monospace; letter-spacing:.04em;">
                                    {{ $b['vehicle_no'] }}
                                </div>
                                <span class="vehicle-chip">
                                    <i class="bi {{ $vic }}" style="font-size:.78rem;"></i>
                                    {{ $b['vehicle_type'] }}
                                </span>
                            </td>

                            {{-- Entry Time --}}
                            <td style="color:#5A6A7A; font-size:.82rem; white-space:nowrap;">
                                <i class="bi bi-arrow-right-circle" style="color:#2ECC71; font-size:.78rem; margin-right:4px;"></i>
                                {{ $b['entry_time'] }}
                            </td>

                            {{-- Exit Time --}}
                            <td style="color:#5A6A7A; font-size:.82rem; white-space:nowrap;">
                                <i class="bi bi-arrow-left-circle" style="color:#e74c3c; font-size:.78rem; margin-right:4px;"></i>
                                {{ $b['exit_time'] }}
                            </td>

                            {{-- Amount --}}
                            <td>
                                <span class="amount-val">{{ $b['amount'] }}</span>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="status-badge {{ $sc['class'] }}">
                                    <span class="dot" style="background:{{ $sc['dot'] }};"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">

                                    {{-- View --}}
                                    <a href="#" class="action-btn action-btn-view" title="View Booking">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="#" class="action-btn action-btn-edit" title="Edit Booking">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Cancel --}}
                                    @if (!in_array($b['status'], ['cancelled', 'completed', 'expired']))
                                        <button
                                            type="button"
                                            class="action-btn action-btn-cancel"
                                            title="Cancel Booking"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelModal"
                                            data-booking="{{ $b['id'] }}"
                                            data-user="{{ $b['user_name'] }}"
                                        >
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            class="action-btn"
                                            disabled
                                            title="Cannot cancel"
                                            style="background:#f5f7f9; color:#c0c8d0; cursor:not-allowed;"
                                        >
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>{{-- /table-responsive --}}

        {{-- ── Footer: meta + pagination ───────────────────────── --}}
        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-3"
            style="border-top:1px solid #f0f3f7; background:#fafbfc;"
        >
            <p class="mb-0" style="font-size:.8rem; color:#8899aa;">
                Showing <strong style="color:#0D1B2A;">1–10</strong> of
                <strong style="color:#0D1B2A;">1,284</strong> bookings
            </p>

            <nav aria-label="Bookings pagination">
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Previous">
                            <i class="bi bi-chevron-left" style="font-size:.68rem;"></i>
                        </a>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <span class="page-link" style="border:none; background:transparent; color:#8899aa; cursor:default;">…</span>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">129</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <i class="bi bi-chevron-right" style="font-size:.68rem;"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>{{-- /page-card --}}

    {{-- ════════════════════════════════════════════════════════
         Cancel Confirmation Modal
    ════════════════════════════════════════════════════════ --}}
    <div
        class="modal fade"
        id="cancelModal"
        tabindex="-1"
        aria-labelledby="cancelModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
            <div class="modal-content" style="border-radius:14px; border:1px solid #e2e8ee; overflow:hidden;">
                <div class="modal-body text-center p-4">

                    {{-- Icon --}}
                    <div
                        class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:58px; height:58px; background:rgba(231,76,60,.1); border-radius:14px;"
                    >
                        <i class="bi bi-calendar-x" style="font-size:1.5rem; color:#e74c3c;"></i>
                    </div>

                    <h6 class="mb-1" style="font-weight:700; color:#0D1B2A;">Cancel Booking?</h6>
                    <p class="mb-1" style="font-size:.855rem; color:#5A6A7A;">
                        You are about to cancel booking
                    </p>
                    <p class="mb-3" style="font-size:.855rem;">
                        <strong id="cancelBookingId" style="color:#0F3D56;"></strong>
                        for <strong id="cancelUserName"></strong>.
                    </p>
                    <p style="font-size:.8rem; color:#e74c3c; margin-bottom:1.25rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This action cannot be undone. A cancellation notification will be sent to the user.
                    </p>

                    <div class="d-flex gap-2 justify-content-center">
                        <button
                            type="button"
                            class="btn btn-sm px-4"
                            data-bs-dismiss="modal"
                            style="border:1px solid #e2e8ee; border-radius:8px; font-size:.855rem; color:#5A6A7A;"
                        >
                            Keep Booking
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm px-4"
                            style="background:#e74c3c; color:#fff; border-radius:8px; font-size:.855rem; font-weight:600;"
                        >
                            Yes, Cancel
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Populate cancel modal with booking details
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            document.getElementById('cancelBookingId').textContent = trigger.getAttribute('data-booking');
            document.getElementById('cancelUserName').textContent  = trigger.getAttribute('data-user');
        });
    }
</script>
@endpush