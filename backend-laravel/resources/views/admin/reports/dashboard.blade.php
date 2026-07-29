@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Reports Dashboard</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>New Report</button>
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
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Reports</h6>
                            <h5 class="mb-0 fw-bold">1,245</h5>
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
                                <i class="bi bi-calendar-day fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Daily Reports</h6>
                            <h5 class="mb-0 fw-bold">28</h5>
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
                                <i class="bi bi-calendar-month fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Monthly Reports</h6>
                            <h5 class="mb-0 fw-bold">124</h5>
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
                                <i class="bi bi-graph-up-arrow fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Revenue Reports</h6>
                            <h5 class="mb-0 fw-bold">42</h5>
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
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-sliders2 fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Custom Reports</h6>
                            <h5 class="mb-0 fw-bold">18</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Reports</h6>
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterDateRange" class="form-label fs-7 fw-semibold text-muted">Date Range</label>
                    <input type="text" class="form-control" id="filterDateRange" placeholder="Select Date Range">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterReportType" class="form-label fs-7 fw-semibold text-muted">Report Type</label>
                    <select class="form-select" id="filterReportType">
                        <option value="">All Types</option>
                        <option value="revenue">Revenue</option>
                        <option value="bookings">Bookings</option>
                        <option value="occupancy">Occupancy</option>
                        <option value="users">Users</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterLocation" class="form-label fs-7 fw-semibold text-muted">Parking Location</label>
                    <select class="form-select" id="filterLocation">
                        <option value="">All Locations</option>
                        <option value="downtown">Downtown Mall</option>
                        <option value="citycenter">City Center</option>
                        <option value="airport">Airport Plaza</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterOwner" class="form-label fs-7 fw-semibold text-muted">Owner</label>
                    <select class="form-select" id="filterOwner">
                        <option value="">All Owners</option>
                        <option value="john">John Doe</option>
                        <option value="jane">Jane Smith</option>
                        <option value="mike">Mike Johnson</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="generating">Generating</option>
                        <option value="failed">Failed</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset</button>
                    <button type="button" class="btn btn-primary flex-grow-1"><i class="bi bi-file-earmark-check me-1"></i>Generate</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories & Recent Activity/Quick Export Row -->
    <div class="row g-4 mb-4">
        <!-- Report Categories -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Report Categories</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-currency-rupee fs-4 text-primary mb-1"></i>
                                    <span class="fw-medium small text-dark">Revenue</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-calendar-check fs-4 text-success mb-1"></i>
                                    <span class="fw-medium small text-dark">Bookings</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-pin-map fs-4 text-info mb-1"></i>
                                    <span class="fw-medium small text-dark">Occupancy</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-people fs-4 text-warning mb-1"></i>
                                    <span class="fw-medium small text-dark">Users</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-credit-card fs-4 text-secondary mb-1"></i>
                                    <span class="fw-medium small text-dark">Payments</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-arrow-return-left fs-4 text-danger mb-1"></i>
                                    <span class="fw-medium small text-dark">Refund</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-house-door fs-4 text-primary mb-1"></i>
                                    <span class="fw-medium small text-dark">Parking Owner</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="bg-light rounded-3 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center transition-hover border">
                                    <i class="bi bi-car-front fs-4 text-success mb-1"></i>
                                    <span class="fw-medium small text-dark">Vehicle</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Export & Recent Activity -->
        <div class="col-xl-4">
            <!-- Quick Export -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-download me-2"></i>Quick Export</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-2">
                        <button class="btn btn-outline-danger btn-sm fw-medium text-start"><i class="bi bi-filetype-pdf me-2"></i>Export PDF</button>
                        <button class="btn btn-outline-success btn-sm fw-medium text-start"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</button>
                        <button class="btn btn-outline-secondary btn-sm fw-medium text-start"><i class="bi bi-filetype-csv me-2"></i>Export CSV</button>
                        <button class="btn btn-primary btn-sm fw-medium text-start"><i class="bi bi-clock-history me-2"></i>Schedule Report</button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-check-circle-fill text-success"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Monthly Revenue Report</span> generated successfully.
                                <div class="text-muted fs-7">2 mins ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-hourglass-split text-warning"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Q3 Bookings Summary</span> is being generated.
                                <div class="text-muted fs-7">15 mins ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-x-circle-fill text-danger"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Parking Occupancy Data</span> failed to generate.
                                <div class="text-muted fs-7">1 hour ago</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2"><i class="bi bi-clock-fill text-secondary"></i></div>
                            <div class="flex-grow-1 small">
                                <span class="fw-medium text-dark">Weekly Payments Report</span> scheduled for 10:00 PM.
                                <div class="text-muted fs-7">3 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports Table -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Reports</h6>
            <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="ps-3">Report ID</th>
                            <th>Report Name</th>
                            <th>Generated By</th>
                            <th>Created Date</th>
                            <th>File Type</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#RPT-1084</span></td>
                            <td>Monthly Revenue Report</td>
                            <td>Admin</td>
                            <td>29 Jul, 2026</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">PDF</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Completed</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Download"><i class="bi bi-download"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#RPT-1083</span></td>
                            <td>Q3 Bookings Summary</td>
                            <td>Manager</td>
                            <td>29 Jul, 2026</td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Excel</span></td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Generating</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Download"><i class="bi bi-download"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#RPT-1082</span></td>
                            <td>Parking Occupancy Data</td>
                            <td>Admin</td>
                            <td>28 Jul, 2026</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">CSV</span></td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Failed</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Download"><i class="bi bi-download"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#RPT-1081</span></td>
                            <td>Weekly Payments Report</td>
                            <td>Accountant</td>
                            <td>27 Jul, 2026</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">PDF</span></td>
                            <td><span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25">Scheduled</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Download"><i class="bi bi-download"></i></button>
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
                <div class="text-muted small">Showing 1 to 4 of 50 entries</div>
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

<style>
    .fs-7 { font-size: 0.75rem; }
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; }
    .table th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 1px solid #e9ecef; }
    .pagination .page-link { font-size: 0.875rem; }
    .transition-hover { transition: all 0.2s ease-in-out; }
    .transition-hover:hover { background-color: #e9ecef !important; border-color: #d0d0d0 !important; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection