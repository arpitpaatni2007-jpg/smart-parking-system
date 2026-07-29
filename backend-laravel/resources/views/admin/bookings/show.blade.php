{{-- ============================================================
     Booking Management — Show / Detail
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Full detail view for a single booking.
               Sections: Booking Info · Customer · Parking ·
               Time · Payment · Timeline · Actions
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@push('styles')
<style>
    /* ── Card shell ──────────────────────────────────────────── */
    .detail-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }
    .detail-card + .detail-card { margin-top: 1.25rem; }

    .detail-card-header {
        display:       flex;
        align-items:   center;
        gap:           .65rem;
        padding:       .9rem 1.25rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
    }
    .detail-card-header .hd-icon {
        width:           34px;
        height:          34px;
        border-radius:   9px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .95rem;
        flex-shrink:     0;
    }
    .detail-card-header h6 {
        margin:      0;
        font-size:   .875rem;
        font-weight: 700;
        color:       #0D1B2A;
        line-height: 1;
    }

    /* ── Info rows ───────────────────────────────────────────── */
    .info-row {
        display:       flex;
        align-items:   flex-start;
        gap:           .75rem;
        padding:       .75rem 1.25rem;
        border-bottom: 1px solid #f5f7f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row-label {
        font-size:      .74rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .04em;
        min-width:      120px;
        flex-shrink:    0;
        padding-top:    .15rem;
    }
    .info-row-value {
        font-size:   .875rem;
        font-weight: 500;
        color:       #0D1B2A;
        line-height: 1.5;
        word-break:  break-word;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .status-badge {
        display:        inline-flex;
        align-items:    center;
        gap:            .3rem;
        padding:        .28em .8em;
        border-radius:  20px;
        font-size:      .73rem;
        font-weight:    600;
        letter-spacing: .02em;
        white-space:    nowrap;
    }
    .dot { width:7px; height:7px; border-radius:50%; display:inline-block; flex-shrink:0; }
    .badge-completed { background:rgba(15,61,86,.1);    color:#0F3D56; }
    .badge-paid      { background:rgba(46,204,113,.14); color:#1aaa5a; }
    .badge-confirmed { background:rgba(46,204,113,.14); color:#1aaa5a; }
    .badge-active    { background:rgba(2,136,209,.12);  color:#0277bd; }
    .badge-cancelled { background:rgba(231,76,60,.12);  color:#c0392b; }
    .badge-pending   { background:rgba(245,158,11,.14); color:#b45309; }
    .badge-refunded  { background:rgba(143,163,180,.15);color:#5A6A7A; }

    /* ── Customer avatar ─────────────────────────────────────── */
    .customer-avatar {
        width:           56px;
        height:          56px;
        border-radius:   12px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1.4rem;
        font-weight:     700;
        color:           #fff;
        flex-shrink:     0;
    }

    /* ── Vehicle chip ────────────────────────────────────────── */
    .vehicle-chip {
        display:     inline-flex;
        align-items: center;
        gap:         .35rem;
        padding:     .28em .75em;
        background:  #f0f3f7;
        border:      1px solid #e2e8ee;
        border-radius:8px;
        font-size:   .8rem;
        font-weight: 600;
        color:       #0D1B2A;
    }

    /* ── Time block ──────────────────────────────────────────── */
    .time-block {
        display:         flex;
        flex-direction:  column;
        align-items:     center;
        justify-content: center;
        padding:         .9rem 1rem;
        background:      #f8f9fa;
        border-radius:   12px;
        border:          1px solid #f0f3f7;
        text-align:      center;
        flex:            1;
        min-width:       110px;
    }
    .time-block .time-label {
        font-size:      .69rem;
        font-weight:    700;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom:  .35rem;
    }
    .time-block .time-icon-wrap {
        width:           38px;
        height:          38px;
        border-radius:   9px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        margin-bottom:   .45rem;
        font-size:       1rem;
    }
    .time-block .time-val  { font-size:.88rem; font-weight:700; color:#0D1B2A; line-height:1.3; }
    .time-block .time-date { font-size:.73rem; color:#8899aa; margin-top:.15rem; }

    .time-connector {
        display:         flex;
        flex-direction:  column;
        align-items:     center;
        justify-content: center;
        padding:         0 .35rem;
        gap:             .2rem;
        flex-shrink:     0;
    }
    .time-connector .tc-line  { width:2px; height:16px; background:#e2e8ee; border-radius:1px; }
    .time-connector .tc-badge {
        background:   #0F3D56;
        color:        #fff;
        font-size:    .68rem;
        font-weight:  700;
        padding:      .22em .6em;
        border-radius:20px;
        white-space:  nowrap;
    }

    /* ── Payment summary ─────────────────────────────────────── */
    .pay-row {
        display:         flex;
        align-items:     center;
        justify-content: space-between;
        padding:         .65rem 1.25rem;
        border-bottom:   1px solid #f5f7f9;
        font-size:       .875rem;
    }
    .pay-row:last-child        { border-bottom: none; }
    .pay-row .pay-label        { color:#5A6A7A; font-weight:500; }
    .pay-row .pay-val          { font-weight:600; color:#0D1B2A; }
    .pay-row.pay-total         { background:#fafbfc; border-top:2px solid #e2e8ee; padding:.85rem 1.25rem; }
    .pay-row.pay-total .pay-label { font-weight:700; color:#0D1B2A; font-size:.9rem; }
    .pay-row.pay-total .pay-val   { font-size:1.05rem; font-weight:800; color:#0F3D56; }
    .pay-row.pay-discount .pay-val{ color:#1aaa5a; }

    /* ── Timeline ────────────────────────────────────────────── */
    .timeline { padding: 1rem 1.25rem; }
    .tl-item {
        display:        flex;
        align-items:    flex-start;
        gap:            .85rem;
        padding-bottom: 1.1rem;
        position:       relative;
    }
    .tl-item:last-child { padding-bottom:0; }
    .tl-dot-wrap {
        display:        flex;
        flex-direction: column;
        align-items:    center;
        flex-shrink:    0;
        width:          34px;
    }
    .tl-dot {
        width:           34px;
        height:          34px;
        border-radius:   50%;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .85rem;
        flex-shrink:     0;
    }
    .tl-line { width:2px; flex:1; min-height:18px; margin-top:3px; border-left:2px dashed #e2e8ee; }
    .tl-content { flex:1; min-width:0; padding-top:.35rem; }
    .tl-title   { font-size:.855rem; font-weight:700; color:#0D1B2A; margin-bottom:.1rem; }
    .tl-sub     { font-size:.76rem;  color:#8899aa; line-height:1.4; }
    .tl-time    { font-size:.72rem;  color:#8899aa; white-space:nowrap; padding-top:.4rem; flex-shrink:0; }

    /* ── Booking ID header band ──────────────────────────────── */
    .booking-band {
        background:    linear-gradient(135deg,#0F3D56 0%,#1A5E80 100%);
        border-radius: 14px;
        padding:       1.1rem 1.4rem;
        box-shadow:    0 4px 18px rgba(15,61,86,.16);
        margin-bottom: 1.25rem;
    }

    /* ── Top action buttons ──────────────────────────────────── */
    .btn-back {
        height:          36px;
        padding:         0 1rem;
        border:          1px solid #e2e8ee;
        border-radius:   8px;
        background:      #fff;
        color:           #0D1B2A;
        font-size:       .835rem;
        font-weight:     600;
        display:         inline-flex;
        align-items:     center;
        gap:             .35rem;
        text-decoration: none;
        white-space:     nowrap;
        transition:      background .15s;
    }
    .btn-back:hover { background:#f0f3f7; color:#0D1B2A; }

    .btn-edit {
        height:          36px;
        padding:         0 1rem;
        border:          1px solid #3490dc;
        border-radius:   8px;
        background:      rgba(52,144,220,.07);
        color:           #3490dc;
        font-size:       .835rem;
        font-weight:     600;
        display:         inline-flex;
        align-items:     center;
        gap:             .35rem;
        text-decoration: none;
        white-space:     nowrap;
        transition:      background .15s, color .15s;
    }
    .btn-edit:hover { background:#3490dc; color:#fff; }

    .btn-cancel-booking {
        height:      36px;
        padding:     0 1rem;
        border:      1px solid #e74c3c;
        border-radius:8px;
        background:  rgba(231,76,60,.07);
        color:       #e74c3c;
        font-size:   .835rem;
        font-weight: 600;
        display:     inline-flex;
        align-items: center;
        gap:         .35rem;
        white-space: nowrap;
        cursor:      pointer;
        transition:  background .15s, color .15s;
    }
    .btn-cancel-booking:hover { background:#e74c3c; color:#fff; }

    .btn-print {
        height:      36px;
        padding:     0 1rem;
        border:      none;
        border-radius:8px;
        background:  #0F3D56;
        color:       #fff;
        font-size:   .835rem;
        font-weight: 600;
        display:     inline-flex;
        align-items: center;
        gap:         .35rem;
        white-space: nowrap;
        cursor:      pointer;
        transition:  background .15s;
    }
    .btn-print:hover { background:#0a2f42; }

    /* ── Monospace ───────────────────────────────────────────── */
    .mono { font-family:monospace; letter-spacing:.04em; font-size:.83rem; }

    /* ── Utility ─────────────────────────────────────────────── */
    .section-divider {
        border:     none;
        border-top: 1px solid #f0f3f7;
        margin:     0;
    }
</style>
@endpush

@section('content')

    @php
        $booking = [
            'id'              => 'BK-20250003',
            'date'            => '15 Jul 2025, 10:58 AM',
            'booking_status'  => 'completed',
            'payment_status'  => 'paid',
            'booking_type'    => 'Online (App)',

            'customer_name'   => 'Rohit Verma',
            'customer_initial'=> 'R',
            'customer_color'  => '27ae60',
            'email'           => 'rohit.verma@gmail.com',
            'phone'           => '+91 98765 43210',
            'vehicle_no'      => 'UP32GH5678',
            'vehicle_type'    => 'SUV',

            'parking_name'    => 'Connaught Place Parking',
            'owner'           => 'Vikram Joshi (PO-001)',
            'address'         => 'Block D, Connaught Place, New Delhi, Delhi - 110001',
            'slot_number'     => 'C-14',
            'floor'           => 'Ground Floor (G)',

            'entry_time'      => '11:00 AM',
            'entry_date'      => '15 Jul 2025',
            'exit_time'       => '02:00 PM',
            'exit_date'       => '15 Jul 2025',
            'duration'        => '3 hours',

            'amount'          => '₹300.00',
            'gst'             => '₹15.00',
            'discount'        => '-₹20.00',
            'total_paid'      => '₹295.00',
            'payment_method'  => 'UPI (Google Pay)',
            'transaction_id'  => 'TXN20250715GPC03812',

            'timeline' => [
                [
                    'title' => 'Booking Created',
                    'sub'   => 'User placed a booking via the Smart Parking app.',
                    'time'  => '15 Jul, 10:58 AM',
                    'icon'  => 'bi-calendar-plus',
                    'color' => '#0F3D56',
                    'bg'    => 'rgba(15,61,86,.1)',
                ],
                [
                    'title' => 'Payment Completed',
                    'sub'   => 'Payment of ₹295.00 received via UPI (Google Pay).',
                    'time'  => '15 Jul, 11:00 AM',
                    'icon'  => 'bi-credit-card',
                    'color' => '#1aaa5a',
                    'bg'    => 'rgba(46,204,113,.14)',
                ],
                [
                    'title' => 'Vehicle Entered',
                    'sub'   => 'QR code scanned at Connaught Place Parking entrance.',
                    'time'  => '15 Jul, 11:00 AM',
                    'icon'  => 'bi-arrow-right-circle',
                    'color' => '#0277bd',
                    'bg'    => 'rgba(2,136,209,.12)',
                ],
                [
                    'title' => 'Vehicle Exited',
                    'sub'   => 'Vehicle checked out. Slot C-14 released.',
                    'time'  => '15 Jul, 02:00 PM',
                    'icon'  => 'bi-arrow-left-circle',
                    'color' => '#b45309',
                    'bg'    => 'rgba(245,158,11,.14)',
                ],
            ],
        ];
    @endphp

    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER — title + breadcrumb + action buttons
         All in one row, no extra spacing above or below.
    ══════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">

        {{-- Left: title + breadcrumb --}}
        <div style="min-width:0;">
            <h4 class="mb-1" style="font-weight:700; color:#0D1B2A; line-height:1.2;">
                Booking Details
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Bookings</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">
                        {{ $booking['id'] }}
                    </li>
                </ol>
            </nav>
        </div>

        {{-- Right: action buttons — single horizontal row --}}
        <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
            <a href="#" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <a href="#" class="btn-edit">
                <i class="bi bi-pencil"></i> Edit Booking
            </a>
            <button
                type="button"
                class="btn-cancel-booking"
                data-bs-toggle="modal"
                data-bs-target="#cancelModal"
            >
                <i class="bi bi-x-circle"></i> Cancel Booking
            </button>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         BOOKING ID HEADER BAND
    ══════════════════════════════════════════════════════════ --}}
    <div class="booking-band">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

            {{-- Left: icon + booking id --}}
            <div class="d-flex align-items-center gap-3">
                <div style="
                    width:46px; height:46px; border-radius:11px;
                    background:rgba(255,255,255,.15);
                    display:flex; align-items:center; justify-content:center;
                    font-size:1.3rem; color:#fff; flex-shrink:0;
                ">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div>
                    <div style="color:rgba(255,255,255,.6); font-size:.72rem; font-weight:600; letter-spacing:.05em; text-transform:uppercase;">
                        Booking ID
                    </div>
                    <div style="color:#fff; font-size:1.2rem; font-weight:800; font-family:monospace; letter-spacing:.06em; line-height:1.25;">
                        {{ $booking['id'] }}
                    </div>
                    <div style="color:rgba(255,255,255,.55); font-size:.76rem; margin-top:.15rem;">
                        {{ $booking['date'] }}
                    </div>
                </div>
            </div>

            {{-- Right: status chips --}}
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="text-center">
                    <div style="color:rgba(255,255,255,.5); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3rem;">
                        Booking Status
                    </div>
                    <span class="status-badge badge-{{ $booking['booking_status'] }}">
                        <span class="dot" style="background:#0F3D56;"></span>
                        {{ ucfirst($booking['booking_status']) }}
                    </span>
                </div>
                <div style="width:1px; height:32px; background:rgba(255,255,255,.18);"></div>
                <div class="text-center">
                    <div style="color:rgba(255,255,255,.5); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3rem;">
                        Payment Status
                    </div>
                    <span class="status-badge badge-{{ $booking['payment_status'] }}">
                        <span class="dot" style="background:#1aaa5a;"></span>
                        {{ ucfirst($booking['payment_status']) }}
                    </span>
                </div>
                <div style="width:1px; height:32px; background:rgba(255,255,255,.18);"></div>
                <div class="text-center">
                    <div style="color:rgba(255,255,255,.5); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3rem;">
                        Booking Type
                    </div>
                    <div style="color:#fff; font-size:.82rem; font-weight:600;">
                        {{ $booking['booking_type'] }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TWO-COLUMN MAIN CONTENT
         Left (col-xl-8): Customer · Parking · Time
         Right (col-xl-4): Payment · Booking Info · Timeline
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 align-items-start">

        {{-- ─────────────────────────────────────────────────────
             LEFT COLUMN
        ───────────────────────────────────────────────────────── --}}
        <div class="col-12 col-xl-8 d-flex flex-column gap-3">

            {{-- CUSTOMER DETAILS ─────────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-person-circle" style="color:#0F3D56;"></i>
                    </div>
                    <h6>Customer Details</h6>
                </div>

                {{-- Avatar + contact block --}}
                <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid #f5f7f9;">
                    <div
                        class="customer-avatar"
                        style="background:#{{ $booking['customer_color'] }};"
                    >
                        {{ $booking['customer_initial'] }}
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:.97rem; font-weight:700; color:#0D1B2A;">
                            {{ $booking['customer_name'] }}
                        </div>
                        <div style="font-size:.79rem; color:#8899aa; margin-top:.2rem;">
                            <i class="bi bi-envelope me-1"></i>{{ $booking['email'] }}
                        </div>
                        <div style="font-size:.79rem; color:#8899aa; margin-top:.1rem;">
                            <i class="bi bi-telephone me-1"></i>{{ $booking['phone'] }}
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-row-label">Vehicle Number</div>
                    <div class="info-row-value">
                        <span class="mono" style="font-weight:700; color:#0F3D56; font-size:.88rem;">
                            {{ $booking['vehicle_no'] }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Vehicle Type</div>
                    <div class="info-row-value">
                        <span class="vehicle-chip">
                            <i class="bi bi-truck" style="color:#0F3D56;"></i>
                            {{ $booking['vehicle_type'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- PARKING DETAILS ──────────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-signpost-2" style="color:#1aaa5a;"></i>
                    </div>
                    <h6>Parking Details</h6>
                </div>

                <div class="info-row">
                    <div class="info-row-label">Parking Name</div>
                    <div class="info-row-value" style="font-weight:700;">
                        {{ $booking['parking_name'] }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Owner</div>
                    <div class="info-row-value">{{ $booking['owner'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Address</div>
                    <div class="info-row-value" style="color:#5A6A7A;">
                        <i class="bi bi-geo-alt me-1" style="color:#8899aa;"></i>
                        {{ $booking['address'] }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Slot Number</div>
                    <div class="info-row-value">
                        <span style="
                            display:inline-flex; align-items:center; gap:.35rem;
                            background:rgba(15,61,86,.08); color:#0F3D56;
                            font-weight:700; font-size:.85rem;
                            padding:.26em .75em; border-radius:8px;
                            font-family:monospace; letter-spacing:.06em;
                        ">
                            <i class="bi bi-grid-3x3-gap" style="font-size:.78rem;"></i>
                            {{ $booking['slot_number'] }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Floor</div>
                    <div class="info-row-value">{{ $booking['floor'] }}</div>
                </div>
            </div>

            {{-- TIME DETAILS ─────────────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(2,136,209,.1);">
                        <i class="bi bi-clock" style="color:#0277bd;"></i>
                    </div>
                    <h6>Time Details</h6>
                </div>

                <div class="p-3">
                    <div class="d-flex align-items-stretch gap-2 flex-wrap">

                        {{-- Entry --}}
                        <div class="time-block">
                            <div class="time-label">Entry Time</div>
                            <div class="time-icon-wrap" style="background:rgba(46,204,113,.14);">
                                <i class="bi bi-arrow-right-circle" style="color:#1aaa5a;"></i>
                            </div>
                            <div class="time-val">{{ $booking['entry_time'] }}</div>
                            <div class="time-date">{{ $booking['entry_date'] }}</div>
                        </div>

                        {{-- Connector --}}
                        <div class="time-connector">
                            <div class="tc-line"></div>
                            <div class="tc-badge">{{ $booking['duration'] }}</div>
                            <div class="tc-line"></div>
                        </div>

                        {{-- Exit --}}
                        <div class="time-block">
                            <div class="time-label">Exit Time</div>
                            <div class="time-icon-wrap" style="background:rgba(231,76,60,.1);">
                                <i class="bi bi-arrow-left-circle" style="color:#e74c3c;"></i>
                            </div>
                            <div class="time-val">{{ $booking['exit_time'] }}</div>
                            <div class="time-date">{{ $booking['exit_date'] }}</div>
                        </div>

                        {{-- Duration --}}
                        <div class="time-block">
                            <div class="time-label">Duration</div>
                            <div class="time-icon-wrap" style="background:rgba(15,61,86,.1);">
                                <i class="bi bi-hourglass-split" style="color:#0F3D56;"></i>
                            </div>
                            <div class="time-val">{{ $booking['duration'] }}</div>
                            <div class="time-date">Total Parked</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>{{-- /left column --}}

        {{-- ─────────────────────────────────────────────────────
             RIGHT COLUMN
        ───────────────────────────────────────────────────────── --}}
        <div class="col-12 col-xl-4 d-flex flex-column gap-3">

            {{-- PAYMENT DETAILS ──────────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(245,158,11,.12);">
                        <i class="bi bi-currency-rupee" style="color:#b45309;"></i>
                    </div>
                    <h6>Payment Details</h6>
                </div>

                <div class="pay-row">
                    <span class="pay-label">Parking Charge</span>
                    <span class="pay-val">{{ $booking['amount'] }}</span>
                </div>
                <div class="pay-row">
                    <span class="pay-label">GST (5%)</span>
                    <span class="pay-val">{{ $booking['gst'] }}</span>
                </div>
                <div class="pay-row pay-discount">
                    <span class="pay-label">
                        <i class="bi bi-tag me-1" style="font-size:.78rem; color:#1aaa5a;"></i>
                        Promo Discount
                    </span>
                    <span class="pay-val">{{ $booking['discount'] }}</span>
                </div>
                <div class="pay-row pay-total">
                    <span class="pay-label">
                        <i class="bi bi-check-circle me-1" style="color:#1aaa5a;"></i>
                        Total Paid
                    </span>
                    <span class="pay-val">{{ $booking['total_paid'] }}</span>
                </div>

                {{-- Method + Transaction --}}
                <div class="px-3 py-3" style="background:#fafbfc; border-top:1px solid #f0f3f7;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="
                            width:28px; height:28px; border-radius:7px;
                            background:rgba(15,61,86,.1);
                            display:flex; align-items:center; justify-content:center;
                            flex-shrink:0;
                        ">
                            <i class="bi bi-phone" style="font-size:.8rem; color:#0F3D56;"></i>
                        </div>
                        <div>
                            <div style="font-size:.68rem; color:#8899aa; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                                Payment Method
                            </div>
                            <div style="font-size:.84rem; font-weight:700; color:#0D1B2A;">
                                {{ $booking['payment_method'] }}
                            </div>
                        </div>
                    </div>
                    <div style="font-size:.68rem; color:#8899aa; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.25rem;">
                        Transaction ID
                    </div>
                    <div class="mono" style="font-size:.78rem; color:#5A6A7A; word-break:break-all;">
                        {{ $booking['transaction_id'] }}
                    </div>
                </div>
            </div>

            {{-- BOOKING INFORMATION ───────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(143,163,180,.14);">
                        <i class="bi bi-info-circle" style="color:#5A6A7A;"></i>
                    </div>
                    <h6>Booking Information</h6>
                </div>

                <div class="info-row">
                    <div class="info-row-label">Booking ID</div>
                    <div class="info-row-value mono" style="color:#0F3D56; font-weight:700;">
                        {{ $booking['id'] }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Booking Date</div>
                    <div class="info-row-value">{{ $booking['date'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Status</div>
                    <div class="info-row-value">
                        <span class="status-badge badge-{{ $booking['booking_status'] }}">
                            <span class="dot" style="background:#0F3D56;"></span>
                            {{ ucfirst($booking['booking_status']) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Payment</div>
                    <div class="info-row-value">
                        <span class="status-badge badge-{{ $booking['payment_status'] }}">
                            <span class="dot" style="background:#1aaa5a;"></span>
                            {{ ucfirst($booking['payment_status']) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-label">Type</div>
                    <div class="info-row-value">{{ $booking['booking_type'] }}</div>
                </div>
            </div>

            {{-- BOOKING TIMELINE ─────────────────────────────── --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="hd-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-activity" style="color:#0F3D56;"></i>
                    </div>
                    <h6>Booking Timeline</h6>
                </div>

                <div class="timeline">
                    @foreach ($booking['timeline'] as $event)
                        <div class="tl-item">
                            <div class="tl-dot-wrap">
                                <div class="tl-dot" style="background:{{ $event['bg'] }};">
                                    <i class="bi {{ $event['icon'] }}" style="color:{{ $event['color'] }};"></i>
                                </div>
                                @if (!$loop->last)
                                    <div class="tl-line"></div>
                                @endif
                            </div>
                            <div class="tl-content">
                                <div class="tl-title">{{ $event['title'] }}</div>
                                <div class="tl-sub">{{ $event['sub'] }}</div>
                            </div>
                            <div class="tl-time">{{ $event['time'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>{{-- /right column --}}

    </div>{{-- /row --}}

    {{-- ══════════════════════════════════════════════════════════
         CANCEL CONFIRMATION MODAL
    ══════════════════════════════════════════════════════════ --}}
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

                    <div
                        class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:56px; height:56px; background:rgba(231,76,60,.1); border-radius:14px;"
                    >
                        <i class="bi bi-calendar-x" style="font-size:1.4rem; color:#e74c3c;"></i>
                    </div>

                    <h6 class="mb-1" style="font-weight:700; color:#0D1B2A;">Cancel Booking?</h6>
                    <p style="font-size:.855rem; color:#5A6A7A; margin-bottom:.35rem;">
                        You are about to cancel booking
                    </p>
                    <p class="mb-2">
                        <strong style="color:#0F3D56; font-family:monospace;">
                            {{ $booking['id'] }}
                        </strong>
                        for <strong>{{ $booking['customer_name'] }}</strong>.
                    </p>
                    <p style="font-size:.8rem; color:#e74c3c; margin-bottom:1.25rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This action cannot be undone and a cancellation notification will be sent.
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