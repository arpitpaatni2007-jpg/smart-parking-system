{{-- ============================================================
     Parking Owners — Index
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  List all parking owners with search, filter,
               status badges and CRUD action buttons.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Parking Owners')
@section('page-title', 'Parking Owners')

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
    .page-card-header {
        padding:       1.1rem 1.4rem;
        border-bottom: 1px solid #f0f3f7;
        background:    #fafbfc;
    }

    /* ── Search input ────────────────────────────────────────── */
    .search-wrap {
        position: relative;
        max-width: 280px;
        width:     100%;
    }
    .search-wrap .bi-search {
        position:  absolute;
        left:      .85rem;
        top:       50%;
        transform: translateY(-50%);
        color:     #8899aa;
        font-size: .85rem;
        pointer-events: none;
    }
    .search-wrap input {
        padding-left:  2.2rem;
        border-radius: 8px;
        border:        1px solid #e2e8ee;
        font-size:     .855rem;
        height:        38px;
        color:         #0D1B2A;
        width:         100%;
        outline:       none;
        transition:    border-color .18s;
    }
    .search-wrap input:focus {
        border-color: #0F3D56;
        box-shadow:   none;
    }

    /* ── Buttons ─────────────────────────────────────────────── */
    .btn-filter {
        height:        38px;
        border:        1px solid #e2e8ee;
        border-radius: 8px;
        background:    #fff;
        color:         #0D1B2A;
        font-size:     .855rem;
        font-weight:   600;
        padding:       0 1rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s, border-color .15s;
        white-space:   nowrap;
    }
    .btn-filter:hover {
        background:    #f0f3f7;
        border-color:  #c8d2dc;
    }
    .btn-add {
        height:        38px;
        border:        none;
        border-radius: 8px;
        background:    #0F3D56;
        color:         #fff;
        font-size:     .855rem;
        font-weight:   600;
        padding:       0 1.1rem;
        display:       inline-flex;
        align-items:   center;
        gap:           .4rem;
        transition:    background .15s;
        white-space:   nowrap;
    }
    .btn-add:hover { background: #0a2f42; color: #fff; }

    /* ── Table ───────────────────────────────────────────────── */
    .owners-table thead th {
        font-size:      .75rem;
        font-weight:    600;
        color:          #8899aa;
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom:  1px solid #f0f3f7 !important;
        border-top:     none !important;
        background:     #fafbfc;
        padding:        .75rem 1rem;
        white-space:    nowrap;
    }
    .owners-table tbody td {
        font-size:      .86rem;
        padding:        .85rem 1rem;
        color:          #0D1B2A;
        border-bottom:  1px solid #f5f7f9;
        vertical-align: middle;
    }
    .owners-table tbody tr:last-child td { border-bottom: none; }
    .owners-table tbody tr:hover td      { background: #fafcff; }

    /* ── Avatar initial ─────────────────────────────────────── */
    .owner-avatar {
        width:         36px;
        height:        36px;
        border-radius: 10px;
        display:       inline-flex;
        align-items:   center;
        justify-content: center;
        font-size:     .8rem;
        font-weight:   700;
        color:         #fff;
        flex-shrink:   0;
    }

    /* ── Status badges ───────────────────────────────────────── */
    .status-badge {
        display:       inline-block;
        padding:       .3em .8em;
        border-radius: 20px;
        font-size:     .72rem;
        font-weight:   600;
        letter-spacing: .02em;
    }
    .badge-approved { background: rgba(46,204,113,.14); color: #1aaa5a; }
    .badge-pending  { background: rgba(255,165,0,.15);  color: #c47d00; }
    .badge-rejected { background: rgba(231,76,60,.12);  color: #c0392b; }

    /* ── Action buttons ──────────────────────────────────────── */
    .action-btn {
        display:        inline-flex;
        align-items:    center;
        justify-content: center;
        width:          30px;
        height:         30px;
        border-radius:  7px;
        border:         1px solid transparent;
        font-size:      .82rem;
        cursor:         pointer;
        transition:     background .15s, border-color .15s, color .15s;
        text-decoration: none;
    }
    .action-btn-view   { background: rgba(15,61,86,.08);  color: #0F3D56; }
    .action-btn-view:hover  { background: #0F3D56; color: #fff; border-color: #0F3D56; }

    .action-btn-edit   { background: rgba(52,144,220,.1); color: #3490dc; }
    .action-btn-edit:hover  { background: #3490dc; color: #fff; border-color: #3490dc; }

    .action-btn-delete { background: rgba(231,76,60,.1);  color: #e74c3c; }
    .action-btn-delete:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination .page-link {
        border-radius: 7px !important;
        margin:        0 2px;
        font-size:     .83rem;
        color:         #0F3D56;
        border:        1px solid #e2e8ee;
        padding:       .38rem .7rem;
        transition:    background .15s, color .15s;
    }
    .pagination .page-link:hover    { background: #f0f3f7; border-color: #c8d2dc; color: #0F3D56; }
    .pagination .page-item.active .page-link {
        background:    #0F3D56;
        border-color:  #0F3D56;
        color:         #fff;
    }
    .pagination .page-item.disabled .page-link { color: #c0c8d0; }

    /* ── Empty / results meta ────────────────────────────────── */
    .results-meta {
        font-size: .8rem;
        color:     #8899aa;
    }
</style>
@endpush

@section('content')

    {{-- ── Page heading ───────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1" style="color:#0D1B2A; font-weight:700;">
                Parking Owners
            </h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Manage all registered parking owners and their approval status.
            </p>
        </div>
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:.8rem;">
                <li class="breadcrumb-item">
                    <a href="#" style="color:#0F3D56; text-decoration:none;">Home</a>
                </li>
                <li class="breadcrumb-item active" style="color:#8899aa;">Parking Owners</li>
            </ol>
        </nav>
    </div>

    {{-- ── Main card ───────────────────────────────────────────── --}}
    <div class="page-card">

        {{-- ── Toolbar ───────────────────────────────────────── --}}
        <div class="page-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">

            {{-- Left: search + filter --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Search --}}
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        placeholder="Search owners…"
                        aria-label="Search parking owners"
                    >
                </div>

                {{-- Filter button --}}
                <button class="btn-filter" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterPanel"
                    aria-expanded="false"
                    aria-controls="filterPanel"
                >
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>

            {{-- Right: Add Owner --}}
            <a href="#" class="btn-add">
                <i class="bi bi-plus-lg"></i> Add Owner
            </a>

        </div>{{-- /toolbar --}}

        {{-- ── Filter panel (collapsible) ─────────────────────── --}}
        <div class="collapse" id="filterPanel">
            <div
                class="d-flex flex-wrap align-items-end gap-3 px-4 py-3"
                style="background:#f8f9fa; border-bottom:1px solid #f0f3f7;"
            >
                {{-- Status filter --}}
                <div>
                    <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#5A6A7A;">
                        Status
                    </label>
                    <select class="form-select form-select-sm" style="border-radius:8px;border-color:#e2e8ee;font-size:.84rem;min-width:140px;">
                        <option value="">All Statuses</option>
                        <option>Approved</option>
                        <option>Pending</option>
                        <option>Rejected</option>
                    </select>
                </div>
                {{-- City filter --}}
                <div>
                    <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#5A6A7A;">
                        City
                    </label>
                    <select class="form-select form-select-sm" style="border-radius:8px;border-color:#e2e8ee;font-size:.84rem;min-width:140px;">
                        <option value="">All Cities</option>
                        <option>Delhi</option>
                        <option>Mumbai</option>
                        <option>Bengaluru</option>
                        <option>Chennai</option>
                        <option>Hyderabad</option>
                    </select>
                </div>
                {{-- Date from --}}
                <div>
                    <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#5A6A7A;">
                        Registered From
                    </label>
                    <input type="date" class="form-control form-control-sm"
                        style="border-radius:8px;border-color:#e2e8ee;font-size:.84rem;min-width:150px;">
                </div>
                {{-- Buttons --}}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm"
                        style="background:#0F3D56;color:#fff;border-radius:8px;font-size:.83rem;font-weight:600;">
                        Apply
                    </button>
                    <button type="button" class="btn btn-sm"
                        style="border:1px solid #e2e8ee;border-radius:8px;font-size:.83rem;color:#5A6A7A;">
                        Reset
                    </button>
                </div>
            </div>
        </div>{{-- /filter panel --}}

        {{-- ── Table ─────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table owners-table mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">#ID</th>
                        <th>Owner Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Registered</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:110px; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $owners = [
                            [
                                'id'         => 'PO-001',
                                'name'       => 'Vikram Joshi',
                                'email'      => 'vikram.joshi@email.com',
                                'phone'      => '+91 98100 12345',
                                'city'       => 'Delhi',
                                'registered' => '12 Jan 2025',
                                'status'     => 'approved',
                                'color'      => '0F3D56',
                            ],
                            [
                                'id'         => 'PO-002',
                                'name'       => 'Meena Reddy',
                                'email'      => 'meena.reddy@email.com',
                                'phone'      => '+91 87654 32100',
                                'city'       => 'Hyderabad',
                                'registered' => '19 Jan 2025',
                                'status'     => 'approved',
                                'color'      => '1a7a50',
                            ],
                            [
                                'id'         => 'PO-003',
                                'name'       => 'Sanjay Gupta',
                                'email'      => 'sanjay.gupta@email.com',
                                'phone'      => '+91 76543 21098',
                                'city'       => 'Mumbai',
                                'registered' => '03 Feb 2025',
                                'status'     => 'pending',
                                'color'      => '2d6a8f',
                            ],
                            [
                                'id'         => 'PO-004',
                                'name'       => 'Pooja Iyer',
                                'email'      => 'pooja.iyer@email.com',
                                'phone'      => '+91 65432 10987',
                                'city'       => 'Bengaluru',
                                'registered' => '14 Feb 2025',
                                'status'     => 'approved',
                                'color'      => '8a4d9e',
                            ],
                            [
                                'id'         => 'PO-005',
                                'name'       => 'Harish Rao',
                                'email'      => 'harish.rao@email.com',
                                'phone'      => '+91 54321 09876',
                                'city'       => 'Chennai',
                                'registered' => '22 Feb 2025',
                                'status'     => 'rejected',
                                'color'      => 'c0392b',
                            ],
                            [
                                'id'         => 'PO-006',
                                'name'       => 'Anjali Bose',
                                'email'      => 'anjali.bose@email.com',
                                'phone'      => '+91 43210 98765',
                                'city'       => 'Kolkata',
                                'registered' => '05 Mar 2025',
                                'status'     => 'pending',
                                'color'      => 'd35400',
                            ],
                            [
                                'id'         => 'PO-007',
                                'name'       => 'Rahul Trivedi',
                                'email'      => 'rahul.trivedi@email.com',
                                'phone'      => '+91 32109 87654',
                                'city'       => 'Pune',
                                'registered' => '18 Mar 2025',
                                'status'     => 'approved',
                                'color'      => '27ae60',
                            ],
                            [
                                'id'         => 'PO-008',
                                'name'       => 'Sneha Kapoor',
                                'email'      => 'sneha.kapoor@email.com',
                                'phone'      => '+91 21098 76543',
                                'city'       => 'Jaipur',
                                'registered' => '29 Mar 2025',
                                'status'     => 'rejected',
                                'color'      => '8e44ad',
                            ],
                            [
                                'id'         => 'PO-009',
                                'name'       => 'Arjun Sharma',
                                'email'      => 'arjun.sharma@email.com',
                                'phone'      => '+91 10987 65432',
                                'city'       => 'Ahmedabad',
                                'registered' => '07 Apr 2025',
                                'status'     => 'pending',
                                'color'      => '1a6b8a',
                            ],
                            [
                                'id'         => 'PO-010',
                                'name'       => 'Divya Nair',
                                'email'      => 'divya.nair@email.com',
                                'phone'      => '+91 90876 54321',
                                'city'       => 'Mumbai',
                                'registered' => '15 Apr 2025',
                                'status'     => 'approved',
                                'color'      => '2e86ab',
                            ],
                        ];
                    @endphp

                    @foreach ($owners as $owner)
                        <tr>
                            {{-- ID --}}
                            <td>
                                <span style="font-size:.78rem;font-weight:700;color:#0F3D56;">
                                    {{ $owner['id'] }}
                                </span>
                            </td>

                            {{-- Owner Name --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        class="owner-avatar"
                                        style="background:#{{ $owner['color'] }};"
                                    >
                                        {{ strtoupper(substr($owner['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:.86rem;">
                                            {{ $owner['name'] }}
                                        </div>
                                        <div style="font-size:.74rem;color:#8899aa;">
                                            {{ $owner['city'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td style="color:#5A6A7A;">
                                <a href="mailto:{{ $owner['email'] }}"
                                    style="color:#5A6A7A;text-decoration:none;"
                                    onmouseover="this.style.color='#0F3D56'"
                                    onmouseout="this.style.color='#5A6A7A'"
                                >
                                    {{ $owner['email'] }}
                                </a>
                            </td>

                            {{-- Phone --}}
                            <td style="color:#5A6A7A;white-space:nowrap;">
                                {{ $owner['phone'] }}
                            </td>

                            {{-- City --}}
                            <td style="color:#5A6A7A;">
                                <i class="bi bi-geo-alt" style="font-size:.75rem;margin-right:3px;"></i>
                                {{ $owner['city'] }}
                            </td>

                            {{-- Registered --}}
                            <td style="color:#8899aa;font-size:.82rem;white-space:nowrap;">
                                {{ $owner['registered'] }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="status-badge badge-{{ $owner['status'] }}">
                                    {{ ucfirst($owner['status']) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    {{-- View --}}
                                    <a href="#" class="action-btn action-btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    {{-- Edit --}}
                                    <a href="#" class="action-btn action-btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    {{-- Delete --}}
                                    <button
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        title="Delete"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-owner="{{ $owner['name'] }}"
                                        data-id="{{ $owner['id'] }}"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>{{-- /table-responsive --}}

        {{-- ── Footer: results meta + pagination ──────────────── --}}
        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-3"
            style="border-top:1px solid #f0f3f7; background:#fafbfc;"
        >
            <p class="results-meta mb-0">
                Showing <strong>1–10</strong> of <strong>48</strong> parking owners
            </p>

            <nav aria-label="Parking owners pagination">
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Previous">
                            <i class="bi bi-chevron-left" style="font-size:.7rem;"></i>
                        </a>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">4</a></li>
                    <li class="page-item"><a class="page-link" href="#">5</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>{{-- /page-card --}}


    {{-- ══════════════════════════════════════════════════════════
         Delete Confirmation Modal
    ══════════════════════════════════════════════════════════ --}}
    <div
        class="modal fade"
        id="deleteModal"
        tabindex="-1"
        aria-labelledby="deleteModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:14px;border:1px solid #e2e8ee;overflow:hidden;">
                <div class="modal-body text-center p-4">
                    <div
                        class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:60px;height:60px;background:rgba(231,76,60,.1);border-radius:14px;"
                    >
                        <i class="bi bi-trash3" style="font-size:1.5rem;color:#e74c3c;"></i>
                    </div>
                    <h6 class="mb-1" style="font-weight:700;color:#0D1B2A;">
                        Delete Parking Owner?
                    </h6>
                    <p class="mb-3" style="font-size:.86rem;color:#5A6A7A;">
                        Are you sure you want to delete
                        <strong id="deleteOwnerName"></strong>?
                        This action cannot be undone.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button
                            type="button"
                            class="btn btn-sm px-4"
                            data-bs-dismiss="modal"
                            style="border:1px solid #e2e8ee;border-radius:8px;font-size:.855rem;color:#5A6A7A;"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm px-4"
                            style="background:#e74c3c;color:#fff;border-radius:8px;font-size:.855rem;font-weight:600;"
                        >
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Populate delete modal with owner name
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const trigger   = event.relatedTarget;
            const ownerName = trigger.getAttribute('data-owner');
            document.getElementById('deleteOwnerName').textContent = ownerName;
        });
    }
</script>
@endpush