@extends('layouts.admin')

@section('title', 'System Users')
@section('page-title', 'System Users')

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
    .sbadge-active  { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-inactive{ background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-blocked { background:rgba(231,76,60,.12);   color:#c0392b; }
    .sbadge-online  { background:rgba(46,204,113,.14);  color:#1aaa5a; }

    /* ── Role badges ─────────────────────────────────────────── */
    .rbadge {
        display:        inline-flex;
        align-items:    center;
        gap:            .25rem;
        padding:        .25em .75em;
        border-radius:  20px;
        font-size:      .72rem;
        font-weight:    600;
        white-space:    nowrap;
    }
    .rbadge-super-admin { background:rgba(15,61,86,.12); color:#0F3D56; }
    .rbadge-admin      { background:rgba(15,61,86,.1);  color:#0F3D56; }
    .rbadge-support    { background:rgba(245,158,11,.12); color:#b45309; }
    .rbadge-finance    { background:rgba(46,204,113,.12); color:#1aaa5a; }
    .rbadge-operations { background:rgba(2,136,209,.1);  color:#0277bd; }

    /* ── Table ────────────────────────────────────────────────── */
    .user-table { width:100%; border-collapse:collapse; }
    .user-table th {
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
    .user-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .user-table tr:last-child td { border-bottom:none; }
    .user-table tr:hover td      { background:#fafcff; }

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
    .btn-action-view        { background:rgba(15,61,86,.1);  color:#0F3D56; }
    .btn-action-view:hover { background:#0F3D56; color:#fff; }
    .btn-action-edit        { background:rgba(46,204,113,.12); color:#1aaa5a; }
    .btn-action-edit:hover { background:#1aaa5a; color:#fff; }
    .btn-action-reset       { background:rgba(245,158,11,.12); color:#b45309; }
    .btn-action-reset:hover { background:#b45309; color:#fff; }
    .btn-action-delete      { background:rgba(231,76,60,.1);   color:#e74c3c; }
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

    /* ── Avatar ──────────────────────────────────────────────── */
    .user-avatar {
        width:           36px;
        height:          36px;
        border-radius:   9px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .85rem;
        font-weight:     700;
        color:           #fff;
        flex-shrink:     0;
    }

    /* ── Online indicator ────────────────────────────────────── */
    .online-dot {
        display:         inline-block;
        width:           8px;
        height:          8px;
        border-radius:   50%;
        background:      #1aaa5a;
        margin-right:    4px;
    }
    .offline-dot {
        display:         inline-block;
        width:           8px;
        height:          8px;
        border-radius:   50%;
        background:      #e2e8ee;
        margin-right:    4px;
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
                'label'    => 'Total Staff',
                'value'    => '47',
                'change'   => '+9.3%',
                'dir'      => 'up',
                'sub'      => 'vs last month 43',
                'icon'     => 'bi-people',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '👥',
            ],
            [
                'label'    => 'Active Users',
                'value'    => '38',
                'change'   => '+8.6%',
                'dir'      => 'up',
                'sub'      => 'vs last month 35',
                'icon'     => 'bi-check-circle',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '✅',
            ],
            [
                'label'    => 'Blocked Users',
                'value'    => '4',
                'change'   => '-20%',
                'dir'      => 'down',
                'sub'      => 'vs last month 5',
                'icon'     => 'bi-ban',
                'iconBg'   => 'rgba(231,76,60,.1)',
                'iconColor'=> '#e74c3c',
                'wm'       => '🚫',
            ],
            [
                'label'    => 'Online Users',
                'value'    => '12',
                'change'   => '+33.3%',
                'dir'      => 'up',
                'sub'      => 'vs last month 9',
                'icon'     => 'bi-wifi',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '📶',
            ],
        ];

        /* ── User data ────────────────────────────────────────── */
        $users = [
            [
                'id'          => 'USR-001',
                'name'        => 'Rajesh Kumar',
                'email'       => 'rajesh@smartparking.com',
                'role'        => 'Super Admin',
                'department'  => 'IT',
                'last_login'  => 'Today, 10:30 AM',
                'status'      => 'active',
                'online'      => true,
                'date'        => '01 Jan 2025',
                'initials'    => 'RK',
                'color'       => '#0F3D56',
            ],
            [
                'id'          => 'USR-002',
                'name'        => 'Priya Sharma',
                'email'       => 'priya@smartparking.com',
                'role'        => 'Admin',
                'department'  => 'Operations',
                'last_login'  => 'Today, 09:15 AM',
                'status'      => 'active',
                'online'      => true,
                'date'        => '15 Jan 2025',
                'initials'    => 'PS',
                'color'       => '#0277bd',
            ],
            [
                'id'          => 'USR-003',
                'name'        => 'Amit Patel',
                'email'       => 'amit@smartparking.com',
                'role'        => 'Support',
                'department'  => 'Customer Support',
                'last_login'  => 'Yesterday, 04:20 PM',
                'status'      => 'active',
                'online'      => false,
                'date'        => '20 Feb 2025',
                'initials'    => 'AP',
                'color'       => '#b45309',
            ],
            [
                'id'          => 'USR-004',
                'name'        => 'Neha Singh',
                'email'       => 'neha@smartparking.com',
                'role'        => 'Finance',
                'department'  => 'Finance',
                'last_login'  => 'Today, 11:45 AM',
                'status'      => 'active',
                'online'      => true,
                'date'        => '10 Mar 2025',
                'initials'    => 'NS',
                'color'       => '#1aaa5a',
            ],
            [
                'id'          => 'USR-005',
                'name'        => 'Vivek Gupta',
                'email'       => 'vivek@smartparking.com',
                'role'        => 'Operations',
                'department'  => 'Operations',
                'last_login'  => '2 days ago, 03:10 PM',
                'status'      => 'active',
                'online'      => false,
                'date'        => '05 Apr 2025',
                'initials'    => 'VG',
                'color'       => '#0F3D56',
            ],
            [
                'id'          => 'USR-006',
                'name'        => 'Anjali Bose',
                'email'       => 'anjali@smartparking.com',
                'role'        => 'Support',
                'department'  => 'Customer Support',
                'last_login'  => '3 days ago, 10:00 AM',
                'status'      => 'inactive',
                'online'      => false,
                'date'        => '18 May 2025',
                'initials'    => 'AB',
                'color'       => '#b45309',
            ],
            [
                'id'          => 'USR-007',
                'name'        => 'Sanjay Reddy',
                'email'       => 'sanjay@smartparking.com',
                'role'        => 'Admin',
                'department'  => 'IT',
                'last_login'  => 'Today, 08:50 AM',
                'status'      => 'active',
                'online'      => true,
                'date'        => '22 Jun 2025',
                'initials'    => 'SR',
                'color'       => '#0277bd',
            ],
            [
                'id'          => 'USR-008',
                'name'        => 'Pooja Iyer',
                'email'       => 'pooja@smartparking.com',
                'role'        => 'Finance',
                'department'  => 'Finance',
                'last_login'  => '1 week ago, 02:30 PM',
                'status'      => 'blocked',
                'online'      => false,
                'date'        => '01 Jul 2025',
                'initials'    => 'PI',
                'color'       => '#e74c3c',
            ],
            [
                'id'          => 'USR-009',
                'name'        => 'Deepak Kumar',
                'email'       => 'deepak@smartparking.com',
                'role'        => 'Operations',
                'department'  => 'Operations',
                'last_login'  => 'Yesterday, 05:45 PM',
                'status'      => 'active',
                'online'      => false,
                'date'        => '15 Jul 2025',
                'initials'    => 'DK',
                'color'       => '#0F3D56',
            ],
            [
                'id'          => 'USR-010',
                'name'        => 'Meena Patel',
                'email'       => 'meena@smartparking.com',
                'role'        => 'Super Admin',
                'department'  => 'IT',
                'last_login'  => 'Today, 09:30 AM',
                'status'      => 'active',
                'online'      => true,
                'date'        => '20 Jul 2025',
                'initials'    => 'MP',
                'color'       => '#0F3D56',
            ],
        ];

        /* ── Filter options ───────────────────────────────────── */
        $roles = ['All Roles', 'Super Admin', 'Admin', 'Support', 'Finance', 'Operations'];
        $statuses = ['All Status', 'Active', 'Inactive', 'Blocked'];
        $departments = ['All Departments', 'IT', 'Operations', 'Customer Support', 'Finance'];

        $statusBadgeMap = [
            'active'   => ['class'=>'sbadge-active',   'icon'=>'bi-check-circle-fill', 'label'=>'Active'],
            'inactive' => ['class'=>'sbadge-inactive', 'icon'=>'bi-clock-fill',        'label'=>'Inactive'],
            'blocked'  => ['class'=>'sbadge-blocked',  'icon'=>'bi-x-circle-fill',     'label'=>'Blocked'],
        ];

        $roleBadgeMap = [
            'Super Admin' => 'rbadge-super-admin',
            'Admin'       => 'rbadge-admin',
            'Support'     => 'rbadge-support',
            'Finance'     => 'rbadge-finance',
            'Operations'  => 'rbadge-operations',
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                System Users
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">System Users</li>
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
                <i class="bi bi-plus-circle"></i> Add User
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
                        <i class="bi bi-search"></i> Search User
                    </label>
                    <input type="text" class="form-control" id="search" placeholder="Name, email, ID..."
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                {{-- Role --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="role" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-shield-person"></i> Role
                    </label>
                    <select class="form-select" id="role"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
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

                {{-- Department --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="department" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-building"></i> Department
                    </label>
                    <select class="form-select" id="department"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action buttons --}}
                <div class="col-12 col-md-6 col-lg-3">
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
                            flex: 1;
                        ">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button type="reset" class="btn" style="
                            background: #f0f3f7;
                            color: #5A6A7A;
                            border: none;
                            border-radius: 8px;
                            height: 40px;
                            padding: 0 1.25rem;
                            font-weight: 600;
                            font-size: .85rem;
                            white-space: nowrap;
                        ">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 3 — USER TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h6>All System Users</h6>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.78rem; color:#8899aa;">
                    Showing {{ count($users) }} users
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
                    <i class="bi bi-database"></i> {{ count($users) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php 
                            $sb = $statusBadgeMap[$u['status']];
                            $rb = $roleBadgeMap[$u['role']];
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
                                    {{ $u['id'] }}
                                </span>
                            </td>

                            {{-- Name with avatar --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="background:{{ $u['color'] }};">
                                        {{ $u['initials'] }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600; font-size:.855rem;">{{ $u['name'] }}</div>
                                        @if($u['online'])
                                            <div style="font-size:.68rem; color:#1aaa5a;">
                                                <span class="online-dot"></span> Online
                                            </div>
                                        @else
                                            <div style="font-size:.68rem; color:#8899aa;">
                                                <span class="offline-dot"></span> Offline
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td style="color:#5A6A7A; font-size:.82rem;">{{ $u['email'] }}</td>

                            {{-- Role --}}
                            <td>
                                <span class="rbadge {{ $rb }}">
                                    <i class="bi bi-shield-check" style="font-size:.62rem;"></i>
                                    {{ $u['role'] }}
                                </span>
                            </td>

                            {{-- Department --}}
                            <td style="color:#5A6A7A; font-size:.82rem;">{{ $u['department'] }}</td>

                            {{-- Last Login --}}
                            <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                <i class="bi bi-clock-history" style="font-size:.68rem;"></i>
                                {{ $u['last_login'] }}
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
                                {{ $u['date'] }}
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
                                    <a href="#" class="btn-action btn-action-reset" title="Reset Password">
                                        <i class="bi bi-key"></i>
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
                <strong style="color:#0D1B2A;">47</strong> results
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