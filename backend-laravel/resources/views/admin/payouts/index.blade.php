@extends('layouts.admin')

@section('title', 'Payout Management')
@section('page-title', 'Payout Management')

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
    .sbadge-pending   { background:rgba(245,158,11,.14);  color:#b45309; }
    .sbadge-processing{ background:rgba(15,61,86,.1);     color:#0F3D56; }
    .sbadge-completed { background:rgba(46,204,113,.14);  color:#1aaa5a; }
    .sbadge-rejected  { background:rgba(231,76,60,.12);   color:#c0392b; }

    /* ── Table ────────────────────────────────────────────────── */
    .payout-table { width:100%; border-collapse:collapse; }
    .payout-table th {
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
    .payout-table td {
        font-size:      .85rem;
        padding:        .75rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .payout-table tr:last-child td { border-bottom:none; }
    .payout-table tr:hover td      { background:#fafcff; }

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
    .btn-action-view    { background:rgba(15,61,86,.1);  color:#0F3D56; }
    .btn-action-view:hover { background:#0F3D56; color:#fff; }
    .btn-action-approve { background:rgba(46,204,113,.12); color:#1aaa5a; }
    .btn-action-approve:hover { background:#1aaa5a; color:#fff; }
    .btn-action-reject  { background:rgba(231,76,60,.1);   color:#e74c3c; }
    .btn-action-reject:hover { background:#e74c3c; color:#fff; }

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
                'label'    => 'Total Payouts',
                'value'    => '1,247',
                'change'   => '+18.6%',
                'dir'      => 'up',
                'sub'      => 'vs last month 1,052',
                'icon'     => 'bi-cash-stack',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '💰',
            ],
            [
                'label'    => 'Pending Payouts',
                'value'    => '342',
                'change'   => '+12.4%',
                'dir'      => 'up',
                'sub'      => 'vs last month 304',
                'icon'     => 'bi-clock-history',
                'iconBg'   => 'rgba(245,158,11,.13)',
                'iconColor'=> '#b45309',
                'wm'       => '⏳',
            ],
            [
                'label'    => 'Completed Payouts',
                'value'    => '872',
                'change'   => '+22.8%',
                'dir'      => 'up',
                'sub'      => 'vs last month 710',
                'icon'     => 'bi-check-circle',
                'iconBg'   => 'rgba(46,204,113,.13)',
                'iconColor'=> '#1aaa5a',
                'wm'       => '✅',
            ],
            [
                'label'    => 'Total Amount Paid',
                'value'    => '₹24,65,890',
                'change'   => '+19.1%',
                'dir'      => 'up',
                'sub'      => 'vs last month ₹20,70,000',
                'icon'     => 'bi-currency-rupee',
                'iconBg'   => 'rgba(15,61,86,.1)',
                'iconColor'=> '#0F3D56',
                'wm'       => '🏦',
            ],
        ];

        /* ── Payout data ─────────────────────────────────────── */
        $payouts = [
            [
                'id'          => 'PO-001',
                'owner'       => 'Vikram Joshi',
                'bank'        => 'State Bank of India',
                'amount'      => '₹52,840',
                'method'      => 'Bank Transfer',
                'requested'   => '15 Jul 2025',
                'paid'        => '18 Jul 2025',
                'status'      => 'completed',
            ],
            [
                'id'          => 'PO-002',
                'owner'       => 'Meena Reddy',
                'bank'        => 'HDFC Bank',
                'amount'      => '₹41,280',
                'method'      => 'UPI',
                'requested'   => '18 Jul 2025',
                'paid'        => '20 Jul 2025',
                'status'      => 'completed',
            ],
            [
                'id'          => 'PO-003',
                'owner'       => 'Sanjay Gupta',
                'bank'        => 'ICICI Bank',
                'amount'      => '₹38,620',
                'method'      => 'Bank Transfer',
                'requested'   => '20 Jul 2025',
                'paid'        => '22 Jul 2025',
                'status'      => 'completed',
            ],
            [
                'id'          => 'PO-004',
                'owner'       => 'Pooja Iyer',
                'bank'        => 'Axis Bank',
                'amount'      => '₹31,050',
                'method'      => 'UPI',
                'requested'   => '22 Jul 2025',
                'paid'        => '--',
                'status'      => 'pending',
            ],
            [
                'id'          => 'PO-005',
                'owner'       => 'Rahul Trivedi',
                'bank'        => 'Kotak Mahindra Bank',
                'amount'      => '₹28,460',
                'method'      => 'Bank Transfer',
                'requested'   => '23 Jul 2025',
                'paid'        => '--',
                'status'      => 'processing',
            ],
            [
                'id'          => 'PO-006',
                'owner'       => 'Anita Sharma',
                'bank'        => 'State Bank of India',
                'amount'      => '₹22,180',
                'method'      => 'UPI',
                'requested'   => '24 Jul 2025',
                'paid'        => '--',
                'status'      => 'pending',
            ],
            [
                'id'          => 'PO-007',
                'owner'       => 'Arjun Singh',
                'bank'        => 'HDFC Bank',
                'amount'      => '₹19,750',
                'method'      => 'Bank Transfer',
                'requested'   => '25 Jul 2025',
                'paid'        => '--',
                'status'      => 'processing',
            ],
            [
                'id'          => 'PO-008',
                'owner'       => 'Neha Patel',
                'bank'        => 'ICICI Bank',
                'amount'      => '₹16,420',
                'method'      => 'UPI',
                'requested'   => '26 Jul 2025',
                'paid'        => '27 Jul 2025',
                'status'      => 'completed',
            ],
            [
                'id'          => 'PO-009',
                'owner'       => 'Deepak Kumar',
                'bank'        => 'Axis Bank',
                'amount'      => '₹14,380',
                'method'      => 'Bank Transfer',
                'requested'   => '27 Jul 2025',
                'paid'        => '--',
                'status'      => 'rejected',
            ],
            [
                'id'          => 'PO-010',
                'owner'       => 'Priya Joshi',
                'bank'        => 'Kotak Mahindra Bank',
                'amount'      => '₹12,560',
                'method'      => 'UPI',
                'requested'   => '28 Jul 2025',
                'paid'        => '29 Jul 2025',
                'status'      => 'completed',
            ],
        ];

        /* ── Filter options ───────────────────────────────────── */
        $owners = ['All Owners', 'Vikram Joshi', 'Meena Reddy', 'Sanjay Gupta', 'Pooja Iyer', 'Rahul Trivedi', 'Anita Sharma', 'Arjun Singh', 'Neha Patel', 'Deepak Kumar', 'Priya Joshi'];
        $statuses = ['All Status', 'Pending', 'Processing', 'Completed', 'Rejected'];
        $methods = ['All Methods', 'Bank Transfer', 'UPI', 'Wallet'];

        $statusBadgeMap = [
            'pending'    => ['class'=>'sbadge-pending',    'icon'=>'bi-clock-fill',        'label'=>'Pending'],
            'processing' => ['class'=>'sbadge-processing', 'icon'=>'bi-arrow-repeat',      'label'=>'Processing'],
            'completed'  => ['class'=>'sbadge-completed',  'icon'=>'bi-check-circle-fill', 'label'=>'Completed'],
            'rejected'   => ['class'=>'sbadge-rejected',   'icon'=>'bi-x-circle-fill',     'label'=>'Rejected'],
        ];
    @endphp

    {{-- ── Page header ────────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        <div>
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                Payout Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Payouts</li>
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
                <i class="bi bi-plus-circle"></i> Create Payout
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
                {{-- Search Owner --}}
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="search" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-search"></i> Search Owner
                    </label>
                    <input type="text" class="form-control" id="search" placeholder="Owner name..."
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                {{-- Payment Status --}}
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

                {{-- Date Range --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="date_from" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-calendar3"></i> Date From
                    </label>
                    <input type="date" class="form-control" id="date_from"
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                <div class="col-12 col-md-6 col-lg-2">
                    <label for="date_to" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-calendar3"></i> Date To
                    </label>
                    <input type="date" class="form-control" id="date_to"
                           style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                </div>

                {{-- Payment Method --}}
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="method" class="form-label" style="font-size:.78rem; font-weight:600; color:#5A6A7A;">
                        <i class="bi bi-credit-card"></i> Payment Method
                    </label>
                    <select class="form-select" id="method"
                            style="border-radius:8px; border-color:#e2e8ee; font-size:.85rem; height:40px;">
                        @foreach ($methods as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
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
         SECTION 3 — PAYOUT TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h6>All Payouts</h6>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.78rem; color:#8899aa;">
                    Showing {{ count($payouts) }} payouts
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
                    <i class="bi bi-database"></i> {{ count($payouts) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="payout-table">
                <thead>
                    <tr>
                        <th>Payout ID</th>
                        <th>Parking Owner</th>
                        <th>Bank Name</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Requested Date</th>
                        <th>Paid Date</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payouts as $p)
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

                            {{-- Owner --}}
                            <td style="font-weight:600;">{{ $p['owner'] }}</td>

                            {{-- Bank Name --}}
                            <td style="color:#5A6A7A; font-size:.82rem;">{{ $p['bank'] }}</td>

                            {{-- Amount --}}
                            <td style="font-weight:700; color:#0F3D56; white-space:nowrap;">
                                {{ $p['amount'] }}
                            </td>

                            {{-- Payment Method --}}
                            <td>
                                <span style="font-size:.82rem; color:#5A6A7A;">
                                    @if($p['method'] == 'Bank Transfer')
                                        <i class="bi bi-building" style="font-size:.68rem;"></i>
                                    @elseif($p['method'] == 'UPI')
                                        <i class="bi bi-phone" style="font-size:.68rem;"></i>
                                    @else
                                        <i class="bi bi-wallet2" style="font-size:.68rem;"></i>
                                    @endif
                                    {{ $p['method'] }}
                                </span>
                            </td>

                            {{-- Requested Date --}}
                            <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                {{ $p['requested'] }}
                            </td>

                            {{-- Paid Date --}}
                            <td style="font-size:.78rem; color:#8899aa; white-space:nowrap;">
                                {{ $p['paid'] }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="sbadge {{ $sb['class'] }}">
                                    <i class="bi {{ $sb['icon'] }}" style="font-size:.62rem;"></i>
                                    {{ $sb['label'] }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="#" class="btn-action btn-action-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($p['status'] == 'pending')
                                        <a href="#" class="btn-action btn-action-approve" title="Approve">
                                            <i class="bi bi-check2"></i>
                                        </a>
                                        <a href="#" class="btn-action btn-action-reject" title="Reject">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    @elseif($p['status'] == 'processing')
                                        <a href="#" class="btn-action btn-action-approve" title="Complete">
                                            <i class="bi bi-check2"></i>
                                        </a>
                                    @endif
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
                <strong style="color:#0D1B2A;">1,247</strong> results
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