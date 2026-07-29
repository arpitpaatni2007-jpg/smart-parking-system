{{-- ============================================================
     Payments — Show (Payment Details)
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Full payment detail view — summary, customer,
               parking info, financial breakdown, gateway
               transaction info, timeline and receipt preview.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@push('styles')
<style>
    /* ── Detail card shell ───────────────────────────────────── */
    .det-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
        height:        100%;
    }
    .det-card-header {
        display:       flex;
        align-items:   center;
        gap:           .75rem;
        padding:       .95rem 1.35rem;
        background:    #fafbfc;
        border-bottom: 1px solid #f0f3f7;
    }
    .det-card-icon {
        width:         36px;
        height:        36px;
        border-radius: 9px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .95rem;
        flex-shrink:   0;
    }
    .det-card-title {
        font-size:   .92rem;
        font-weight: 700;
        color:       #0D1B2A;
        margin:      0;
    }
    .det-card-body { padding: 1.25rem 1.35rem; }

    /* ── Key-value pair rows ─────────────────────────────────── */
    .kv-row {
        display:       flex;
        align-items:   flex-start;
        justify-content: space-between;
        gap:           1rem;
        padding:       .65rem 0;
        border-bottom: 1px solid #f5f7f9;
    }
    .kv-row:last-child { border-bottom: none; }
    .kv-key {
        font-size:  .8rem;
        color:      #8899aa;
        font-weight: 500;
        white-space: nowrap;
        flex-shrink: 0;
        min-width:   130px;
    }
    .kv-val {
        font-size:   .86rem;
        color:       #0D1B2A;
        font-weight: 600;
        text-align:  right;
        word-break:  break-word;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .status-pill {
        display:       inline-flex;
        align-items:   center;
        gap:           .3rem;
        padding:       .28em .8em;
        border-radius: 20px;
        font-size:     .72rem;
        font-weight:   700;
        white-space:   nowrap;
    }
    .s-paid       { background: rgba(46,204,113,.14); color: #1aaa5a; }
    .s-pending    { background: rgba(245,158,11,.14); color: #c47d00; }
    .s-refunded   { background: rgba(52,144,220,.13); color: #2469ad; }
    .s-failed     { background: rgba(231,76,60,.12);  color: #c0392b; }
    .s-settled    { background: rgba(15,61,86,.1);    color: #0F3D56; }
    .s-processing { background: rgba(139,92,246,.1);  color: #6d28d9; }
    .s-na         { background: #f0f3f7; color: #8899aa; }

    /* ── Payment method badge ────────────────────────────────── */
    .method-chip {
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        padding:       .3em .85em;
        border-radius: 8px;
        font-size:     .78rem;
        font-weight:   700;
        background:    rgba(15,61,86,.09);
        color:         #0F3D56;
    }

    /* ── Customer avatar ─────────────────────────────────────── */
    .cust-avatar-lg {
        width:         64px;
        height:        64px;
        border-radius: 16px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1.5rem;
        font-weight:   700;
        color:         #fff;
        flex-shrink:   0;
    }

    /* ── Payment breakdown ───────────────────────────────────── */
    .breakdown-row {
        display:     flex;
        align-items: center;
        justify-content: space-between;
        padding:     .6rem 0;
    }
    .breakdown-row + .breakdown-row {
        border-top: 1px dashed #f0f3f7;
    }
    .breakdown-label {
        font-size:  .84rem;
        color:      #5A6A7A;
    }
    .breakdown-value {
        font-size:   .88rem;
        color:       #0D1B2A;
        font-weight: 600;
    }
    .breakdown-total {
        display:     flex;
        align-items: center;
        justify-content: space-between;
        padding:     .9rem 1.1rem;
        background:  #0F3D56;
        border-radius: 10px;
        margin-top:  .75rem;
    }
    .breakdown-total-label {
        font-size:   .9rem;
        font-weight: 700;
        color:       rgba(255,255,255,.85);
    }
    .breakdown-total-value {
        font-size:   1.3rem;
        font-weight: 700;
        color:       #fff;
    }
    .breakdown-discount { color: #1aaa5a; }
    .breakdown-coupon   { color: #2469ad; }

    /* ── Payment timeline ────────────────────────────────────── */
    .timeline { position: relative; }
    .tl-item  {
        display:       flex;
        gap:           1rem;
        position:      relative;
        padding-bottom: 1.2rem;
    }
    .tl-item:last-child { padding-bottom: 0; }
    .tl-item:last-child .tl-line { display: none; }
    .tl-left  { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
    .tl-dot   {
        width:         36px;
        height:        36px;
        border-radius: 50%;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .85rem;
        flex-shrink:   0;
        z-index:       1;
        position:      relative;
    }
    .tl-dot.done    { background: rgba(46,204,113,.15); color: #1aaa5a; }
    .tl-dot.active  { background: #0F3D56; color: #fff; box-shadow: 0 0 0 4px rgba(15,61,86,.12); }
    .tl-dot.pending { background: #f0f3f7; color: #8899aa; }
    .tl-line  {
        width:         2px;
        flex:          1;
        min-height:    18px;
        background:    #f0f3f7;
        margin:        4px 0;
    }
    .tl-line.done { background: rgba(46,204,113,.35); }
    .tl-content { padding-top: .45rem; }
    .tl-title   { font-size:.875rem; font-weight:700; color:#0D1B2A; margin:0; }
    .tl-sub     { font-size:.78rem; color:#8899aa; margin:.15rem 0 0; }
    .tl-time    { font-size:.75rem; color:#3490dc; font-weight:600; margin:.1rem 0 0; }

    /* ── Receipt preview card ────────────────────────────────── */
    .receipt-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .receipt-header {
        background:    #0F3D56;
        padding:       1.5rem 1.5rem 1rem;
        position:      relative;
        overflow:      hidden;
    }
    .receipt-header::after {
        content:    '';
        position:   absolute;
        bottom:     -20px;
        left:       0;
        right:      0;
        height:     40px;
        background: #fff;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .receipt-logo-text {
        font-size:   1.1rem;
        font-weight: 700;
        color:       #fff;
    }
    .receipt-logo-sub { font-size:.73rem; color:rgba(255,255,255,.65); }
    .receipt-body    { padding: 1.25rem 1.5rem; }
    .receipt-divider {
        border:     none;
        border-top: 1.5px dashed #e2e8ee;
        margin:     1rem 0;
    }
    .receipt-row {
        display:       flex;
        justify-content: space-between;
        align-items:   center;
        margin-bottom: .45rem;
    }
    .receipt-row span:first-child { font-size:.78rem; color:#8899aa; }
    .receipt-row span:last-child  { font-size:.82rem; font-weight:600; color:#0D1B2A; }
    .receipt-total-row {
        display:       flex;
        justify-content: space-between;
        align-items:   center;
        background:    rgba(15,61,86,.05);
        border-radius: 10px;
        padding:       .75rem 1rem;
        margin-top:    .75rem;
    }
    .receipt-qr {
        width:         90px;
        height:        90px;
        border:        1.5px solid #e2e8ee;
        border-radius: 10px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        flex-shrink:   0;
        background:    #fafbfc;
        overflow:      hidden;
    }
    /* CSS-only QR placeholder grid */
    .qr-grid {
        display:               grid;
        grid-template-columns: repeat(7, 1fr);
        grid-template-rows:    repeat(7, 1fr);
        gap:                   2px;
        padding:               6px;
        width:                 100%;
        height:                100%;
    }
    .qr-cell { border-radius: 1px; }
    .qr-dark { background: #0F3D56; }
    .qr-lite { background: transparent; }

    /* ── Action buttons ──────────────────────────────────────── */
    .btn-back {
        height:        40px;
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        background:    #fff;
        color:         #5A6A7A;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.2rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s;
    }
    .btn-back:hover { background:#f0f3f7; color:#5A6A7A; }
    .btn-refund {
        height:        40px;
        border:        1.5px solid #3490dc;
        border-radius: 9px;
        background:    transparent;
        color:         #3490dc;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.2rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s, color .15s;
    }
    .btn-refund:hover { background:#3490dc; color:#fff; }
    .btn-dl {
        height:        40px;
        border:        none;
        border-radius: 9px;
        background:    #2ECC71;
        color:         #fff;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.2rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s;
    }
    .btn-dl:hover { background:#27ae60; color:#fff; }
    .btn-print {
        height:        40px;
        border:        none;
        border-radius: 9px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.2rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration: none;
        transition:    background .15s;
    }
    .btn-print:hover { background:#0a2f42; color:#fff; }

    /* ── Info strip ──────────────────────────────────────────── */
    .info-strip {
        background:    rgba(15,61,86,.04);
        border:        1px solid rgba(15,61,86,.1);
        border-radius: 10px;
        padding:       .75rem 1rem;
        font-size:     .8rem;
        color:         #0F3D56;
        display:       flex;
        align-items:   center;
        gap:           .6rem;
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading + breadcrumb ─────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A;font-weight:700;">Payment Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56;text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56;text-decoration:none;">Payments</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Payment Details</li>
                </ol>
            </nav>
        </div>

        {{-- Action buttons (top) --}}
        <div class="d-flex flex-wrap gap-2">
            <a href="#" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="#" class="btn-refund"
               data-bs-toggle="modal" data-bs-target="#refundModal">
                <i class="bi bi-arrow-counterclockwise"></i> Refund
            </a>
            <a href="#" class="btn-dl">
                <i class="bi bi-download"></i> Receipt
            </a>
            <a href="#" class="btn-print" onclick="window.print(); return false;">
                <i class="bi bi-printer"></i> Print
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ROW 1 — Payment Summary + Customer Info
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">

        {{-- Payment Summary ───────────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-receipt-cutoff" style="color:#0F3D56;"></i>
                    </div>
                    <p class="det-card-title">Payment Summary</p>
                </div>
                <div class="det-card-body">

                    {{-- Hero amount --}}
                    <div class="text-center mb-4 pt-1">
                        <div style="font-size:2.4rem;font-weight:700;color:#0D1B2A;line-height:1;">
                            ₹&nbsp;636.00
                        </div>
                        <div style="font-size:.78rem;color:#8899aa;margin-top:.3rem;">Grand Total (incl. GST)</div>
                        <div class="mt-2">
                            <span class="status-pill s-paid">
                                <i class="bi bi-check-circle-fill"></i> Paid
                            </span>
                        </div>
                    </div>

                    @php
                        $summary = [
                            ['Transaction ID',   'TXN-8841'],
                            ['Booking ID',       'BK-2201'],
                            ['Invoice Number',   'INV-2025-07-8841'],
                            ['Payment Date',     '29 Jul 2025'],
                            ['Payment Time',     '09:14:32 AM'],
                            ['Payment Method',   '__method__'],
                        ];
                    @endphp
                    @foreach ($summary as [$k, $v])
                        <div class="kv-row">
                            <span class="kv-key">{{ $k }}</span>
                            @if ($v === '__method__')
                                <span class="kv-val">
                                    <span class="method-chip">
                                        <i class="bi bi-phone-fill"></i> UPI
                                    </span>
                                </span>
                            @else
                                <span class="kv-val" style="color:{{ $k === 'Transaction ID' || $k === 'Invoice Number' ? '#0F3D56' : '#0D1B2A' }};">
                                    {{ $v }}
                                </span>
                            @endif
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- Customer Information ─────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-person-fill" style="color:#2ECC71;"></i>
                    </div>
                    <p class="det-card-title">Customer Information</p>
                </div>
                <div class="det-card-body">

                    {{-- Profile --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3"
                         style="background:#f8f9fa;border-radius:12px;">
                        <div class="cust-avatar-lg" style="background:#0F3D56;">RS</div>
                        <div>
                            <div style="font-size:1rem;font-weight:700;color:#0D1B2A;">Riya Sharma</div>
                            <div style="font-size:.78rem;color:#8899aa;margin-top:.15rem;">
                                <i class="bi bi-shield-check-fill me-1" style="color:#2ECC71;font-size:.7rem;"></i>
                                Verified Customer
                            </div>
                            <div style="font-size:.75rem;color:#3490dc;margin-top:.1rem;">
                                Member since Jan 2024
                            </div>
                        </div>
                    </div>

                    @php
                        $custRows = [
                            ['bi-envelope-fill',   '#2ECC71',   'Email',          'riya.sharma@email.com'],
                            ['bi-telephone-fill',  '#3490dc',   'Phone',          '+91 98100 12345'],
                            ['bi-car-front-fill',  '#f59e0b',   'Vehicle Number', 'DL-01-AB-1234'],
                            ['bi-geo-alt-fill',    '#e74c3c',   'City',           'New Delhi'],
                            ['bi-bookmark-star',   '#8a4d9e',   'Membership',     'Gold Member'],
                        ];
                    @endphp
                    @foreach ($custRows as [$icon, $icolor, $label, $val])
                        <div class="kv-row">
                            <span class="kv-key d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }}" style="color:{{ $icolor }};font-size:.8rem;"></i>
                                {{ $label }}
                            </span>
                            <span class="kv-val">{{ $val }}</span>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- Parking Information ──────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(52,144,220,.12);">
                        <i class="bi bi-p-square-fill" style="color:#3490dc;"></i>
                    </div>
                    <p class="det-card-title">Parking Information</p>
                </div>
                <div class="det-card-body">

                    {{-- Parking name banner --}}
                    <div class="mb-4 p-3" style="background:rgba(15,61,86,.05);border-radius:12px;border-left:4px solid #0F3D56;">
                        <div style="font-size:.95rem;font-weight:700;color:#0F3D56;">
                            Green Park Parking
                        </div>
                        <div style="font-size:.78rem;color:#8899aa;margin-top:.2rem;">
                            <i class="bi bi-geo-alt me-1"></i>
                            Green Park, New Delhi — 110016
                        </div>
                        <div class="mt-2">
                            <span class="status-pill s-settled" style="font-size:.68rem;">
                                <i class="bi bi-star-fill" style="font-size:.6rem;"></i> 4.8 Rating
                            </span>
                        </div>
                    </div>

                    @php
                        $parkRows = [
                            ['bi-person-badge-fill', '#0F3D56',  'Parking Owner',  'Vikram Joshi'],
                            ['bi-signpost-fill',     '#2ECC71',  'Full Address',   '14, Green Park Ext., New Delhi'],
                            ['bi-grid-3x3-gap-fill', '#3490dc',  'Slot Number',    'B-07 (Floor 1)'],
                            ['bi-clock-fill',        '#f59e0b',  'Check-In',       '29 Jul 2025 · 09:00 AM'],
                            ['bi-clock-history',     '#e74c3c',  'Check-Out',      '29 Jul 2025 · 01:00 PM'],
                            ['bi-stopwatch-fill',    '#8a4d9e',  'Duration',       '4 Hours'],
                        ];
                    @endphp
                    @foreach ($parkRows as [$icon, $icolor, $label, $val])
                        <div class="kv-row">
                            <span class="kv-key d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }}" style="color:{{ $icolor }};font-size:.78rem;"></i>
                                {{ $label }}
                            </span>
                            <span class="kv-val">{{ $val }}</span>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>{{-- /row 1 --}}


    {{-- ══════════════════════════════════════════════════════════
         ROW 2 — Payment Breakdown + Transaction Info + Timeline
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">

        {{-- Payment Breakdown ────────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(245,158,11,.12);">
                        <i class="bi bi-calculator-fill" style="color:#f59e0b;"></i>
                    </div>
                    <p class="det-card-title">Payment Breakdown</p>
                </div>
                <div class="det-card-body">

                    @php
                        $breakdown = [
                            ['Parking Charges (4 hrs × ₹120)',  '₹ 480.00', '', false],
                            ['GST @ 12%',                       '₹ 57.60',  '', false],
                            ['Convenience Fee',                  '₹ 18.00',  '', false],
                            ['Discount (Weekend Deal)',          '- ₹ 12.00','breakdown-discount', false],
                            ['Coupon — PARK20',                  '- ₹ 7.60', 'breakdown-coupon',   false],
                        ];
                    @endphp

                    @foreach ($breakdown as [$label, $val, $cls, $bold])
                        <div class="breakdown-row">
                            <span class="breakdown-label">{{ $label }}</span>
                            <span class="breakdown-value {{ $cls }}">{{ $val }}</span>
                        </div>
                    @endforeach

                    <div class="breakdown-total">
                        <span class="breakdown-total-label">Grand Total</span>
                        <span class="breakdown-total-value">₹ 636.00</span>
                    </div>

                    <div class="info-strip mt-3">
                        <i class="bi bi-shield-check-fill" style="font-size:.9rem;flex-shrink:0;"></i>
                        Payment secured via Razorpay PG with 256-bit SSL encryption.
                    </div>

                </div>
            </div>
        </div>

        {{-- Transaction Information ──────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(139,92,246,.1);">
                        <i class="bi bi-diagram-3-fill" style="color:#7c3aed;"></i>
                    </div>
                    <p class="det-card-title">Transaction Information</p>
                </div>
                <div class="det-card-body">

                    @php
                        $txnRows = [
                            ['Payment Gateway',        'Razorpay',              ''],
                            ['Gateway Txn Ref.',       'rzp_live_Xy8AbC1234Zq', '#0F3D56'],
                            ['UPI Reference ID',       '519274810293847',        '#3490dc'],
                            ['UPI VPA',                'riya@okicici',           ''],
                            ['Gateway Status',         '__status_success__',     ''],
                            ['Settlement Status',      '__status_settled__',     ''],
                            ['Refund Status',          '__status_na__',          ''],
                            ['Webhook Received',       '29 Jul 2025 · 09:14:35',''],
                        ];
                    @endphp
                    @foreach ($txnRows as [$k, $v, $vc])
                        <div class="kv-row">
                            <span class="kv-key">{{ $k }}</span>
                            <span class="kv-val">
                                @if ($v === '__status_success__')
                                    <span class="status-pill s-paid">
                                        <i class="bi bi-check-circle-fill"></i> Success
                                    </span>
                                @elseif ($v === '__status_settled__')
                                    <span class="status-pill s-settled">
                                        <i class="bi bi-bank2"></i> Settled
                                    </span>
                                @elseif ($v === '__status_na__')
                                    <span class="status-pill s-na">
                                        — N/A
                                    </span>
                                @else
                                    <span style="{{ $vc ? 'color:'.$vc.';' : '' }}font-size:.8rem;word-break:break-all;">
                                        {{ $v }}
                                    </span>
                                @endif
                            </span>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- Payment Timeline ─────────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="det-card">
                <div class="det-card-header">
                    <div class="det-card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-clock-history" style="color:#2ECC71;"></i>
                    </div>
                    <p class="det-card-title">Payment Timeline</p>
                </div>
                <div class="det-card-body">
                    <div class="timeline">

                        @php
                            $timeline = [
                                [
                                    'icon'    => 'bi-calendar-plus-fill',
                                    'state'   => 'done',
                                    'title'   => 'Booking Created',
                                    'sub'     => 'Customer initiated parking booking',
                                    'time'    => '29 Jul 2025 · 09:10:02 AM',
                                ],
                                [
                                    'icon'    => 'bi-credit-card-fill',
                                    'state'   => 'done',
                                    'title'   => 'Payment Initiated',
                                    'sub'     => 'Redirected to Razorpay UPI gateway',
                                    'time'    => '29 Jul 2025 · 09:12:18 AM',
                                ],
                                [
                                    'icon'    => 'bi-check-circle-fill',
                                    'state'   => 'active',
                                    'title'   => 'Payment Successful',
                                    'sub'     => 'Amount of ₹636.00 debited via UPI',
                                    'time'    => '29 Jul 2025 · 09:14:32 AM',
                                ],
                                [
                                    'icon'    => 'bi-receipt',
                                    'state'   => 'done',
                                    'title'   => 'Receipt Generated',
                                    'sub'     => 'Invoice INV-2025-07-8841 created',
                                    'time'    => '29 Jul 2025 · 09:14:35 AM',
                                ],
                                [
                                    'icon'    => 'bi-envelope-check-fill',
                                    'state'   => 'done',
                                    'title'   => 'Confirmation Sent',
                                    'sub'     => 'Email & SMS sent to customer',
                                    'time'    => '29 Jul 2025 · 09:14:40 AM',
                                ],
                                [
                                    'icon'    => 'bi-bank2',
                                    'state'   => 'done',
                                    'title'   => 'Settlement Completed',
                                    'sub'     => 'Amount settled to parking owner account',
                                    'time'    => '30 Jul 2025 · 08:00:00 AM',
                                ],
                            ];
                        @endphp

                        @foreach ($timeline as $tl)
                            <div class="tl-item">
                                <div class="tl-left">
                                    <div class="tl-dot {{ $tl['state'] }}">
                                        <i class="bi {{ $tl['icon'] }}" style="font-size:.8rem;"></i>
                                    </div>
                                    <div class="tl-line {{ $tl['state'] === 'done' ? 'done' : '' }}"></div>
                                </div>
                                <div class="tl-content">
                                    <p class="tl-title">{{ $tl['title'] }}</p>
                                    <p class="tl-sub">{{ $tl['sub'] }}</p>
                                    <p class="tl-time">
                                        <i class="bi bi-clock me-1" style="font-size:.65rem;"></i>
                                        {{ $tl['time'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row 2 --}}


    {{-- ══════════════════════════════════════════════════════════
         ROW 3 — Receipt Preview (full width)
    ══════════════════════════════════════════════════════════ --}}
    <div class="receipt-card mb-4">
        <div class="receipt-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.15);
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-p-square-fill" style="color:#fff;font-size:.95rem;"></i>
                        </div>
                        <div>
                            <div class="receipt-logo-text">Smart Parking</div>
                            <div class="receipt-logo-sub">Official Payment Receipt</div>
                        </div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);">Invoice Number</div>
                    <div style="font-size:.95rem;font-weight:700;color:#fff;">INV-2025-07-8841</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.1rem;">29 July 2025</div>
                </div>
            </div>
        </div>

        <div class="receipt-body">
            <div class="row g-4 align-items-start">

                {{-- Left: invoice details --}}
                <div class="col-12 col-md-7">
                    {{-- Billed to --}}
                    <div class="mb-3">
                        <p style="font-size:.72rem;font-weight:700;color:#8899aa;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">
                            Billed To
                        </p>
                        <p style="font-size:.95rem;font-weight:700;color:#0D1B2A;margin:0;">Riya Sharma</p>
                        <p style="font-size:.8rem;color:#5A6A7A;margin:.1rem 0 0;">riya.sharma@email.com · +91 98100 12345</p>
                        <p style="font-size:.8rem;color:#5A6A7A;margin:.1rem 0 0;">Vehicle: DL-01-AB-1234</p>
                    </div>

                    <hr class="receipt-divider">

                    {{-- Line items --}}
                    <div class="receipt-row">
                        <span style="font-size:.72rem;font-weight:700;color:#8899aa;text-transform:uppercase;">Description</span>
                        <span style="font-size:.72rem;font-weight:700;color:#8899aa;text-transform:uppercase;">Amount</span>
                    </div>
                    @php
                        $receiptItems = [
                            ['Parking Charges — Green Park (B-07)',       '₹ 480.00'],
                            ['GST @ 12%',                                  '₹  57.60'],
                            ['Convenience Fee',                             '₹  18.00'],
                            ['Discount (Weekend Deal)',                     '− ₹ 12.00'],
                            ['Coupon Applied — PARK20',                    '− ₹  7.60'],
                        ];
                    @endphp
                    @foreach ($receiptItems as [$desc, $amount])
                        <div class="receipt-row">
                            <span>{{ $desc }}</span>
                            <span style="{{ str_contains($amount,'−') ? 'color:#1aaa5a;' : '' }}">{{ $amount }}</span>
                        </div>
                    @endforeach

                    <div class="receipt-total-row">
                        <span style="font-size:.92rem;font-weight:700;color:#0F3D56;">Grand Total</span>
                        <span style="font-size:1.2rem;font-weight:700;color:#0F3D56;">₹ 636.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3">
                        <span class="status-pill s-paid"><i class="bi bi-check-circle-fill"></i> PAID</span>
                        <span style="font-size:.78rem;color:#8899aa;">via UPI · TXN-8841 · 29 Jul 2025</span>
                    </div>
                </div>

                {{-- Right: QR + parking details --}}
                <div class="col-12 col-md-5">
                    <div class="d-flex flex-column align-items-center text-center mb-3">
                        {{-- CSS QR Placeholder --}}
                        <div class="receipt-qr mb-2">
                            @php
                                // 7×7 QR pattern (decorative only)
                                $qr = [
                                    [1,1,1,1,1,1,1],
                                    [1,0,0,0,0,0,1],
                                    [1,0,1,0,1,0,1],
                                    [1,0,0,1,0,0,1],
                                    [1,0,1,0,1,0,1],
                                    [1,0,0,0,0,0,1],
                                    [1,1,1,1,1,1,1],
                                ];
                            @endphp
                            <div class="qr-grid">
                                @foreach ($qr as $row)
                                    @foreach ($row as $cell)
                                        <div class="qr-cell {{ $cell ? 'qr-dark' : 'qr-lite' }}"></div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                        <p style="font-size:.7rem;color:#8899aa;margin:0;">Scan to verify receipt</p>
                    </div>

                    <hr class="receipt-divider">

                    <div style="background:#f8f9fa;border-radius:10px;padding:.9rem 1rem;">
                        <p style="font-size:.72rem;font-weight:700;color:#8899aa;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.6rem;">
                            Parking Details
                        </p>
                        @php
                            $pdetails = [
                                ['Location',  'Green Park, New Delhi'],
                                ['Slot',      'B-07 · Floor 1'],
                                ['Check-In',  '29 Jul · 09:00 AM'],
                                ['Check-Out', '29 Jul · 01:00 PM'],
                                ['Duration',  '4 Hours'],
                            ];
                        @endphp
                        @foreach ($pdetails as [$k,$v])
                            <div class="d-flex justify-content-between mb-1">
                                <span style="font-size:.76rem;color:#8899aa;">{{ $k }}</span>
                                <span style="font-size:.78rem;font-weight:600;color:#0D1B2A;">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="btn-dl w-50" style="justify-content:center;">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <a href="#" class="btn-print w-50" style="justify-content:center;"
                           onclick="window.print(); return false;">
                            <i class="bi bi-printer"></i> Print
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>{{-- /receipt card --}}


    {{-- ══════════════════════════════════════════════════════════
         BOTTOM ACTION BAR
    ══════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-4"
         style="background:#fff;border:1px solid #e2e8ee;border-radius:14px;box-shadow:0 2px 12px rgba(15,61,86,.06);">
        <a href="#" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Payments
        </a>
        <div class="d-flex flex-wrap gap-2">
            <a href="#" class="btn-refund"
               data-bs-toggle="modal" data-bs-target="#refundModal">
                <i class="bi bi-arrow-counterclockwise"></i> Refund Payment
            </a>
            <a href="#" class="btn-dl">
                <i class="bi bi-download"></i> Download Receipt
            </a>
            <a href="#" class="btn-print" onclick="window.print(); return false;">
                <i class="bi bi-printer"></i> Print Invoice
            </a>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════
         REFUND MODAL
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="refundModal" tabindex="-1"
         aria-labelledby="refundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
            <div class="modal-content" style="border-radius:14px;border:1px solid #e2e8ee;overflow:hidden;">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="mx-auto d-flex align-items-center justify-content-center mb-3"
                             style="width:56px;height:56px;background:rgba(52,144,220,.1);border-radius:14px;">
                            <i class="bi bi-arrow-counterclockwise" style="font-size:1.4rem;color:#3490dc;"></i>
                        </div>
                        <h6 style="font-weight:700;color:#0D1B2A;margin-bottom:.25rem;">Refund Payment</h6>
                        <p style="font-size:.86rem;color:#5A6A7A;margin:0;">
                            TXN-8841 · Riya Sharma · <strong style="color:#0F3D56;">₹ 636.00</strong>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Refund Type</label>
                        <select class="form-select">
                            <option>Full Refund — ₹ 636.00</option>
                            <option>Partial Refund</option>
                        </select>
                    </div>
                    <div class="mb-3" id="partial-amount-wrap" style="display:none;">
                        <label class="form-label">Refund Amount (₹)</label>
                        <input type="number" class="form-control" placeholder="Enter amount" max="636">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Refund <span style="color:#e74c3c;">*</span></label>
                        <select class="form-select">
                            <option value="">Select reason…</option>
                            <option>Cancelled by customer</option>
                            <option>Duplicate payment</option>
                            <option>Parking unavailable</option>
                            <option>Technical error</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" rows="2"
                            style="border:1px solid #e2e8ee;border-radius:9px;font-size:.855rem;height:auto;"
                            placeholder="Optional note for internal records…"></textarea>
                    </div>
                    <div class="info-strip mb-3" style="font-size:.78rem;">
                        <i class="bi bi-info-circle-fill" style="flex-shrink:0;"></i>
                        Refund will be processed to the original UPI within 5–7 business days.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" data-bs-dismiss="modal"
                            style="flex:1;height:40px;border:1px solid #e2e8ee;border-radius:9px;background:#fff;
                                   color:#5A6A7A;font-size:.875rem;font-weight:600;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="button"
                            style="flex:1;height:40px;border:none;border-radius:9px;background:#3490dc;
                                   color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;">
                            <i class="bi bi-check-lg me-1"></i> Confirm Refund
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    /* ── Show / hide partial amount field ───────────────────── */
    document.querySelector('#refundModal select')?.addEventListener('change', function () {
        const wrap = document.getElementById('partial-amount-wrap');
        if (wrap) wrap.style.display = this.value.includes('Partial') ? '' : 'none';
    });
</script>
@endpush