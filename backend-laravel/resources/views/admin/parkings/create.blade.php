{{-- ============================================================
     Add / Edit Parking
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Multi-section form to create or edit a parking lot.
               Sections: Basic Info · Pricing & Slots · Facilities
                         Images · Location
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Add Parking')
@section('page-title', 'Add Parking')

@push('styles')
<style>
    /* ── Section card ────────────────────────────────────────── */
    .form-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
        margin-bottom: 1.5rem;
    }
    .form-card-header {
        display:        flex;
        align-items:    center;
        gap:            .75rem;
        padding:        1rem 1.4rem;
        border-bottom:  1px solid #f0f3f7;
        background:     #fafbfc;
    }
    .form-card-header-icon {
        width:           36px;
        height:          36px;
        border-radius:   9px;
        background:      rgba(15,61,86,.09);
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1rem;
        color:           #0F3D56;
        flex-shrink:     0;
    }
    .form-card-title {
        font-size:   .93rem;
        font-weight: 700;
        color:       #0D1B2A;
        margin:      0;
    }
    .form-card-body {
        padding: 1.4rem;
    }

    /* ── Form controls ───────────────────────────────────────── */
    .form-label {
        font-size:   .78rem;
        font-weight: 600;
        color:       #5A6A7A;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .4rem;
    }
    .form-control,
    .form-select {
        font-size:     .875rem;
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        padding:       .55rem .85rem;
        color:         #0D1B2A;
        transition:    border-color .18s, box-shadow .18s;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: #0F3D56;
        box-shadow:   0 0 0 3px rgba(15,61,86,.1);
    }
    .form-control::placeholder { color: #b0bec5; }

    /* ── Section step badge ──────────────────────────────────── */
    .step-badge {
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        width:           22px;
        height:          22px;
        border-radius:   50%;
        background:      #0F3D56;
        color:           #fff;
        font-size:       .7rem;
        font-weight:     700;
        flex-shrink:     0;
    }

    /* ── Pricing type toggle ─────────────────────────────────── */
    .price-toggle {
        display:       inline-flex;
        border:        1px solid #e2e8ee;
        border-radius: 9px;
        overflow:      hidden;
        background:    #f8f9fa;
    }
    .price-toggle-btn {
        padding:         .45rem 1.2rem;
        font-size:       .84rem;
        font-weight:     600;
        color:           #5A6A7A;
        border:          none;
        background:      transparent;
        cursor:          pointer;
        transition:      background .15s, color .15s;
    }
    .price-toggle-btn.active {
        background:   #0F3D56;
        color:        #fff;
        border-radius:8px;
    }

    /* ── Facility checkbox cards ─────────────────────────────── */
    .facility-card {
        border:        1px solid #e2e8ee;
        border-radius: 10px;
        padding:       .85rem 1rem;
        display:       flex;
        align-items:   center;
        gap:           .75rem;
        cursor:        pointer;
        transition:    border-color .18s, background .18s;
        user-select:   none;
    }
    .facility-card:hover {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.03);
    }
    .facility-card.checked {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.06);
    }
    .facility-card input[type="checkbox"] {
        width:         18px;
        height:        18px;
        accent-color:  #0F3D56;
        cursor:        pointer;
        flex-shrink:   0;
    }
    .facility-icon {
        width:           36px;
        height:          36px;
        border-radius:   8px;
        background:      rgba(15,61,86,.08);
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       1rem;
        color:           #0F3D56;
        flex-shrink:     0;
    }
    .facility-label {
        font-size:   .875rem;
        font-weight: 600;
        color:       #0D1B2A;
    }

    /* ── Vehicle type checkboxes ─────────────────────────────── */
    .vehicle-chip {
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        padding:       .4rem .9rem;
        border:        1px solid #e2e8ee;
        border-radius: 20px;
        font-size:     .84rem;
        font-weight:   600;
        color:         #5A6A7A;
        cursor:        pointer;
        transition:    border-color .15s, background .15s, color .15s;
        user-select:   none;
    }
    .vehicle-chip input[type="checkbox"] {
        accent-color: #0F3D56;
        cursor:       pointer;
    }
    .vehicle-chip.checked {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.08);
        color:        #0F3D56;
    }

    /* ── Image upload zone ───────────────────────────────────── */
    .upload-zone {
        border:        2px dashed #c8d2dc;
        border-radius: 12px;
        padding:       2.5rem 1.5rem;
        text-align:    center;
        background:    #fafbfc;
        cursor:        pointer;
        transition:    border-color .18s, background .18s;
    }
    .upload-zone:hover {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.03);
    }
    .upload-zone-icon {
        font-size:     2.5rem;
        color:         #b0bec5;
        margin-bottom: .75rem;
    }

    /* ── Image preview card ──────────────────────────────────── */
    .img-preview-card {
        border:        1px solid #e2e8ee;
        border-radius: 10px;
        overflow:      hidden;
        position:      relative;
    }
    .img-preview-placeholder {
        height:          110px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       2rem;
        color:           #b0bec5;
    }
    .img-preview-footer {
        padding:       .4rem .65rem;
        font-size:     .74rem;
        color:         #8899aa;
        border-top:    1px solid #f0f3f7;
        display:       flex;
        align-items:   center;
        justify-content: space-between;
    }
    .img-delete-btn {
        background:      none;
        border:          none;
        color:           #e74c3c;
        font-size:       .8rem;
        padding:         0;
        cursor:          pointer;
        display:         inline-flex;
        align-items:     center;
        gap:             .2rem;
        transition:      opacity .15s;
    }
    .img-delete-btn:hover { opacity: .75; }

    /* ── Map placeholder ─────────────────────────────────────── */
    .map-placeholder {
        height:          260px;
        border:          1px solid #e2e8ee;
        border-radius:   10px;
        background:      linear-gradient(135deg, #e8f4f8 0%, #d0e8f0 100%);
        display:         flex;
        flex-direction:  column;
        align-items:     center;
        justify-content: center;
        color:           #8899aa;
        font-size:       .875rem;
        gap:             .5rem;
        position:        relative;
        overflow:        hidden;
    }
    .map-grid-line {
        position:   absolute;
        background: rgba(15,61,86,.07);
    }

    /* ── Bottom action bar ───────────────────────────────────── */
    .action-bar {
        position:      sticky;
        bottom:        0;
        background:    #fff;
        border-top:    1px solid #e2e8ee;
        padding:       1rem 1.75rem;
        display:       flex;
        align-items:   center;
        justify-content: flex-end;
        gap:           .75rem;
        z-index:       100;
        box-shadow:    0 -2px 12px rgba(15,61,86,.06);
        margin:        0 -1.75rem -2.5rem;
    }
    .btn-cancel {
        height:        40px;
        padding:       0 1.25rem;
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        background:    #fff;
        color:         #5A6A7A;
        font-size:     .875rem;
        font-weight:   600;
        cursor:        pointer;
        transition:    background .15s;
        text-decoration: none;
        display:       inline-flex;
        align-items:   center;
    }
    .btn-cancel:hover { background: #f0f3f7; color: #0D1B2A; }

    .btn-draft {
        height:        40px;
        padding:       0 1.25rem;
        border:        1px solid #0F3D56;
        border-radius: 8px;
        background:    transparent;
        color:         #0F3D56;
        font-size:     .875rem;
        font-weight:   600;
        cursor:        pointer;
        transition:    background .15s, color .15s;
        display:       inline-flex;
        align-items:   center;
        gap:           .35rem;
    }
    .btn-draft:hover { background: #0F3D56; color: #fff; }

    .btn-save {
        height:        40px;
        padding:       0 1.4rem;
        border:        none;
        border-radius: 8px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .875rem;
        font-weight:   600;
        cursor:        pointer;
        transition:    background .15s;
        display:       inline-flex;
        align-items:   center;
        gap:           .35rem;
    }
    .btn-save:hover { background: #0a2f42; }

    /* ── Price input group ───────────────────────────────────── */
    .price-input-group {
        position:  relative;
    }
    .price-input-group .rupee-sign {
        position:    absolute;
        left:        .85rem;
        top:         50%;
        transform:   translateY(-50%);
        color:       #8899aa;
        font-size:   .875rem;
        font-weight: 600;
        pointer-events: none;
    }
    .price-input-group .form-control {
        padding-left: 1.75rem;
    }

    /* ── Slot input ──────────────────────────────────────────── */
    .slot-type-row {
        display:       flex;
        align-items:   center;
        gap:           1rem;
        padding:       .75rem 1rem;
        border:        1px solid #e2e8ee;
        border-radius: 10px;
        background:    #fafbfc;
    }
    .slot-type-icon {
        width:           34px;
        height:          34px;
        border-radius:   8px;
        background:      rgba(15,61,86,.08);
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .95rem;
        color:           #0F3D56;
        flex-shrink:     0;
    }
    .slot-type-label {
        flex:        1;
        font-size:   .875rem;
        font-weight: 600;
        color:       #0D1B2A;
    }
    .slot-type-input {
        width:         80px;
        text-align:    center;
        font-weight:   700;
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading ───────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Add New Parking
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Parkings</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Add Parking</li>
                </ol>
            </nav>
        </div>

        {{-- Step indicator --}}
        <div class="d-flex align-items-center gap-2" style="font-size:.8rem; color:#8899aa;">
            @foreach (['Basic Info', 'Pricing', 'Facilities', 'Images', 'Location'] as $i => $step)
                <span
                    class="step-badge"
                    style="background:{{ $i === 0 ? '#0F3D56' : '#e2e8ee' }}; color:{{ $i === 0 ? '#fff' : '#8899aa' }};"
                >{{ $i + 1 }}</span>
                <span style="color:{{ $i === 0 ? '#0D1B2A' : '#8899aa' }}; font-weight:{{ $i === 0 ? '600' : '400' }};">
                    {{ $step }}
                </span>
                @if ($i < 4)
                    <i class="bi bi-chevron-right" style="font-size:.65rem;"></i>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 1 — Basic Information
    ══════════════════════════════════════════════════════════ --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon">
                <i class="bi bi-info-circle"></i>
            </div>
            <div>
                <p class="form-card-title">Basic Information</p>
                <p class="mb-0" style="font-size:.76rem; color:#8899aa;">
                    Primary details about the parking lot
                </p>
            </div>
        </div>
        <div class="form-card-body">
            <div class="row g-3">

                {{-- Parking Name --}}
                <div class="col-12 col-md-6">
                    <label class="form-label">Parking Name <span style="color:#e74c3c;">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="e.g. Connaught Place Parking"
                        value="Connaught Place Parking"
                    >
                </div>

                {{-- Owner --}}
                <div class="col-12 col-md-6">
                    <label class="form-label">Parking Owner <span style="color:#e74c3c;">*</span></label>
                    <select class="form-select">
                        <option value="">Select Owner</option>
                        <option selected>Modern Parking Solutions — Rahul Sharma</option>
                        <option>City Parkings — Meena Reddy</option>
                        <option>Secure Park India — Vikram Joshi</option>
                        <option>Fast Park Pvt Ltd — Sanjay Gupta</option>
                    </select>
                </div>

                {{-- Parking Type --}}
                <div class="col-12 col-md-6">
                    <label class="form-label">Parking Type <span style="color:#e74c3c;">*</span></label>
                    <div class="d-flex gap-3 mt-1 flex-wrap">
                        @foreach (['Paid' => true, 'Free' => false] as $type => $checked)
                            <label
                                class="d-flex align-items-center gap-2"
                                style="cursor:pointer; font-size:.875rem; font-weight:600; color:#0D1B2A;"
                            >
                                <input
                                    type="radio"
                                    name="parking_type"
                                    value="{{ strtolower($type) }}"
                                    {{ $checked ? 'checked' : '' }}
                                    style="accent-color:#0F3D56; width:16px; height:16px;"
                                >
                                {{ $type }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Vehicle Types --}}
                <div class="col-12 col-md-6">
                    <label class="form-label">Vehicle Types Accepted <span style="color:#e74c3c;">*</span></label>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @php
                            $vehicles = [
                                ['label' => 'Two Wheeler', 'icon' => 'bi-bicycle',   'checked' => true],
                                ['label' => 'Four Wheeler','icon' => 'bi-car-front', 'checked' => true],
                                ['label' => 'Taxi',        'icon' => 'bi-taxi-front','checked' => false],
                                ['label' => 'Bus / Truck', 'icon' => 'bi-truck',     'checked' => false],
                            ];
                        @endphp
                        @foreach ($vehicles as $v)
                            <label class="vehicle-chip {{ $v['checked'] ? 'checked' : '' }}">
                                <input
                                    type="checkbox"
                                    {{ $v['checked'] ? 'checked' : '' }}
                                    onchange="this.closest('.vehicle-chip').classList.toggle('checked', this.checked)"
                                >
                                <i class="bi {{ $v['icon'] }}"></i>
                                {{ $v['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        class="form-control"
                        rows="3"
                        placeholder="Brief description about this parking…"
                        style="resize:vertical;"
                    >Centrally located covered parking at Connaught Place. 24/7 CCTV surveillance, dedicated EV charging stations and round-the-clock security.</textarea>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 2 — Pricing & Slots
    ══════════════════════════════════════════════════════════ --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div>
                <p class="form-card-title">Pricing &amp; Slots</p>
                <p class="mb-0" style="font-size:.76rem; color:#8899aa;">
                    Set pricing model and slot capacity
                </p>
            </div>
        </div>
        <div class="form-card-body">

            {{-- Pricing Type --}}
            <div class="mb-4">
                <label class="form-label d-block">Pricing Type</label>
                <div class="price-toggle">
                    <button
                        type="button"
                        class="price-toggle-btn active"
                        onclick="setPricingType(this)"
                    >Hourly Based</button>
                    <button
                        type="button"
                        class="price-toggle-btn"
                        onclick="setPricingType(this)"
                    >Flat Rate</button>
                </div>
            </div>

            <div class="row g-3">

                {{-- Two Wheeler --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">
                        <i class="bi bi-bicycle me-1"></i>Two Wheeler (per hour)
                    </label>
                    <div class="price-input-group">
                        <span class="rupee-sign">₹</span>
                        <input type="number" class="form-control" value="20" min="0">
                    </div>
                </div>

                {{-- Four Wheeler --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">
                        <i class="bi bi-car-front me-1"></i>Four Wheeler (per hour)
                    </label>
                    <div class="price-input-group">
                        <span class="rupee-sign">₹</span>
                        <input type="number" class="form-control" value="40" min="0">
                    </div>
                </div>

                {{-- Taxi --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">
                        <i class="bi bi-taxi-front me-1"></i>Taxi (per hour)
                    </label>
                    <div class="price-input-group">
                        <span class="rupee-sign">₹</span>
                        <input type="number" class="form-control" value="50" min="0">
                    </div>
                </div>

                {{-- Extra charges note --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Extra Charge After (hrs)</label>
                    <input type="number" class="form-control" value="1" min="0">
                    <div style="font-size:.74rem; color:#8899aa; margin-top:.3rem;">
                        Overtime multiplier kicks in after this
                    </div>
                </div>

            </div>

            <hr style="border-color:#f0f3f7; margin:1.5rem 0;">

            {{-- Total Slots --}}
            <label class="form-label d-block mb-3">Total Slots per Vehicle Type</label>
            <div class="row g-3">
                @php
                    $slotTypes = [
                        ['label' => 'Two Wheeler',  'icon' => 'bi-bicycle',    'value' => 50],
                        ['label' => 'Four Wheeler', 'icon' => 'bi-car-front',  'value' => 20],
                        ['label' => 'Taxi',         'icon' => 'bi-taxi-front', 'value' => 10],
                    ];
                @endphp
                @foreach ($slotTypes as $slot)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="slot-type-row">
                            <div class="slot-type-icon">
                                <i class="bi {{ $slot['icon'] }}"></i>
                            </div>
                            <div class="slot-type-label">{{ $slot['label'] }}</div>
                            <input
                                type="number"
                                class="form-control slot-type-input"
                                value="{{ $slot['value'] }}"
                                min="0"
                            >
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total badge --}}
            <div
                class="d-inline-flex align-items-center gap-2 mt-3 px-3 py-2"
                style="background:rgba(15,61,86,.06); border-radius:9px; font-size:.875rem;"
            >
                <i class="bi bi-grid-3x3-gap" style="color:#0F3D56;"></i>
                <span style="color:#5A6A7A;">Total Slots:</span>
                <strong style="color:#0F3D56;">80</strong>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 3 — Facilities
    ══════════════════════════════════════════════════════════ --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <p class="form-card-title">Facilities &amp; Amenities</p>
                <p class="mb-0" style="font-size:.76rem; color:#8899aa;">
                    Select all facilities available at this parking
                </p>
            </div>
        </div>
        <div class="form-card-body">
            <div class="row g-3">
                @php
                    $facilities = [
                        ['label' => 'CCTV Surveillance', 'icon' => 'bi-camera-video',      'checked' => true],
                        ['label' => 'Security Guard',     'icon' => 'bi-shield-person',     'checked' => true],
                        ['label' => 'EV Charging',        'icon' => 'bi-ev-station',        'checked' => true],
                        ['label' => 'Washroom',           'icon' => 'bi-water',             'checked' => false],
                        ['label' => 'Covered Parking',    'icon' => 'bi-house-door',        'checked' => true],
                        ['label' => 'Disabled Access',    'icon' => 'bi-person-wheelchair', 'checked' => false],
                        ['label' => 'Car Wash',           'icon' => 'bi-droplet',           'checked' => false],
                        ['label' => 'Digital Receipt',    'icon' => 'bi-receipt',           'checked' => true],
                    ];
                @endphp
                @foreach ($facilities as $f)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label
                            class="facility-card {{ $f['checked'] ? 'checked' : '' }}"
                            onclick="this.classList.toggle('checked')"
                        >
                            <input
                                type="checkbox"
                                {{ $f['checked'] ? 'checked' : '' }}
                                onclick="event.stopPropagation(); this.closest('.facility-card').classList.toggle('checked', this.checked)"
                            >
                            <div class="facility-icon">
                                <i class="bi {{ $f['icon'] }}"></i>
                            </div>
                            <span class="facility-label">{{ $f['label'] }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 4 — Images
    ══════════════════════════════════════════════════════════ --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon">
                <i class="bi bi-images"></i>
            </div>
            <div>
                <p class="form-card-title">Parking Images</p>
                <p class="mb-0" style="font-size:.76rem; color:#8899aa;">
                    Upload up to 8 images. First image will be used as cover.
                </p>
            </div>
        </div>
        <div class="form-card-body">

            {{-- Upload zone --}}
            <div
                class="upload-zone mb-4"
                onclick="document.getElementById('imageUpload').click()"
            >
                <div class="upload-zone-icon">
                    <i class="bi bi-cloud-arrow-up"></i>
                </div>
                <p class="mb-1" style="font-weight:700; color:#0D1B2A; font-size:.9rem;">
                    Click to upload or drag &amp; drop
                </p>
                <p class="mb-0" style="font-size:.8rem; color:#8899aa;">
                    PNG, JPG or WEBP &nbsp;&middot;&nbsp; Max 5 MB per image
                </p>
                <input type="file" id="imageUpload" multiple accept="image/*" hidden>
            </div>

            {{-- Image previews --}}
            <div class="row g-3">
                @php
                    $images = [
                        ['label' => 'parking-entry.jpg',  'size' => '2.1 MB', 'bg' => '#d0e8f0', 'icon' => 'bi-door-open',       'badge' => 'Cover'],
                        ['label' => 'parking-slots.jpg',  'size' => '1.8 MB', 'bg' => '#d0ead8', 'icon' => 'bi-grid-3x3-gap',    'badge' => null],
                        ['label' => 'ev-charging.jpg',    'size' => '1.3 MB', 'bg' => '#fde8c8', 'icon' => 'bi-ev-station',       'badge' => null],
                        ['label' => 'security-desk.jpg',  'size' => '900 KB', 'bg' => '#e8d0f0', 'icon' => 'bi-shield-person',   'badge' => null],
                    ];
                @endphp
                @foreach ($images as $img)
                    <div class="col-6 col-sm-4 col-lg-3">
                        <div class="img-preview-card">
                            {{-- Cover badge --}}
                            @if ($img['badge'])
                                <div
                                    style="
                                        position:      absolute;
                                        top:           8px;
                                        left:          8px;
                                        background:    #0F3D56;
                                        color:         #fff;
                                        font-size:     .68rem;
                                        font-weight:   700;
                                        border-radius: 5px;
                                        padding:       .15em .55em;
                                        z-index:       2;
                                    "
                                >{{ $img['badge'] }}</div>
                            @endif
                            {{-- Placeholder image --}}
                            <div
                                class="img-preview-placeholder"
                                style="background:{{ $img['bg'] }};"
                            >
                                <i class="bi {{ $img['icon'] }}" style="color:rgba(15,61,86,.4);"></i>
                            </div>
                            <div class="img-preview-footer">
                                <span
                                    style="
                                        white-space:   nowrap;
                                        overflow:      hidden;
                                        text-overflow: ellipsis;
                                        max-width:     70%;
                                    "
                                    title="{{ $img['label'] }}"
                                >{{ $img['label'] }}</span>
                                <button type="button" class="img-delete-btn" title="Remove">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Add more placeholder --}}
                <div class="col-6 col-sm-4 col-lg-3">
                    <div
                        class="img-preview-card d-flex align-items-center justify-content-center"
                        style="
                            height:          100%;
                            min-height:      135px;
                            border:          2px dashed #c8d2dc;
                            border-radius:   10px;
                            cursor:          pointer;
                            background:      #fafbfc;
                            flex-direction:  column;
                            gap:             .4rem;
                            transition:      border-color .15s;
                        "
                        onclick="document.getElementById('imageUpload').click()"
                        onmouseover="this.style.borderColor='#0F3D56'"
                        onmouseout="this.style.borderColor='#c8d2dc'"
                    >
                        <i class="bi bi-plus-circle" style="font-size:1.5rem; color:#c8d2dc;"></i>
                        <span style="font-size:.75rem; color:#8899aa; font-weight:600;">Add More</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SECTION 5 — Location
    ══════════════════════════════════════════════════════════ --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div>
                <p class="form-card-title">Location</p>
                <p class="mb-0" style="font-size:.76rem; color:#8899aa;">
                    Address and map pin for the parking lot
                </p>
            </div>
        </div>
        <div class="form-card-body">
            <div class="row g-3">

                {{-- Address --}}
                <div class="col-12">
                    <label class="form-label">Full Address <span style="color:#e74c3c;">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Street, Area, Locality"
                        value="Connaught Place, New Delhi, Delhi 110001"
                    >
                </div>

                {{-- State --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">State <span style="color:#e74c3c;">*</span></label>
                    <select class="form-select">
                        <option value="">Select State</option>
                        <option selected>Delhi</option>
                        <option>Maharashtra</option>
                        <option>Karnataka</option>
                        <option>Tamil Nadu</option>
                        <option>Rajasthan</option>
                        <option>Uttar Pradesh</option>
                        <option>West Bengal</option>
                        <option>Gujarat</option>
                    </select>
                </div>

                {{-- City --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">City <span style="color:#e74c3c;">*</span></label>
                    <select class="form-select">
                        <option value="">Select City</option>
                        <option selected>New Delhi</option>
                        <option>Mumbai</option>
                        <option>Bengaluru</option>
                        <option>Chennai</option>
                        <option>Hyderabad</option>
                        <option>Jaipur</option>
                        <option>Kolkata</option>
                        <option>Ahmedabad</option>
                    </select>
                </div>

                {{-- Pincode --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Pincode <span style="color:#e74c3c;">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="110001"
                        value="110001"
                        maxlength="6"
                    >
                </div>

                {{-- Landmark --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Landmark</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Near …"
                        value="Near PVR Cinema"
                    >
                </div>

                {{-- Coordinates row --}}
                <div class="col-12 col-sm-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" placeholder="e.g. 28.6315" value="28.6315">
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" placeholder="e.g. 77.2167" value="77.2167">
                </div>

                {{-- Map placeholder --}}
                <div class="col-12">
                    <label class="form-label">Map Preview</label>
                    <div class="map-placeholder">
                        {{-- Grid lines for map feel --}}
                        @for ($r = 0; $r < 9; $r++)
                            <div
                                class="map-grid-line"
                                style="left:0;right:0;top:{{ $r * 12.5 }}%;height:1px;"
                            ></div>
                        @endfor
                        @for ($c = 0; $c < 9; $c++)
                            <div
                                class="map-grid-line"
                                style="top:0;bottom:0;left:{{ $c * 12.5 }}%;width:1px;"
                            ></div>
                        @endfor

                        {{-- Road lines --}}
                        <div style="
                            position:   absolute;
                            left:0; right:0;
                            top:50%; height:7px;
                            background: rgba(15,61,86,.12);
                            transform:  translateY(-50%);
                        "></div>
                        <div style="
                            position:   absolute;
                            top:0; bottom:0;
                            left:50%; width:7px;
                            background: rgba(15,61,86,.12);
                            transform:  translateX(-50%);
                        "></div>

                        {{-- Pin --}}
                        <div style="position:relative; z-index:2; text-align:center;">
                            <div style="
                                width:           46px;
                                height:          46px;
                                border-radius:   50%;
                                background:      #0F3D56;
                                display:         flex;
                                align-items:     center;
                                justify-content: center;
                                margin:          0 auto .4rem;
                                box-shadow:      0 4px 16px rgba(15,61,86,.35);
                            ">
                                <i class="bi bi-p-square-fill" style="color:#2ECC71; font-size:1.2rem;"></i>
                            </div>
                            <span style="
                                background:    #fff;
                                border:        1px solid #e2e8ee;
                                border-radius: 6px;
                                padding:       .25rem .65rem;
                                font-size:     .78rem;
                                font-weight:   600;
                                color:         #0D1B2A;
                                box-shadow:    0 2px 8px rgba(0,0,0,.1);
                            ">Connaught Place Parking</span>
                        </div>

                        {{-- Overlay hint --}}
                        <div style="
                            position:    absolute;
                            bottom:      12px;
                            right:       12px;
                            background:  rgba(255,255,255,.9);
                            border:      1px solid #e2e8ee;
                            border-radius: 7px;
                            padding:     .3rem .7rem;
                            font-size:   .74rem;
                            color:       #5A6A7A;
                            font-weight: 600;
                        ">
                            <i class="bi bi-map me-1"></i>Google Maps Preview
                        </div>
                    </div>
                    <p style="font-size:.75rem; color:#8899aa; margin-top:.5rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Enter latitude &amp; longitude above to pin the location on the live map.
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Bottom Action Bar
    ══════════════════════════════════════════════════════════ --}}
    <div class="action-bar">
        <a href="#" class="btn-cancel">
            Cancel
        </a>
        <button type="button" class="btn-draft">
            <i class="bi bi-floppy"></i> Save Draft
        </button>
        <button type="button" class="btn-save">
            <i class="bi bi-check-circle"></i> Save Parking
        </button>
    </div>

@endsection

@push('scripts')
<script>
    // ── Pricing type toggle ────────────────────────────────────
    function setPricingType(btn) {
        btn.closest('.price-toggle')
           .querySelectorAll('.price-toggle-btn')
           .forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
</script>
@endpush