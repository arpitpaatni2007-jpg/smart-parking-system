@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">CMS Pages Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">CMS Pages</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>New Page</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xxl-2 col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-files fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Pages</h6>
                            <h5 class="mb-0 fw-bold">78</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-2 col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Published</h6>
                            <h5 class="mb-0 fw-bold">52</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-2 col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Draft</h6>
                            <h5 class="mb-0 fw-bold">15</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-2 col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-archive fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Archived</h6>
                            <h5 class="mb-0 fw-bold">6</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-2 col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Recently Updated</h6>
                            <h5 class="mb-0 fw-bold">9</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Left Column (Table) -->
        <div class="col-xl-9">
            
            <!-- Filter Section -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Pages</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label for="searchPage" class="form-label fs-7 fw-semibold text-muted">Search</label>
                            <input type="text" class="form-control" id="searchPage" placeholder="Title, Slug or ID...">
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label for="filterTitle" class="form-label fs-7 fw-semibold text-muted">Page Title</label>
                            <input type="text" class="form-control" id="filterTitle" placeholder="e.g. About Us">
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label for="filterSlug" class="form-label fs-7 fw-semibold text-muted">Page Slug</label>
                            <input type="text" class="form-control" id="filterSlug" placeholder="e.g. about-us">
                        </div>
                        <div class="col-xl-1 col-lg-3 col-md-6">
                            <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                                <option value="pending">Pending Review</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label for="filterAuthor" class="form-label fs-7 fw-semibold text-muted">Author</label>
                            <select class="form-select" id="filterAuthor">
                                <option value="">All</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset</button>
                            <button type="button" class="btn btn-primary flex-grow-1">Apply Filters</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CMS Pages Table -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>All CMS Pages</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3" style="width: 80px;">Page ID</th>
                                    <th>Page Title</th>
                                    <th>Slug</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th class="text-center" style="width: 90px;">SEO Score</th>
                                    <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#P-001</span></td>
                                    <td><span class="fw-medium">About Us</span></td>
                                    <td><span class="text-muted small">/about-us</span></td>
                                    <td>Admin</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Published</span></td>
                                    <td><span class="small text-muted">29 Jul, 2026</span></td>
                                    <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">92%</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Preview"><i class="bi bi-display"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#P-002</span></td>
                                    <td><span class="fw-medium">Contact Us</span></td>
                                    <td><span class="text-muted small">/contact-us</span></td>
                                    <td>Editor</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Published</span></td>
                                    <td><span class="small text-muted">28 Jul, 2026</span></td>
                                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">78%</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Preview"><i class="bi bi-display"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#P-003</span></td>
                                    <td><span class="fw-medium">Privacy Policy</span></td>
                                    <td><span class="text-muted small">/privacy-policy</span></td>
                                    <td>Admin</td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Draft</span></td>
                                    <td><span class="small text-muted">27 Jul, 2026</span></td>
                                    <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">--</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Preview"><i class="bi bi-display"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#P-004</span></td>
                                    <td><span class="fw-medium">Terms of Service</span></td>
                                    <td><span class="text-muted small">/terms-of-service</span></td>
                                    <td>Manager</td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">Archived</span></td>
                                    <td><span class="small text-muted">25 Jul, 2026</span></td>
                                    <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">85%</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Preview"><i class="bi bi-display"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#P-005</span></td>
                                    <td><span class="fw-medium">FAQ</span></td>
                                    <td><span class="text-muted small">/faq</span></td>
                                    <td>Admin</td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Pending Review</span></td>
                                    <td><span class="small text-muted">24 Jul, 2026</span></td>
                                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">45%</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Preview"><i class="bi bi-display"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="card-footer bg-transparent border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">Showing 1 to 5 of 78 entries</div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column (Sidebar) -->
        <div class="col-xl-3">
            <!-- Quick Actions Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-2">
                        <button class="btn btn-primary btn-sm fw-medium text-start"><i class="bi bi-plus-circle me-2"></i>Create New Page</button>
                        <button class="btn btn-outline-success btn-sm fw-medium text-start"><i class="bi bi-pencil-square me-2"></i>Create Blog</button>
                        <button class="btn btn-outline-warning btn-sm fw-medium text-start"><i class="bi bi-question-circle me-2"></i>Create FAQ</button>
                        <button class="btn btn-outline-secondary btn-sm fw-medium text-start"><i class="bi bi-file-earmark-text me-2"></i>Create Terms & Cond.</button>
                        <button class="btn btn-outline-info btn-sm fw-medium text-start"><i class="bi bi-shield-check me-2"></i>Create Privacy Policy</button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Sidebar -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-pencil-square text-primary"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">About Us</span> was updated by Admin.
                                <div class="text-muted fs-7">2 hours ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-check-circle-fill text-success"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Contact Us</span> was published by Editor.
                                <div class="text-muted fs-7">5 hours ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-clock-fill text-warning"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Privacy Policy</span> was saved as draft by Admin.
                                <div class="text-muted fs-7">1 day ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-archive text-secondary"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Terms of Service</span> was archived by Manager.
                                <div class="text-muted fs-7">2 days ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fs-7 { font-size: 0.75rem; }
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; }
    .table th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 1px solid #e9ecef; }
    .pagination .page-link { font-size: 0.875rem; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection