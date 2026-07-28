{{-- ============================================================
     Pricing & Slots Configuration
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Configure pricing tiers, slot counts, operating
               hours and extra charges for a parking location.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Pricing & Slots')
@section('page-title', 'Pricing & Slots')

@push('styles')
<style>
    /* ── Section card ────────────────────────────────────────── */
    .cfg-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
        margin-bottom: 1.25rem;
    }
    .cfg-card-header {
        display:       flex;
        align-items:   center;
        gap:           .75rem;
        padding:       1rem 1.4rem;
        background:    #fafbfc;
        border-bottom: 1px solid #f0f3f7;
    }
    .cfg-card-icon {
        width:         38px;
        height:        38px;
        border-radius: 10px;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1rem;
        flex-shrink:   0;
    }
    .cfg-card-title {
        font-size:   .95rem;
        font-weight: 700;
        color:       #0D1B2A;
        margin:      0;
    }
    .cfg-card-sub {
        font-size: .77rem;
        color:     #8899aa;
        margin:    0;
    }
    .cfg-card-body {
        padding: 1.4rem;
    }

    /* ── Form controls ───────────────────────────────────────── */
    .form-label {
        font-size:   .79rem;
        font-weight: 600;
        color:       #5A6A7A;
        margin-bottom: .35rem;
    }
    .form-control,
    .form-select {
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        font-size:     .875rem;
        color:         #0D1B2A;
        height:        40px;
        transition:    border-color .18s, box-shadow .18s;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: #0F3D56;
        box-shadow:   0 0 0 3px rgba(15,61,86,.1);
    }
    textarea.form-control { height: auto; }

    /* Input group prefix (₹) */
    .input-group-text {
        background:    #f0f3f7;
        border:        1px solid #e2e8ee;
        border-right:  none;
        border-radius: 9px 0 0 9px;
        font-size:     .82rem;
        font-weight:   600;
        color:         #5A6A7A;
        height:        40px;
        padding:       0 .8rem;
    }
    .input-group .form-control {
        border-left:   none;
        border-radius: 0 9px 9px 0;
    }
    .input-group .form-control:focus {
        box-shadow: none;
        border-color: #0F3D56;
    }
    .input-group:focus-within .input-group-text {
        border-color: #0F3D56;
    }

    /* ── Pricing type segmented control ─────────────────────── */
    .pricing-toggle {
        display:       inline-flex;
        background:    #f0f3f7;
        border-radius: 10px;
        padding:       4px;
        gap:           4px;
    }
    .pricing-toggle input[type="radio"] { display: none; }
    .pricing-toggle label {
        padding:       .4rem 1.25rem;
        border-radius: 7px;
        font-size:     .83rem;
        font-weight:   600;
        color:         #5A6A7A;
        cursor:        pointer;
        transition:    background .18s, color .18s, box-shadow .18s;
        margin:        0;
        user-select:   none;
    }
    .pricing-toggle input[type="radio"]:checked + label {
        background: #0F3D56;
        color:      #fff;
        box-shadow: 0 2px 8px rgba(15,61,86,.25);
    }

    /* ── Slot stat chips ─────────────────────────────────────── */
    .slot-chip {
        border:        1px solid #e2e8ee;
        border-radius: 10px;
        padding:       .85rem 1rem;
        text-align:    center;
        background:    #fafbfc;
        transition:    border-color .18s;
    }
    .slot-chip:focus-within { border-color: #0F3D56; }
    .slot-chip-value {
        font-size:   1.35rem;
        font-weight: 700;
        color:       #0D1B2A;
        display:     block;
        line-height: 1;
        margin-bottom: .3rem;
    }
    .slot-chip-label {
        font-size: .73rem;
        color:     #8899aa;
        font-weight: 600;
    }
    .slot-chip input[type="number"] {
        border:        none;
        background:    transparent;
        font-size:     1.35rem;
        font-weight:   700;
        color:         #0D1B2A;
        text-align:    center;
        width:         100%;
        outline:       none;
        padding:       0;
        height:        auto;
        line-height:   1;
    }
    .slot-chip input[type="number"]::-webkit-inner-spin-button,
    .slot-chip input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }

    /* ── 24x7 toggle ─────────────────────────────────────────── */
    .form-check-input:checked {
        background-color: #0F3D56;
        border-color:     #0F3D56;
    }
    .form-check-input:focus { box-shadow: 0 0 0 3px rgba(15,61,86,.15); }
    .switch-label {
        font-size:  .875rem;
        font-weight: 600;
        color:       #0D1B2A;
    }

    /* ── Weekend / Holiday row ───────────────────────────────── */
    .special-row {
        background:    #fafbfc;
        border:        1px solid #f0f3f7;
        border-radius: 10px;
        padding:       .9rem 1rem;
    }

    /* ── Progress visual for slots ───────────────────────────── */
    .slot-bar-label {
        display:       flex;
        justify-content: space-between;
        font-size:     .78rem;
        margin-bottom: .3rem;
    }
    .slot-bar-label span:first-child { font-weight:600; color:#0D1B2A; }
    .slot-bar-label span:last-child  { color:#8899aa; }
    .progress { height: 7px; border-radius: 6px; background: #f0f3f7; }

    /* ── Hint text ───────────────────────────────────────────── */
    .form-hint {
        font-size: .75rem;
        color:     #8899aa;
        margin-top: .3rem;
    }

    /* ── Action bar ──────────────────────────────────────────── */
    .action-bar {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        padding:       1rem 1.4rem;
        display:       flex;
        align-items:   center;
        justify-content: space-between;
        flex-wrap:     wrap;
        gap:           .75rem;
    }
    .btn-prev {
        height:        40px;
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        background:    #fff;
        color:         #5A6A7A;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.25rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s, border-color .15s;
    }
    .btn-prev:hover { background:#f0f3f7; border-color:#c8d2dc; }

    .btn-draft {
        height:        40px;
        border:        1px solid #0F3D56;
        border-radius: 9px;
        background:    transparent;
        color:         #0F3D56;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.25rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s;
    }
    .btn-draft:hover { background: rgba(15,61,86,.06); }

    .btn-continue {
        height:        40px;
        border:        none;
        border-radius: 9px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .875rem;
        font-weight:   600;
        padding:       0 1.4rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s, box-shadow .15s;
    }
    .btn-continue:hover {
        background:  #0a2f42;
        box-shadow:  0 4px 14px rgba(15,61,86,.3);
    }

    /* ── Step indicator ──────────────────────────────────────── */
    .step-bar {
        display:     flex;
        align-items: center;
        gap:         0;
    }
    .step-item {
        display:     flex;
        align-items: center;
        gap:         .4rem;
    }
    .step-dot {
        width:        28px;
        height:       28px;
        border-radius: 50%;
        display:      flex;
        align-items:  center;
        justify-content: center;
        font-size:    .72rem;
        font-weight:  700;
        flex-shrink:  0;
    }
    .step-dot.done    { background: rgba(46,204,113,.15); color: #1aaa5a; }
    .step-dot.active  { background: #0F3D56; color: #fff; }
    .step-dot.pending { background: #f0f3f7; color: #8899aa; }
    .step-text {
        font-size:  .76rem;
        font-weight: 600;
        color:      #8899aa;
        white-space: nowrap;
    }
    .step-text.active { color: #0D1B2A; }
    .step-line {
        width:      32px;
        height:     1px;
        background: #e2e8ee;
        flex-shrink: 0;
    }

    /* ── Divider label ───────────────────────────────────────── */
    .section-divider {
        display:     flex;
        align-items: center;
        gap:         .75rem;
        margin:      1.25rem 0 1rem;
    }
    .section-divider span {
        font-size:      .72rem;
        font-weight:    700;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .07em;
        white-space:    nowrap;
    }
    .section-divider hr {
        flex:          1;
        border-color:  #f0f3f7;
        margin:        0;
    }

    /* responsive tweaks */
    @media (max-width: 575.98px) {
        .pricing-toggle { flex-direction: column; width: 100%; }
        .pricing-toggle label { text-align: center; }
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading ───────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Pricing &amp; Slots
            </h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Configure pricing tiers, slot availability and operating hours.
            </p>
        </div>

        {{-- Step indicator --}}
        <div class="step-bar d-none d-md-flex">
            <div class="step-item">
                <div class="step-dot done"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
                <span class="step-text">Basic Info</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot done"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
                <span class="step-text">Location</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot active">3</div>
                <span class="step-text active">Pricing &amp; Slots</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot pending">4</div>
                <span class="step-text">Documents</span>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ═══════════════════════════════════════════════════════
             LEFT COLUMN — Pricing + Timings + Extra Charges
        ═══════════════════════════════════════════════════════ --}}
        <div class="col-12 col-xl-7">

            {{-- ── 1. Pricing Configuration ──────────────────── --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(15,61,86,.1);">
                        <i class="bi bi-currency-rupee" style="color:#0F3D56;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Pricing Configuration</p>
                        <p class="cfg-card-sub">Set per-vehicle-type rates and special pricing rules</p>
                    </div>
                </div>
                <div class="cfg-card-body">

                    {{-- Pricing type --}}
                    <div class="mb-4">
                        <label class="form-label d-block">Pricing Type</label>
                        <div class="pricing-toggle">
                            <input type="radio" id="pt-hourly" name="pricing_type" value="hourly" checked>
                            <label for="pt-hourly">
                                <i class="bi bi-clock me-1"></i> Hourly
                            </label>
                            <input type="radio" id="pt-flat" name="pricing_type" value="flat">
                            <label for="pt-flat">
                                <i class="bi bi-tag me-1"></i> Flat Rate
                            </label>
                        </div>
                        <p class="form-hint">Hourly — charged per hour of stay. Flat Rate — fixed charge per session.</p>
                    </div>

                    {{-- Vehicle prices --}}
                    <div class="section-divider">
                        <span>Vehicle Rates</span><hr>
                    </div>

                    <div class="row g-3">
                        {{-- Two Wheeler --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="p-two">
                                <i class="bi bi-bicycle me-1"></i> Two Wheeler
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="p-two" class="form-control"
                                    value="20" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Per hour · Two-wheelers</p>
                        </div>

                        {{-- Four Wheeler --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="p-four">
                                <i class="bi bi-car-front me-1"></i> Four Wheeler
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="p-four" class="form-control"
                                    value="50" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Per hour · Cars &amp; SUVs</p>
                        </div>

                        {{-- Taxi --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="p-taxi">
                                <i class="bi bi-taxi-front me-1"></i> Taxi / Auto
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="p-taxi" class="form-control"
                                    value="40" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Per hour · Commercial taxis</p>
                        </div>

                        {{-- Bus / Truck --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="p-heavy">
                                <i class="bi bi-truck me-1"></i> Bus / Truck
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="p-heavy" class="form-control"
                                    value="120" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Per hour · Heavy vehicles</p>
                        </div>
                    </div>

                    {{-- Special pricing --}}
                    <div class="section-divider mt-4">
                        <span>Special Pricing</span><hr>
                    </div>

                    {{-- Weekend pricing --}}
                    <div class="special-row mb-2">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-week" style="color:#0F3D56;font-size:.95rem;"></i>
                                <span style="font-size:.875rem;font-weight:600;color:#0D1B2A;">Weekend Pricing</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                    id="toggle-weekend" role="switch" checked
                                    onchange="toggleSection('weekend-fields', this)">
                                <label class="form-check-label" for="toggle-weekend"></label>
                            </div>
                        </div>
                        <div id="weekend-fields">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="w-two">Two Wheeler</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="w-two" class="form-control" value="30" min="0">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="w-four">Four Wheeler</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="w-four" class="form-control" value="70" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Holiday pricing --}}
                    <div class="special-row">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-stars" style="color:#f59e0b;font-size:.95rem;"></i>
                                <span style="font-size:.875rem;font-weight:600;color:#0D1B2A;">Holiday Pricing</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                    id="toggle-holiday" role="switch"
                                    onchange="toggleSection('holiday-fields', this)">
                                <label class="form-check-label" for="toggle-holiday"></label>
                            </div>
                        </div>
                        <div id="holiday-fields" style="display:none;">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="h-two">Two Wheeler</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="h-two" class="form-control" value="35" min="0">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="h-four">Four Wheeler</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="h-four" class="form-control" value="80" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p id="holiday-placeholder" class="mb-0 form-hint">
                            Enable to set custom rates for public holidays.
                        </p>
                    </div>

                </div>
            </div>{{-- /pricing card --}}

            {{-- ── 3. Parking Timings ─────────────────────────── --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-clock-history" style="color:#2ECC71;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Parking Timings</p>
                        <p class="cfg-card-sub">Set operating hours for this parking location</p>
                    </div>
                </div>
                <div class="cfg-card-body">

                    {{-- 24×7 toggle --}}
                    <div class="special-row mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="switch-label">Open 24 × 7</span>
                            <p class="form-hint mb-0">Enable for round-the-clock parking access</p>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                id="toggle-247" role="switch"
                                onchange="toggle247(this)">
                            <label class="form-check-label" for="toggle-247"></label>
                        </div>
                    </div>

                    {{-- Time fields --}}
                    <div id="timing-fields">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="t-open">Opening Time</label>
                                <input type="time" id="t-open" class="form-control" value="07:00">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="t-close">Closing Time</label>
                                <input type="time" id="t-close" class="form-control" value="22:00">
                            </div>
                        </div>
                        <p class="form-hint mt-2">Bookings outside these hours will not be accepted.</p>
                    </div>

                    <div id="timing-placeholder" style="display:none;">
                        <div
                            class="text-center py-2"
                            style="background:rgba(46,204,113,.08);border-radius:9px;"
                        >
                            <i class="bi bi-check-circle-fill" style="color:#2ECC71;font-size:1.1rem;"></i>
                            <span style="font-size:.855rem;font-weight:600;color:#1aaa5a;margin-left:.5rem;">
                                This parking is open 24 hours a day, 7 days a week.
                            </span>
                        </div>
                    </div>

                </div>
            </div>{{-- /timings card --}}

            {{-- ── 4. Extra Charges ───────────────────────────── --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(231,76,60,.1);">
                        <i class="bi bi-receipt-cutoff" style="color:#e74c3c;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Extra Charges</p>
                        <p class="cfg-card-sub">One-off fees applied in specific situations</p>
                    </div>
                </div>
                <div class="cfg-card-body">
                    <div class="row g-3">
                        {{-- Lost ticket --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="fee-ticket">
                                <i class="bi bi-ticket-perforated me-1"></i> Lost Ticket Fee
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="fee-ticket" class="form-control"
                                    value="100" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Charged when exit ticket is lost</p>
                        </div>

                        {{-- Overnight --}}
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="fee-overnight">
                                <i class="bi bi-moon-stars me-1"></i> Overnight Parking Fee
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" id="fee-overnight" class="form-control"
                                    value="250" min="0" placeholder="0">
                            </div>
                            <p class="form-hint">Flat fee for overnight stays</p>
                        </div>
                    </div>
                </div>
            </div>{{-- /extra charges --}}

        </div>{{-- /left col --}}


        {{-- ═══════════════════════════════════════════════════════
             RIGHT COLUMN — Slot Configuration
        ═══════════════════════════════════════════════════════ --}}
        <div class="col-12 col-xl-5">

            {{-- ── 2. Slot Configuration ──────────────────────── --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(52,144,220,.12);">
                        <i class="bi bi-grid-3x3-gap-fill" style="color:#3490dc;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Slot Configuration</p>
                        <p class="cfg-card-sub">Define total capacity and specialised bays</p>
                    </div>
                </div>
                <div class="cfg-card-body">

                    {{-- Capacity chips --}}
                    <div class="row g-2 mb-4">
                        @php
                            $slotChips = [
                                ['id'=>'s-total',     'label'=>'Total Slots',     'val'=>200, 'color'=>'#0F3D56'],
                                ['id'=>'s-available', 'label'=>'Available',       'val'=>134, 'color'=>'#2ECC71'],
                                ['id'=>'s-reserved',  'label'=>'Reserved',        'val'=>24,  'color'=>'#f59e0b'],
                                ['id'=>'s-ev',        'label'=>'EV Charging',     'val'=>18,  'color'=>'#3490dc'],
                                ['id'=>'s-disabled',  'label'=>'Disabled Bays',   'val'=>8,   'color'=>'#e74c3c'],
                                ['id'=>'s-other',     'label'=>'Out of Service',  'val'=>16,  'color'=>'#8899aa'],
                            ];
                        @endphp
                        @foreach ($slotChips as $chip)
                            <div class="col-6 col-sm-4 col-xl-6 col-xxl-4">
                                <div class="slot-chip">
                                    <input
                                        type="number"
                                        id="{{ $chip['id'] }}"
                                        value="{{ $chip['val'] }}"
                                        min="0"
                                        aria-label="{{ $chip['label'] }}"
                                        style="color:{{ $chip['color'] }};"
                                    >
                                    <span class="slot-chip-label">{{ $chip['label'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Utilisation breakdown --}}
                    <div class="section-divider">
                        <span>Utilisation</span><hr>
                    </div>

                    @php
                        $bars = [
                            ['label'=>'Occupied',     'pct'=>67, 'color'=>'#0F3D56'],
                            ['label'=>'Available',    'pct'=>67, 'color'=>'#2ECC71'],
                            ['label'=>'Reserved',     'pct'=>12, 'color'=>'#f59e0b'],
                            ['label'=>'EV Charging',  'pct'=>9,  'color'=>'#3490dc'],
                            ['label'=>'Disabled',     'pct'=>4,  'color'=>'#e74c3c'],
                        ];
                    @endphp
                    @foreach ($bars as $bar)
                        <div class="slot-bar-label">
                            <span>{{ $bar['label'] }}</span>
                            <span>{{ $bar['pct'] }}%</span>
                        </div>
                        <div class="progress mb-3">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width:{{ $bar['pct'] }}%; background:{{ $bar['color'] }}; border-radius:6px;"
                                aria-valuenow="{{ $bar['pct'] }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    @endforeach

                    {{-- Slot layout selector --}}
                    <div class="section-divider mt-1">
                        <span>Layout</span><hr>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="s-floors">Number of Floors</label>
                            <input type="number" id="s-floors" class="form-control" value="2" min="1">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="s-zones">Number of Zones</label>
                            <select id="s-zones" class="form-select">
                                <option>1 Zone</option>
                                <option selected>2 Zones</option>
                                <option>3 Zones</option>
                                <option>4 Zones</option>
                            </select>
                        </div>
                    </div>

                    {{-- Sample mini slot map --}}
                    <p style="font-size:.75rem;font-weight:600;color:#8899aa;text-transform:uppercase;letter-spacing:.05em;" class="mb-2">
                        Preview — Zone A / Floor 1
                    </p>
                    @php
                        $previewSlots = [
                            'A1'=>'occupied','A2'=>'available','A3'=>'occupied','A4'=>'reserved',
                            'B1'=>'ev',      'B2'=>'occupied', 'B3'=>'available','B4'=>'occupied',
                            'C1'=>'occupied','C2'=>'disabled', 'C3'=>'occupied', 'C4'=>'available',
                            'D1'=>'available','D2'=>'occupied','D3'=>'ev',       'D4'=>'occupied',
                        ];
                        $slotStyles = [
                            'occupied'  => ['bg'=>'rgba(15,61,86,.1)',   'color'=>'#0F3D56',  'label'=>'Occupied'],
                            'available' => ['bg'=>'rgba(46,204,113,.14)','color'=>'#1aaa5a',  'label'=>'Free'],
                            'reserved'  => ['bg'=>'rgba(245,158,11,.14)','color'=>'#c47d00',  'label'=>'Reserved'],
                            'ev'        => ['bg'=>'rgba(52,144,220,.13)','color'=>'#3490dc',  'label'=>'EV'],
                            'disabled'  => ['bg'=>'rgba(231,76,60,.12)', 'color'=>'#c0392b',  'label'=>'Disabled'],
                        ];
                    @endphp
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;" class="mb-3">
                        @foreach ($previewSlots as $id => $type)
                            @php $st = $slotStyles[$type]; @endphp
                            <div
                                style="
                                    background:{{ $st['bg'] }};
                                    color:{{ $st['color'] }};
                                    border-radius:8px;
                                    padding:.5rem .3rem;
                                    text-align:center;
                                    font-size:.7rem;
                                    font-weight:700;
                                "
                            >
                                {{ $id }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($slotStyles as $type => $st)
                            <span style="font-size:.7rem;color:#5A6A7A;display:flex;align-items:center;gap:4px;">
                                <span style="display:inline-block;width:9px;height:9px;border-radius:3px;background:{{ $st['color'] }};"></span>
                                {{ $st['label'] }}
                            </span>
                        @endforeach
                    </div>

                </div>
            </div>{{-- /slot card --}}

            {{-- ── Quick Summary card ─────────────────────────── --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(15,61,86,.08);">
                        <i class="bi bi-lightning-charge-fill" style="color:#0F3D56;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Quick Summary</p>
                        <p class="cfg-card-sub">Effective pricing at a glance</p>
                    </div>
                </div>
                <div class="cfg-card-body p-0">
                    @php
                        $summary = [
                            ['icon'=>'bi-bicycle',          'label'=>'Two Wheeler',   'std'=>'₹ 20/hr', 'wknd'=>'₹ 30/hr'],
                            ['icon'=>'bi-car-front',         'label'=>'Four Wheeler',  'std'=>'₹ 50/hr', 'wknd'=>'₹ 70/hr'],
                            ['icon'=>'bi-taxi-front',        'label'=>'Taxi / Auto',   'std'=>'₹ 40/hr', 'wknd'=>'₹ 40/hr'],
                            ['icon'=>'bi-truck',             'label'=>'Bus / Truck',   'std'=>'₹ 120/hr','wknd'=>'₹ 120/hr'],
                        ];
                    @endphp
                    <table class="table mb-0" style="font-size:.82rem;">
                        <thead>
                            <tr style="background:#fafbfc;">
                                <th style="padding:.65rem 1rem;color:#8899aa;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #f0f3f7;">Vehicle</th>
                                <th style="padding:.65rem 1rem;color:#8899aa;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #f0f3f7;">Standard</th>
                                <th style="padding:.65rem 1rem;color:#8899aa;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #f0f3f7;">Weekend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary as $i => $row)
                                <tr style="{{ $i === count($summary)-1 ? '' : 'border-bottom:1px solid #f5f7f9;' }}">
                                    <td style="padding:.7rem 1rem;vertical-align:middle;">
                                        <i class="bi {{ $row['icon'] }} me-2" style="color:#0F3D56;"></i>
                                        <span style="font-weight:500;color:#0D1B2A;">{{ $row['label'] }}</span>
                                    </td>
                                    <td style="padding:.7rem 1rem;font-weight:600;color:#0D1B2A;">{{ $row['std'] }}</td>
                                    <td style="padding:.7rem 1rem;">
                                        <span style="color:#1aaa5a;font-weight:600;">{{ $row['wknd'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /right col --}}
    </div>{{-- /row --}}


    {{-- ══════════════════════════════════════════════════════════
         Action Bar — Previous · Save Draft · Save & Continue
    ══════════════════════════════════════════════════════════ --}}
    <div class="action-bar mt-1">
        {{-- Left --}}
        <a href="#" class="btn-prev">
            <i class="bi bi-arrow-left"></i> Previous
        </a>

        {{-- Right --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="#" class="btn-draft">
                <i class="bi bi-floppy"></i> Save Draft
            </a>
            <a href="#" class="btn-continue">
                Save &amp; Continue <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    /* ── Weekend / holiday section toggle ──────────────────────── */
    function toggleSection(fieldId, checkbox) {
        const fields      = document.getElementById(fieldId);
        const placeholder = document.getElementById(
            fieldId === 'weekend-fields' ? null : 'holiday-placeholder'
        );

        if (fields)      fields.style.display      = checkbox.checked ? '' : 'none';
        if (placeholder) placeholder.style.display = checkbox.checked ? 'none' : '';
    }

    /* Initialise holiday toggle state */
    (function () {
        const hToggle = document.getElementById('toggle-holiday');
        if (hToggle) toggleSection('holiday-fields', hToggle);
    })();

    /* ── 24×7 timing toggle ─────────────────────────────────────── */
    function toggle247(checkbox) {
        const fields      = document.getElementById('timing-fields');
        const placeholder = document.getElementById('timing-placeholder');
        if (fields)      fields.style.display      = checkbox.checked ? 'none' : '';
        if (placeholder) placeholder.style.display = checkbox.checked ? '' : 'none';
    }
</script>
@endpush