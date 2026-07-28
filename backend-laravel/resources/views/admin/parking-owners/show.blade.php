{{-- ============================================================
     Parking Owner — Show / Detail
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Full profile view for a single parking owner.
               Tabs: Details · Documents · Bank Details · Parkings
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Parking Owner Details')
@section('page-title', 'Parking Owner Details')

@push('styles')
<style>
    /* ── Page card shell ─────────────────────────────────────── */
    .page-card {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        overflow:      hidden;
    }

    /* ── Profile header card ─────────────────────────────────── */
    .profile-header {
        background:    #fff;
        border:        1px solid #e2e8ee;
        border-radius: 14px;
        box-shadow:    0 2px 12px rgba(15,61,86,.06);
        padding:       1.5rem;
    }

    /* ── Avatar placeholder ──────────────────────────────────── */
    .owner-avatar-lg {
        width:           80px;
        height:          80px;
        border-radius:   16px;
        background:      #0F3D56;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       2rem;
        font-weight:     700;
        color:           #fff;
        flex-shrink:     0;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .status-badge {
        display:        inline-block;
        padding:        .3em .9em;
        border-radius:  20px;
        font-size:      .75rem;
        font-weight:    600;
        letter-spacing: .02em;
    }
    .badge-approved { background: rgba(46,204,113,.14); color: #1aaa5a; }
    .badge-pending  { background: rgba(255,165,0,.15);  color: #c47d00; }
    .badge-rejected { background: rgba(231,76,60,.12);  color: #c0392b; }

    /* ── Tab nav ─────────────────────────────────────────────── */
    .owner-tabs .nav-link {
        color:       #5A6A7A;
        font-size:   .875rem;
        font-weight: 500;
        padding:     .6rem 1.1rem;
        border-radius: 8px;
        border:      none;
        background:  transparent;
        transition:  background .15s, color .15s;
    }
    .owner-tabs .nav-link:hover {
        background: #f0f3f7;
        color:      #0F3D56;
    }
    .owner-tabs .nav-link.active {
        background:  #0F3D56;
        color:       #fff;
        font-weight: 600;
    }

    /* ── Info rows ───────────────────────────────────────────── */
    .info-label {
        font-size:      .78rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom:  .2rem;
    }
    .info-value {
        font-size:   .875rem;
        font-weight: 500;
        color:       #0D1B2A;
    }

    /* ── Section title ───────────────────────────────────────── */
    .section-title {
        font-size:      .7rem;
        font-weight:    700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color:          #8899aa;
        padding:        .9rem 1.4rem .45rem;
        border-bottom:  1px solid #f0f3f7;
        background:     #fafbfc;
    }

    /* ── Document card ───────────────────────────────────────── */
    .doc-card {
        border:        1px solid #e2e8ee;
        border-radius: 10px;
        padding:       1rem 1.1rem;
        display:       flex;
        align-items:   center;
        gap:           .85rem;
        transition:    box-shadow .15s;
    }
    .doc-card:hover { box-shadow: 0 2px 10px rgba(15,61,86,.09); }
    .doc-icon {
        width:           40px;
        height:          40px;
        border-radius:   9px;
        background:      rgba(15,61,86,.08);
        display:         flex;
        align-items:     center;
        justify-content: center;
        flex-shrink:     0;
        font-size:       1.1rem;
        color:           #0F3D56;
    }
    .doc-view-btn {
        font-size:       .78rem;
        font-weight:     600;
        color:           #0F3D56;
        text-decoration: none;
        padding:         .25rem .75rem;
        border:          1px solid #0F3D56;
        border-radius:   6px;
        white-space:     nowrap;
        transition:      background .15s, color .15s;
        margin-left:     auto;
    }
    .doc-view-btn:hover {
        background: #0F3D56;
        color:      #fff;
    }

    /* ── Parking list table ──────────────────────────────────── */
    .parkings-table thead th {
        font-size:      .74rem;
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
    .parkings-table tbody td {
        font-size:      .855rem;
        padding:        .8rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .parkings-table tbody tr:last-child td { border-bottom: none; }
    .parkings-table tbody tr:hover td      { background: #fafcff; }

    /* ── Action buttons ──────────────────────────────────────── */
    .action-btn {
        display:         inline-flex;
        align-items:     center;
        justify-content: center;
        width:           30px;
        height:          30px;
        border-radius:   7px;
        border:          1px solid transparent;
        font-size:       .82rem;
        cursor:          pointer;
        transition:      background .15s, border-color .15s, color .15s;
        text-decoration: none;
    }
    .action-btn-view       { background: rgba(15,61,86,.08);  color: #0F3D56; }
    .action-btn-view:hover { background: #0F3D56; color: #fff; border-color: #0F3D56; }
    .action-btn-edit       { background: rgba(52,144,220,.1); color: #3490dc; }
    .action-btn-edit:hover { background: #3490dc; color: #fff; border-color: #3490dc; }

    /* ── Approve / Reject action buttons ─────────────────────── */
    .btn-approve {
        height:        34px;
        border:        none;
        border-radius: 8px;
        background:    rgba(46,204,113,.12);
        color:         #1aaa5a;
        font-size:     .83rem;
        font-weight:   600;
        padding:       0 1rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .35rem;
        transition:    background .15s, color .15s;
        cursor:        pointer;
    }
    .btn-approve:hover { background: #2ecc71; color: #fff; }

    .btn-reject {
        height:        34px;
        border:        none;
        border-radius: 8px;
        background:    rgba(231,76,60,.1);
        color:         #e74c3c;
        font-size:     .83rem;
        font-weight:   600;
        padding:       0 1rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .35rem;
        transition:    background .15s, color .15s;
        cursor:        pointer;
    }
    .btn-reject:hover { background: #e74c3c; color: #fff; }

    /* ── Bank detail row ─────────────────────────────────────── */
    .bank-row {
        display:       flex;
        align-items:   flex-start;
        gap:           1rem;
        padding:       .9rem 1.4rem;
        border-bottom: 1px solid #f5f7f9;
    }
    .bank-row:last-child  { border-bottom: none; }
    .bank-row-label {
        font-size:      .78rem;
        font-weight:    600;
        color:          #8899aa;
        min-width:      170px;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding-top:    .1rem;
    }
    .bank-row-value {
        font-size:   .875rem;
        color:       #0D1B2A;
        font-weight: 500;
    }
</style>
@endpush

@section('content')

    @php
        /* ── Dummy owner data ─────────────────────────────────── */
        $owner = [
            'id'            => 'PO-001',
            'name'          => 'Rahul Sharma',
            'email'         => 'rahul@gmail.com',
            'phone'         => '9876543210',
            'status'        => 'approved',
            'color'         => '0F3D56',
            'initial'       => 'R',
            'full_name'     => 'Rahul Sharma',
            'mobile'        => '9876543210',
            'business_name' => 'Modern Parking Solutions',
            'address'       => 'Connaught Place, New Delhi, Delhi 110001',
            'gst_number'    => '07ABCDE1234F1Z',
            'pan_number'    => '0TAC001234F2',
            'status_label'  => 'Approved',
            'registered_on' => '20 May, 2024, 10:30 AM',
            'parkings_count'=> 5,
        ];

        $documents = [
            ['name' => 'PAN Card',       'type' => 'PDF',   'size' => '320 KB', 'icon' => 'bi-file-earmark-person'],
            ['name' => 'Aadhar Card',    'type' => 'Image', 'size' => '1.2 MB', 'icon' => 'bi-file-earmark-image'],
            ['name' => 'Business Proof', 'type' => 'PDF',   'size' => '540 KB', 'icon' => 'bi-file-earmark-text'],
        ];

        $bankDetails = [
            'account_name'   => 'Rahul Sharma',
            'bank_name'      => 'HDFC Bank',
            'account_number' => '●●●● ●●●● 4291',
            'ifsc_code'      => 'HDFC0001234',
            'account_type'   => 'Current',
            'branch'         => 'Connaught Place, New Delhi',
        ];

        $parkings = [
            [
                'id'        => 'PK-001',
                'name'      => 'Connaught Place Parking',
                'address'   => 'Connaught Place, New Delhi',
                'slots'     => 50,
                'available' => 12,
                'revenue'   => '₹4,56,780',
                'status'    => 'active',
            ],
            [
                'id'        => 'PK-002',
                'name'      => 'Karol Bagh Parking',
                'address'   => 'Karol Bagh, New Delhi',
                'slots'     => 30,
                'available' => 8,
                'revenue'   => '₹2,18,400',
                'status'    => 'active',
            ],
            [
                'id'        => 'PK-003',
                'name'      => 'Lajpat Nagar Parking',
                'address'   => 'Lajpat Nagar, New Delhi',
                'slots'     => 40,
                'available' => 0,
                'revenue'   => '₹1,92,000',
                'status'    => 'inactive',
            ],
            [
                'id'        => 'PK-004',
                'name'      => 'Saket Mall Parking',
                'address'   => 'Saket, New Delhi',
                'slots'     => 80,
                'available' => 25,
                'revenue'   => '₹5,80,120',
                'status'    => 'active',
            ],
            [
                'id'        => 'PK-005',
                'name'      => 'Dwarka Sector 10',
                'address'   => 'Dwarka, New Delhi',
                'slots'     => 60,
                'available' => 18,
                'revenue'   => '₹3,10,500',
                'status'    => 'active',
            ],
        ];
    @endphp

    {{-- ── Breadcrumb + back ──────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Parking Owner Details
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Parking Owners</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">{{ $owner['name'] }}</li>
                </ol>
            </nav>
        </div>

        <a
            href="#"
            class="d-inline-flex align-items-center gap-2"
            style="
                height:          36px;
                padding:         0 1rem;
                border:          1px solid #e2e8ee;
                border-radius:   8px;
                background:      #fff;
                font-size:       .84rem;
                font-weight:     600;
                color:           #0D1B2A;
                text-decoration: none;
                transition:      background .15s;
            "
            onmouseover="this.style.background='#f0f3f7'"
            onmouseout="this.style.background='#fff'"
        >
            <i class="bi bi-arrow-left" style="font-size:.85rem;"></i>
            Back to List
        </a>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Profile Header Card
    ══════════════════════════════════════════════════════════ --}}
    <div class="profile-header mb-4">
        <div class="d-flex align-items-start flex-wrap gap-3">

            {{-- Avatar --}}
            <div class="owner-avatar-lg">
                {{ $owner['initial'] }}
            </div>

            {{-- Name, email, phone --}}
            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h5 class="mb-0" style="font-weight:700; color:#0D1B2A;">
                        {{ $owner['name'] }}
                    </h5>
                    <span class="status-badge badge-{{ $owner['status'] }}">
                        {{ $owner['status_label'] }}
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:.84rem; color:#5A6A7A;">
                    <span>
                        <i class="bi bi-envelope me-1" style="font-size:.8rem;"></i>
                        {{ $owner['email'] }}
                    </span>
                    <span>
                        <i class="bi bi-telephone me-1" style="font-size:.8rem;"></i>
                        {{ $owner['phone'] }}
                    </span>
                    <span>
                        <i class="bi bi-calendar3 me-1" style="font-size:.8rem;"></i>
                        Registered: {{ $owner['registered_on'] }}
                    </span>
                    <span>
                        <i class="bi bi-p-square me-1" style="font-size:.8rem;"></i>
                        ID: <strong style="color:#0D1B2A;">{{ $owner['id'] }}</strong>
                    </span>
                </div>
            </div>

            {{-- Approve / Reject + Edit --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn-approve">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
                <button type="button" class="btn-reject">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
                <a
                    href="#"
                    class="d-inline-flex align-items-center gap-1"
                    style="
                        height:          34px;
                        padding:         0 1rem;
                        border:          1px solid #e2e8ee;
                        border-radius:   8px;
                        background:      #fff;
                        font-size:       .83rem;
                        font-weight:     600;
                        color:           #0D1B2A;
                        text-decoration: none;
                        transition:      background .15s;
                    "
                    onmouseover="this.style.background='#f0f3f7'"
                    onmouseout="this.style.background='#fff'"
                >
                    <i class="bi bi-pencil" style="font-size:.8rem;"></i> Edit
                </a>
            </div>

        </div>

        {{-- ── Quick stats row ──────────────────────────────── --}}
        <div class="row g-3 mt-3 pt-3" style="border-top:1px solid #f0f3f7;">
            @php
                $stats = [
                    ['label' => 'Total Parkings', 'value' => '5',          'icon' => 'bi-signpost-2',      'color' => '#0F3D56'],
                    ['label' => 'Total Slots',     'value' => '260',        'icon' => 'bi-grid-3x3-gap',    'color' => '#2ecc71'],
                    ['label' => 'Total Bookings',  'value' => '1,284',      'icon' => 'bi-calendar2-check', 'color' => '#3490dc'],
                    ['label' => 'Total Revenue',   'value' => '₹17,57,800', 'icon' => 'bi-currency-rupee',  'color' => '#f59e0b'],
                ];
            @endphp
            @foreach ($stats as $s)
                <div class="col-6 col-sm-3">
                    <div
                        class="d-flex align-items-center gap-2"
                        style="
                            background:    #f8f9fa;
                            border-radius: 10px;
                            padding:       .75rem 1rem;
                            border:        1px solid #f0f3f7;
                        "
                    >
                        <div style="
                            width:           36px;
                            height:          36px;
                            border-radius:   9px;
                            background:      {{ $s['color'] }}20;
                            display:         flex;
                            align-items:     center;
                            justify-content: center;
                            flex-shrink:     0;
                        ">
                            <i class="bi {{ $s['icon'] }}" style="color:{{ $s['color'] }}; font-size:.95rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.72rem; color:#8899aa; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                                {{ $s['label'] }}
                            </div>
                            <div style="font-size:1rem; font-weight:700; color:#0D1B2A; line-height:1.2;">
                                {{ $s['value'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>{{-- /profile-header --}}

    {{-- ══════════════════════════════════════════════════════════
         Tab Navigation
    ══════════════════════════════════════════════════════════ --}}
    <ul class="nav owner-tabs mb-3 flex-nowrap overflow-auto pb-1" id="ownerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                id="tab-details"
                data-bs-toggle="tab"
                data-bs-target="#pane-details"
                type="button"
                role="tab"
                aria-selected="true"
            >
                <i class="bi bi-person-lines-fill me-1"></i> Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="tab-documents"
                data-bs-toggle="tab"
                data-bs-target="#pane-documents"
                type="button"
                role="tab"
                aria-selected="false"
            >
                <i class="bi bi-file-earmark-text me-1"></i> Documents
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="tab-bank"
                data-bs-toggle="tab"
                data-bs-target="#pane-bank"
                type="button"
                role="tab"
                aria-selected="false"
            >
                <i class="bi bi-bank me-1"></i> Bank Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="tab-parkings"
                data-bs-toggle="tab"
                data-bs-target="#pane-parkings"
                type="button"
                role="tab"
                aria-selected="false"
            >
                <i class="bi bi-signpost-2 me-1"></i>
                Parkings
                <span
                    class="ms-1"
                    style="
                        background:      #0F3D56;
                        color:           #fff;
                        font-size:       .68rem;
                        font-weight:     700;
                        border-radius:   20px;
                        padding:         .15em .55em;
                        vertical-align:  middle;
                    "
                >{{ $owner['parkings_count'] }}</span>
            </button>
        </li>
    </ul>

    {{-- ══════════════════════════════════════════════════════════
         Tab Panes
    ══════════════════════════════════════════════════════════ --}}
    <div class="tab-content" id="ownerTabContent">

        {{-- ────────────────────────────────────────────────────
             TAB 1 — Details
        ──────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="pane-details" role="tabpanel">
            <div class="page-card">

                <div class="section-title">Personal Information</div>

                <div class="row g-0">

                    {{-- Left column --}}
                    <div class="col-12 col-md-6" style="border-right:1px solid #f5f7f9;">
                        @php
                            $personalLeft = [
                                ['label' => 'Full Name',     'value' => $owner['full_name']],
                                ['label' => 'Email Address', 'value' => $owner['email']],
                                ['label' => 'Mobile Number', 'value' => $owner['mobile']],
                                ['label' => 'Business Name', 'value' => $owner['business_name']],
                            ];
                        @endphp
                        @foreach ($personalLeft as $row)
                            <div style="padding:.85rem 1.4rem; border-bottom:1px solid #f5f7f9;">
                                <div class="info-label">{{ $row['label'] }}</div>
                                <div class="info-value">{{ $row['value'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Right column --}}
                    <div class="col-12 col-md-6">
                        @php
                            $personalRight = [
                                ['label' => 'Address',    'value' => $owner['address']],
                                ['label' => 'GST Number', 'value' => $owner['gst_number']],
                                ['label' => 'PAN Number', 'value' => $owner['pan_number']],
                                ['label' => 'Status',     'value' => $owner['status_label']],
                            ];
                        @endphp
                        @foreach ($personalRight as $row)
                            <div style="padding:.85rem 1.4rem; border-bottom:1px solid #f5f7f9;">
                                <div class="info-label">{{ $row['label'] }}</div>
                                @if ($row['label'] === 'Status')
                                    <span class="status-badge badge-{{ $owner['status'] }}">
                                        {{ $row['value'] }}
                                    </span>
                                @else
                                    <div class="info-value">{{ $row['value'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- Registered on — full-width row --}}
                <div style="padding:.85rem 1.4rem;">
                    <div class="info-label">Registered On</div>
                    <div class="info-value">{{ $owner['registered_on'] }}</div>
                </div>

            </div>
        </div>{{-- /pane-details --}}

        {{-- ────────────────────────────────────────────────────
             TAB 2 — Documents
        ──────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="pane-documents" role="tabpanel">
            <div class="page-card">

                <div class="section-title">Uploaded Documents</div>

                <div class="p-4">
                    @if (count($documents) === 0)
                        <div class="text-center py-5" style="color:#8899aa;">
                            <i class="bi bi-file-earmark-x" style="font-size:2.5rem; display:block; margin-bottom:.75rem;"></i>
                            <p class="mb-0" style="font-size:.875rem;">No documents uploaded yet.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($documents as $doc)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="doc-card">
                                        <div class="doc-icon">
                                            <i class="bi {{ $doc['icon'] }}"></i>
                                        </div>
                                        <div style="min-width:0;">
                                            <div style="
                                                font-size:      .87rem;
                                                font-weight:    600;
                                                color:          #0D1B2A;
                                                white-space:    nowrap;
                                                overflow:       hidden;
                                                text-overflow:  ellipsis;
                                            ">
                                                {{ $doc['name'] }}
                                            </div>
                                            <div style="font-size:.74rem; color:#8899aa; margin-top:.15rem;">
                                                {{ $doc['type'] }} &middot; {{ $doc['size'] }}
                                            </div>
                                        </div>
                                        <a href="#" class="doc-view-btn">
                                            <i class="bi bi-eye me-1" style="font-size:.75rem;"></i>View
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>{{-- /pane-documents --}}

        {{-- ────────────────────────────────────────────────────
             TAB 3 — Bank Details
        ──────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="pane-bank" role="tabpanel">
            <div class="page-card">

                <div class="section-title">Bank Account Information</div>

                <div class="bank-row">
                    <div class="bank-row-label">Account Holder Name</div>
                    <div class="bank-row-value">{{ $bankDetails['account_name'] }}</div>
                </div>
                <div class="bank-row">
                    <div class="bank-row-label">Bank Name</div>
                    <div class="bank-row-value">{{ $bankDetails['bank_name'] }}</div>
                </div>
                <div class="bank-row">
                    <div class="bank-row-label">Account Number</div>
                    <div class="bank-row-value" style="font-family:monospace; letter-spacing:.08em;">
                        {{ $bankDetails['account_number'] }}
                    </div>
                </div>
                <div class="bank-row">
                    <div class="bank-row-label">IFSC Code</div>
                    <div class="bank-row-value" style="font-family:monospace; letter-spacing:.08em;">
                        {{ $bankDetails['ifsc_code'] }}
                    </div>
                </div>
                <div class="bank-row">
                    <div class="bank-row-label">Account Type</div>
                    <div class="bank-row-value">{{ $bankDetails['account_type'] }}</div>
                </div>
                <div class="bank-row">
                    <div class="bank-row-label">Branch</div>
                    <div class="bank-row-value">{{ $bankDetails['branch'] }}</div>
                </div>

            </div>
        </div>{{-- /pane-bank --}}

        {{-- ────────────────────────────────────────────────────
             TAB 4 — Parkings
        ──────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="pane-parkings" role="tabpanel">
            <div class="page-card">

                <div class="section-title">Registered Parkings ({{ count($parkings) }})</div>

                <div class="table-responsive">
                    <table class="table parkings-table mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Parking Name</th>
                                <th>Address</th>
                                <th style="text-align:center;">Total Slots</th>
                                <th style="text-align:center;">Available</th>
                                <th>Total Revenue</th>
                                <th style="width:100px;">Status</th>
                                <th style="width:90px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($parkings as $p)
                                <tr>
                                    {{-- ID --}}
                                    <td>
                                        <span style="font-size:.78rem; font-weight:700; color:#0F3D56;">
                                            {{ $p['id'] }}
                                        </span>
                                    </td>

                                    {{-- Name --}}
                                    <td>
                                        <div style="font-weight:600;">{{ $p['name'] }}</div>
                                    </td>

                                    {{-- Address --}}
                                    <td style="color:#5A6A7A; font-size:.82rem;">
                                        <i class="bi bi-geo-alt" style="font-size:.75rem; margin-right:3px;"></i>
                                        {{ $p['address'] }}
                                    </td>

                                    {{-- Total slots --}}
                                    <td style="text-align:center;">
                                        <span style="font-weight:600;">{{ $p['slots'] }}</span>
                                    </td>

                                    {{-- Available --}}
                                    <td style="text-align:center;">
                                        <span style="
                                            font-weight: 600;
                                            color: {{ $p['available'] === 0 ? '#e74c3c' : '#1aaa5a' }};
                                        ">
                                            {{ $p['available'] }}
                                        </span>
                                    </td>

                                    {{-- Revenue --}}
                                    <td style="font-weight:600; color:#0D1B2A;">
                                        {{ $p['revenue'] }}
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if ($p['status'] === 'active')
                                            <span class="status-badge badge-approved">Active</span>
                                        @else
                                            <span class="status-badge badge-rejected">Inactive</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="#" class="action-btn action-btn-view" title="View Parking">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="#" class="action-btn action-btn-edit" title="Edit Parking">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>{{-- /pane-parkings --}}

    </div>{{-- /tab-content --}}

@endsection