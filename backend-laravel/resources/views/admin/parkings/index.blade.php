@extends('layouts.admin')

@section('title', 'Parking Management')
@section('page-title', 'Parking Management')

@push('styles')
<style>
    /* ── Shared card shell ───────────────────────────────────── */
    .dash-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
        height:        100%;
    }
    .dash-card-header {
        display:       flex;
        align-items:   center;
        justify-content: space-between;
        gap:           .65rem;
        padding:       .9rem 1.25rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
    }
    .dash-card-header h6 {
        margin:      0;
        font-size:   .875rem;
        font-weight: 700;
        color:       #0D1B2A;
    }
    .dash-card-body { padding: 1.25rem; }

    /* ── Summary stat cards ──────────────────────────────────── */
    .stat-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        padding:       1.2rem 1.3rem;
        position:      relative;
        overflow:      hidden;
        height:        100%;
        transition:    box-shadow .18s, transform .18s;
    }
    .stat-card:hover {
        box-shadow:  0 6px 22px rgba(15,61,86,.11);
        transform:   translateY(-2px);
    }
    .stat-card .watermark {
        position:    absolute;
        right:       -14px;
        bottom:      -14px;
        font-size:   5.5rem;
        opacity:     .05;
        line-height: 1;
        pointer-events: none;
        user-select: none;
    }
    .stat-icon {
        width:           44px;
        height:          44px;
        border-radius:   11px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1.2rem;
        flex-shrink:     0;
    }
    .stat-label {
        font-size:      .74rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom:  .25rem;
    }
    .stat-value {
        font-size:   1.55rem;
        font-weight: 800;
        color:       #0D1B2A;
        line-height: 1.15;
    }
    .stat-change {
        display:     inline-flex;
        align-items: center;
        gap:         .25rem;
        font-size:   .76rem;
        font-weight: 600;
        margin-top:  .3rem;
    }
    .stat-change.up   { color: #1aaa5a; }
    .stat-change.down { color: #e74c3c; }
    .stat-sub {
        font-size:  .74rem;
        color:      #8899aa;
        margin-top: .15rem;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .sbadge {
        display:        inline-flex;
        align-items:    center;
        gap:            .25rem;
        padding:        .25em .75em;
        border-radius:  20px;
        font-size:      .72rem;
        font-weight:    600;
        white-space:    nowrap;
    }
    .sbadge-active   { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-pending  { background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-disabled { background:rgba(231,76,60,.12);   color:#c0392b; }

    /* ── Table ────────────────────────────────────────────────── */
    .parking-table { width:100%; border-collapse:collapse; }
    .parking-table th {
        font-size:      .72rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding:        .65rem 1rem;
        border-bottom:  1px solid #f0f3f7;
        background:     #fafbfc;
        white-space:    nowrap;
    }
    .parking-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .parking-table tr:last-child td { border-bottom:none; }
    .parking-table tr:hover td      { background:#fafcff; }

    /* ── Action buttons ──────────────────────────────────────── */
    .btn-action {
        width:           32px;
        height:          32px;
        border-radius:   8px;
        border:          none;
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        transition:      all .15s;
        text-decoration: none;
        font-size:       .85rem;
    }
    .btn-action-view   { background:rgba(15,61,86,.1);  color:#0F3D56; }
    .btn-action-view:hover { background:#0F3D56; color:#fff; }
    .btn-action-edit   { background:rgba(46,204,113,.12); color:#1aaa5a; }
    .btn-action-edit:hover { background:#1aaa5a; color:#fff; }
    .btn-action-delete { background:rgba(231,76,60,.1);   color:#e74c3c; }
    .btn-action-delete:hover { background:#e74c3c; color:#fff; }

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination-custom {
        display:         flex;
        align-items:     center;
        gap:             .25rem;
        flex-wrap:       wrap;
    }
    .pagination-custom .page-item {
        list-style: none;
    }
    .pagination-custom .page-link {
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        min-width:       36px;
        height:          36px;
        padding:         0 .75rem;
        border-radius:   8px;
        border:          1px solid #e2e8ee;
        background:      #fff;
        color:           #0D1B2A;
        font-size:       .85rem;
        font-weight:     600;
        text-decoration: none;
        transition:      all .15s;
    }
    .pagination-custom .page-link:hover {
        background: #f0f3f7;
        border-color: #c8d9e6;
    }
    .pagination-custom .page-item.active .page-link {
        background: #0F3D56;
        border-color: #0F3D56;
        color: #fff;
    }
    .pagination-custom .page-item.disabled .page-link {
        opacity: .4;
        pointer-events: none;
    }
</style>
@endpush

@section('content')

    @php
        /* ── Summary stats ────────────────────────────────────── */
        $stats = [
            [
                'label'    => 'Total Parkings',
                'value'    => '48',
                'change'   => '+12.5%',
                'dir'      => 'up',
                'sub'      => 'vs last month 42',
                'icon'     => 'bi-building',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '🏢',
            ],
            [
                'label'    => 'Active Parkings',
                'value'    => '34',
                'change'   => '+8.2%',
                'dir'      => 'up',
                'sub'      => 'vs last month 31',
                'icon'     => 'bi-check-circle',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '✅',
            ],
            [
                'label'    => 'Pending Approval',
                'value'    => '7',
                'change'   => '+40%',
                'dir'      => 'up',
                'sub'      => 'vs last month 5',
                'icon'     => 'bi-clock-history',
                'iconBg'   => 'rgba(245,158,11,.13)',
                'iconColor'=> '#b45309',
                'wm'       => '⏳',
            ],
            [
                'label'    => 'Disabled Parkings',
                'value'    => '7',
                'change'   => '-12.5%',
                'dir'      => 'down',
                'sub'      => 'vs last month 8',
                'icon'     => 'bi-x-octagon',
                'iconBg'   => 'rgba(231,76,60,.1)',
                'iconColor'=> '#e74c3c',
                'wm'       => '🚫',
            ],
        ];

        /* ── Parking data ─────────────────────────────────────── */
        $parkings = [
            [
                'id'        => 'PRK-001',
                'name'      => 'Connaught Place Parking',
                'owner'     => 'Vikram Joshi',
                'city'      => 'New Delhi',
                'slots'     => 120,
                'available' => 105,
                'occupancy' => 88,
                'status'    => 'active',
                'date'      => '15 Jan 2025',
            ],
            [
                'id'        => 'PRK-002',
                'name'      => 'Ambience Mall Parking',
                'owner'     => 'Meena Reddy',
                'city'      => 'Gurugram',
                'slots'     => 85,
                'available' => 70,
                'occupancy' => 82,
                'status'    => 'active',
                'date'      => '20 Feb 2025',
            ],
            [
                'id'        => 'PRK-003',
                'name'      => 'Saket Mall Parking',
                'owner'     => 'Sanjay Gupta',
                'city'      => 'New Delhi',
                'slots'     => 95,
                'available' => 75,
                'occupancy' => 79,
                'status'    => 'active',
                'date'      => '10 Mar 2025',
            ],
            [
                'id'        => 'PRK-004',
                'name'      => 'DLF Phase 2 EV Hub',
                'owner'     => 'Pooja Iyer',
                'city'      => 'Gurugram',
                'slots'     => 50,
                'available' => 37,
                'occupancy' => 74,
                'status'    => 'pending',
                'date'      => '05 Apr 2025',
            ],
            [
                'id'        => 'PRK-005',
                'name'      => 'MGF Metropolitan Mall',
                'owner'     => 'Rahul Trivedi',
                'city'      => 'Gurugram',
                'slots'     => 60,
                'available' => 41,
                'occupancy' => 68,
                'status'    => 'active',
                'date'      => '12 Apr 2025',
            ],
            [
                'id'        => 'PRK-006',
                'name'      => 'City Center Parking',
                'owner'     => 'Anita Sharma',
                'city'      => 'Noida',
                'slots'     => 45,
                'available' => 32,
                'occupancy' => 71,
                'status'    => 'disabled',
                'date'      => '18 May 2025',
            ],
            [
                'id'        => 'PRK-007',
                'name'      => 'Pacific Mall Parking',
                'owner'     => 'Arjun Singh',
                'city'      => 'Ghaziabad',
                'slots'     => 70,
                'available' => 58,
                'occupancy' => 83,
                'status'    => 'active',
                'date'      => '22 Jun 2025',
            ],
            [
                'id'        => 'PRK-008',
                'name'      => 'Cyber Hub Parking',
                'owner'     => 'Neha Patel',
                'city'      => 'Gurugram',
                'slots'     => 40,
                'available' => 28,
                'occupancy' => 70,
                'status'    => 'pending',
                'date'      => '01 Jul 2025',
            ],
            [
                'id'        => 'PRK-009',
                'name'      => 'Select Citywalk Parking',
                'owner'     => 'Deepak Kumar',
                'city'      => 'New Delhi',
                'slots'     => 90,
                'available' => 75,
                'occupancy' => 83,
                'status'    => 'active',
                'date'      => '15 Jul 2025',
            ],
            [
                'id'        => 'PRK-010',
                'name'      => 'World Trade Center',
                'owner'     => 'Priya Joshi',
                'city'      => 'Noida',
                'slots'     => 55,
                'available' => 38,
                'occupancy' => 69,
                'status'    => 'disabled',
                'date'      => '20 Jul 2025',
            ],
        ];

        /* ── Filter options ───────────────────────────────────── */
        $cities = ['All Cities', 'New Delhi', 'Gurugram', 'Noida', 'Ghaziabad'];
        $owners = ['All Owners', 'Vikram Joshi', 'Meena Reddy', 'Sanjay Gupta', 'Pooja Iyer', 'Rahul Trivedi', 'Anita Sharma', 'Arjun Singh', 'Neha Patel', 'Deepak Kumar', 'Priya Joshi'];
        $statuses = ['All Status', 'Active', 'Pending', 'Disabled'];
        $types = ['All Types', 'Multi-level', 'Ground', 'Basement', 'EV Hub'];

        $statusBadgeMap = [
            'active'   => ['class'=>'sbadge-active',  'icon'=>'bi-check-circle-fill', 'label'=>'Active'],
            'pending'  => ['class'=>'sbadge-pending',  'icon'=>'bi-clock-fill',        'label'=>'Pending'],
            'disabled' => ['class'=>'sbadge-disabled', 'icon'=>'bi-x-circle-fill',     'label'=>'Disabled'],
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                Parking Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Parkings</li>
                </ol>
            </nav>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn-export btn-export-primary" style="
                height:        36px;
                padding:       0 1.25rem;
                border-radius: 8px;
                font-size:     .835rem;
                font-weight:   600;
                display:       inline-flex;
                align-items:   center;
                gap:           .4rem;
                cursor:        pointer;
                white-space:   nowrap;
                text-decoration:none;
                transition:    background .15s, color .15s;
                border:        none;
                background:    #0F3D56;
                color:         #fff;
            ">
                <i class="bi bi-plus-circle"></i> Add Parking
            </button>
            <button type="button" class="btn-export btn-export-xl" style="
                height:        36px;
                padding:       0 1rem;
                border-radius: 8px;
                font-size:     .835rem;
                font-weight:   600;
                display:       inline-flex;
                align-items:   center;
                gap:           .4rem;
                cursor:        pointer;
                white-space:   nowrap;
                text-decoration:none;
                transition:    background .15s, color .15s;
                border:        none;
                background:    rgba(46,204,113,.12);
                color:         #1aaa5a;
            ">
                <i class="bi bi-file-earmark-excel"></i> Export
            </button>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 1 — SUMMARY CARDS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        @foreach ($stats as $stat)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="watermark">{{ $stat['wm'] }}</div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <div class="stat-icon" style="background:{{ $stat['iconBg'] }};">
                            <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['iconColor'] }};"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    <div class="stat-change {{ $stat['dir'] }}">
                        <i class="bi bi-arrow-{{ $stat['dir'] == 'up' ? 'up' : 'down' }}-right-circle-fill"></i>
                        {{ $stat['change'] }}
                    </div>
                    <div class="stat-sub">{{ $stat['sub'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 2 — FILTERS
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card mb-4">
        <div class="dash-card-body">
            <form class="row g-3 align-items-end">
                {{-- Search --}}
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="search" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-search"></i> Search Parking
                    </label>
                    <input type="text" class="form-control" id="search" placeholder="Name, ID, address..."
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                {{-- City --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="city" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-geo-alt"></i> City
                    </label>
                    <select class="form-select" id="city"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Owner --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="owner" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-person"></i> Owner
                    </label>
                    <select class="form-select" id="owner"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($owners as $owner)
                            <option value="{{ $owner }}">{{ $owner }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="status" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-circle"></i> Status
                    </label>
                    <select class="form-select" id="status"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Parking Type --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="type" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-building"></i> Parking Type
                    </label>
                    <select class="form-select" id="type"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action buttons --}}
                <div class="col-12 col-md-6 col-lg-1">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn" style="
                            background: #0F3D56;
                            color: #fff;
                            border: none;
                            border-radius: 8px;
                            height: 40px;
                            padding: 0 1.25rem;
                            font-weight: 600;
                            font-size: .85rem;
                            white-space: nowrap;
                            width: 100%;
                        ">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 3 — PARKING TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h6>All Parkings</h6>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.78rem; color:#8899aa;">
                    Showing {{ count($parkings) }} parkings
                </span>
                <span class="card-badge" style="
                    font-size: .72rem;
                    font-weight: 600;
                    padding: .22em .7em;
                    border-radius: 20px;
                    white-space: nowrap;
                    background: rgba(15,61,86,.1);
                    color: #0F3D56;
                ">
                    <i class="bi bi-database"></i> {{ count($parkings) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="parking-table">
                <thead>
                    <tr>
                        <th>Parking ID</th>
                        <th>Parking Name</th>
                        <th>Owner</th>
                        <th>City</th>
                        <th>Slots</th>
                        <th>Available</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parkings as $p)
                        @php $sb = $statusBadgeMap[$p['status']]; @endphp
                        <tr>
                            {{-- ID --}}
                            <td>
                                <span class="mono" style="
                                    font-size: .8rem;
                                    color: #0F3D56;
                                    font-weight: 700;
                                    font-family: monospace;
                                    letter-spacing: .03em;
                                ">
                                    {{ $p['id'] }}
                                </span>
                            </td>

                            {{-- Name --}}
                            <td>
                                <div style="font-weight:700; font-size:.855rem;">{{ $p['name'] }}</div>
                            </td>

                            {{-- Owner --}}
                            <td style="font-weight:600;">{{ $p['owner'] }}</td>

                            {{-- City --}}
                            <td style="color:#5A6A7A;">
                                <i class="bi bi-geo-alt" style="font-size:.68rem;"></i>
                                {{ $p['city'] }}
                            </td>

                            {{-- Slots --}}
                            <td style="font-weight:600;">{{ $p['slots'] }}</td>

                            {{-- Available --}}
                            <td style="color:#1aaa5a; font-weight:600;">{{ $p['available'] }}</td>

                            {{-- Occupancy --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="
                                        flex:1; max-width:50px; height:5px;
                                        background:#f0f3f7; border-radius:3px; overflow:hidden;
                                    ">
                                        <div style="
                                            width:{{ $p['occupancy'] }}%; height:100%;
                                            background:{{ $p['occupancy'] >= 80 ? '#1aaa5a' : ($p['occupancy'] >= 65 ? '#b45309' : '#e74c3c') }};
                                            border-radius:3px;
                                        "></div>
                                    </div>
                                    <span style="font-size:.8rem; font-weight:700; color:#0D1B2A; white-space:nowrap;">
                                        {{ $p['occupancy'] }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="sbadge {{ $sb['class'] }}">
                                    <i class="bi {{ $sb['icon'] }}" style="font-size:.62rem;"></i>
                                    {{ $sb['label'] }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                {{ $p['date'] }}
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="#" class="btn-action btn-action-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="#" class="btn-action btn-action-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" class="btn-action btn-action-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap px-4 py-3 border-top" style="border-color:#f0f3f7;">
            <div style="font-size:.8rem; color:#8899aa;">
                Showing <strong style="color:#0D1B2A;">1</strong> to
                <strong style="color:#0D1B2A;">10</strong> of
                <strong style="color:#0D1B2A;">48</strong> results
            </div>

            <ul class="pagination-custom mb-0">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">
                        <i class="bi bi-chevron-left" style="font-size:.75rem;"></i>
                    </a>
                </li>
                <li class="page-item active">
                    <a class="page-link" href="#">1</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">3</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">4</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">5</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">
                        <i class="bi bi-chevron-right" style="font-size:.75rem;"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

@endsection