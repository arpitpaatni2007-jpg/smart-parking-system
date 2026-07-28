{{-- ============================================================
     Parkings — Add New · Step 6: Review & Submit
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Final review of all collected parking data before
               submission. Shows summary cards for each wizard
               step, a validation checklist, and the submit
               action with confirmation checkbox.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Review & Submit Parking')
@section('page-title', 'Add New Parking')

@push('styles')
<style>
    /* ── Wizard step bar ─────────────────────────────────────── */
    .wizard-bar {
        display:        flex;
        align-items:    center;
        gap:            0;
        background:     #fff;
        border:         1px solid #e2e8ee;
        border-radius:  14px;
        box-shadow:     0 2px 12px rgba(15,61,86,.06);
        padding:        1.1rem 1.4rem;
        overflow-x:     auto;
        scrollbar-width: none;
        margin-bottom:  1.75rem;
    }
    .wizard-bar::-webkit-scrollbar { display: none; }

    .wizard-step {
        display:        flex;
        align-items:    center;
        gap:            .55rem;
        flex-shrink:    0;
        cursor:         default;
    }
    .wizard-step-num {
        width:          28px;
        height:         28px;
        border-radius:  50%;
        display:        flex;
        align-items:    center;
        justify-content: center;
        font-size:      .75rem;
        font-weight:    700;
        flex-shrink:    0;
        transition:     background .2s, color .2s;
    }
    .wizard-step.done .wizard-step-num {
        background: #2ECC71;
        color:      #fff;
    }
    .wizard-step.active .wizard-step-num {
        background: #0F3D56;
        color:      #fff;
    }
    .wizard-step.pending .wizard-step-num {
        background: #f0f3f7;
        color:      #8899aa;
    }
    .wizard-step-label {
        font-size:   .8rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .wizard-step.done   .wizard-step-label { color: #2ECC71; }
    .wizard-step.active .wizard-step-label { color: #0F3D56; }
    .wizard-step.pending .wizard-step-label { color: #8899aa; }

    .wizard-divider {
        flex:           1;
        min-width:      20px;
        height:         2px;
        background:     #e2e8ee;
        margin:         0 .4rem;
        flex-shrink:    0;
    }
    .wizard-divider.done { background: #2ECC71; }

    /* ── Page card shell ─────────────────────────────────────── */
    .page-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
        margin-bottom: 1.25rem;
    }
    .page-card-header {
        padding:       .9rem 1.4rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
        display:       flex;
        align-items:   center;
        gap:           .6rem;
    }
    .page-card-header .card-icon {
        width:           32px;
        height:          32px;
        border-radius:   8px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .95rem;
        flex-shrink:     0;
    }
    .page-card-header h6 {
        margin:      0;
        font-size:   .875rem;
        font-weight: 700;
        color:       #0D1B2A;
    }
    .page-card-header .step-badge {
        margin-left:    auto;
        font-size:      .7rem;
        font-weight:    600;
        background:     rgba(46,204,113,.14);
        color:          #1aaa5a;
        padding:        .2em .7em;
        border-radius:  20px;
    }

    /* ── Info grid rows ──────────────────────────────────────── */
    .info-grid {
        display:               grid;
        grid-template-columns: 1fr 1fr;
        gap:                   0;
    }
    @media (max-width: 575.98px) {
        .info-grid { grid-template-columns: 1fr; }
    }
    .info-cell {
        padding:       .8rem 1.4rem;
        border-bottom: 1px solid #f5f7f9;
        border-right:  1px solid #f5f7f9;
    }
    .info-cell:nth-child(even) { border-right: none; }
    .info-cell:nth-last-child(-n+2) { border-bottom: none; }
    @media (max-width: 575.98px) {
        .info-cell              { border-right: none; }
        .info-cell:last-child   { border-bottom: none; }
        .info-cell:nth-last-child(2) { border-bottom: 1px solid #f5f7f9; }
    }
    .info-label {
        font-size:      .72rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom:  .25rem;
    }
    .info-value {
        font-size:   .875rem;
        font-weight: 500;
        color:       #0D1B2A;
        line-height: 1.4;
    }
    .info-full {
        padding:       .8rem 1.4rem;
        border-bottom: 1px solid #f5f7f9;
    }
    .info-full:last-child { border-bottom: none; }

    /* ── Status badge ────────────────────────────────────────── */
    .status-badge {
        display:        inline-block;
        padding:        .28em .8em;
        border-radius:  20px;
        font-size:      .72rem;
        font-weight:    600;
        letter-spacing: .02em;
    }
    .badge-active   { background: rgba(46,204,113,.14); color: #1aaa5a; }
    .badge-inactive { background: rgba(231,76,60,.12);  color: #c0392b; }
    .badge-hourly   { background: rgba(15,61,86,.1);    color: #0F3D56; }

    /* ── Facility chips ──────────────────────────────────────── */
    .facility-chip {
        display:        inline-flex;
        align-items:    center;
        gap:            .35rem;
        padding:        .3rem .75rem;
        background:     #f0f3f7;
        border:         1px solid #e2e8ee;
        border-radius:  20px;
        font-size:      .78rem;
        font-weight:    500;
        color:          #0D1B2A;
    }
    .facility-chip i { color: #0F3D56; font-size: .8rem; }

    /* ── Image thumbnails ────────────────────────────────────── */
    .img-thumb {
        width:         80px;
        height:        64px;
        object-fit:    cover;
        border-radius: 8px;
        border:        1px solid #e2e8ee;
    }
    .img-thumb-placeholder {
        width:           80px;
        height:          64px;
        border-radius:   8px;
        border:          1px solid #e2e8ee;
        background:      #f0f3f7;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1.3rem;
        color:           #b0c0cc;
        flex-shrink:     0;
    }
    .img-primary-badge {
        position:    absolute;
        top:         4px;
        left:        4px;
        background:  #0F3D56;
        color:       #fff;
        font-size:   .6rem;
        font-weight: 700;
        padding:     .1em .45em;
        border-radius: 4px;
    }

    /* ── Slot breakdown table ────────────────────────────────── */
    .slot-table thead th {
        font-size:      .72rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom:  1px solid #f0f3f7 !important;
        border-top:     none !important;
        background:     #fafbfc;
        padding:        .6rem 1rem;
        white-space:    nowrap;
    }
    .slot-table tbody td {
        font-size:      .855rem;
        padding:        .7rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .slot-table tbody tr:last-child td { border-bottom: none; }

    /* ── Pricing pill ────────────────────────────────────────── */
    .pricing-pill {
        display:     inline-flex;
        align-items: center;
        gap:         .3rem;
        padding:     .3rem .85rem;
        border-radius: 8px;
        font-size:   .82rem;
        font-weight: 600;
        background:  rgba(15,61,86,.08);
        color:       #0F3D56;
    }
    .pricing-pill .currency { font-size: .7rem; font-weight: 500; }

    /* ── Validation alert strip ──────────────────────────────── */
    .val-alert {
        display:        flex;
        align-items:    center;
        gap:            .75rem;
        padding:        .75rem 1.2rem;
        border-radius:  10px;
        font-size:      .855rem;
        font-weight:    500;
        margin-bottom:  .6rem;
    }
    .val-alert:last-child { margin-bottom: 0; }
    .val-alert .val-icon {
        width:           30px;
        height:          30px;
        border-radius:   8px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .9rem;
        flex-shrink:     0;
    }
    .val-success {
        background:  rgba(46,204,113,.1);
        border:      1px solid rgba(46,204,113,.25);
        color:       #1aaa5a;
    }
    .val-success .val-icon { background: rgba(46,204,113,.18); }
    .val-info {
        background:  rgba(2,136,209,.08);
        border:      1px solid rgba(2,136,209,.2);
        color:       #0277bd;
    }
    .val-info .val-icon { background: rgba(2,136,209,.14); }
    .val-warning {
        background:  rgba(245,158,11,.1);
        border:      1px solid rgba(245,158,11,.25);
        color:       #b45309;
    }
    .val-warning .val-icon { background: rgba(245,158,11,.18); }

    /* ── Confirmation card ───────────────────────────────────── */
    .confirm-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        padding:       1.4rem;
        margin-bottom: 1.25rem;
    }

    /* ── Checkbox custom ─────────────────────────────────────── */
    .confirm-check {
        display:     flex;
        align-items: center;
        gap:         .75rem;
        cursor:      pointer;
    }
    .confirm-check input[type="checkbox"] {
        width:         20px;
        height:        20px;
        border-radius: 5px;
        border:        2px solid #0F3D56;
        flex-shrink:   0;
        accent-color:  #0F3D56;
        cursor:        pointer;
    }
    .confirm-check span {
        font-size:   .875rem;
        font-weight: 500;
        color:       #0D1B2A;
        line-height: 1.4;
    }

    /* ── Action buttons ──────────────────────────────────────── */
    .btn-prev {
        height:        40px;
        padding:       0 1.25rem;
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        background:    #fff;
        color:         #0D1B2A;
        font-size:     .86rem;
        font-weight:   600;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s, border-color .15s;
        cursor:        pointer;
    }
    .btn-prev:hover { background: #f0f3f7; border-color: #c8d2dc; color: #0D1B2A; }

    .btn-draft {
        height:        40px;
        padding:       0 1.25rem;
        border:        1px solid #8FA3B4;
        border-radius: 9px;
        background:    #fff;
        color:         #5A6A7A;
        font-size:     .86rem;
        font-weight:   600;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s, border-color .15s, color .15s;
        cursor:        pointer;
    }
    .btn-draft:hover { background: #f8f9fa; border-color: #5A6A7A; color: #0D1B2A; }

    .btn-submit {
        height:        40px;
        padding:       0 1.5rem;
        border:        none;
        border-radius: 9px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .86rem;
        font-weight:   700;
        display:       inline-flex;
        align-items:   center;
        gap:           .45rem;
        cursor:        pointer;
        transition:    background .15s;
    }
    .btn-submit:hover    { background: #0a2f42; }
    .btn-submit:disabled { background: #b0c8d8; cursor: not-allowed; }
</style>
@endpush

@section('content')

    @php
        /* ── Static demo data (mirrors previous wizard steps) ── */
        $parking = [
            'name'          => 'Cyber Hub Parking Complex',
            'owner'         => 'Vikram Joshi (PO-001)',
            'type'          => 'Multi-Level',
            'address'       => 'Block B, Cyber Hub, DLF Cyber City',
            'city'          => 'Gurugram',
            'state'         => 'Haryana',
            'pincode'       => '122002',
            'latitude'      => '28.4943° N',
            'longitude'     => '77.0890° E',
            'total_slots'   => 120,
            'description'   => 'Premium multi-level parking facility located in the heart of Cyber Hub, DLF Cyber City. 24/7 security, CCTV coverage, and EV charging stations available.',
            'status'        => 'active',
            'open_time'     => '06:00 AM',
            'close_time'    => '11:00 PM',
            'is_24hrs'      => false,
        ];

        $pricing = [
            ['vehicle_type' => 'Car',        'base_price' => 80,  'extra_hour' => 40, 'daily' => 600,  'monthly' => 8000],
            ['vehicle_type' => 'Motorcycle', 'base_price' => 30,  'extra_hour' => 15, 'daily' => 250,  'monthly' => 3500],
            ['vehicle_type' => 'EV',         'base_price' => 100, 'extra_hour' => 50, 'daily' => 750,  'monthly' => 10000],
            ['vehicle_type' => 'SUV / MUV',  'base_price' => 100, 'extra_hour' => 50, 'daily' => 800,  'monthly' => 11000],
        ];

        $slots = [
            ['type' => 'Standard', 'total' => 60, 'available' => 60, 'status' => 'active'],
            ['type' => 'EV Charging', 'total' => 20, 'available' => 20, 'status' => 'active'],
            ['type' => 'Premium / Covered', 'total' => 30, 'available' => 30, 'status' => 'active'],
            ['type' => 'Handicapped', 'total' => 10, 'available' => 10, 'status' => 'active'],
        ];

        $facilities = [
            ['icon' => 'bi-shield-check',       'label' => '24/7 CCTV Security'],
            ['icon' => 'bi-lightning-charge',   'label' => 'EV Charging Stations'],
            ['icon' => 'bi-wifi',               'label' => 'Free Wi-Fi'],
            ['icon' => 'bi-droplet',            'label' => 'Car Wash'],
            ['icon' => 'bi-person-badge',       'label' => 'Valet Parking'],
            ['icon' => 'bi-lamp',               'label' => 'Well Lit'],
            ['icon' => 'bi-camera-video',       'label' => 'Video Monitoring'],
            ['icon' => 'bi-wheelchair',         'label' => 'Accessible Entry'],
        ];

        $images = [
            ['label' => 'Main Entrance',  'primary' => true,  'bg' => '#d5e9f5'],
            ['label' => 'Level 1 Overview','primary' => false, 'bg' => '#d5f0e8'],
            ['label' => 'EV Zone',        'primary' => false, 'bg' => '#f5ead5'],
            ['label' => 'Exit Gate',      'primary' => false, 'bg' => '#ead5f5'],
        ];
    @endphp

    {{-- ── Page heading ────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">Add New Parking</h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Review all details before submitting the parking for approval.
            </p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                <li class="breadcrumb-item">
                    <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" style="color:#0F3D56; text-decoration:none;">Parkings</a>
                </li>
                <li class="breadcrumb-item active" style="color:#8899aa;">Add New · Review</li>
            </ol>
        </nav>
    </div>

    {{-- ════════════════════════════════════════════════════════
         WIZARD STEP BAR
    ════════════════════════════════════════════════════════ --}}
    <div class="wizard-bar">

        {{-- Step 1: Basic Info — done --}}
        <div class="wizard-step done">
            <div class="wizard-step-num"><i class="bi bi-check" style="font-size:.8rem;"></i></div>
            <span class="wizard-step-label">Basic Info</span>
        </div>
        <div class="wizard-divider done"></div>

        {{-- Step 2: Pricing & Slots — done --}}
        <div class="wizard-step done">
            <div class="wizard-step-num"><i class="bi bi-check" style="font-size:.8rem;"></i></div>
            <span class="wizard-step-label">Pricing & Slots</span>
        </div>
        <div class="wizard-divider done"></div>

        {{-- Step 3: Facilities — done --}}
        <div class="wizard-step done">
            <div class="wizard-step-num"><i class="bi bi-check" style="font-size:.8rem;"></i></div>
            <span class="wizard-step-label">Facilities</span>
        </div>
        <div class="wizard-divider done"></div>

        {{-- Step 4: Rules — done --}}
        <div class="wizard-step done">
            <div class="wizard-step-num"><i class="bi bi-check" style="font-size:.8rem;"></i></div>
            <span class="wizard-step-label">Rules</span>
        </div>
        <div class="wizard-divider done"></div>

        {{-- Step 5: Images — done --}}
        <div class="wizard-step done">
            <div class="wizard-step-num"><i class="bi bi-check" style="font-size:.8rem;"></i></div>
            <span class="wizard-step-label">Images</span>
        </div>
        <div class="wizard-divider done"></div>

        {{-- Step 6: Review — active --}}
        <div class="wizard-step active">
            <div class="wizard-step-num">6</div>
            <span class="wizard-step-label">Review & Submit</span>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════
         MAIN TWO-COLUMN LAYOUT
    ════════════════════════════════════════════════════════ --}}
    <div class="row g-4 align-items-start">

        {{-- ── LEFT COLUMN: Summary sections ─────────────────── --}}
        <div class="col-12 col-xl-8">

            {{-- ── 1. Basic Information ──────────────────────── --}}
            <div class="page-card">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-building" style="color:#0F3D56;"></i>
                    </div>
                    <h6>Basic Information</h6>
                    <span class="step-badge">Step 1</span>
                </div>

                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Parking Name</div>
                        <div class="info-value">{{ $parking['name'] }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Owner</div>
                        <div class="info-value">{{ $parking['owner'] }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Parking Type</div>
                        <div class="info-value">{{ $parking['type'] }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge badge-active">Active</span>
                        </div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Opening Time</div>
                        <div class="info-value">{{ $parking['open_time'] }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Closing Time</div>
                        <div class="info-value">{{ $parking['close_time'] }}</div>
                    </div>
                </div>

                {{-- Address — full width --}}
                <div class="info-full" style="border-top:1px solid #f0f3f7;">
                    <div class="info-label">Address</div>
                    <div class="info-value">
                        {{ $parking['address'] }}, {{ $parking['city'] }},
                        {{ $parking['state'] }} — {{ $parking['pincode'] }}
                    </div>
                </div>

                <div class="info-grid" style="border-top:1px solid #f0f3f7;">
                    <div class="info-cell" style="border-bottom:none;">
                        <div class="info-label">Latitude</div>
                        <div class="info-value" style="font-family:monospace; font-size:.83rem;">{{ $parking['latitude'] }}</div>
                    </div>
                    <div class="info-cell" style="border-bottom:none;">
                        <div class="info-label">Longitude</div>
                        <div class="info-value" style="font-family:monospace; font-size:.83rem;">{{ $parking['longitude'] }}</div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="info-full" style="border-top:1px solid #f0f3f7;">
                    <div class="info-label">Description</div>
                    <div class="info-value" style="color:#5A6A7A; font-size:.84rem; line-height:1.6;">
                        {{ $parking['description'] }}
                    </div>
                </div>
            </div>

            {{-- ── 2. Pricing & Slots ─────────────────────────── --}}
            <div class="page-card">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-currency-rupee" style="color:#1aaa5a;"></i>
                    </div>
                    <h6>Pricing & Slots</h6>
                    <span class="step-badge">Step 2</span>
                </div>

                {{-- Pricing table --}}
                <div class="table-responsive">
                    <table class="table slot-table mb-0">
                        <thead>
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Base Price / hr</th>
                                <th>Extra Hour</th>
                                <th>Daily</th>
                                <th>Monthly</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pricing as $p)
                                <tr>
                                    <td style="font-weight:600;">{{ $p['vehicle_type'] }}</td>
                                    <td>
                                        <span class="pricing-pill">
                                            <span class="currency">₹</span>{{ $p['base_price'] }}
                                        </span>
                                    </td>
                                    <td style="color:#5A6A7A;">₹{{ $p['extra_hour'] }}</td>
                                    <td style="color:#5A6A7A;">₹{{ $p['daily'] }}</td>
                                    <td style="color:#5A6A7A;">₹{{ number_format($p['monthly']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Slot breakdown --}}
                <div style="border-top:1px solid #f0f3f7; padding:.8rem 1.4rem .5rem;">
                    <div class="info-label mb-2">Slot Breakdown</div>
                    <div class="table-responsive">
                        <table class="table slot-table mb-0">
                            <thead>
                                <tr>
                                    <th>Slot Type</th>
                                    <th style="text-align:center;">Total</th>
                                    <th style="text-align:center;">Available</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($slots as $s)
                                    <tr>
                                        <td style="font-weight:600;">{{ $s['type'] }}</td>
                                        <td style="text-align:center; font-weight:700; color:#0F3D56;">{{ $s['total'] }}</td>
                                        <td style="text-align:center; color:#1aaa5a; font-weight:600;">{{ $s['available'] }}</td>
                                        <td><span class="status-badge badge-active">Active</span></td>
                                    </tr>
                                @endforeach
                                {{-- Total row --}}
                                <tr style="background:#fafbfc;">
                                    <td style="font-weight:700; color:#0D1B2A;">Total</td>
                                    <td style="text-align:center; font-weight:700; color:#0F3D56;">{{ $parking['total_slots'] }}</td>
                                    <td style="text-align:center; font-weight:700; color:#1aaa5a;">{{ $parking['total_slots'] }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── 3. Facilities ──────────────────────────────── --}}
            <div class="page-card">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(245,158,11,.12);">
                        <i class="bi bi-stars" style="color:#b45309;"></i>
                    </div>
                    <h6>Facilities</h6>
                    <span class="step-badge">Step 3</span>
                </div>
                <div class="p-4">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($facilities as $f)
                            <span class="facility-chip">
                                <i class="bi {{ $f['icon'] }}"></i>
                                {{ $f['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── 4. Images ───────────────────────────────────── --}}
            <div class="page-card">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(2,136,209,.1);">
                        <i class="bi bi-images" style="color:#0277bd;"></i>
                    </div>
                    <h6>Uploaded Images</h6>
                    <span class="step-badge">Step 5</span>
                </div>
                <div class="p-4">
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($images as $img)
                            <div class="position-relative" style="flex-shrink:0;">
                                <div
                                    class="img-thumb-placeholder"
                                    style="background:{{ $img['bg'] }}; width:100px; height:76px;"
                                >
                                    <i class="bi bi-image" style="font-size:1.6rem; color:#8899aa;"></i>
                                </div>
                                @if ($img['primary'])
                                    <span class="img-primary-badge">Primary</span>
                                @endif
                                <div style="font-size:.72rem; color:#8899aa; text-align:center; margin-top:.3rem; max-width:100px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $img['label'] }}
                                </div>
                            </div>
                        @endforeach

                        {{-- Count summary --}}
                        <div class="d-flex align-items-center" style="color:#8899aa; font-size:.82rem; gap:.3rem; padding:.5rem 0;">
                            <i class="bi bi-info-circle" style="font-size:.9rem;"></i>
                            {{ count($images) }} image(s) uploaded &middot; 1 set as primary
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /left column --}}

        {{-- ── RIGHT COLUMN: Validation + Confirmation ─────────── --}}
        <div class="col-12 col-xl-4">

            {{-- ── Validation Summary ─────────────────────────── --}}
            <div class="page-card" style="margin-bottom:1.25rem;">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-clipboard2-check" style="color:#1aaa5a;"></i>
                    </div>
                    <h6>Validation Summary</h6>
                </div>
                <div class="p-3">

                    <div class="val-alert val-success">
                        <div class="val-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Basic Information Complete</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                Name, address, type and schedule are set.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-success">
                        <div class="val-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Pricing Configured</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                4 vehicle types with hourly, daily &amp; monthly rates.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-success">
                        <div class="val-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Slots Defined</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                {{ $parking['total_slots'] }} total slots across 4 types.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-success">
                        <div class="val-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Facilities Added</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                {{ count($facilities) }} facilities selected for this parking.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-success">
                        <div class="val-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Images Uploaded</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                {{ count($images) }} images uploaded with 1 primary photo.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-info">
                        <div class="val-icon">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Pending Admin Approval</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                Submission will be reviewed before going live.
                            </div>
                        </div>
                    </div>

                    <div class="val-alert val-warning" style="margin-bottom:0;">
                        <div class="val-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.84rem;">Bank Details Required</div>
                            <div style="font-size:.76rem; margin-top:.1rem; opacity:.85;">
                                Owner must add bank details to receive payouts.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Parking Summary Quick Stats ──────────────────── --}}
            <div class="page-card" style="margin-bottom:1.25rem;">
                <div class="page-card-header">
                    <div class="card-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-bar-chart-line" style="color:#0F3D56;"></i>
                    </div>
                    <h6>Summary at a Glance</h6>
                </div>
                <div>
                    @php
                        $quickStats = [
                            ['icon' => 'bi-grid-3x3-gap', 'color' => '#0F3D56', 'bg' => 'rgba(15,61,86,.1)',    'label' => 'Total Slots',    'value' => $parking['total_slots']],
                            ['icon' => 'bi-stars',         'color' => '#b45309', 'bg' => 'rgba(245,158,11,.12)', 'label' => 'Facilities',     'value' => count($facilities)],
                            ['icon' => 'bi-images',        'color' => '#0277bd', 'bg' => 'rgba(2,136,209,.1)',   'label' => 'Photos',         'value' => count($images)],
                            ['icon' => 'bi-currency-rupee','color' => '#1aaa5a', 'bg' => 'rgba(46,204,113,.12)', 'label' => 'Price Plans',    'value' => count($pricing)],
                        ];
                    @endphp
                    <div class="row g-0">
                        @foreach ($quickStats as $i => $qs)
                            <div class="col-6" style="padding:.9rem 1.2rem; {{ $i < 2 ? 'border-bottom:1px solid #f5f7f9;' : '' }} {{ $i % 2 === 0 ? 'border-right:1px solid #f5f7f9;' : '' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:34px; height:34px; border-radius:9px; background:{{ $qs['bg'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <i class="bi {{ $qs['icon'] }}" style="color:{{ $qs['color'] }}; font-size:.9rem;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:.7rem; color:#8899aa; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">{{ $qs['label'] }}</div>
                                        <div style="font-size:1.15rem; font-weight:800; color:#0D1B2A; line-height:1.1;">{{ $qs['value'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>{{-- /right column --}}

    </div>{{-- /row --}}

    {{-- ════════════════════════════════════════════════════════
         CONFIRMATION CHECKBOX
    ════════════════════════════════════════════════════════ --}}
    <div class="confirm-card">
        <div class="d-flex align-items-start gap-3 flex-wrap">

            {{-- Confirmation icon --}}
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(15,61,86,.08); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="bi bi-patch-check" style="font-size:1.3rem; color:#0F3D56;"></i>
            </div>

            <div class="flex-grow-1">
                <div style="font-size:.92rem; font-weight:700; color:#0D1B2A; margin-bottom:.5rem;">
                    Final Confirmation
                </div>
                <p style="font-size:.84rem; color:#5A6A7A; margin-bottom:.9rem; line-height:1.5;">
                    Please review all the parking details carefully before submitting.
                    Once submitted, the parking will be sent for admin approval and
                    cannot be edited until it is reviewed.
                </p>

                <label class="confirm-check" id="confirmLabel">
                    <input
                        type="checkbox"
                        id="confirmCheck"
                        onchange="toggleSubmit(this)"
                    >
                    <span>
                        I confirm that all parking information provided is accurate and complete,
                        and I authorise the submission of this parking listing for admin review.
                    </span>
                </label>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         ACTION BUTTONS
    ════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-2 mb-2">

        {{-- Previous --}}
        <a href="#" class="btn-prev">
            <i class="bi bi-arrow-left" style="font-size:.82rem;"></i>
            Previous
        </a>

        {{-- Right group --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- Save as Draft --}}
            <button type="button" class="btn-draft">
                <i class="bi bi-floppy" style="font-size:.82rem;"></i>
                Save as Draft
            </button>

            {{-- Submit Parking --}}
            <button
                type="button"
                class="btn-submit"
                id="submitBtn"
                disabled
                title="Please confirm details before submitting."
            >
                <i class="bi bi-send-check" style="font-size:.88rem;"></i>
                Submit Parking
            </button>

        </div>
    </div>

@endsection

@push('scripts')
<script>
    /**
     * Enable / disable the Submit button based on the confirmation checkbox.
     * No libraries required — plain DOM manipulation.
     */
    function toggleSubmit(checkbox) {
        const btn = document.getElementById('submitBtn');
        if (checkbox.checked) {
            btn.disabled = false;
            btn.title    = 'Submit this parking for admin review.';
        } else {
            btn.disabled = true;
            btn.title    = 'Please confirm details before submitting.';
        }
    }
</script>
@endpush