{{-- ============================================================
     Add Parking — Step 4: Images
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Image upload step in the Add Parking multi-step
               wizard. Handles drag & drop, previews, cover
               image selection and upload guidelines.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Add Parking — Images')
@section('page-title', 'Add Parking')

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
        padding:        1rem 1.5rem;
        box-shadow:     0 2px 12px rgba(15,61,86,.06);
        overflow-x:     auto;
        flex-wrap:      nowrap;
        margin-bottom:  1.75rem;
    }
    .wizard-step {
        display:         flex;
        align-items:     center;
        gap:             .55rem;
        flex-shrink:     0;
        text-decoration: none;
    }
    .wizard-step-num {
        width:           30px;
        height:          30px;
        border-radius:   50%;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       .78rem;
        font-weight:     700;
        flex-shrink:     0;
        transition:      background .2s, color .2s;
    }
    .wizard-step-num.done {
        background: #2ecc71;
        color:      #fff;
    }
    .wizard-step-num.active {
        background: #0F3D56;
        color:      #fff;
    }
    .wizard-step-num.pending {
        background: #f0f3f7;
        color:      #b0bec5;
    }
    .wizard-step-label {
        font-size:   .82rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .wizard-step-label.done   { color: #2ecc71; }
    .wizard-step-label.active { color: #0F3D56; }
    .wizard-step-label.pending{ color: #b0bec5; }
    .wizard-divider {
        flex:         1;
        min-width:    24px;
        height:       2px;
        border-radius:1px;
        margin:       0 .5rem;
        transition:   background .2s;
    }
    .wizard-divider.done   { background: #2ecc71; }
    .wizard-divider.pending{ background: #e2e8ee; }

    /* ── Page card shell ─────────────────────────────────────── */
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
        line-height: 1.2;
    }
    .form-card-subtitle {
        font-size:  .76rem;
        color:      #8899aa;
        margin:     .15rem 0 0;
    }
    .form-card-body { padding: 1.4rem; }

    /* ── Upload zone ─────────────────────────────────────────── */
    .upload-zone {
        border:         2px dashed #c8d2dc;
        border-radius:  14px;
        padding:        3rem 1.5rem;
        text-align:     center;
        background:     #fafbfc;
        cursor:         pointer;
        transition:     border-color .2s, background .2s;
        position:       relative;
    }
    .upload-zone.dragover {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.04);
    }
    .upload-zone:hover {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.03);
    }
    .upload-zone-icon {
        width:           72px;
        height:          72px;
        border-radius:   18px;
        background:      rgba(15,61,86,.07);
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       2rem;
        color:           #0F3D56;
        margin:          0 auto 1rem;
    }
    .btn-browse {
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        margin-top:    1rem;
        height:        38px;
        padding:       0 1.25rem;
        border:        2px solid #0F3D56;
        border-radius: 8px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .855rem;
        font-weight:   600;
        cursor:        pointer;
        transition:    background .15s;
    }
    .btn-browse:hover { background: #0a2f42; }

    /* ── Image preview grid ──────────────────────────────────── */
    .img-grid {
        display:               grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap:                   1rem;
    }

    /* ── Single image card ───────────────────────────────────── */
    .img-card {
        border:        1px solid #e2e8ee;
        border-radius: 12px;
        overflow:      hidden;
        position:      relative;
        background:    #fff;
        transition:    box-shadow .18s, border-color .18s;
    }
    .img-card:hover {
        box-shadow:   0 4px 16px rgba(15,61,86,.12);
        border-color: #c8d2dc;
    }
    .img-card.is-cover {
        border-color: #0F3D56;
        box-shadow:   0 0 0 3px rgba(15,61,86,.12);
    }

    /* Thumbnail area */
    .img-thumb {
        height:          140px;
        display:         flex;
        align-items:     center;
        justify-content: center;
        font-size:       2.5rem;
        position:        relative;
        overflow:        hidden;
    }

    /* Cover badge */
    .cover-badge {
        position:      absolute;
        top:           8px;
        left:          8px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .65rem;
        font-weight:   700;
        border-radius: 6px;
        padding:       .2em .55em;
        letter-spacing:.02em;
        text-transform:uppercase;
        z-index:       2;
    }

    /* Set cover radio overlay */
    .cover-radio-wrap {
        position:  absolute;
        top:       8px;
        right:     8px;
        z-index:   2;
    }
    .cover-radio-wrap input[type="radio"] {
        width:        18px;
        height:       18px;
        accent-color: #0F3D56;
        cursor:       pointer;
    }

    /* Remove button */
    .img-remove-btn {
        position:        absolute;
        bottom:          8px;
        right:           8px;
        width:           28px;
        height:          28px;
        border-radius:   7px;
        background:      rgba(231,76,60,.85);
        border:          none;
        color:           #fff;
        font-size:       .75rem;
        display:         flex;
        align-items:     center;
        justify-content: center;
        cursor:          pointer;
        transition:      background .15s;
        z-index:         2;
    }
    .img-remove-btn:hover { background: #c0392b; }

    /* Card footer */
    .img-card-footer {
        padding:         .55rem .75rem;
        border-top:      1px solid #f0f3f7;
        background:      #fafbfc;
    }
    .img-filename {
        font-size:      .78rem;
        font-weight:    600;
        color:          #0D1B2A;
        white-space:    nowrap;
        overflow:       hidden;
        text-overflow:  ellipsis;
    }
    .img-filesize {
        font-size: .7rem;
        color:     #8899aa;
    }

    /* ── Add more card ───────────────────────────────────────── */
    .img-add-card {
        border:          2px dashed #c8d2dc;
        border-radius:   12px;
        height:          195px;
        display:         flex;
        flex-direction:  column;
        align-items:     center;
        justify-content: center;
        gap:             .4rem;
        cursor:          pointer;
        background:      #fafbfc;
        transition:      border-color .15s, background .15s;
    }
    .img-add-card:hover {
        border-color: #0F3D56;
        background:   rgba(15,61,86,.03);
    }

    /* ── Guideline card ──────────────────────────────────────── */
    .guideline-card {
        background:    #e8f4fb;
        border:        1px solid #b8ddf0;
        border-radius: 12px;
        padding:       1.1rem 1.25rem;
    }
    .guideline-item {
        display:     flex;
        align-items: flex-start;
        gap:         .6rem;
        font-size:   .855rem;
        color:       #1a5276;
        padding:     .3rem 0;
    }
    .guideline-item i {
        font-size:  .9rem;
        color:      #2e86ab;
        flex-shrink:0;
        margin-top: .1rem;
    }

    /* ── Bottom action bar ───────────────────────────────────── */
    .action-bar {
        position:       sticky;
        bottom:         0;
        background:     #fff;
        border-top:     1px solid #e2e8ee;
        padding:        1rem 1.75rem;
        display:        flex;
        align-items:    center;
        justify-content:space-between;
        gap:            .75rem;
        z-index:        100;
        box-shadow:     0 -2px 12px rgba(15,61,86,.06);
        margin:         0 -1.75rem -2.5rem;
        flex-wrap:      wrap;
    }
    .btn-prev {
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
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        text-decoration:none;
    }
    .btn-prev:hover { background: #f0f3f7; color: #0D1B2A; }

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
        gap:           .4rem;
    }
    .btn-draft:hover { background: #0F3D56; color: #fff; }

    .btn-next {
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
        gap:           .4rem;
    }
    .btn-next:hover { background: #0a2f42; }

    /* ── Upload counter badge ────────────────────────────────── */
    .upload-counter {
        display:        inline-flex;
        align-items:    center;
        gap:            .4rem;
        background:     rgba(15,61,86,.07);
        border-radius:  20px;
        padding:        .3rem .85rem;
        font-size:      .8rem;
        font-weight:    600;
        color:          #0F3D56;
    }
</style>
@endpush

@section('content')

    {{-- ── Breadcrumb ──────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">Add New Parking</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" style="color:#0F3D56; text-decoration:none;">Parkings</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#8899aa;">Add Parking — Images</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Wizard Step Bar
    ══════════════════════════════════════════════════════════ --}}
    <div class="wizard-bar">

        @php
            $steps = [
                ['num' => 1, 'label' => 'Basic Info',    'state' => 'done'],
                ['num' => 2, 'label' => 'Pricing',       'state' => 'done'],
                ['num' => 3, 'label' => 'Facilities',    'state' => 'done'],
                ['num' => 4, 'label' => 'Images',        'state' => 'active'],
                ['num' => 5, 'label' => 'Location',      'state' => 'pending'],
            ];
        @endphp

        @foreach ($steps as $i => $step)
            <div class="wizard-step">
                <div class="wizard-step-num {{ $step['state'] }}">
                    @if ($step['state'] === 'done')
                        <i class="bi bi-check2" style="font-size:.85rem;"></i>
                    @else
                        {{ $step['num'] }}
                    @endif
                </div>
                <span class="wizard-step-label {{ $step['state'] }}">{{ $step['label'] }}</span>
            </div>
            @if ($i < count($steps) - 1)
                <div class="wizard-divider {{ $step['state'] === 'done' ? 'done' : 'pending' }}"></div>
            @endif
        @endforeach

    </div>

    {{-- ══════════════════════════════════════════════════════════
         Row: Upload Zone + Guidelines
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-4 align-items-start">

        {{-- ── Left col: upload + previews ────────────────────── --}}
        <div class="col-12 col-lg-8">

            {{-- ── Upload zone card ───────────────────────────── --}}
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <p class="form-card-title">Upload Parking Images</p>
                        <p class="form-card-subtitle">
                            Drag &amp; drop images or click Browse to select files
                        </p>
                    </div>
                    <div class="ms-auto">
                        <span class="upload-counter">
                            <i class="bi bi-images"></i>
                            4 / 10 uploaded
                        </span>
                    </div>
                </div>
                <div class="form-card-body">

                    {{-- Drop zone --}}
                    <div
                        class="upload-zone"
                        id="dropZone"
                        onclick="document.getElementById('fileInput').click()"
                        ondragover="event.preventDefault(); this.classList.add('dragover')"
                        ondragleave="this.classList.remove('dragover')"
                        ondrop="event.preventDefault(); this.classList.remove('dragover')"
                    >
                        <div class="upload-zone-icon">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </div>
                        <h6 style="font-weight:700; color:#0D1B2A; margin-bottom:.4rem;">
                            Drag &amp; Drop Images Here
                        </h6>
                        <p style="font-size:.855rem; color:#8899aa; margin-bottom:0;">
                            or click the button below to browse from your device
                        </p>
                        <button type="button" class="btn-browse" onclick="event.stopPropagation(); document.getElementById('fileInput').click()">
                            <i class="bi bi-folder2-open"></i>
                            Browse Files
                        </button>
                        <p style="font-size:.74rem; color:#b0bec5; margin-top:.75rem; margin-bottom:0;">
                            JPG, PNG, WEBP &nbsp;&middot;&nbsp; Max 5 MB per file &nbsp;&middot;&nbsp; Up to 10 images
                        </p>
                        <input
                            type="file"
                            id="fileInput"
                            multiple
                            accept="image/jpeg,image/png,image/webp"
                            hidden
                        >
                    </div>

                </div>
            </div>

            {{-- ── Image previews card ──────────────────────────── --}}
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-icon">
                        <i class="bi bi-grid"></i>
                    </div>
                    <div>
                        <p class="form-card-title">Uploaded Images</p>
                        <p class="form-card-subtitle">
                            Select one image as Cover · Click <i class="bi bi-trash3" style="font-size:.7rem;"></i> to remove
                        </p>
                    </div>
                </div>
                <div class="form-card-body">

                    @php
                        $images = [
                            [
                                'filename' => 'parking-entry.jpg',
                                'size'     => '2.1 MB',
                                'bg'       => '#d4eaf5',
                                'icon'     => 'bi-door-open-fill',
                                'color'    => '#1a6b8a',
                                'cover'    => true,
                            ],
                            [
                                'filename' => 'parking-slots-overview.jpg',
                                'size'     => '1.8 MB',
                                'bg'       => '#d4f0e2',
                                'icon'     => 'bi-grid-3x3-gap-fill',
                                'color'    => '#1a7a50',
                                'cover'    => false,
                            ],
                            [
                                'filename' => 'ev-charging-station.jpg',
                                'size'     => '1.3 MB',
                                'bg'       => '#fde8c8',
                                'icon'     => 'bi-ev-station-fill',
                                'color'    => '#c47d00',
                                'cover'    => false,
                            ],
                            [
                                'filename' => 'security-desk.jpg',
                                'size'     => '940 KB',
                                'bg'       => '#e8d4f5',
                                'icon'     => 'bi-shield-fill-check',
                                'color'    => '#6a3a9a',
                                'cover'    => false,
                            ],
                        ];
                    @endphp

                    <div class="img-grid">

                        @foreach ($images as $idx => $img)
                            <div class="img-card {{ $img['cover'] ? 'is-cover' : '' }}" id="imgCard{{ $idx }}">

                                {{-- Cover badge --}}
                                @if ($img['cover'])
                                    <div class="cover-badge">
                                        <i class="bi bi-star-fill me-1" style="font-size:.6rem;"></i>Cover
                                    </div>
                                @endif

                                {{-- Select as cover radio --}}
                                <div class="cover-radio-wrap" title="Set as cover image">
                                    <input
                                        type="radio"
                                        name="cover_image"
                                        value="{{ $idx }}"
                                        {{ $img['cover'] ? 'checked' : '' }}
                                        onchange="setCover({{ $idx }})"
                                    >
                                </div>

                                {{-- Thumbnail --}}
                                <div
                                    class="img-thumb"
                                    style="background:{{ $img['bg'] }};"
                                >
                                    <i class="bi {{ $img['icon'] }}" style="color:{{ $img['color'] }};"></i>
                                </div>

                                {{-- Remove button --}}
                                <button
                                    type="button"
                                    class="img-remove-btn"
                                    title="Remove image"
                                    onclick="removeImage({{ $idx }})"
                                >
                                    <i class="bi bi-trash3-fill"></i>
                                </button>

                                {{-- Footer --}}
                                <div class="img-card-footer">
                                    <div class="img-filename" title="{{ $img['filename'] }}">
                                        {{ $img['filename'] }}
                                    </div>
                                    <div class="img-filesize">{{ $img['size'] }}</div>
                                </div>

                            </div>
                        @endforeach

                        {{-- Add more placeholder --}}
                        <div
                            class="img-add-card"
                            onclick="document.getElementById('fileInput').click()"
                            title="Upload more images"
                        >
                            <i class="bi bi-plus-circle" style="font-size:1.75rem; color:#c8d2dc;"></i>
                            <span style="font-size:.78rem; color:#8899aa; font-weight:600;">Add More</span>
                            <span style="font-size:.72rem; color:#b0bec5;">6 slots remaining</span>
                        </div>

                    </div>

                    {{-- Cover selection note --}}
                    <div
                        class="d-flex align-items-center gap-2 mt-3 p-3"
                        style="background:#f8f9fa; border-radius:9px; border:1px solid #f0f3f7;"
                    >
                        <i class="bi bi-info-circle" style="color:#0F3D56; flex-shrink:0;"></i>
                        <span style="font-size:.8rem; color:#5A6A7A;">
                            Use the <strong style="color:#0D1B2A;">radio button</strong> on each image to set it as the
                            <strong style="color:#0D1B2A;">Cover Image</strong>.
                            The cover image appears as the primary photo in search results.
                        </span>
                    </div>

                </div>
            </div>

        </div>

        {{-- ── Right col: guidelines + summary ────────────────── --}}
        <div class="col-12 col-lg-4">

            {{-- Upload guidelines --}}
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-icon">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <p class="form-card-title">Upload Guidelines</p>
                        <p class="form-card-subtitle">Follow these for best results</p>
                    </div>
                </div>
                <div class="form-card-body pt-1 pb-1">
                    <div class="guideline-card">
                        <div class="guideline-item">
                            <i class="bi bi-file-earmark-image"></i>
                            <span><strong>Formats:</strong> JPG, PNG, WEBP only</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-hdd"></i>
                            <span><strong>Max file size:</strong> 5 MB per image</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-images"></i>
                            <span><strong>Max images:</strong> 10 images per parking</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-aspect-ratio"></i>
                            <span><strong>Recommended:</strong> 1200 × 800 pixels</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-sun"></i>
                            <span><strong>Quality:</strong> Use well-lit, clear photos</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-star"></i>
                            <span><strong>Cover:</strong> Pick your best shot as cover</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-x-circle text-danger"></i>
                            <span><strong>Avoid:</strong> Blurry, dark or irrelevant images</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload progress summary --}}
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-icon">
                        <i class="bi bi-bar-chart-steps"></i>
                    </div>
                    <div>
                        <p class="form-card-title">Upload Summary</p>
                        <p class="form-card-subtitle">Current session</p>
                    </div>
                </div>
                <div class="form-card-body">

                    {{-- Progress bar --}}
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size:.78rem; font-weight:600; color:#5A6A7A;">Images Uploaded</span>
                        <span style="font-size:.8rem; font-weight:700; color:#0F3D56;">4 / 10</span>
                    </div>
                    <div class="progress mb-3" style="height:7px; border-radius:4px; background:#f0f3f7;">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            style="width:40%; background:#0F3D56; border-radius:4px;"
                            aria-valuenow="4"
                            aria-valuemin="0"
                            aria-valuemax="10"
                        ></div>
                    </div>

                    {{-- Stats --}}
                    @php
                        $summaryStats = [
                            ['label' => 'Total Size',      'value' => '6.1 MB',  'icon' => 'bi-hdd',              'color' => '#0F3D56'],
                            ['label' => 'Cover Image',     'value' => 'Set ✓',   'icon' => 'bi-star-fill',        'color' => '#2ecc71'],
                            ['label' => 'Slots Remaining', 'value' => '6 left',  'icon' => 'bi-plus-circle',      'color' => '#f59e0b'],
                            ['label' => 'Image Types',     'value' => 'JPG, PNG','icon' => 'bi-file-earmark-image','color' => '#3490dc'],
                        ];
                    @endphp
                    <div class="row g-2">
                        @foreach ($summaryStats as $s)
                            <div class="col-6">
                                <div
                                    style="
                                        background:    #f8f9fa;
                                        border:        1px solid #f0f3f7;
                                        border-radius: 9px;
                                        padding:       .65rem .75rem;
                                    "
                                >
                                    <div style="font-size:.7rem; color:#8899aa; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                                        {{ $s['label'] }}
                                    </div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <i class="bi {{ $s['icon'] }}" style="color:{{ $s['color'] }}; font-size:.8rem;"></i>
                                        <span style="font-size:.84rem; font-weight:700; color:#0D1B2A;">{{ $s['value'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- Tips card --}}
            <div
                style="
                    background:    #fff8e8;
                    border:        1px solid #fde8b0;
                    border-radius: 12px;
                    padding:       1rem 1.1rem;
                "
            >
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-lightbulb-fill" style="color:#f59e0b; font-size:1rem;"></i>
                    <span style="font-weight:700; font-size:.855rem; color:#92600a;">Pro Tips</span>
                </div>
                <ul style="font-size:.8rem; color:#92600a; margin:0; padding-left:1.1rem; line-height:1.8;">
                    <li>Upload photos from multiple angles</li>
                    <li>Include entry gate &amp; signage photos</li>
                    <li>Show EV chargers if available</li>
                    <li>Daytime photos look better in listings</li>
                </ul>
            </div>

        </div>

    </div>{{-- /row --}}

    {{-- ══════════════════════════════════════════════════════════
         Bottom Action Bar
    ══════════════════════════════════════════════════════════ --}}
    <div class="action-bar">

        {{-- Left: Previous --}}
        <a href="#" class="btn-prev">
            <i class="bi bi-arrow-left"></i> Previous
        </a>

        {{-- Right: Draft + Next --}}
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-draft">
                <i class="bi bi-floppy"></i> Save Draft
            </button>
            <button type="button" class="btn-next">
                Next <i class="bi bi-arrow-right"></i>
            </button>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    // ── Set cover image ────────────────────────────────────────
    function setCover(selectedIdx) {
        document.querySelectorAll('.img-card').forEach(function (card, idx) {
            const badge = card.querySelector('.cover-badge');
            if (idx === selectedIdx) {
                card.classList.add('is-cover');
                if (!badge) {
                    const b = document.createElement('div');
                    b.className = 'cover-badge';
                    b.innerHTML = '<i class="bi bi-star-fill me-1" style="font-size:.6rem;"></i>Cover';
                    card.prepend(b);
                }
            } else {
                card.classList.remove('is-cover');
                if (badge) badge.remove();
            }
        });
    }

    // ── Remove image card ──────────────────────────────────────
    function removeImage(idx) {
        const card = document.getElementById('imgCard' + idx);
        if (card) {
            card.style.opacity = '0';
            card.style.transition = 'opacity .25s';
            setTimeout(function () { card.remove(); }, 250);
        }
    }

    // ── Drag & drop visual feedback ────────────────────────────
    const zone = document.getElementById('dropZone');
    if (zone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            zone.addEventListener(evt, function () {
                zone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            zone.addEventListener(evt, function () {
                zone.classList.remove('dragover');
            });
        });
    }
</script>
@endpush