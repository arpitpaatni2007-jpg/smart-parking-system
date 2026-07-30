@extends('layouts.admin')

@section('title', 'CMS Page Editor')
@section('page-title', 'CMS Page Editor')

@push('styles')
<style>
    .fs-7 { font-size: 0.75rem; }
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; }
    .table th {
        font-weight:    600;
        font-size:      0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color:          #6c757d;
        border-bottom:  1px solid #e9ecef;
    }
    .bg-light { background-color: #f8f9fa !important; }
    .form-floating > textarea { min-height: 150px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">CMS Page Editor</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none">CMS Pages</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Page</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </button>
            <button class="btn btn-outline-info btn-sm">
                <i class="bi bi-display me-1"></i>Preview
            </button>
            <button class="btn btn-outline-warning btn-sm">
                <i class="bi bi-save me-1"></i>Save Draft
            </button>
            <button class="btn btn-success btn-sm">
                <i class="bi bi-check-circle me-1"></i>Publish
            </button>
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash3 me-1"></i>Delete
            </button>
        </div>
    </div>

    <div class="row g-4">

        {{-- ══════════════════════════════════════════════════════
             LEFT COLUMN
        ══════════════════════════════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- Page Information ─────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-info-circle me-2"></i>Page Information
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pageTitle" class="form-label fs-7 fw-semibold text-muted">
                                Page Title
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="pageTitle"
                                value="About Us"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="pageSlug" class="form-label fs-7 fw-semibold text-muted">
                                Page Slug
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small">/</span>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="pageSlug"
                                    value="about-us"
                                >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="metaTitle" class="form-label fs-7 fw-semibold text-muted">
                                Meta Title
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="metaTitle"
                                value="About Us | Smart Parking System"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="metaDesc" class="form-label fs-7 fw-semibold text-muted">
                                Meta Description
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="metaDesc"
                                value="Learn more about our Smart Parking System and how we are revolutionizing the parking industry with advanced technology."
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="seoKeywords" class="form-label fs-7 fw-semibold text-muted">
                                SEO Keywords
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="seoKeywords"
                                value="smart parking, about us, parking system, technology"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="canonicalUrl" class="form-label fs-7 fw-semibold text-muted">
                                Canonical URL
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="canonicalUrl"
                                value="https://smartparking.com/about-us"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content Editor ───────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Content Editor
                    </h6>
                </div>
                <div class="card-body p-4">

                    {{-- Toolbar (UI Only) --}}
                    <div class="bg-light bg-opacity-50 p-2 rounded-3 border mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Bold">
                                <i class="bi bi-type-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Italic">
                                <i class="bi bi-type-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Underline">
                                <i class="bi bi-type-underline"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Unordered List">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Ordered List">
                                <i class="bi bi-list-ol"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Heading">
                                <i class="bi bi-type-h1"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Link">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Image">
                                <i class="bi bi-image"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Table">
                                <i class="bi bi-table"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Align Left">
                                <i class="bi bi-text-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Align Center">
                                <i class="bi bi-text-center"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Align Right">
                                <i class="bi bi-text-right"></i>
                            </button>
                        </div>
                        <div class="vr mx-1"></div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" title="Code Block">
                                <i class="bi bi-code-slash"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" title="Quote">
                                <i class="bi bi-chat-quote"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Editor textarea --}}
                    <div class="form-floating">
                        <textarea
                            class="form-control"
                            id="pageContent"
                            style="height:350px; resize:vertical;"
                        >&lt;h2&gt;Welcome to Smart Parking System&lt;/h2&gt;
&lt;p&gt;We are a leading provider of smart parking solutions, dedicated to making urban mobility seamless and stress-free. Our platform integrates cutting-edge IoT technology with real-time data analytics to deliver unparalleled parking experiences.&lt;/p&gt;
&lt;h3&gt;Our Mission&lt;/h3&gt;
&lt;p&gt;To revolutionize the parking industry by providing intelligent, efficient, and eco-friendly parking management solutions for cities, businesses, and drivers.&lt;/p&gt;
&lt;ul&gt;
    &lt;li&gt;Real-time slot availability&lt;/li&gt;
    &lt;li&gt;Seamless mobile payments&lt;/li&gt;
    &lt;li&gt;Advanced booking systems&lt;/li&gt;
&lt;/ul&gt;</textarea>
                        <label for="pageContent">Page Content</label>
                    </div>

                </div>
            </div>

            {{-- Revision History ─────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-clock-history me-2"></i>Revision History
                    </h6>
                    <span class="badge bg-secondary rounded-pill">3 Versions</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3">Version</th>
                                    <th>Edited By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th class="pe-3 text-end">Restore</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium">v1.3</span></td>
                                    <td>Admin</td>
                                    <td>29 Jul, 2026 10:30 AM</td>
                                    <td>Content Update</td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="Restore v1.3">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium">v1.2</span></td>
                                    <td>Editor</td>
                                    <td>28 Jul, 2026 02:15 PM</td>
                                    <td>SEO Meta Tags Update</td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="Restore v1.2">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium">v1.1</span></td>
                                    <td>Admin</td>
                                    <td>25 Jul, 2026 09:00 AM</td>
                                    <td>Page Created</td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="Restore v1.1">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Live Page Preview ────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-phone me-2"></i>Live Page Preview
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div
                        class="border rounded-3 p-3 bg-light bg-opacity-50 mx-auto mb-3"
                        style="max-width:100%; min-height:280px;"
                    >
                        <div class="bg-white border rounded-2 p-3 shadow-sm text-start">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <span class="small fw-bold text-truncate" style="max-width:150px;">About Us</span>
                                <i class="bi bi-three-dots-vertical text-muted small"></i>
                            </div>
                            <h6 class="fw-bold small mb-1 text-primary">Welcome to Smart Parking</h6>
                            <p
                                class="small text-muted mb-2"
                                style="font-size:0.7rem; line-height:1.4;"
                            >
                                We are a leading provider of smart parking solutions, dedicated to making urban mobility seamless.
                            </p>
                            <div class="bg-light rounded p-2 mb-1" style="height:8px; width:100%;"></div>
                            <div class="bg-light rounded p-2" style="height:8px; width:75%;"></div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-center gap-2">
                        <span class="badge bg-primary rounded-pill">
                            <i class="bi bi-phone me-1"></i> Mobile
                        </span>
                        <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill border border-secondary border-opacity-25">
                            <i class="bi bi-laptop me-1"></i> Desktop
                        </span>
                    </div>
                </div>
            </div>

            {{-- Bottom action buttons ────────────────────────── --}}
            <div class="d-flex flex-wrap justify-content-end gap-2 pt-2">
                <button class="btn btn-outline-secondary">Cancel</button>
                <button class="btn btn-outline-warning">
                    <i class="bi bi-save me-1"></i>Save Draft
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-arrow-up-circle me-1"></i>Update Page
                </button>
                <button class="btn btn-success">
                    <i class="bi bi-check-circle me-1"></i>Publish
                </button>
            </div>

        </div>{{-- /col-lg-8 --}}

        {{-- ══════════════════════════════════════════════════════
             RIGHT COLUMN
        ══════════════════════════════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Page Settings ────────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-gear me-2"></i>Page Settings
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div>
                            <label for="status" class="form-label fs-7 fw-semibold text-muted">Status</label>
                            <select class="form-select" id="status">
                                <option value="published" selected>Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div>
                            <label for="visibility" class="form-label fs-7 fw-semibold text-muted">Visibility</label>
                            <select class="form-select" id="visibility">
                                <option value="public" selected>Public</option>
                                <option value="private">Private</option>
                                <option value="role">Role Based</option>
                            </select>
                        </div>
                        <div>
                            <label for="publishDate" class="form-label fs-7 fw-semibold text-muted">
                                Publish Date
                            </label>
                            <input
                                type="datetime-local"
                                class="form-control"
                                id="publishDate"
                                value="2026-07-29T10:30"
                            >
                        </div>
                        <div>
                            <label for="author" class="form-label fs-7 fw-semibold text-muted">Author</label>
                            <select class="form-select" id="author">
                                <option value="admin" selected>Admin</option>
                                <option value="editor">Editor</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div>
                            <label for="category" class="form-label fs-7 fw-semibold text-muted">Category</label>
                            <input
                                type="text"
                                class="form-control"
                                id="category"
                                placeholder="e.g. General, Legal"
                            >
                        </div>
                        <div>
                            <label for="tags" class="form-label fs-7 fw-semibold text-muted">Tags</label>
                            <input
                                type="text"
                                class="form-control"
                                id="tags"
                                placeholder="e.g. parking, system, about"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO Analysis ─────────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-graph-up me-2"></i>SEO Analysis
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">SEO Score</span>
                                <span class="small fw-bold text-success">92%</span>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div
                                    class="progress-bar bg-success"
                                    role="progressbar"
                                    style="width:92%;"
                                    aria-valuenow="92"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">Readability</span>
                                <span class="small fw-bold text-warning">78%</span>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div
                                    class="progress-bar bg-warning"
                                    role="progressbar"
                                    style="width:78%;"
                                    aria-valuenow="78"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>
                        </div>
                        <div class="border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Meta Description</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">
                                    Optimal
                                </span>
                            </div>
                        </div>
                        <div class="border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Keyword Density</span>
                                <span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">
                                    Medium
                                </span>
                            </div>
                        </div>
                        <div class="border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Image Optimization</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">
                                    Needs Work
                                </span>
                            </div>
                        </div>
                        <div class="border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Internal Links</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">
                                    14
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">External Links</span>
                                <span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25 small">
                                    5
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page Statistics ──────────────────────────────── --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Page Statistics
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium">
                                <i class="bi bi-file-text me-1"></i> Word Count
                            </span>
                            <span class="fw-bold text-secondary">1,245</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium">
                                <i class="bi bi-fonts me-1"></i> Character Count
                            </span>
                            <span class="fw-bold text-secondary">6,820</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium">
                                <i class="bi bi-clock me-1"></i> Est. Reading Time
                            </span>
                            <span class="fw-bold text-secondary">5 mins</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium">
                                <i class="bi bi-calendar-check me-1"></i> Last Updated
                            </span>
                            <span class="fw-bold text-secondary">29 Jul, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium">
                                <i class="bi bi-eye me-1"></i> Page Views
                            </span>
                            <span class="fw-bold text-secondary">15,240</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}

    </div>{{-- /row --}}

</div>{{-- /container-fluid --}}
@endsection