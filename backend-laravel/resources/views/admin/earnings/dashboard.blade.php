{{-- ============================================================
     Revenue / Earnings Dashboard
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Full revenue analytics dashboard — summary cards,
               charts, parking performance, payment breakdown,
               recent transactions, and top owner earnings.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Revenue Dashboard')
@section('page-title', 'Revenue Dashboard')

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

    /* ── Chart placeholder ───────────────────────────────────── */
    .chart-placeholder {
        width:       100%;
        border-radius: 10px;
        background:  #f8f9fa;
        border:      1px solid #f0f3f7;
        display:     flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        overflow:    hidden;
        gap:         0;
    }
    .chart-bars {
        display:         flex;
        align-items:     flex-end;
        justify-content: space-around;
        width:           100%;
        padding:         1rem 1rem 0;
        gap:             .4rem;
    }
    .chart-bar-wrap { display:flex; flex-direction:column; align-items:center; gap:.3rem; flex:1; }
    .chart-bar {
        width:         100%;
        border-radius: 6px 6px 0 0;
        transition:    height .3s;
    }
    .chart-bar-label { font-size:.68rem; color:#8899aa; font-weight:600; white-space:nowrap; }
    .chart-bar-val   { font-size:.7rem;  color:#0D1B2A; font-weight:700; }
    .chart-x-line {
        width:      100%;
        height:     1px;
        background: #e2e8ee;
        margin-top: .5rem;
    }

    /* ── Line chart SVG placeholder ─────────────────────────── */
    .line-chart-wrap {
        position:  relative;
        width:     100%;
        background:#f8f9fa;
        border-radius:10px;
        border:    1px solid #f0f3f7;
        overflow:  hidden;
    }
    .line-chart-y {
        position: absolute;
        left:     0; top:0; bottom:0;
        display:  flex;
        flex-direction: column;
        justify-content: space-between;
        padding:  .6rem .5rem .6rem .75rem;
    }
    .line-chart-y span {
        font-size:  .65rem;
        color:      #b0bec5;
        font-weight:600;
        font-family:monospace;
    }

    /* ── Parking performance table ───────────────────────────── */
    .perf-table { width:100%; border-collapse:collapse; }
    .perf-table th {
        font-size:      .72rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding:        .6rem 1rem;
        border-bottom:  1px solid #f0f3f7;
        background:     #fafbfc;
        white-space:    nowrap;
    }
    .perf-table td {
        font-size:      .855rem;
        padding:        .8rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .perf-table tr:last-child td { border-bottom:none; }
    .perf-table tr:hover td      { background:#fafcff; }
    .rank-badge {
        width:24px; height:24px; border-radius:7px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.73rem; font-weight:800; flex-shrink:0;
    }

    /* ── Progress bar method ─────────────────────────────────── */
    .method-row { padding:.7rem 0; border-bottom:1px solid #f5f7f9; }
    .method-row:last-child { border-bottom:none; padding-bottom:0; }
    .method-label {
        display:       flex;
        align-items:   center;
        justify-content: space-between;
        margin-bottom: .4rem;
    }
    .method-name {
        display:     flex;
        align-items: center;
        gap:         .5rem;
        font-size:   .855rem;
        font-weight: 600;
        color:       #0D1B2A;
    }
    .method-pct {
        font-size:  .78rem;
        font-weight:700;
        color:      #0D1B2A;
    }
    .method-amount {
        font-size: .73rem;
        color:     #8899aa;
    }

    /* ── Transactions table ──────────────────────────────────── */
    .txn-table { width:100%; border-collapse:collapse; }
    .txn-table th {
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
    .txn-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .txn-table tr:last-child td { border-bottom:none; }
    .txn-table tr:hover td      { background:#fafcff; }

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
    .sbadge-success  { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-pending  { background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-failed   { background:rgba(231,76,60,.12);   color:#c0392b; }
    .sbadge-refunded { background:rgba(143,163,180,.15); color:#5A6A7A; }

    /* ── Owner rows ──────────────────────────────────────────── */
    .owner-row {
        display:         flex;
        align-items:     center;
        gap:             .85rem;
        padding:         .8rem 1.25rem;
        border-bottom:   1px solid #f5f7f9;
        transition:      background .15s;
    }
    .owner-row:last-child { border-bottom:none; }
    .owner-row:hover      { background:#fafcff; }
    .owner-avatar {
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
    .owner-meta { flex:1; min-width:0; }
    .owner-name {
        font-size:   .875rem;
        font-weight: 700;
        color:       #0D1B2A;
        white-space: nowrap;
        overflow:    hidden;
        text-overflow:ellipsis;
    }
    .owner-sub { font-size:.74rem; color:#8899aa; }

    /* ── Export buttons ──────────────────────────────────────── */
    .btn-export {
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
    }
    .btn-export-primary { background:#0F3D56; color:#fff; }
    .btn-export-primary:hover { background:#0a2f42; color:#fff; }
    .btn-export-pdf  { background:rgba(231,76,60,.1); color:#e74c3c; border:1px solid rgba(231,76,60,.25); }
    .btn-export-pdf:hover  { background:#e74c3c; color:#fff; }
    .btn-export-xl   { background:rgba(46,204,113,.12); color:#1aaa5a; border:1px solid rgba(46,204,113,.25); }
    .btn-export-xl:hover   { background:#1aaa5a; color:#fff; }

    /* ── Growth chip ─────────────────────────────────────────── */
    .growth-chip {
        display:      inline-flex;
        align-items:  center;
        gap:          .25rem;
        padding:      .25em .7em;
        border-radius:20px;
        font-size:    .73rem;
        font-weight:  700;
    }
    .growth-up   { background:rgba(46,204,113,.12);  color:#1aaa5a; }
    .growth-down { background:rgba(231,76,60,.1);    color:#e74c3c; }

    /* ── Mono ────────────────────────────────────────────────── */
    .mono { font-family:monospace; letter-spacing:.03em; }
</style>
@endpush

@section('content')

    @php
        /* ── Static dummy data ───────────────────────────────── */

        // Summary cards
        $summaryCards = [
            [
                'label'    => "Today's Revenue",
                'value'    => '₹12,480',
                'change'   => '+18.4%',
                'dir'      => 'up',
                'sub'      => 'vs yesterday ₹10,540',
                'icon'     => 'bi-sun',
                'iconBg'   => 'rgba(245,158,11,.13)',
                'iconColor'=> '#b45309',
                'wm'       => '☀',
            ],
            [
                'label'    => 'Weekly Revenue',
                'value'    => '₹84,560',
                'change'   => '+11.2%',
                'dir'      => 'up',
                'sub'      => 'vs last week ₹76,020',
                'icon'     => 'bi-calendar-week',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '📅',
            ],
            [
                'label'    => 'Monthly Revenue',
                'value'    => '₹3,45,890',
                'change'   => '+15.7%',
                'dir'      => 'up',
                'sub'      => 'vs last month ₹2,98,940',
                'icon'     => 'bi-graph-up-arrow',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '📈',
            ],
            [
                'label'    => 'Yearly Revenue',
                'value'    => '₹24,65,890',
                'change'   => '+19.1%',
                'dir'      => 'up',
                'sub'      => 'vs last year ₹20,70,000',
                'icon'     => 'bi-trophy',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '🏆',
            ],
            [
                'label'    => 'Pending Settlements',
                'value'    => '₹18,240',
                'change'   => '-4.3%',
                'dir'      => 'down',
                'sub'      => '12 owners awaiting payout',
                'icon'     => 'bi-hourglass-split',
                'iconBg'   => 'rgba(231,76,60,.1)',
                'iconColor'=> '#e74c3c',
                'wm'       => '⏳',
            ],
        ];

        // Monthly chart bars
        $chartMonths = [
            ['mon'=>'Jan','val'=>182000,'h'=>42,'color'=>'#c8d9e6'],
            ['mon'=>'Feb','val'=>198000,'h'=>46,'color'=>'#c8d9e6'],
            ['mon'=>'Mar','val'=>215000,'h'=>50,'color'=>'#c8d9e6'],
            ['mon'=>'Apr','val'=>231000,'h'=>54,'color'=>'#c8d9e6'],
            ['mon'=>'May','val'=>248000,'h'=>57,'color'=>'#c8d9e6'],
            ['mon'=>'Jun','val'=>272000,'h'=>63,'color'=>'#c8d9e6'],
            ['mon'=>'Jul','val'=>296000,'h'=>69,'color'=>'#0F3D56'],
            ['mon'=>'Aug','val'=>318000,'h'=>74,'color'=>'#c8d9e6'],
            ['mon'=>'Sep','val'=>298000,'h'=>69,'color'=>'#c8d9e6'],
            ['mon'=>'Oct','val'=>324000,'h'=>75,'color'=>'#c8d9e6'],
            ['mon'=>'Nov','val'=>338000,'h'=>78,'color'=>'#c8d9e6'],
            ['mon'=>'Dec','val'=>345890,'h'=>80,'color'=>'#2ecc71'],
        ];

        // Top parking locations
        $topParkings = [
            ['rank'=>1,'name'=>'Connaught Place Parking', 'city'=>'New Delhi',  'revenue'=>'₹5,28,400','bookings'=>'2,140','occ'=>'88%','color'=>'#0F3D56','bg'=>'rgba(15,61,86,.1)'],
            ['rank'=>2,'name'=>'Ambience Mall Parking',   'city'=>'Gurugram',   'revenue'=>'₹4,12,800','bookings'=>'1,870','occ'=>'82%','color'=>'#1aaa5a','bg'=>'rgba(46,204,113,.12)'],
            ['rank'=>3,'name'=>'Saket Mall Parking',      'city'=>'New Delhi',  'revenue'=>'₹3,86,200','bookings'=>'1,720','occ'=>'79%','color'=>'#b45309','bg'=>'rgba(245,158,11,.12)'],
            ['rank'=>4,'name'=>'DLF Phase 2 EV Hub',      'city'=>'Gurugram',   'revenue'=>'₹3,10,500','bookings'=>'1,480','occ'=>'74%','color'=>'#0277bd','bg'=>'rgba(2,136,209,.1)'],
            ['rank'=>5,'name'=>'MGF Metropolitan Mall',   'city'=>'Gurugram',   'revenue'=>'₹2,84,600','bookings'=>'1,210','occ'=>'68%','color'=>'#8e44ad','bg'=>'rgba(142,68,173,.1)'],
        ];

        // Payment methods
        $methods = [
            ['name'=>'UPI',          'icon'=>'bi-phone',              'color'=>'#0F3D56','pct'=>42,'amt'=>'₹1,02,600'],
            ['name'=>'Credit Card',  'icon'=>'bi-credit-card',        'color'=>'#3490dc','pct'=>28,'amt'=>'₹68,400'],
            ['name'=>'Debit Card',   'icon'=>'bi-credit-card-2-front','color'=>'#1aaa5a','pct'=>15,'amt'=>'₹36,640'],
            ['name'=>'Wallet',       'icon'=>'bi-wallet2',            'color'=>'#b45309','pct'=>10,'amt'=>'₹24,430'],
            ['name'=>'Cash',         'icon'=>'bi-cash-stack',         'color'=>'#8899aa','pct'=>5, 'amt'=>'₹12,220'],
        ];

        // Recent transactions
        $transactions = [
            ['id'=>'TXN20250721001','customer'=>'Rahul Sharma', 'parking'=>'Connaught Place Parking','amount'=>'₹120','date'=>'21 Jul 2025, 10:14 AM','status'=>'success'],
            ['id'=>'TXN20250721002','customer'=>'Priya Patel',  'parking'=>'Ambience Mall Parking',  'amount'=>'₹80', 'date'=>'21 Jul 2025, 09:52 AM','status'=>'success'],
            ['id'=>'TXN20250721003','customer'=>'Amit Kumar',   'parking'=>'Mall Parking',           'amount'=>'₹100','date'=>'21 Jul 2025, 09:30 AM','status'=>'pending'],
            ['id'=>'TXN20250721004','customer'=>'Neha Singh',   'parking'=>'City Center Parking',    'amount'=>'₹60', 'date'=>'21 Jul 2025, 09:10 AM','status'=>'success'],
            ['id'=>'TXN20250721005','customer'=>'Vivek Patel',  'parking'=>'Connaught Place Parking','amount'=>'₹120','date'=>'21 Jul 2025, 08:45 AM','status'=>'failed'],
            ['id'=>'TXN20250720001','customer'=>'Anjali Bose',  'parking'=>'Saket Mall Parking',     'amount'=>'₹90', 'date'=>'20 Jul 2025, 06:30 PM','status'=>'refunded'],
        ];

        // Top owners
        $topOwners = [
            ['name'=>'Vikram Joshi',  'init'=>'V','color'=>'#0F3D56','earnings'=>'₹5,28,400','bookings'=>'2,140','commission'=>'₹52,840'],
            ['name'=>'Meena Reddy',   'init'=>'M','color'=>'#1aaa5a','earnings'=>'₹4,12,800','bookings'=>'1,870','commission'=>'₹41,280'],
            ['name'=>'Sanjay Gupta',  'init'=>'S','color'=>'#b45309','earnings'=>'₹3,86,200','bookings'=>'1,720','commission'=>'₹38,620'],
            ['name'=>'Pooja Iyer',    'init'=>'P','color'=>'#0277bd','earnings'=>'₹3,10,500','bookings'=>'1,480','commission'=>'₹31,050'],
            ['name'=>'Rahul Trivedi', 'init'=>'R','color'=>'#8e44ad','earnings'=>'₹2,84,600','bookings'=>'1,210','commission'=>'₹28,460'],
        ];

        $statusBadgeMap = [
            'success'  => ['class'=>'sbadge-success',  'icon'=>'bi-check-circle-fill', 'label'=>'Success'],
            'pending'  => ['class'=>'sbadge-pending',  'icon'=>'bi-clock-fill',        'label'=>'Pending'],
            'failed'   => ['class'=>'sbadge-failed',   'icon'=>'bi-x-circle-fill',     'label'=>'Failed'],
            'refunded' => ['class'=>'sbadge-refunded', 'icon'=>'bi-arrow-counterclockwise','label'=>'Refunded'],
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                Revenue Dashboard
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Earnings</li>
                </ol>
            </nav>
        </div>

        {{-- Export action buttons --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn-export btn-export-pdf">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </button>
            <button type="button" class="btn-export btn-export-xl">
                <i class="bi bi-file-earmark-excel"></i> Download Excel
            </button>
            <button type="button" class="btn-export btn-export-primary">
                <i class="bi bi-box-arrow-up-right"></i> Export Report
            </button>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 1 — SUMMARY CARDS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        @foreach ($summaryCards as $card)
            <div class="col-12 col-sm-6 col-xxl">
                <div class="stat-card">
                    <div class="watermark">{{ $card['wm'] }}</div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="stat-label">{{ $card['label'] }}</div>
                        <div class="stat-icon" style="background:{{ $card['iconBg'] }};">
                            <i class="bi {{ $card['icon'] }}" style="color:{{ $card['iconColor'] }};"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ $card['value'] }}</div>
                    <div class="stat-change {{ $card['dir'] }}">
                        <i class="bi bi-arrow-{{ $card['dir'] == 'up' ? 'up' : 'down' }}-right-circle-fill"></i>
                        {{ $card['change'] }} vs last period
                    </div>
                    <div class="stat-sub">{{ $card['sub'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 2 — REVENUE ANALYTICS (Chart + KPIs)
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Large bar chart ──────────────────────────────────── --}}
        <div class="col-12 col-xl-8">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h6>Monthly Earnings Overview</h6>
                        <div style="font-size:.75rem; color:#8899aa; margin-top:.1rem;">
                            Jan 2025 — Dec 2025 &nbsp;·&nbsp; All Parkings
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="growth-chip growth-up">
                            <i class="bi bi-arrow-up-right"></i> +19.1% YoY
                        </span>
                        <select
                            class="form-select form-select-sm"
                            style="width:auto; font-size:.78rem; border-radius:7px; border-color:#e2e8ee;"
                        >
                            <option selected>2025</option>
                            <option>2024</option>
                            <option>2023</option>
                        </select>
                    </div>
                </div>
                <div class="dash-card-body">

                    {{-- Y-axis labels + bars --}}
                    <div style="display:flex; gap:.5rem; align-items:flex-end;">

                        {{-- Y labels --}}
                        <div style="
                            display:flex; flex-direction:column;
                            justify-content:space-between;
                            height:200px; flex-shrink:0;
                            padding:.25rem 0;
                        ">
                            @foreach (['4.0L','3.0L','2.0L','1.0L','0'] as $yl)
                                <span style="font-size:.62rem; color:#b0bec5; font-weight:600; line-height:1; font-family:monospace;">
                                    {{ $yl }}
                                </span>
                            @endforeach
                        </div>

                        {{-- Bars --}}
                        <div style="flex:1; display:flex; flex-direction:column; gap:0;">
                            {{-- Horizontal grid lines --}}
                            <div style="
                                position:relative; height:200px;
                                display:flex; align-items:flex-end;
                            ">
                                {{-- Grid lines --}}
                                @foreach ([0,25,50,75,100] as $pct)
                                    <div style="
                                        position:absolute; left:0; right:0;
                                        bottom:{{ $pct }}%; height:1px;
                                        background:{{ $pct === 0 ? '#e2e8ee' : '#f0f3f7' }};
                                    "></div>
                                @endforeach

                                {{-- Bar columns --}}
                                <div style="
                                    display:flex; align-items:flex-end;
                                    gap:6px; width:100%; height:100%;
                                    position:relative; z-index:1;
                                ">
                                    @foreach ($chartMonths as $m)
                                        <div style="
                                            flex:1; display:flex;
                                            flex-direction:column;
                                            align-items:center;
                                            justify-content:flex-end;
                                            height:100%; gap:0;
                                        ">
                                            <div style="
                                                width:100%;
                                                height:{{ $m['h'] }}%;
                                                background:{{ $m['color'] }};
                                                border-radius:5px 5px 0 0;
                                                transition:height .3s;
                                            "></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- X labels --}}
                            <div style="
                                display:flex; gap:6px;
                                border-top:1px solid #e2e8ee;
                                padding-top:.4rem;
                            ">
                                @foreach ($chartMonths as $m)
                                    <div style="
                                        flex:1; text-align:center;
                                        font-size:.62rem; color:#8899aa;
                                        font-weight:600;
                                    ">{{ $m['mon'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="d-flex align-items-center gap-4 mt-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2" style="font-size:.75rem; color:#5A6A7A;">
                            <div style="width:12px;height:12px;background:#0F3D56;border-radius:3px;flex-shrink:0;"></div>
                            Current Month
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:.75rem; color:#5A6A7A;">
                            <div style="width:12px;height:12px;background:#2ecc71;border-radius:3px;flex-shrink:0;"></div>
                            Highest Month
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:.75rem; color:#5A6A7A;">
                            <div style="width:12px;height:12px;background:#c8d9e6;border-radius:3px;flex-shrink:0;"></div>
                            Other Months
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- KPI summary column ───────────────────────────────── --}}
        <div class="col-12 col-xl-4">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <h6>Growth Summary</h6>
                    <span class="card-badge" style="background:rgba(46,204,113,.12);color:#1aaa5a;">2025</span>
                </div>
                <div class="dash-card-body d-flex flex-column gap-3">

                    @php
                        $kpis = [
                            ['label'=>'Total Annual Revenue',  'val'=>'₹24,65,890','growth'=>'+19.1%','up'=>true,'icon'=>'bi-currency-rupee','bg'=>'rgba(15,61,86,.1)','c'=>'#0F3D56'],
                            ['label'=>'Average Monthly',       'val'=>'₹2,05,490', 'growth'=>'+11.4%','up'=>true,'icon'=>'bi-graph-up',      'bg'=>'rgba(46,204,113,.12)','c'=>'#1aaa5a'],
                            ['label'=>'Peak Month (Dec)',       'val'=>'₹3,45,890', 'growth'=>'+22.8%','up'=>true,'icon'=>'bi-trophy',        'bg'=>'rgba(245,158,11,.12)','c'=>'#b45309'],
                            ['label'=>'Total Transactions',    'val'=>'14,820',     'growth'=>'+16.3%','up'=>true,'icon'=>'bi-receipt',       'bg'=>'rgba(2,136,209,.1)','c'=>'#0277bd'],
                            ['label'=>'Avg Booking Value',     'val'=>'₹166',       'growth'=>'+2.1%', 'up'=>true,'icon'=>'bi-calculator',   'bg'=>'rgba(142,68,173,.1)','c'=>'#8e44ad'],
                            ['label'=>'Cancellation Rate',     'val'=>'3.2%',       'growth'=>'-0.8%', 'up'=>false,'icon'=>'bi-x-circle',    'bg'=>'rgba(231,76,60,.1)','c'=>'#e74c3c'],
                        ];
                    @endphp

                    @foreach ($kpis as $kpi)
                        <div class="d-flex align-items-center gap-3">
                            <div style="
                                width:38px; height:38px; border-radius:9px;
                                background:{{ $kpi['bg'] }};
                                display:flex; align-items:center; justify-content:center;
                                font-size:.95rem; flex-shrink:0;
                            ">
                                <i class="bi {{ $kpi['icon'] }}" style="color:{{ $kpi['c'] }};"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:.74rem; color:#8899aa; font-weight:600;">
                                    {{ $kpi['label'] }}
                                </div>
                                <div style="font-size:.92rem; font-weight:800; color:#0D1B2A; line-height:1.2;">
                                    {{ $kpi['val'] }}
                                </div>
                            </div>
                            <span class="growth-chip {{ $kpi['up'] ? 'growth-up' : 'growth-down' }}">
                                <i class="bi bi-arrow-{{ $kpi['up'] ? 'up' : 'down' }}-right"></i>
                                {{ $kpi['growth'] }}
                            </span>
                        </div>
                        @if (!$loop->last)
                            <hr style="margin:0; border-color:#f5f7f9;">
                        @endif
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 3 — PARKING PERFORMANCE + PAYMENT METHODS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Top 5 Parking Locations ──────────────────────────── --}}
        <div class="col-12 col-xl-7">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Top 5 Parking Locations by Revenue</h6>
                    <span class="card-badge" style="background:rgba(15,61,86,.1);color:#0F3D56;">
                        This Year
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="perf-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Parking Location</th>
                                <th>Revenue</th>
                                <th>Bookings</th>
                                <th>Occupancy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topParkings as $p)
                                <tr>
                                    {{-- Rank --}}
                                    <td>
                                        <div
                                            class="rank-badge"
                                            style="background:{{ $p['bg'] }}; color:{{ $p['color'] }};"
                                        >{{ $p['rank'] }}</div>
                                    </td>

                                    {{-- Name --}}
                                    <td>
                                        <div style="font-weight:700; font-size:.855rem;">{{ $p['name'] }}</div>
                                        <div style="font-size:.73rem; color:#8899aa;">
                                            <i class="bi bi-geo-alt" style="font-size:.68rem;"></i>
                                            {{ $p['city'] }}
                                        </div>
                                    </td>

                                    {{-- Revenue --}}
                                    <td>
                                        <span style="font-weight:700; color:#0F3D56;">{{ $p['revenue'] }}</span>
                                    </td>

                                    {{-- Bookings --}}
                                    <td style="color:#5A6A7A;">{{ $p['bookings'] }}</td>

                                    {{-- Occupancy --}}
                                    <td>
                                        @php $oNum = (int)$p['occ']; @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="
                                                flex:1; max-width:60px; height:5px;
                                                background:#f0f3f7; border-radius:3px; overflow:hidden;
                                            ">
                                                <div style="
                                                    width:{{ $oNum }}%; height:100%;
                                                    background:{{ $oNum >= 80 ? '#1aaa5a' : ($oNum >= 65 ? '#b45309' : '#e74c3c') }};
                                                    border-radius:3px;
                                                "></div>
                                            </div>
                                            <span style="font-size:.8rem; font-weight:700; color:#0D1B2A; white-space:nowrap;">
                                                {{ $p['occ'] }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment Method Breakdown ─────────────────────────── --}}
        <div class="col-12 col-xl-5">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <h6>Payment Method Breakdown</h6>
                    <span class="card-badge" style="background:rgba(46,204,113,.12);color:#1aaa5a;">
                        This Month
                    </span>
                </div>
                <div class="dash-card-body">

                    {{-- Donut-style summary --}}
                    <div
                        class="d-flex align-items-center justify-content-center gap-3 mb-4 p-3"
                        style="background:#f8f9fa; border-radius:10px; border:1px solid #f0f3f7;"
                    >
                        <div class="text-center">
                            <div style="font-size:1.4rem; font-weight:800; color:#0F3D56;">₹2,44,290</div>
                            <div style="font-size:.72rem; color:#8899aa; font-weight:600;">Total This Month</div>
                        </div>
                        <div style="width:1px; height:36px; background:#e2e8ee;"></div>
                        <div class="text-center">
                            <div style="font-size:1.4rem; font-weight:800; color:#1aaa5a;">14,820</div>
                            <div style="font-size:.72rem; color:#8899aa; font-weight:600;">Transactions</div>
                        </div>
                    </div>

                    {{-- Progress bars --}}
                    @foreach ($methods as $m)
                        <div class="method-row">
                            <div class="method-label">
                                <div class="method-name">
                                    <div style="
                                        width:28px; height:28px; border-radius:7px;
                                        background:{{ $m['color'] }}18;
                                        display:flex; align-items:center; justify-content:center;
                                        flex-shrink:0; font-size:.8rem;
                                    ">
                                        <i class="bi {{ $m['icon'] }}" style="color:{{ $m['color'] }};"></i>
                                    </div>
                                    {{ $m['name'] }}
                                </div>
                                <div class="text-end">
                                    <div class="method-pct">{{ $m['pct'] }}%</div>
                                    <div class="method-amount">{{ $m['amt'] }}</div>
                                </div>
                            </div>
                            <div style="height:7px; background:#f0f3f7; border-radius:4px; overflow:hidden;">
                                <div style="
                                    width:{{ $m['pct'] }}%; height:100%;
                                    background:{{ $m['color'] }};
                                    border-radius:4px;
                                    transition:width .4s;
                                "></div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 4 — RECENT TRANSACTIONS + TOP OWNERS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3">

        {{-- Recent Transactions ──────────────────────────────── --}}
        <div class="col-12 col-xl-7">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h6>Recent Transactions</h6>
                    <a href="#" class="text-decoration-none fw-bold small text-primary">
                        View All <i class="bi bi-arrow-right" style="font-size:.7rem;"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="txn-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Parking</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $txn)
                                @php $sb = $statusBadgeMap[$txn['status']]; @endphp
                                <tr>
                                    {{-- TXN ID --}}
                                    <td>
                                        <span class="mono" style="font-size:.8rem; color:#0F3D56; font-weight:700;">
                                            {{ $txn['id'] }}
                                        </span>
                                    </td>

                                    {{-- Customer --}}
                                    <td style="font-weight:600;">{{ $txn['customer'] }}</td>

                                    {{-- Parking --}}
                                    <td style="color:#5A6A7A; font-size:.82rem; max-width:160px;">
                                        <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; max-width:150px;">
                                            {{ $txn['parking'] }}
                                        </span>
                                    </td>

                                    {{-- Amount --}}
                                    <td style="font-weight:700; white-space:nowrap;">{{ $txn['amount'] }}</td>

                                    {{-- Date --}}
                                    <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                        {{ $txn['date'] }}
                                    </td>

                                    {{-- Status --}}
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
            </div>
        </div>

        {{-- Top Owners ───────────────────────────────────────── --}}
        <div class="col-12 col-xl-5">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <h6>Top Earning Owners</h6>
                    <a href="#" class="text-decoration-none fw-bold small text-primary">
                        View All <i class="bi bi-arrow-right" style="font-size:.7rem;"></i>
                    </a>
                </div>

                {{-- Column headers --}}
                <div style="
                    display:grid;
                    grid-template-columns:1fr auto auto auto;
                    gap:.5rem;
                    padding:.55rem 1.25rem;
                    border-bottom:1px solid #f0f3f7;
                    background:#fafbfc;
                    font-size:.7rem;
                    font-weight:600;
                    color:#8899aa;
                    text-transform:uppercase;
                    letter-spacing:.05em;
                ">
                    <div>Owner</div>
                    <div style="text-align:right; white-space:nowrap;">Earnings</div>
                    <div style="text-align:right;">Bookings</div>
                    <div style="text-align:right;">Commission</div>
                </div>

                @foreach ($topOwners as $ow)
                    <div style="
                        display:grid;
                        grid-template-columns:1fr auto auto auto;
                        gap:.5rem;
                        align-items:center;
                        padding:.75rem 1.25rem;
                        border-bottom:1px solid #f5f7f9;
                        transition:background .15s;
                    "
                    onmouseover="this.style.background='#fafcff'"
                    onmouseout="this.style.background='transparent'"
                    >
                        {{-- Owner name --}}
                        <div class="d-flex align-items-center gap-2" style="min-width:0;">
                            <div
                                class="owner-avatar"
                                style="background:{{ $ow['color'] }}; width:34px; height:34px; border-radius:9px; font-size:.82rem;"
                            >{{ $ow['init'] }}</div>
                            <div style="min-width:0;">
                                <div style="font-size:.855rem; font-weight:700; color:#0D1B2A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:110px;">
                                    {{ $ow['name'] }}
                                </div>
                                <div style="font-size:.7rem; color:#8899aa;">Parking Owner</div>
                            </div>
                        </div>

                        {{-- Earnings --}}
                        <div style="text-align:right; font-weight:700; color:#0F3D56; font-size:.84rem; white-space:nowrap;">
                            {{ $ow['earnings'] }}
                        </div>

                        {{-- Bookings --}}
                        <div style="text-align:right; font-size:.82rem; color:#5A6A7A; font-weight:600;">
                            {{ $ow['bookings'] }}
                        </div>

                        {{-- Commission --}}
                        <div style="text-align:right; font-size:.82rem; font-weight:700; color:#1aaa5a; white-space:nowrap;">
                            {{ $ow['commission'] }}
                        </div>
                    </div>
                @endforeach

                {{-- Footer note --}}
                <div
                    class="d-flex align-items-center gap-2 px-4 py-3"
                    style="border-top:1px solid #f0f3f7; background:#fafbfc;"
                >
                    <i class="bi bi-info-circle" style="font-size:.8rem; color:#8899aa;"></i>
                    <span style="font-size:.74rem; color:#8899aa;">
                        Commission is calculated at 10% of total earnings.
                    </span>
                </div>

            </div>
        </div>

    </div>{{-- /row --}}

@endsection