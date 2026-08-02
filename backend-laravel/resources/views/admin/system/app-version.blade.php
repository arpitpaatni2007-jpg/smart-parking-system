@extends('layouts.admin')

@section('title', 'App Version')
@section('page-title', 'App Version')

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
    .dash-card-header .card-badge {
        font-size:     .72rem;
        font-weight:   600;
        padding:       .22em .7em;
        border-radius: 20px;
        white-space:   nowrap;
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
    .sbadge-stable    { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-beta      { background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-maintenance{ background:rgba(15,61,86,.1);    color:#0F3D56; }

    /* ── Version info list ──────────────────────────────────── */
    .version-item {
        display:         flex;
        align-items:     center;
        justify-content: space-between;
        padding:         .7rem 0;
        border-bottom:   1px solid #f5f7f9;
    }
    .version-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .version-label {
        font-size:      .85rem;
        font-weight:    600;
        color:          #5A6A7A;
    }
    .version-value {
        font-size:      .92rem;
        font-weight:    700;
        color:          #0D1B2A;
        font-family:    monospace;
        letter-spacing: .03em;
    }

    /* ── Table ────────────────────────────────────────────────── */
    .history-table { width:100%; border-collapse:collapse; }
    .history-table th {
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
    .history-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .history-table tr:last-child td { border-bottom:none; }
    .history-table tr:hover td      { background:#fafcff; }

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

    /* ── Release notes ──────────────────────────────────────── */
    .release-item {
        padding: .75rem 0;
        border-bottom: 1px solid #f5f7f9;
    }
    .release-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .release-badge {
        font-size: .68rem;
        font-weight: 600;
        padding: .15em .6em;
        border-radius: 20px;
        background: rgba(46,204,113,.12);
        color: #1aaa5a;
        white-space: nowrap;
    }
    .release-badge.fix {
        background: rgba(15,61,86,.08);
        color: #0F3D56;
    }
    .release-badge.update {
        background: rgba(245,158,11,.12);
        color: #b45309;
    }

    /* ── Download buttons ────────────────────────────────────── */
    .btn-download {
        height:        40px;
        padding:       0 1.25rem;
        border-radius: 8px;
        font-size:     .835rem;
        font-weight:   600;
        display:       inline-flex;
        align-items:   center;
        gap:           .5rem;
        cursor:        pointer;
        white-space:   nowrap;
        text-decoration: none;
        transition:    all .15s;
        border:        none;
    }
    .btn-download-android {
        background: #0F3D56;
        color: #fff;
    }
    .btn-download-android:hover {
        background: #0a2f42;
        color: #fff;
    }
    .btn-download-ios {
        background: #1a1a2e;
        color: #fff;
    }
    .btn-download-ios:hover {
        background: #0f0f1f;
        color: #fff;
    }
    .btn-download-pdf {
        background: rgba(231,76,60,.1);
        color: #e74c3c;
        border: 1px solid rgba(231,76,60,.25);
    }
    .btn-download-pdf:hover {
        background: #e74c3c;
        color: #fff;
    }

    /* ── Switch styling ──────────────────────────────────────── */
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked {
        background-color: #0F3D56;
        border-color: #0F3D56;
    }
    .form-switch .form-check-label {
        font-weight: 600;
        color: #0D1B2A;
        margin-left: .5rem;
    }
    .switch-status {
        font-size: .78rem;
        color: #8899aa;
        margin-left: .5rem;
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
                'label'    => 'Current Android Version',
                'value'    => 'v3.2.1',
                'change'   => 'Latest',
                'dir'      => 'up',
                'sub'      => 'Released 15 Jul 2025',
                'icon'     => 'bi-android2',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '🤖',
            ],
            [
                'label'    => 'Current iOS Version',
                'value'    => 'v3.2.0',
                'change'   => 'Latest',
                'dir'      => 'up',
                'sub'      => 'Released 10 Jul 2025',
                'icon'     => 'bi-apple',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '🍎',
            ],
            [
                'label'    => 'Latest Release Date',
                'value'    => '15 Jul 2025',
                'change'   => '7 days ago',
                'dir'      => 'up',
                'sub'      => 'Next release: 01 Aug 2025',
                'icon'     => 'bi-calendar3',
                'iconBg'   => 'rgba(245,158,11,.13)',
                'iconColor'=> '#b45309',
                'wm'       => '📅',
            ],
            [
                'label'    => 'Update Status',
                'value'    => 'Stable',
                'change'   => '100% Users',
                'dir'      => 'up',
                'sub'      => '98% adoption rate',
                'icon'     => 'bi-shield-check',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '🛡️',
            ],
        ];

        /* ── Version History ──────────────────────────────────── */
        $history = [
            ['version' => 'v3.2.1', 'platform' => 'Android', 'build' => '3210', 'date' => '15 Jul 2025', 'status' => 'stable'],
            ['version' => 'v3.2.0', 'platform' => 'iOS',     'build' => '3201', 'date' => '10 Jul 2025', 'status' => 'stable'],
            ['version' => 'v3.1.0', 'platform' => 'Both',    'build' => '3102', 'date' => '20 Jun 2025', 'status' => 'stable'],
            ['version' => 'v3.0.2', 'platform' => 'Android', 'build' => '3021', 'date' => '01 Jun 2025', 'status' => 'beta'],
            ['version' => 'v3.0.1', 'platform' => 'iOS',     'build' => '3011', 'date' => '25 May 2025', 'status' => 'beta'],
            ['version' => 'v3.0.0', 'platform' => 'Both',    'build' => '3005', 'date' => '10 May 2025', 'status' => 'stable'],
            ['version' => 'v2.9.5', 'platform' => 'Both',    'build' => '2952', 'date' => '15 Apr 2025', 'status' => 'maintenance'],
            ['version' => 'v2.9.4', 'platform' => 'Android', 'build' => '2941', 'date' => '01 Apr 2025', 'status' => 'maintenance'],
            ['version' => 'v2.9.3', 'platform' => 'iOS',     'build' => '2930', 'date' => '20 Mar 2025', 'status' => 'stable'],
            ['version' => 'v2.9.2', 'platform' => 'Both',    'build' => '2925', 'date' => '01 Mar 2025', 'status' => 'stable'],
        ];

        $statusBadgeMap = [
            'stable'      => ['class'=>'sbadge-stable',      'icon'=>'bi-check-circle-fill', 'label'=>'Stable'],
            'beta'        => ['class'=>'sbadge-beta',        'icon'=>'bi-clock-fill',        'label'=>'Beta'],
            'maintenance' => ['class'=>'sbadge-maintenance', 'icon'=>'bi-tools',             'label'=>'Maintenance'],
        ];

        /* ── Release notes ────────────────────────────────────── */
        $releaseNotes = [
            ['type' => 'feature', 'icon' => 'bi-star-fill', 'color' => '#1aaa5a', 'text' => 'Real-time parking availability tracking with live updates'],
            ['type' => 'feature', 'icon' => 'bi-star-fill', 'color' => '#1aaa5a', 'text' => 'Advanced analytics dashboard for parking owners'],
            ['type' => 'update',  'icon' => 'bi-arrow-up-circle', 'color' => '#b45309', 'text' => 'Improved UI/UX for booking flow and payment gateway'],
            ['type' => 'update',  'icon' => 'bi-arrow-up-circle', 'color' => '#b45309', 'text' => 'Enhanced security with biometric authentication'],
            ['type' => 'fix',     'icon' => 'bi-bug-fill', 'color' => '#0F3D56', 'text' => 'Fixed crash issue on low-end Android devices'],
            ['type' => 'fix',     'icon' => 'bi-bug-fill', 'color' => '#0F3D56', 'text' => 'Resolved payment timeout bug in UPI transactions'],
            ['type' => 'fix',     'icon' => 'bi-bug-fill', 'color' => '#0F3D56', 'text' => 'Fixed notification delivery delay issue'],
            ['type' => 'feature', 'icon' => 'bi-star-fill', 'color' => '#1aaa5a', 'text' => 'Multi-language support (English, Hindi, Regional)'],
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                App Version
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span style="color:#5A6A7A;">System</span>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">App Version</li>
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
                <i class="bi bi-plus-circle"></i> New Release
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
         SECTION 2 — VERSION INFORMATION + RELEASE NOTES
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Version Information Card --}}
        <div class="col-12 col-xl-6">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Version Information</h6>
                    <span class="card-badge" style="background:rgba(46,204,113,.12);color:#1aaa5a;">
                        <i class="bi bi-check-circle-fill"></i> Latest
                    </span>
                </div>
                <div class="dash-card-body">
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-android2" style="color:#1aaa5a;"></i> Android Version
                        </span>
                        <span class="version-value">v3.2.1</span>
                    </div>
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-apple" style="color:#0F3D56;"></i> iOS Version
                        </span>
                        <span class="version-value">v3.2.0</span>
                    </div>
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-code-square"></i> API Version
                        </span>
                        <span class="version-value">v4.1.0</span>
                    </div>
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-database"></i> Database Version
                        </span>
                        <span class="version-value">v2.8.3</span>
                    </div>
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-calendar3"></i> Release Date
                        </span>
                        <span class="version-value" style="font-family:inherit;">15 July 2025</span>
                    </div>
                    <div class="version-item">
                        <span class="version-label">
                            <i class="bi bi-hash"></i> Build Number
                        </span>
                        <span class="version-value">#3210</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Release Notes Card --}}
        <div class="col-12 col-xl-6">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Release Notes</h6>
                    <span class="card-badge" style="background:rgba(15,61,86,.1);color:#0F3D56;">
                        v3.2.1
                    </span>
                </div>
                <div class="dash-card-body">
                    @foreach ($releaseNotes as $note)
                        <div class="release-item">
                            <div class="d-flex align-items-start gap-2">
                                <div style="margin-top:2px;">
                                    <i class="bi {{ $note['icon'] }}" style="color:{{ $note['color'] }};"></i>
                                </div>
                                <div style="flex:1;">
                                    <span class="release-badge 
                                        @if($note['type'] == 'feature') 
                                        @elseif($note['type'] == 'update') update
                                        @else fix
                                        @endif
                                    ">
                                        {{ ucfirst($note['type']) }}
                                    </span>
                                    <span style="font-size:.855rem; color:#0D1B2A; margin-left:.5rem;">
                                        {{ $note['text'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 3 — FORCE UPDATE SETTINGS + DOWNLOAD
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Force Update Settings --}}
        <div class="col-12 col-xl-7">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Update Settings</h6>
                    <span class="card-badge" style="background:rgba(245,158,11,.12);color:#b45309;">
                        <i class="bi bi-gear"></i> Configure
                    </span>
                </div>
                <div class="dash-card-body">
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:#f5f7f9;">
                        <div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="forceUpdate" checked>
                                <label class="form-check-label" for="forceUpdate">Force Update</label>
                            </div>
                            <div style="font-size:.78rem; color:#8899aa; margin-left:3.5rem;">
                                Users must update to continue using the app
                            </div>
                        </div>
                        <span class="switch-status">
                            <span style="color:#1aaa5a; font-weight:600;">Enabled</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:#f5f7f9;">
                        <div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="optionalUpdate">
                                <label class="form-check-label" for="optionalUpdate">Optional Update</label>
                            </div>
                            <div style="font-size:.78rem; color:#8899aa; margin-left:3.5rem;">
                                Users can choose to update or skip
                            </div>
                        </div>
                        <span class="switch-status">
                            <span style="color:#b45309; font-weight:600;">Disabled</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-2" style="border-color:#f5f7f9;">
                        <div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenanceMode">
                                <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                            </div>
                            <div style="font-size:.78rem; color:#8899aa; margin-left:3.5rem;">
                                Show maintenance screen to all users
                            </div>
                        </div>
                        <span class="switch-status">
                            <span style="color:#0F3D56; font-weight:600;">Disabled</span>
                        </span>
                    </div>

                    <div class="mt-3 pt-2">
                        <button class="btn" style="
                            background: #0F3D56;
                            color: #fff;
                            border: none;
                            border-radius: 8px;
                            height: 40px;
                            padding: 0 1.5rem;
                            font-weight: 600;
                            font-size: .85rem;
                        ">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Download Section --}}
        <div class="col-12 col-xl-5">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Downloads</h6>
                    <span class="card-badge" style="background:rgba(15,61,86,.1);color:#0F3D56;">
                        <i class="bi bi-download"></i> Latest Build
                    </span>
                </div>
                <div class="dash-card-body">
                    <div class="d-flex flex-column gap-3">
                        <button class="btn-download btn-download-android" style="width:100%; justify-content:center;">
                            <i class="bi bi-android2"></i> Download Android APK
                            <span style="font-size:.7rem; font-weight:400; opacity:.8;">(v3.2.1)</span>
                        </button>
                        <button class="btn-download btn-download-ios" style="width:100%; justify-content:center;">
                            <i class="bi bi-apple"></i> Download iOS Build
                            <span style="font-size:.7rem; font-weight:400; opacity:.8;">(v3.2.0)</span>
                        </button>
                        <button class="btn-download btn-download-pdf" style="width:100%; justify-content:center;">
                            <i class="bi bi-file-earmark-pdf"></i> Release Notes PDF
                        </button>
                    </div>
                    <div class="mt-3 pt-2 border-top" style="border-color:#f5f7f9;">
                        <div style="font-size:.78rem; color:#8899aa; text-align:center;">
                            <i class="bi bi-info-circle"></i>
                            Latest build: <strong style="color:#0D1B2A;">#3210</strong>
                            &nbsp;·&nbsp; Size: <strong style="color:#0D1B2A;">24.5 MB</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 4 — VERSION HISTORY TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h6>Version History</h6>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.78rem; color:#8899aa;">
                    Showing {{ count($history) }} versions
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
                    <i class="bi bi-database"></i> {{ count($history) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Platform</th>
                        <th>Build Number</th>
                        <th>Release Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $h)
                        @php $sb = $statusBadgeMap[$h['status']]; @endphp
                        <tr>
                            <td>
                                <span style="font-weight:700; color:#0F3D56; font-family:monospace; letter-spacing:.03em;">
                                    {{ $h['version'] }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#5A6A7A;">
                                    @if($h['platform'] == 'Android')
                                        <i class="bi bi-android2" style="color:#1aaa5a;"></i>
                                    @elseif($h['platform'] == 'iOS')
                                        <i class="bi bi-apple" style="color:#0F3D56;"></i>
                                    @else
                                        <i class="bi bi-phone"></i>
                                    @endif
                                    {{ $h['platform'] }}
                                </span>
                            </td>
                            <td style="font-family:monospace; letter-spacing:.03em; color:#5A6A7A;">
                                #{{ $h['build'] }}
                            </td>
                            <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                {{ $h['date'] }}
                            </td>
                            <td>
                                <span class="sbadge {{ $sb['class'] }}">
                                    <i class="bi {{ $sb['icon'] }}" style="font-size:.62rem;"></i>
                                    {{ $sb['label'] }}
                                </span>
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
                <strong style="color:#0D1B2A;">18</strong> results
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
                    <a class="page-link" href="#">
                        <i class="bi bi-chevron-right" style="font-size:.75rem;"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

@endsection