@extends('layouts.admin')

@section('title', 'Vehicle Management')
@section('page-title', 'Vehicle Management')

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
    .sbadge-active    { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-blocked   { background:rgba(231,76,60,.12);   color:#c0392b; }
    .sbadge-verified  { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-pending   { background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-rejected  { background:rgba(231,76,60,.12);   color:#c0392b; }

    /* ── Table ────────────────────────────────────────────────── */
    .vehicle-table { width:100%; border-collapse:collapse; }
    .vehicle-table th {
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
    .vehicle-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .vehicle-table tr:last-child td { border-bottom:none; }
    .vehicle-table tr:hover td      { background:#fafcff; }

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

    /* ── Mono ────────────────────────────────────────────────── */
    .mono { font-family:monospace; letter-spacing:.03em; }
</style>
@endpush

@section('content')

    @php
        /* ── Summary stats ────────────────────────────────────── */
        $stats = [
            [
                'label'    => 'Total Vehicles',
                'value'    => '1,284',
                'change'   => '+15.3%',
                'dir'      => 'up',
                'sub'      => 'vs last month 1,114',
                'icon'     => 'bi-car-front',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '🚗',
            ],
            [
                'label'    => 'Active Vehicles',
                'value'    => '1,026',
                'change'   => '+11.2%',
                'dir'      => 'up',
                'sub'      => 'vs last month 922',
                'icon'     => 'bi-check-circle',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '✅',
            ],
            [
                'label'    => 'Blocked Vehicles',
                'value'    => '143',
                'change'   => '-7.8%',
                'dir'      => 'down',
                'sub'      => 'vs last month 155',
                'icon'     => 'bi-ban',
                'iconBg'   => 'rgba(231,76,60,.1)',
                'iconColor'=> '#e74c3c',
                'wm'       => '🚫',
            ],
            [
                'label'    => 'Pending Verification',
                'value'    => '115',
                'change'   => '+23.6%',
                'dir'      => 'up',
                'sub'      => 'vs last month 93',
                'icon'     => 'bi-clock-history',
                'iconBg'   => 'rgba(245,158,11,.13)',
                'iconColor'=> '#b45309',
                'wm'       => '⏳',
            ],
        ];

        /* ── Vehicle data ─────────────────────────────────────── */
        $vehicles = [
            [
                'id'          => 'VH-001',
                'number'      => 'DL-01-AB-1234',
                'owner'       => 'Rahul Sharma',
                'type'        => 'Sedan',
                'brand'       => 'Toyota',
                'model'       => 'Camry',
                'verification'=> 'verified',
                'status'      => 'active',
                'date'        => '15 Jan 2025',
            ],
            [
                'id'          => 'VH-002',
                'number'      => 'HR-26-CD-5678',
                'owner'       => 'Priya Patel',
                'type'        => 'SUV',
                'brand'       => 'Hyundai',
                'model'       => 'Creta',
                'verification'=> 'verified',
                'status'      => 'active',
                'date'        => '20 Feb 2025',
            ],
            [
                'id'          => 'VH-003',
                'number'      => 'UP-32-EF-9012',
                'owner'       => 'Amit Kumar',
                'type'        => 'Hatchback',
                'brand'       => 'Maruti Suzuki',
                'model'       => 'Swift',
                'verification'=> 'pending',
                'status'      => 'active',
                'date'        => '10 Mar 2025',
            ],
            [
                'id'          => 'VH-004',
                'number'      => 'DL-04-GH-3456',
                'owner'       => 'Neha Singh',
                'type'        => 'Electric',
                'brand'       => 'Tesla',
                'model'       => 'Model 3',
                'verification'=> 'verified',
                'status'      => 'active',
                'date'        => '05 Apr 2025',
            ],
            [
                'id'          => 'VH-005',
                'number'      => 'HR-55-IJ-7890',
                'owner'       => 'Vivek Patel',
                'type'        => 'SUV',
                'brand'       => 'Mahindra',
                'model'       => 'XUV700',
                'verification'=> 'rejected',
                'status'      => 'blocked',
                'date'        => '12 Apr 2025',
            ],
            [
                'id'          => 'VH-006',
                'number'      => 'UP-16-KL-2345',
                'owner'       => 'Anjali Bose',
                'type'        => 'Sedan',
                'brand'       => 'Honda',
                'model'       => 'City',
                'verification'=> 'pending',
                'status'      => 'active',
                'date'        => '18 May 2025',
            ],
            [
                'id'          => 'VH-007',
                'number'      => 'DL-07-MN-6789',
                'owner'       => 'Sanjay Gupta',
                'type'        => 'Compact SUV',
                'brand'       => 'Kia',
                'model'       => 'Seltos',
                'verification'=> 'verified',
                'status'      => 'active',
                'date'        => '22 Jun 2025',
            ],
            [
                'id'          => 'VH-008',
                'number'      => 'HR-38-OP-0123',
                'owner'       => 'Pooja Iyer',
                'type'        => 'Electric',
                'brand'       => 'Tata',
                'model'       => 'Nexon EV',
                'verification'=> 'verified',
                'status'      => 'blocked',
                'date'        => '01 Jul 2025',
            ],
            [
                'id'          => 'VH-009',
                'number'      => 'UP-49-QR-4567',
                'owner'       => 'Deepak Kumar',
                'type'        => 'Hatchback',
                'brand'       => 'Maruti Suzuki',
                'model'       => 'Baleno',
                'verification'=> 'pending',
                'status'      => 'active',
                'date'        => '15 Jul 2025',
            ],
            [
                'id'          => 'VH-010',
                'number'      => 'DL-10-ST-8901',
                'owner'       => 'Meena Reddy',
                'type'        => 'Sedan',
                'brand'       => 'BMW',
                'model'       => '3 Series',
                'verification'=> 'verified',
                'status'      => 'active',
                'date'        => '20 Jul 2025',
            ],
        ];

        /* ── Filter options ───────────────────────────────────── */
        $vehicleTypes = ['All Types', 'Sedan', 'SUV', 'Hatchback', 'Electric', 'Compact SUV'];
        $owners = ['All Owners', 'Rahul Sharma', 'Priya Patel', 'Amit Kumar', 'Neha Singh', 'Vivek Patel', 'Anjali Bose', 'Sanjay Gupta', 'Pooja Iyer', 'Deepak Kumar', 'Meena Reddy'];
        $verificationStatuses = ['All Status', 'Verified', 'Pending', 'Rejected'];
        $statuses = ['All Status', 'Active', 'Blocked'];

        $verificationBadgeMap = [
            'verified' => ['class'=>'sbadge-verified',  'icon'=>'bi-check-circle-fill', 'label'=>'Verified'],
            'pending'  => ['class'=>'sbadge-pending',   'icon'=>'bi-clock-fill',        'label'=>'Pending'],
            'rejected' => ['class'=>'sbadge-rejected',  'icon'=>'bi-x-circle-fill',     'label'=>'Rejected'],
        ];

        $statusBadgeMap = [
            'active'  => ['class'=>'sbadge-active',   'icon'=>'bi-check-circle-fill', 'label'=>'Active'],
            'blocked' => ['class'=>'sbadge-blocked',  'icon'=>'bi-x-circle-fill',     'label'=>'Blocked'],
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                Vehicle Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Vehicles</li>
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
                <i class="bi bi-plus-circle"></i> Add Vehicle
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
                        <i class="bi bi-search"></i> Search Vehicle
                    </label>
                    <input type="text" class="form-control" id="search" placeholder="Number, owner, brand..."
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                {{-- Vehicle Type --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="type" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-car-front"></i> Vehicle Type
                    </label>
                    <select class="form-select" id="type"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($vehicleTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
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

                {{-- Verification Status --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="verification" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-shield-check"></i> Verification
                    </label>
                    <select class="form-select" id="verification"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($verificationStatuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
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
         SECTION 3 — VEHICLE TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h6>All Vehicles</h6>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.78rem; color:#8899aa;">
                    Showing {{ count($vehicles) }} vehicles
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
                    <i class="bi bi-database"></i> {{ count($vehicles) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="vehicle-table">
                <thead>
                    <tr>
                        <th>Vehicle ID</th>
                        <th>Vehicle Number</th>
                        <th>Owner</th>
                        <th>Vehicle Type</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Verification</th>
                        <th>Status</th>
                        <th>Added Date</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehicles as $v)
                        @php 
                            $vb = $verificationBadgeMap[$v['verification']];
                            $sb = $statusBadgeMap[$v['status']];
                        @endphp
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
                                    {{ $v['id'] }}
                                </span>
                            </td>

                            {{-- Vehicle Number --}}
                            <td>
                                <span style="font-weight:700; font-size:.855rem; color:#0D1B2A;">
                                    {{ $v['number'] }}
                                </span>
                            </td>

                            {{-- Owner --}}
                            <td style="font-weight:600;">{{ $v['owner'] }}</td>

                            {{-- Vehicle Type --}}
                            <td style="color:#5A6A7A;">
                                <i class="bi bi-car-front" style="font-size:.68rem;"></i>
                                {{ $v['type'] }}
                            </td>

                            {{-- Brand --}}
                            <td style="font-weight:600;">{{ $v['brand'] }}</td>

                            {{-- Model --}}
                            <td style="color:#5A6A7A;">{{ $v['model'] }}</td>

                            {{-- Verification --}}
                            <td>
                                <span class="sbadge {{ $vb['class'] }}">
                                    <i class="bi {{ $vb['icon'] }}" style="font-size:.62rem;"></i>
                                    {{ $vb['label'] }}
                                </span>
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
                                {{ $v['date'] }}
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
                <strong style="color:#0D1B2A;">1,284</strong> results
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