{{-- ============================================================
     Payments Management — Index
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Full payment transaction list with summary cards,
               advanced filters, method badges, status chips,
               and per-row action buttons.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Payments Management')
@section('page-title', 'Payments Management')

@push('styles')
<style>
    /* ── Summary stat cards ──────────────────────────────────── */
    .stat-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        padding:       1.25rem 1.35rem;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        height:        100%;
        transition:    box-shadow .18s, transform .18s;
    }
    .stat-card:hover {
        box-shadow: 0 6px 22px rgba(15,61,86,.11);
        transform:  translateY(-2px);
    }
    .stat-icon {
        width:         46px;
        height:        46px;
        border-radius: 12px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1.2rem;
        flex-shrink:   0;
    }
    .stat-value {
        font-size:   1.55rem;
        font-weight: 700;
        color:       #0D1B2A;
        line-height: 1.1;
    }
    .stat-label {
        font-size:  .78rem;
        color:      #8899aa;
        margin-top: .2rem;
        font-weight: 500;
    }
    .stat-badge {
        display:       inline-flex;
        align-items:   center;
        padding:       .22em .6em;
        border-radius: 20px;
        font-size:     .7rem;
        font-weight:   700;
        margin-top:    .4rem;
    }
    .badge-up   { background: rgba(46,204,113,.13); color: #1aaa5a; }
    .badge-down { background: rgba(231,76,60,.12);  color: #c0392b; }
    .badge-warn { background: rgba(245,158,11,.14); color: #c47d00; }

    /* ── Filter card ─────────────────────────────────────────── */
    .filter-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .filter-header {
        display:       flex;
        align-items:   center;
        justify-content: space-between;
        padding:       .85rem 1.25rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
        cursor:        pointer;
        user-select:   none;
    }
    .filter-header-title {
        font-size:   .875rem;
        font-weight: 700;
        color:       #0D1B2A;
    }
    .filter-body { padding: 1.1rem 1.25rem; }

    /* ── Form controls ───────────────────────────────────────── */
    .form-label {
        font-size:     .78rem;
        font-weight:   600;
        color:         #5A6A7A;
        margin-bottom: .3rem;
    }
    .form-control,
    .form-select {
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        font-size:     .86rem;
        color:         #0D1B2A;
        height:        38px;
        transition:    border-color .18s, box-shadow .18s;
        padding-left:  .85rem;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: #0F3D56;
        box-shadow:   0 0 0 3px rgba(15,61,86,.1);
    }
    .search-wrap { position: relative; }
    .search-wrap .bi-search {
        position:       absolute;
        left:           .8rem;
        top:            50%;
        transform:      translateY(-50%);
        color:          #8899aa;
        font-size:      .82rem;
        pointer-events: none;
    }
    .search-wrap .form-control { padding-left: 2.1rem; }

    /* ── Buttons ─────────────────────────────────────────────── */
    .btn-apply {
        height:        38px;
        border:        none;
        border-radius: 9px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .86rem;
        font-weight:   600;
        padding:       0 1.2rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s;
        white-space:   nowrap;
    }
    .btn-apply:hover { background: #0a2f42; }
    .btn-reset {
        height:        38px;
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        background:    #fff;
        color:         #5A6A7A;
        font-size:     .86rem;
        font-weight:   600;
        padding:       0 1.1rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s;
        white-space:   nowrap;
    }
    .btn-reset:hover { background: #f0f3f7; border-color: #c8d2dc; }

    /* ── Main table card ─────────────────────────────────────── */
    .table-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .table-card-header {
        display:       flex;
        align-items:   center;
        justify-content: space-between;
        padding:       1rem 1.35rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
        flex-wrap:     wrap;
        gap:           .6rem;
    }
    .pay-table thead th {
        font-size:      .72rem;
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
    .pay-table tbody td {
        font-size:      .855rem;
        padding:        .82rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .pay-table tbody tr:last-child td { border-bottom: none; }
    .pay-table tbody tr:hover td      { background: #fafcff; }

    /* ── Status badges ───────────────────────────────────────── */
    .status-pill {
        display:       inline-block;
        padding:       .28em .8em;
        border-radius: 20px;
        font-size:     .72rem;
        font-weight:   700;
        white-space:   nowrap;
    }
    .status-paid      { background: rgba(46,204,113,.14); color: #1aaa5a; }
    .status-pending   { background: rgba(245,158,11,.14); color: #c47d00; }
    .status-refunded  { background: rgba(52,144,220,.13); color: #2469ad; }
    .status-failed    { background: rgba(231,76,60,.12);  color: #c0392b; }

    /* ── Payment method badge ────────────────────────────────── */
    .method-pill {
        display:       inline-flex;
        align-items:   center;
        gap:           .3rem;
        padding:       .25em .7em;
        border-radius: 7px;
        font-size:     .72rem;
        font-weight:   600;
        white-space:   nowrap;
        background:    #f0f3f7;
        color:         #0D1B2A;
    }
    .method-upi        { background:rgba(15,61,86,.09);    color:#0F3D56; }
    .method-credit     { background:rgba(231,76,60,.1);    color:#c0392b; }
    .method-debit      { background:rgba(52,144,220,.1);   color:#2469ad; }
    .method-netbanking { background:rgba(245,158,11,.12);  color:#c47d00; }
    .method-wallet     { background:rgba(139,92,246,.1);   color:#6d28d9; }
    .method-cash       { background:rgba(46,204,113,.12);  color:#1aaa5a; }

    /* ── Action buttons ──────────────────────────────────────── */
    .act-btn {
        display:        inline-flex;
        align-items:    center;
        justify-content: center;
        height:         28px;
        border-radius:  7px;
        border:         1px solid transparent;
        font-size:      .72rem;
        font-weight:    600;
        cursor:         pointer;
        transition:     background .15s, color .15s, border-color .15s;
        padding:        0 .65rem;
        text-decoration: none;
        white-space:    nowrap;
        gap:            .3rem;
    }
    .act-view     { background:rgba(15,61,86,.08);   color:#0F3D56; }
    .act-view:hover   { background:#0F3D56; color:#fff; border-color:#0F3D56; }
    .act-receipt  { background:rgba(46,204,113,.1);  color:#1aaa5a; }
    .act-receipt:hover{ background:#2ECC71; color:#fff; border-color:#2ECC71; }
    .act-refund   { background:rgba(52,144,220,.1);  color:#2469ad; }
    .act-refund:hover { background:#3490dc; color:#fff; border-color:#3490dc; }
    .act-btn-icon {
        width:         28px;
        height:        28px;
        border-radius: 7px;
        border:        1px solid transparent;
        font-size:     .8rem;
        cursor:        pointer;
        transition:    background .15s, color .15s;
        display:       inline-flex;
        align-items:   center;
        justify-content: center;
        text-decoration: none;
    }

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination .page-link {
        border-radius: 8px !important;
        margin:        0 2px;
        font-size:     .83rem;
        color:         #0F3D56;
        border:        1px solid #e2e8ee;
        padding:       .36rem .72rem;
        transition:    background .15s, color .15s;
    }
    .pagination .page-link:hover       { background: #f0f3f7; border-color: #c8d2dc; }
    .pagination .page-item.active .page-link {
        background: #0F3D56; border-color: #0F3D56; color: #fff;
    }
    .pagination .page-item.disabled .page-link { color: #c0c8d0; }

    /* ── Divider label ───────────────────────────────────────── */
    .results-meta { font-size: .8rem; color: #8899aa; }

    /* ── Avatar initials ─────────────────────────────────────── */
    .cust-avatar {
        width:         32px;
        height:        32px;
        border-radius: 8px;
        display:       inline-flex;
        align-items:   center;
        justify-content: center;
        font-size:     .75rem;
        font-weight:   700;
        color:         #fff;
        flex-shrink:   0;
    }

    /* responsive */
    @media (max-width:575.98px) {
        .stat-value { font-size: 1.3rem; }
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading + breadcrumb ─────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A;font-weight:700;">
                Payments Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56;text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Payments</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="#" class="btn-reset" style="height:36px;">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="#" class="btn-apply" style="height:36px;">
                <i class="bi bi-plus-lg"></i> Record Payment
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SUMMARY CARDS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        @php
            $cards = [
                [
                    'icon'    => 'bi-arrow-left-right',
                    'ibg'     => 'rgba(15,61,86,.1)',
                    'icolor'  => '#0F3D56',
                    'value'   => '24,813',
                    'label'   => 'Total Transactions',
                    'badge'   => '+8.4%',
                    'bclass'  => 'badge-up',
                    'bicon'   => 'bi-arrow-up-short',
                ],
                [
                    'icon'    => 'bi-calendar-check-fill',
                    'ibg'     => 'rgba(46,204,113,.12)',
                    'icolor'  => '#2ECC71',
                    'value'   => '₹ 84,250',
                    'label'   => "Today's Collection",
                    'badge'   => '+12.1%',
                    'bclass'  => 'badge-up',
                    'bicon'   => 'bi-arrow-up-short',
                ],
                [
                    'icon'    => 'bi-hourglass-split',
                    'ibg'     => 'rgba(245,158,11,.12)',
                    'icolor'  => '#f59e0b',
                    'value'   => '₹ 18,640',
                    'label'   => 'Pending Payments',
                    'badge'   => '142 txns',
                    'bclass'  => 'badge-warn',
                    'bicon'   => 'bi-clock',
                ],
                [
                    'icon'    => 'bi-arrow-counterclockwise',
                    'ibg'     => 'rgba(52,144,220,.12)',
                    'icolor'  => '#3490dc',
                    'value'   => '₹ 9,380',
                    'label'   => 'Refunded Payments',
                    'badge'   => '38 txns',
                    'bclass'  => 'badge-warn',
                    'bicon'   => 'bi-arrow-return-left',
                ],
                [
                    'icon'    => 'bi-graph-up-arrow',
                    'ibg'     => 'rgba(139,92,246,.1)',
                    'icolor'  => '#7c3aed',
                    'value'   => '₹ 6.42L',
                    'label'   => 'Monthly Revenue',
                    'badge'   => '+18.7%',
                    'bclass'  => 'badge-up',
                    'bicon'   => 'bi-arrow-up-short',
                ],
            ];
        @endphp

        @foreach ($cards as $c)
            <div class="col-12 col-sm-6 col-xl">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:{{ $c['ibg'] }};">
                            <i class="bi {{ $c['icon'] }}" style="color:{{ $c['icolor'] }};"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="stat-value">{{ $c['value'] }}</div>
                            <div class="stat-label">{{ $c['label'] }}</div>
                            <div class="stat-badge {{ $c['bclass'] }}">
                                <i class="bi {{ $c['bicon'] }} me-1"></i>{{ $c['badge'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>{{-- /summary cards --}}


    {{-- ══════════════════════════════════════════════════════════
         FILTER SECTION
    ══════════════════════════════════════════════════════════ --}}
    <div class="filter-card mb-4">
        <div class="filter-header"
             data-bs-toggle="collapse"
             data-bs-target="#filterBody"
             aria-expanded="true"
             aria-controls="filterBody"
        >
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill" style="color:#0F3D56;font-size:.9rem;"></i>
                <span class="filter-header-title">Search &amp; Filter Payments</span>
            </div>
            <i class="bi bi-chevron-down" style="font-size:.8rem;color:#8899aa;transition:transform .2s;" id="filter-chevron"></i>
        </div>

        <div class="collapse show" id="filterBody">
            <div class="filter-body">
                <div class="row g-3 align-items-end">

                    {{-- Search any --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                        <label class="form-label">Search Payment</label>
                        <div class="search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" placeholder="Name, ID, parking…">
                        </div>
                    </div>

                    {{-- Transaction ID --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" placeholder="TXN-XXXX">
                    </div>

                    {{-- Booking ID --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Booking ID</label>
                        <input type="text" class="form-control" placeholder="BK-XXXX">
                    </div>

                    {{-- Payment Method --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select">
                            <option value="">All Methods</option>
                            <option>UPI</option>
                            <option>Credit Card</option>
                            <option>Debit Card</option>
                            <option>Net Banking</option>
                            <option>Wallet</option>
                            <option>Cash</option>
                        </select>
                    </div>

                    {{-- Payment Status --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option value="">All Statuses</option>
                            <option>Paid</option>
                            <option>Pending</option>
                            <option>Refunded</option>
                            <option>Failed</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" value="2025-07-01">
                    </div>

                    {{-- Date To --}}
                    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" value="2025-07-29">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 col-sm-auto">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-reset">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
                            <button type="button" class="btn-apply">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>{{-- /filter card --}}


    {{-- ══════════════════════════════════════════════════════════
         PAYMENTS TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="table-card">

        {{-- Table header --}}
        <div class="table-card-header">
            <div>
                <h6 class="mb-0" style="font-weight:700;color:#0D1B2A;">
                    Payment Transactions
                </h6>
                <p class="mb-0" style="font-size:.78rem;color:#8899aa;">
                    Showing 1–12 of 312 transactions
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Per page --}}
                <select class="form-select form-select-sm"
                    style="width:auto;border-radius:8px;border-color:#e2e8ee;font-size:.82rem;">
                    <option>12 / page</option>
                    <option>25 / page</option>
                    <option>50 / page</option>
                </select>
            </div>
        </div>

        {{-- Scrollable table --}}
        <div class="table-responsive">
            <table class="table pay-table mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Parking Name</th>
                        <th>Amount</th>
                        <th>GST</th>
                        <th>Method</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $payments = [
                            ['TXN-8841','BK-2201','Riya Sharma',     'riya',  '0F3D56','Green Park, Delhi',       480,  57.60,'UPI',        'upi',        '29 Jul 2025, 09:14','paid'    ],
                            ['TXN-8840','BK-2200','Aman Verma',      'aman',  '1a7a50','Metro Station, Mumbai',   340,  40.80,'Credit Card', 'credit',     '29 Jul 2025, 08:52','paid'    ],
                            ['TXN-8839','BK-2199','Sneha Patel',     'sneha', '2d6a8f','City Mall, Ahmedabad',    800, 96.00, 'Net Banking', 'netbanking', '28 Jul 2025, 22:30','refunded'],
                            ['TXN-8838','BK-2198','Rohan Mehta',     'rohan', '8a4d9e','Sector 18, Noida',        600,  72.00,'Debit Card',  'debit',      '28 Jul 2025, 19:05','paid'    ],
                            ['TXN-8837','BK-2197','Priya Singh',     'priya', 'c0392b','MG Road, Bengaluru',      380,  45.60,'UPI',         'upi',        '28 Jul 2025, 17:41','pending' ],
                            ['TXN-8836','BK-2196','Karan Kapoor',    'karan', 'd35400','Saket, New Delhi',        700,  84.00,'Wallet',      'wallet',     '28 Jul 2025, 15:22','paid'    ],
                            ['TXN-8835','BK-2195','Divya Nair',      'divya', '27ae60','Andheri West, Mumbai',    440,  52.80,'UPI',         'upi',        '27 Jul 2025, 21:08','failed'  ],
                            ['TXN-8834','BK-2194','Vikram Joshi',    'vikra', '3490dc','Green Park, Delhi',       960, 115.20,'Credit Card', 'credit',     '27 Jul 2025, 14:35','paid'    ],
                            ['TXN-8833','BK-2193','Meena Reddy',     'meena', '7c3aed','Banjara Hills, Hyd',      520,  62.40,'Net Banking', 'netbanking', '27 Jul 2025, 11:19','pending' ],
                            ['TXN-8832','BK-2192','Harish Rao',      'haros', '0F3D56','Anna Nagar, Chennai',     280,  33.60,'Cash',        'cash',       '26 Jul 2025, 20:00','paid'    ],
                            ['TXN-8831','BK-2191','Anjali Bose',     'anjal', '1a7a50','Park Street, Kolkata',    360,  43.20,'Debit Card',  'debit',      '26 Jul 2025, 16:44','refunded'],
                            ['TXN-8830','BK-2190','Rahul Trivedi',   'rahul', '8a4d9e','FC Road, Pune',           640,  76.80,'UPI',         'upi',        '26 Jul 2025, 09:30','paid'    ],
                        ];

                        $statusClass  = ['paid'=>'status-paid','pending'=>'status-pending','refunded'=>'status-refunded','failed'=>'status-failed'];
                        $methodClass  = ['upi'=>'method-upi','credit'=>'method-credit','debit'=>'method-debit','netbanking'=>'method-netbanking','wallet'=>'method-wallet','cash'=>'method-cash'];
                        $methodIcon   = ['upi'=>'bi-phone-fill','credit'=>'bi-credit-card-fill','debit'=>'bi-credit-card-2-back-fill','netbanking'=>'bi-bank','wallet'=>'bi-wallet2','cash'=>'bi-cash-stack'];
                    @endphp

                    @foreach ($payments as [
                        $txn, $bk, $cust, $init, $avatarBg,
                        $parking, $amount, $gst,
                        $method, $methodKey,
                        $date, $status
                    ])
                        <tr>
                            {{-- Transaction ID --}}
                            <td>
                                <span style="font-weight:700;font-size:.8rem;color:#0F3D56;">
                                    {{ $txn }}
                                </span>
                            </td>

                            {{-- Booking ID --}}
                            <td>
                                <a href="#" style="color:#3490dc;font-size:.82rem;font-weight:600;text-decoration:none;">
                                    {{ $bk }}
                                </a>
                            </td>

                            {{-- Customer --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cust-avatar" style="background:#{{ $avatarBg }};">
                                        {{ strtoupper($init) }}
                                    </div>
                                    <span style="font-weight:500;white-space:nowrap;">{{ $cust }}</span>
                                </div>
                            </td>

                            {{-- Parking --}}
                            <td style="color:#5A6A7A;font-size:.82rem;max-width:160px;">
                                <i class="bi bi-geo-alt me-1" style="color:#0F3D56;font-size:.7rem;"></i>
                                {{ $parking }}
                            </td>

                            {{-- Amount --}}
                            <td>
                                <span style="font-weight:700;font-size:.9rem;">
                                    ₹&nbsp;{{ number_format($amount) }}
                                </span>
                            </td>

                            {{-- GST --}}
                            <td style="color:#8899aa;font-size:.82rem;">
                                ₹&nbsp;{{ number_format($gst, 2) }}
                            </td>

                            {{-- Method --}}
                            <td>
                                <span class="method-pill {{ $methodClass[$methodKey] }}">
                                    <i class="bi {{ $methodIcon[$methodKey] }}"></i>
                                    {{ $method }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td style="color:#5A6A7A;font-size:.8rem;white-space:nowrap;">
                                {{ $date }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="status-pill {{ $statusClass[$status] }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="#" class="act-btn act-view" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="#" class="act-btn act-receipt" title="Download Receipt">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @if ($status === 'paid')
                                        <button
                                            type="button"
                                            class="act-btn act-refund"
                                            title="Issue Refund"
                                            data-bs-toggle="modal"
                                            data-bs-target="#refundModal"
                                            data-txn="{{ $txn }}"
                                            data-amount="{{ $amount }}"
                                            data-cust="{{ $cust }}"
                                        >
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>{{-- /table-responsive --}}

        {{-- Footer: meta + pagination --}}
        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-3"
            style="border-top:1px solid #f0f3f7;background:#fafbfc;"
        >
            <p class="results-meta mb-0">
                Showing <strong>1–12</strong> of <strong>312</strong> transactions
                &nbsp;·&nbsp; Total collected: <strong style="color:#0F3D56;">₹ 6,480</strong>
            </p>
            <nav aria-label="Payment transactions pagination">
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">
                            <i class="bi bi-chevron-left" style="font-size:.68rem;"></i>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#" style="letter-spacing:.1em;">…</a></li>
                    <li class="page-item"><a class="page-link" href="#">26</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="bi bi-chevron-right" style="font-size:.68rem;"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>{{-- /table-card --}}


    {{-- ══════════════════════════════════════════════════════════
         REFUND CONFIRMATION MODAL
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="refundModal" tabindex="-1"
         aria-labelledby="refundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:14px;border:1px solid #e2e8ee;overflow:hidden;">
                <div class="modal-body text-center p-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:58px;height:58px;background:rgba(52,144,220,.1);border-radius:14px;">
                        <i class="bi bi-arrow-counterclockwise" style="font-size:1.5rem;color:#3490dc;"></i>
                    </div>
                    <h6 class="mb-1" style="font-weight:700;color:#0D1B2A;">
                        Issue Refund?
                    </h6>
                    <p class="mb-1" style="font-size:.86rem;color:#5A6A7A;">
                        Refunding <strong id="refund-cust"></strong>
                    </p>
                    <p class="mb-3" style="font-size:.86rem;color:#5A6A7A;">
                        Transaction <strong id="refund-txn" style="color:#0F3D56;"></strong>
                        &nbsp;·&nbsp;
                        Amount: <strong id="refund-amount" style="color:#3490dc;"></strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label text-start d-block" style="font-size:.78rem;">
                            Refund Reason <span style="color:#e74c3c;">*</span>
                        </label>
                        <select class="form-select">
                            <option value="">Select reason…</option>
                            <option>Cancelled by customer</option>
                            <option>Duplicate payment</option>
                            <option>Parking unavailable</option>
                            <option>Technical error</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn-reset" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn-apply" style="background:#3490dc;">
                            <i class="bi bi-check-lg"></i> Confirm Refund
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    /* ── Populate refund modal ───────────────────────────────── */
    const refundModal = document.getElementById('refundModal');
    if (refundModal) {
        refundModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('refund-txn').textContent    = btn.dataset.txn;
            document.getElementById('refund-amount').textContent = '₹ ' + parseInt(btn.dataset.amount).toLocaleString('en-IN');
            document.getElementById('refund-cust').textContent   = btn.dataset.cust;
        });
    }

    /* ── Rotate filter chevron ──────────────────────────────── */
    const filterBody = document.getElementById('filterBody');
    const chevron    = document.getElementById('filter-chevron');
    if (filterBody && chevron) {
        filterBody.addEventListener('show.bs.collapse',  () => chevron.style.transform = 'rotate(180deg)');
        filterBody.addEventListener('hide.bs.collapse',  () => chevron.style.transform = 'rotate(0deg)');
    }
</script>
@endpush