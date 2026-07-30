@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Roles Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Export Roles</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Create New Role</button>
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
                                <i class="bi bi-person-badge fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Roles</h6>
                            <h5 class="mb-0 fw-bold">24</h5>
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
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Active Roles</h6>
                            <h5 class="mb-0 fw-bold">18</h5>
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
                                <i class="bi bi-gear fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">System Roles</h6>
                            <h5 class="mb-0 fw-bold">5</h5>
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
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Custom Roles</h6>
                            <h5 class="mb-0 fw-bold">19</h5>
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
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Users Assigned</h6>
                            <h5 class="mb-0 fw-bold">342</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Roles</h6>
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="searchRole" class="form-label fs-7 fw-semibold text-muted">Search Role</label>
                    <input type="text" class="form-control" id="searchRole" placeholder="Name or ID...">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterType" class="form-label fs-7 fw-semibold text-muted">Role Type</label>
                    <select class="form-select" id="filterType">
                        <option value="">All Types</option>
                        <option value="system">System</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterCreator" class="form-label fs-7 fw-semibold text-muted">Created By</label>
                    <select class="form-select" id="filterCreator">
                        <option value="">All</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterDateRange" class="form-label fs-7 fw-semibold text-muted">Date Range</label>
                    <input type="text" class="form-control" id="filterDateRange" placeholder="Select Dates">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset Filters</button>
                    <button type="button" class="btn btn-primary flex-grow-1">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table & Quick Statistics Row -->
    <div class="row g-4">
        <!-- Main Table -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>All Roles</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3" style="width: 90px;">Role ID</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th class="text-center" style="width: 100px;">Users</th>
                                    <th class="text-center" style="width: 100px;">Permissions</th>
                                    <th>Created Date</th>
                                    <th style="width: 110px;">Status</th>
                                    <th class="pe-3 text-end" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#R-001</span></td>
                                    <td><span class="fw-medium">Super Admin</span></td>
                                    <td><span class="text-muted small text-truncate d-block" style="max-width: 180px;">Full system access with all permissions</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">5</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">120</span></td>
                                    <td><span class="small text-muted">01 Jan, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                        <button class="btn btn-sm btn-outline-warning border-0 p-1" title="Manage Permissions"><i class="bi bi-shield-lock"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#R-002</span></td>
                                    <td><span class="fw-medium">Admin</span></td>
                                    <td><span class="text-muted small text-truncate d-block" style="max-width: 180px;">Administrative access with management tools</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">12</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">85</span></td>
                                    <td><span class="small text-muted">15 Feb, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                        <button class="btn btn-sm btn-outline-warning border-0 p-1" title="Manage Permissions"><i class="bi bi-shield-lock"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#R-003</span></td>
                                    <td><span class="fw-medium">Parking Owner</span></td>
                                    <td><span class="text-muted small text-truncate d-block" style="max-width: 180px;">Manage parking locations, slots, and bookings</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">84</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">45</span></td>
                                    <td><span class="small text-muted">20 Mar, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                        <button class="btn btn-sm btn-outline-warning border-0 p-1" title="Manage Permissions"><i class="bi bi-shield-lock"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#R-004</span></td>
                                    <td><span class="fw-medium">Support Agent</span></td>
                                    <td><span class="text-muted small text-truncate d-block" style="max-width: 180px;">Handle support tickets and user inquiries</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">23</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">30</span></td>
                                    <td><span class="small text-muted">10 Apr, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                        <button class="btn btn-sm btn-outline-warning border-0 p-1" title="Manage Permissions"><i class="bi bi-shield-lock"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#R-005</span></td>
                                    <td><span class="fw-medium">Finance Manager</span></td>
                                    <td><span class="text-muted small text-truncate d-block" style="max-width: 180px;">Access to payments, earnings, and reports</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">4</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">55</span></td>
                                    <td><span class="small text-muted">15 May, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                        <button class="btn btn-sm btn-outline-warning border-0 p-1" title="Manage Permissions"><i class="bi bi-shield-lock"></i></button>
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
                        <div class="text-muted small">Showing 1 to 5 of 24 entries</div>
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

        <!-- Quick Statistics Sidebar -->
        <div class="col-xl-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>Quick Statistics</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-trophy me-1"></i> Most Used Role</span>
                            <span class="fw-bold text-primary small">Parking Owner</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock-history me-1"></i> Recently Created</span>
                            <span class="fw-bold text-success small">Operations Manager</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-key me-1"></i> Total Permissions</span>
                            <span class="fw-bold text-secondary small">350</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-people me-1"></i> Avg. Users per Role</span>
                            <span class="fw-bold text-secondary small">14.2</span>
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