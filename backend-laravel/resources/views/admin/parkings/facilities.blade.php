{{-- ============================================================
     Add Parking — Step 4: Facilities & Services
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Select parking facilities, rules and additional
               services as part of the multi-step add-parking
               wizard. Step 4 is active.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Facilities & Services')
@section('page-title', 'Facilities & Services')

@push('styles')
<style>
    /* ── Section card shell ─────────────────────────────────── */
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
    .cfg-card-body { padding: 1.4rem; }

    /* ── Selectable facility card ────────────────────────────── */
    .fac-item {
        position: relative;
    }
    /* hide native checkbox */
    .fac-item input[type="checkbox"] {
        position: absolute;
        opacity:  0;
        width:    0;
        height:   0;
    }
    .fac-card {
        display:        flex;
        flex-direction: column;
        align-items:    center;
        justify-content: center;
        gap:            .5rem;
        padding:        1rem .75rem .85rem;
        border:         1.5px solid #e2e8ee;
        border-radius:  12px;
        cursor:         pointer;
        background:     #fafbfc;
        text-align:     center;
        transition:     border-color .18s, background .18s, box-shadow .18s;
        user-select:    none;
        min-height:     100px;
    }
    .fac-card:hover {
        border-color: #0F3D56;
        background:   #fff;
    }
    .fac-item input[type="checkbox"]:checked + .fac-card {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.05);
        box-shadow:   0 0 0 3px rgba(15,61,86,.1);
    }
    .fac-item input[type="checkbox"]:checked + .fac-card .fac-icon {
        background: #0F3D56;
        color:      #fff;
    }
    .fac-item input[type="checkbox"]:checked + .fac-card .fac-check {
        opacity:    1;
        background: #0F3D56;
        color:      #fff;
    }
    .fac-item input[type="checkbox"]:checked + .fac-card .fac-label {
        color: #0F3D56;
        font-weight: 700;
    }
    .fac-icon {
        width:         42px;
        height:        42px;
        border-radius: 11px;
        background:    rgba(15,61,86,.08);
        color:         #0F3D56;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1.15rem;
        transition:    background .18s, color .18s;
        flex-shrink:   0;
    }
    .fac-label {
        font-size:   .77rem;
        font-weight: 600;
        color:       #5A6A7A;
        line-height: 1.25;
        transition:  color .18s;
    }
    /* tick badge */
    .fac-check {
        position:      absolute;
        top:           8px;
        right:         8px;
        width:         20px;
        height:        20px;
        border-radius: 50%;
        border:        1.5px solid #e2e8ee;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .65rem;
        opacity:       0;
        transition:    opacity .18s, background .18s, border-color .18s;
        background:    #fff;
        color:         transparent;
    }

    /* ── Rule / service toggle row ───────────────────────────── */
    .toggle-row {
        display:         flex;
        align-items:     center;
        justify-content: space-between;
        padding:         .85rem 0;
        border-bottom:   1px solid #f5f7f9;
        gap:             1rem;
    }
    .toggle-row:last-child { border-bottom: none; }
    .toggle-label {
        font-size:   .875rem;
        font-weight: 600;
        color:       #0D1B2A;
        margin:      0;
    }
    .toggle-sub {
        font-size: .75rem;
        color:     #8899aa;
        margin:    .1rem 0 0;
    }
    .form-check-input:checked {
        background-color: #0F3D56;
        border-color:     #0F3D56;
    }
    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(15,61,86,.15);
    }

    /* ── Step indicator ──────────────────────────────────────── */
    .step-bar      { display: flex; align-items: center; gap: 0; }
    .step-item     { display: flex; align-items: center; gap: .4rem; }
    .step-dot {
        width:         28px;
        height:        28px;
        border-radius: 50%;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .72rem;
        font-weight:   700;
        flex-shrink:   0;
    }
    .step-dot.done    { background: rgba(46,204,113,.15); color: #1aaa5a; }
    .step-dot.active  { background: #0F3D56; color: #fff; }
    .step-dot.pending { background: #f0f3f7; color: #8899aa; }
    .step-text { font-size: .76rem; font-weight: 600; color: #8899aa; white-space: nowrap; }
    .step-text.active { color: #0D1B2A; }
    .step-line { width: 28px; height: 1px; background: #e2e8ee; flex-shrink: 0; }

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
        text-decoration: none;
        transition:    background .15s, border-color .15s;
    }
    .btn-prev:hover { background: #f0f3f7; border-color: #c8d2dc; color: #5A6A7A; }
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
        text-decoration: none;
        transition:    background .15s;
    }
    .btn-draft:hover { background: rgba(15,61,86,.06); color: #0F3D56; }
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
        text-decoration: none;
        transition:    background .15s, box-shadow .15s;
    }
    .btn-continue:hover {
        background: #0a2f42;
        box-shadow: 0 4px 14px rgba(15,61,86,.3);
        color:      #fff;
    }

    /* ── Selected count badge ────────────────────────────────── */
    .count-badge {
        display:       inline-flex;
        align-items:   center;
        justify-content: center;
        min-width:     22px;
        height:        22px;
        border-radius: 30px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .7rem;
        font-weight:   700;
        padding:       0 .45rem;
        margin-left:   .5rem;
    }

    /* ── Divider label ───────────────────────────────────────── */
    .section-divider {
        display:     flex;
        align-items: center;
        gap:         .75rem;
        margin:      1.1rem 0 .9rem;
    }
    .section-divider span {
        font-size:      .72rem;
        font-weight:    700;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .07em;
        white-space:    nowrap;
    }
    .section-divider hr { flex: 1; border-color: #f0f3f7; margin: 0; }

    /* ── Service card (Additional Services) ─────────────────── */
    .svc-item { position: relative; }
    .svc-item input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
    .svc-card {
        display:     flex;
        align-items: center;
        gap:         .85rem;
        padding:     .85rem 1rem;
        border:      1.5px solid #e2e8ee;
        border-radius: 11px;
        cursor:      pointer;
        background:  #fafbfc;
        transition:  border-color .18s, background .18s, box-shadow .18s;
        user-select: none;
    }
    .svc-card:hover { border-color: #0F3D56; background: #fff; }
    .svc-item input:checked + .svc-card {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.04);
        box-shadow:   0 0 0 3px rgba(15,61,86,.1);
    }
    .svc-item input:checked + .svc-card .svc-icon { background: #0F3D56; color: #fff; }
    .svc-item input:checked + .svc-card .svc-name { color: #0F3D56; }
    .svc-icon {
        width:         38px;
        height:        38px;
        border-radius: 9px;
        background:    rgba(15,61,86,.08);
        color:         #0F3D56;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     1rem;
        flex-shrink:   0;
        transition:    background .18s, color .18s;
    }
    .svc-name { font-size: .855rem; font-weight: 600; color: #0D1B2A; transition: color .18s; }
    .svc-desc { font-size: .75rem; color: #8899aa; margin-top: .1rem; }
    .svc-chk  {
        margin-left:   auto;
        width:         20px;
        height:        20px;
        border-radius: 6px;
        border:        1.5px solid #e2e8ee;
        display:       flex;
        align-items:   center;
        justify-content: center;
        font-size:     .7rem;
        flex-shrink:   0;
        transition:    background .18s, border-color .18s;
    }
    .svc-item input:checked + .svc-card .svc-chk {
        background:   #0F3D56;
        border-color: #0F3D56;
        color:        #fff;
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading ───────────────────────────────────────── --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Facilities &amp; Services
            </h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Select the facilities available at this parking location.
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
                <div class="step-dot done"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
                <span class="step-text">Pricing</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot active">4</div>
                <span class="step-text active">Facilities</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot pending">5</div>
                <span class="step-text">Documents</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 1 — Parking Facilities
    ═══════════════════════════════════════════════════════════ --}}
    <div class="cfg-card">
        <div class="cfg-card-header">
            <div class="cfg-card-icon" style="background:rgba(15,61,86,.1);">
                <i class="bi bi-shield-check" style="color:#0F3D56;"></i>
            </div>
            <div class="flex-grow-1">
                <p class="cfg-card-title">
                    Parking Facilities
                    <span class="count-badge" id="fac-count">7</span>
                </p>
                <p class="cfg-card-sub">Click to toggle — selected facilities will be shown to drivers</p>
            </div>
        </div>
        <div class="cfg-card-body">

            @php
                $facilities = [
                    // [id, icon, label, checked]
                    ['fac-cctv',        'bi-camera-video-fill',   'CCTV Surveillance',    true ],
                    ['fac-security',    'bi-shield-lock-fill',    '24×7 Security',         true ],
                    ['fac-covered',     'bi-house-fill',          'Covered Parking',       true ],
                    ['fac-ev',          'bi-lightning-charge-fill','EV Charging',          true ],
                    ['fac-wheelchair',  'bi-person-wheelchair',   'Wheelchair Accessible', true ],
                    ['fac-fire',        'bi-fire',                'Fire Safety',           true ],
                    ['fac-washroom',    'bi-droplet-half',        'Washroom',              true ],
                    ['fac-water',       'bi-cup-straw',           'Drinking Water',        false],
                    ['fac-valet',       'bi-person-badge-fill',   'Valet Parking',         false],
                    ['fac-carwash',     'bi-water',               'Car Wash',              false],
                    ['fac-lift',        'bi-arrow-up-square-fill','Lift / Elevator',       false],
                    ['fac-lounge',      'bi-door-open-fill',      'Waiting Lounge',        false],
                ];
            @endphp

            <div class="row g-2" id="fac-grid">
                @foreach ($facilities as [$id, $icon, $label, $checked])
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                        <div class="fac-item">
                            <input
                                type="checkbox"
                                id="{{ $id }}"
                                name="facilities[]"
                                value="{{ $id }}"
                                class="fac-checkbox"
                                {{ $checked ? 'checked' : '' }}
                            >
                            <label class="fac-card h-100" for="{{ $id }}">
                                <div class="fac-check">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div class="fac-icon">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                <span class="fac-label">{{ $label }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mb-0 mt-3" style="font-size:.78rem;color:#8899aa;">
                <i class="bi bi-info-circle me-1"></i>
                Facilities are shown on the parking listing and help drivers choose the right spot.
            </p>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 2 — Parking Rules + Section 3 — Additional Services
    ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-3">

        {{-- LEFT — Parking Rules ──────────────────────────────── --}}
        <div class="col-12 col-lg-6">
            <div class="cfg-card h-100">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(231,76,60,.1);">
                        <i class="bi bi-exclamation-triangle-fill" style="color:#e74c3c;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Parking Rules</p>
                        <p class="cfg-card-sub">Enable the rules that apply at this location</p>
                    </div>
                </div>
                <div class="cfg-card-body">

                    @php
                        $rules = [
                            ['rule-overnight', 'bi-moon-stars-fill',  'No Overnight Parking',
                             'Vehicles must be removed before closing time',       true,  '#e74c3c'],
                            ['rule-helmet',    'bi-shield-fill',       'Helmet Mandatory',
                             'Two-wheeler riders must carry helmets at all times', true,  '#f59e0b'],
                            ['rule-speed',     'bi-speedometer2',      'Speed Limit 10 km/h',
                             'Maximum speed inside the parking compound',          true,  '#3490dc'],
                            ['rule-smoking',   'bi-slash-circle-fill', 'No Smoking',
                             'Smoking is strictly prohibited inside the premises', true,  '#e74c3c'],
                            ['rule-pets',      'bi-heart-fill',        'Pets Allowed',
                             'Owners may bring pets in covered carriers',          false, '#2ECC71'],
                        ];
                    @endphp

                    @foreach ($rules as [$id, $icon, $label, $desc, $checked, $color])
                        <div class="toggle-row">
                            <div class="d-flex align-items-center gap-3">
                                <div style="
                                    width:36px; height:36px; border-radius:9px; flex-shrink:0;
                                    background:{{ $color }}1a;
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:.9rem; color:{{ $color }};
                                ">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                <div>
                                    <p class="toggle-label">{{ $label }}</p>
                                    <p class="toggle-sub">{{ $desc }}</p>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0 ms-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="{{ $id }}"
                                    name="rules[]"
                                    value="{{ $id }}"
                                    role="switch"
                                    {{ $checked ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="{{ $id }}"></label>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- RIGHT — Additional Services ──────────────────────── --}}
        <div class="col-12 col-lg-6">
            <div class="cfg-card h-100">
                <div class="cfg-card-header">
                    <div class="cfg-card-icon" style="background:rgba(46,204,113,.12);">
                        <i class="bi bi-stars" style="color:#2ECC71;"></i>
                    </div>
                    <div>
                        <p class="cfg-card-title">Additional Services</p>
                        <p class="cfg-card-sub">Premium and convenience services offered at this parking</p>
                    </div>
                </div>
                <div class="cfg-card-body">

                    @php
                        $services = [
                            ['svc-monthly',   'bi-calendar-check-fill',  '#0F3D56',
                             'Monthly Pass',        'Discounted monthly subscription for regular users',    true ],
                            ['svc-vip',       'bi-gem',                  '#8a4d9e',
                             'VIP Parking',         'Priority reserved spots for premium members',          false],
                            ['svc-reserved',  'bi-bookmark-fill',        '#3490dc',
                             'Reserved Parking',    'Pre-book specific numbered slots in advance',          true ],
                            ['svc-online',    'bi-globe2',               '#2ECC71',
                             'Online Booking',      'Drivers can book and pay through the app or website',  true ],
                        ];
                    @endphp

                    <div class="d-flex flex-column gap-2">
                        @foreach ($services as [$id, $icon, $color, $name, $desc, $checked])
                            <div class="svc-item">
                                <input
                                    type="checkbox"
                                    id="{{ $id }}"
                                    name="services[]"
                                    value="{{ $id }}"
                                    {{ $checked ? 'checked' : '' }}
                                >
                                <label class="svc-card" for="{{ $id }}">
                                    <div class="svc-icon" style="background:{{ $color }}1a; color:{{ $color }};">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="svc-name mb-0">{{ $name }}</p>
                                        <p class="svc-desc mb-0">{{ $desc }}</p>
                                    </div>
                                    <div class="svc-chk">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    {{-- ── Quick rules summary ─────────────────── --}}
                    <div class="section-divider mt-3">
                        <span>Compliance Notes</span><hr>
                    </div>

                    <textarea
                        class="form-control"
                        rows="3"
                        placeholder="Add any additional compliance notes or special instructions for drivers…"
                        style="border:1px solid #e2e8ee; border-radius:9px; font-size:.855rem; color:#0D1B2A; resize:vertical;"
                    >Parking is monitored 24×7 by CCTV. All vehicles must display valid booking confirmation at the entry gate. Management is not responsible for valuables left inside vehicles.</textarea>
                    <p style="font-size:.75rem; color:#8899aa; margin-top:.35rem;">
                        This note will be displayed to users before they confirm a booking.
                    </p>

                </div>
            </div>
        </div>

    </div>{{-- /row --}}

    {{-- ═══════════════════════════════════════════════════════════
         SELECTION SUMMARY STRIP
    ═══════════════════════════════════════════════════════════ --}}
    <div
        class="d-flex flex-wrap align-items-center gap-3 px-4 py-3 mb-3 mt-0"
        style="background:#fff; border:1px solid #e2e8ee; border-radius:14px; box-shadow:0 2px 12px rgba(15,61,86,.06);"
    >
        <i class="bi bi-check2-all" style="color:#0F3D56; font-size:1.1rem;"></i>
        <span style="font-size:.855rem; font-weight:600; color:#0D1B2A;">Selection Summary:</span>

        <span style="font-size:.82rem; color:#5A6A7A;">
            <span style="font-weight:700; color:#0F3D56;" id="sum-facilities">7</span> Facilities
        </span>
        <span style="color:#e2e8ee;">|</span>
        <span style="font-size:.82rem; color:#5A6A7A;">
            <span style="font-weight:700; color:#e74c3c;">4</span> Rules active
        </span>
        <span style="color:#e2e8ee;">|</span>
        <span style="font-size:.82rem; color:#5A6A7A;">
            <span style="font-weight:700; color:#2ECC71;">3</span> Additional services
        </span>

        <div class="ms-auto">
            <span
                style="
                    display:inline-flex; align-items:center; gap:.4rem;
                    background:rgba(46,204,113,.12); color:#1aaa5a;
                    border-radius:20px; padding:.25em .85em;
                    font-size:.75rem; font-weight:600;
                "
            >
                <i class="bi bi-check-circle-fill"></i> Looking good
            </span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         Action Bar
    ═══════════════════════════════════════════════════════════ --}}
    <div class="action-bar">
        <a href="#" class="btn-prev">
            <i class="bi bi-arrow-left"></i> Previous
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="#" class="btn-draft">
                <i class="bi bi-floppy"></i> Save Draft
            </a>
            <a href="#" class="btn-continue">
                Next <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    /* ── Update selected facilities count badge ─────────────── */
    function updateFacCount() {
        const total = document.querySelectorAll('.fac-checkbox:checked').length;
        document.getElementById('fac-count').textContent = total;
        document.getElementById('sum-facilities').textContent = total;
    }

    document.querySelectorAll('.fac-checkbox').forEach(cb => {
        cb.addEventListener('change', updateFacCount);
    });

    /* Sync fac-check tick visibility via CSS (handled by :checked + .fac-card .fac-check)
       — no JS needed for the tick, only for the counter above. */
</script>
@endpush