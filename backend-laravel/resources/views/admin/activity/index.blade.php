@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Activity Logs</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">System</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clear Filters</button>
            <button class="btn btn-outline-success btn-sm"><i class="bi bi-filetype-csv me-1"></i>Download CSV</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-download me-1"></i>Export Logs</button>
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
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Activities</h6>
                            <h5 class="mb-0 fw-bold">12,540</h5>
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
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Today's Activities</h6>
                            <h5 class="mb-0 fw-bold">342</h5>
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
                                <i class="bi bi-x-octagon fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Failed Actions</h6>
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
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-shield-exclamation fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Security Events</h6>
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
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Active Users</h6>
                            <h5 class="mb-0 fw-bold">56</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Activities</h6>
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="searchActivity" class="form-label fs-7 fw-semibold text-muted">Search Activity</label>
                    <input type="text" class="form-control" id="searchActivity" placeholder="ID, User, or Event...">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterUser" class="form-label fs-7 fw-semibold text-muted">User</label>
                    <select class="form-select" id="filterUser">
                        <option value="">All Users</option>
                        <option value="rahul">Rahul Sharma</option>
                        <option value="priya">Priya Singh</option>
                        <option value="amit">Amit Kumar</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterRole" class="form-label fs-7 fw-semibold text-muted">Role</label>
                    <select class="form-select" id="filterRole">
                        <option value="">All</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Parking Owner</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterType" class="form-label fs-7 fw-semibold text-muted">Activity Type</label>
                    <select class="form-select" id="filterType">
                        <option value="">All Types</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="payment">Payment</option>
                        <option value="booking">Booking</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterModule" class="form-label fs-7 fw-semibold text-muted">Module</label>
                    <select class="form-select" id="filterModule">
                        <option value="">All</option>
                        <option value="users">Users</option>
                        <option value="parking">Parking</option>
                        <option value="bookings">Bookings</option>
                        <option value="payments">Payments</option>
                        <option value="reports">Reports</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
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

    <!-- Activity Table & Sidebar Row -->
    <div class="row g-4">
        <!-- Main Table -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Activity Logs</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3" style="width: 90px;">Log ID</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Activity</th>
                                    <th>Module</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#LOG-1092</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                <span class="text-secondary small fw-bold">RS</span>
                                            </div>
                                            <span class="fw-medium small">Rahul Sharma</span>
                                        </div>
                                    </td>
                                    <td><span class="small">Admin</span></td>
                                    <td><span class="small">Login</span></td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25 small">Users</span></td>
                                    <td><span class="small">192.168.1.45</span></td>
                                    <td><span class="small text-muted">30 Jul, 10:30 AM</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Success</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View Details"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Export Record"><i class="bi bi-download"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Log"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#LOG-1091</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                <span class="text-secondary small fw-bold">AK</span>
                                            </div>
                                            <span class="fw-medium small">Amit Kumar</span>
                                        </div>
                                    </td>
                                    <td><span class="small">Owner</span></td>
                                    <td><span class="small">Create</span></td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25 small">Parking</span></td>
                                    <td><span class="small">10.0.0.12</span></td>
                                    <td><span class="small text-muted">30 Jul, 09:45 AM</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Success</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View Details"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Export Record"><i class="bi bi-download"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Log"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#LOG-1090</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                <span class="text-secondary small fw-bold">PS</span>
                                            </div>
                                            <span class="fw-medium small">Priya Singh</span>
                                        </div>
                                    </td>
                                    <td><span class="small">User</span></td>
                                    <td><span class="small">Payment Action</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Payments</span></td>
                                    <td><span class="small">172.16.0.8</span></td>
                                    <td><span class="small text-muted">30 Jul, 09:20 AM</span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">Warning</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View Details"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Export Record"><i class="bi bi-download"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Log"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#LOG-1089</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                <span class="text-secondary small fw-bold">SM</span>
                                            </div>
                                            <span class="fw-medium small">Sneha Mehta</span>
                                        </div>
                                    </td>
                                    <td><span class="small">Admin</span></td>
                                    <td><span class="small">Login</span></td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25 small">Users</span></td>
                                    <td><span class="small">192.168.1.10</span></td>
                                    <td><span class="small text-muted">30 Jul, 08:55 AM</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Failed</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View Details"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Export Record"><i class="bi bi-download"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Log"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#LOG-1088</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                <span class="text-secondary small fw-bold">VP</span>
                                            </div>
                                            <span class="fw-medium small">Vikram Patel</span>
                                        </div>
                                    </td>
                                    <td><span class="small">User</span></td>
                                    <td><span class="small">Logout</span></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25 small">System</span></td>
                                    <td><span class="small">10.0.0.25</span></td>
                                    <td><span class="small text-muted">30 Jul, 08:30 AM</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Success</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View Details"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Export Record"><i class="bi bi-download"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Log"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="card-footer bg-transparent border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">Showing 1 to 5 of 12,540 entries</div>
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

        <!-- Right Column: Sidebar -->
        <div class="col-xl-3">
            <!-- Activity Statistics -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Activity Statistics</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-person-circle me-1"></i> Most Active User</span>
                            <span class="fw-bold text-primary small">Rahul Sharma</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-grid me-1"></i> Most Used Module</span>
                            <span class="fw-bold text-secondary small">Parking</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-lock me-1"></i> Failed Login Attempts</span>
                            <span class="fw-bold text-danger small">8 (Last 24h)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock me-1"></i> System Uptime</span>
                            <span class="fw-bold text-success small">14 days, 6 hrs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd me-1"></i> Last Backup Time</span>
                            <span class="fw-bold text-secondary small">30 Jul, 02:00 AM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Security Events (Timeline) -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Recent Security Events</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3 position-relative ps-3" style="border-left: 2px solid #e9ecef;">
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-warning rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 6px;"></span>
                            <div class="mb-1">
                                <span class="fw-medium small text-dark">Failed Login Attempt</span>
                                <span class="text-muted small d-block">User: sneha.m@email.com | IP: 192.168.1.10</span>
                                <span class="text-muted small">30 Jul, 08:55 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-danger rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 6px;"></span>
                            <div class="mb-1">
                                <span class="fw-medium small text-dark">Unusual Activity Detected</span>
                                <span class="text-muted small d-block">Multiple requests from same IP in 1s.</span>
                                <span class="text-muted small">30 Jul, 08:20 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-success rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 6px;"></span>
                            <div class="mb-1">
                                <span class="fw-medium small text-dark">Password Changed Successfully</span>
                                <span class="text-muted small d-block">User: rahul.s@email.com</span>
                                <span class="text-muted small">30 Jul, 07:15 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-success rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 6px;"></span>
                            <div class="mb-1">
                                <span class="fw-medium small text-dark">2FA Enabled</span>
                                <span class="text-muted small d-block">User: vikram.p@email.com</span>
                                <span class="text-muted small">29 Jul, 11:45 PM</span>
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