@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Support Tickets</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Support</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>New Ticket</button>
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
                                <i class="bi bi-ticket-perforated fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Tickets</h6>
                            <h5 class="mb-0 fw-bold">1,784</h5>
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
                                <i class="bi bi-envelope-exclamation fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Open Tickets</h6>
                            <h5 class="mb-0 fw-bold">54</h5>
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
                                <i class="bi bi-arrow-repeat fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">In Progress</h6>
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
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Resolved</h6>
                            <h5 class="mb-0 fw-bold">1,652</h5>
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
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Closed</h6>
                            <h5 class="mb-0 fw-bold">50</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Tickets</h6>
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="searchTicket" class="form-label fs-7 fw-semibold text-muted">Search</label>
                    <input type="text" class="form-control" id="searchTicket" placeholder="Subject or ID...">
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterTicketId" class="form-label fs-7 fw-semibold text-muted">Ticket ID</label>
                    <input type="text" class="form-control" id="filterTicketId" placeholder="e.g. #TKT-">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterCustomer" class="form-label fs-7 fw-semibold text-muted">Customer Name</label>
                    <input type="text" class="form-control" id="filterCustomer" placeholder="Customer name">
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterPriority" class="form-label fs-7 fw-semibold text-muted">Priority</label>
                    <select class="form-select" id="filterPriority">
                        <option value="">All</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterCategory" class="form-label fs-7 fw-semibold text-muted">Category</label>
                    <select class="form-select" id="filterCategory">
                        <option value="">All</option>
                        <option value="technical">Technical</option>
                        <option value="billing">Billing</option>
                        <option value="account">Account</option>
                        <option value="feature">Feature Request</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterAgent" class="form-label fs-7 fw-semibold text-muted">Assigned Agent</label>
                    <select class="form-select" id="filterAgent">
                        <option value="">All</option>
                        <option value="john">John Doe</option>
                        <option value="jane">Jane Smith</option>
                        <option value="mike">Mike Ross</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All</option>
                        <option value="open">Open</option>
                        <option value="inprogress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-3 col-md-6">
                    <label for="filterDateRange" class="form-label fs-7 fw-semibold text-muted">Date Range</label>
                    <input type="text" class="form-control" id="filterDateRange" placeholder="Select Dates">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset</button>
                    <button type="button" class="btn btn-primary flex-grow-1">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Tickets Table & Quick Sidebar Row -->
    <div class="row g-4">
        <!-- Main Table -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>All Support Tickets</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3">Ticket ID</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TKT-4210</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <span class="text-secondary small fw-bold">RS</span>
                                            </div>
                                            <span class="fw-medium small">Rahul Sharma</span>
                                        </div>
                                    </td>
                                    <td><span class="small text-truncate d-block" style="max-width: 150px;">Unable to book a parking slot</span></td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25 small">Technical</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Critical</span></td>
                                    <td><span class="small">Jane Smith</span></td>
                                    <td><span class="small text-muted">29 Jul, 2026</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Open</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Assign"><i class="bi bi-person-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Reply"><i class="bi bi-reply"></i></button>
                                        <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resolve"><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TKT-4209</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <span class="text-secondary small fw-bold">PS</span>
                                            </div>
                                            <span class="fw-medium small">Priya Singh</span>
                                        </div>
                                    </td>
                                    <td><span class="small text-truncate d-block" style="max-width: 150px;">Incorrect billing amount charged</span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">Billing</span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">High</span></td>
                                    <td><span class="small">John Doe</span></td>
                                    <td><span class="small text-muted">29 Jul, 2026</span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">In Progress</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Assign"><i class="bi bi-person-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Reply"><i class="bi bi-reply"></i></button>
                                        <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resolve"><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TKT-4208</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <span class="text-secondary small fw-bold">AK</span>
                                            </div>
                                            <span class="fw-medium small">Amit Kumar</span>
                                        </div>
                                    </td>
                                    <td><span class="small text-truncate d-block" style="max-width: 150px;">App crashing on login</span></td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25 small">Technical</span></td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25 small">Medium</span></td>
                                    <td><span class="small">Unassigned</span></td>
                                    <td><span class="small text-muted">28 Jul, 2026</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Open</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Assign"><i class="bi bi-person-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Reply"><i class="bi bi-reply"></i></button>
                                        <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resolve"><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TKT-4207</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <span class="text-secondary small fw-bold">SM</span>
                                            </div>
                                            <span class="fw-medium small">Sneha Mehta</span>
                                        </div>
                                    </td>
                                    <td><span class="small text-truncate d-block" style="max-width: 150px;">Request for premium membership discount</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Account</span></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25 small">Low</span></td>
                                    <td><span class="small">Mike Ross</span></td>
                                    <td><span class="small text-muted">27 Jul, 2026</span></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Resolved</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Assign"><i class="bi bi-person-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Reply"><i class="bi bi-reply"></i></button>
                                        <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resolve"><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TKT-4206</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <span class="text-secondary small fw-bold">VP</span>
                                            </div>
                                            <span class="fw-medium small">Vikram Patel</span>
                                        </div>
                                    </td>
                                    <td><span class="small text-truncate d-block" style="max-width: 150px;">Payment gateway not working</span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25 small">Billing</span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Critical</span></td>
                                    <td><span class="small">Jane Smith</span></td>
                                    <td><span class="small text-muted">26 Jul, 2026</span></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25 small">Closed</span></td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-info border-0 p-1" title="Assign"><i class="bi bi-person-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Reply"><i class="bi bi-reply"></i></button>
                                        <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resolve"><i class="bi bi-check-lg"></i></button>
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
                        <div class="text-muted small">Showing 1 to 5 of 100 entries</div>
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

        <!-- Quick Sidebar Card -->
        <div class="col-xl-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-calendar-day me-1"></i> Today's Tickets</span>
                            <span class="fw-bold">12</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock-history me-1"></i> Avg Response Time</span>
                            <span class="fw-bold text-primary">2.4 hrs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hourglass-split me-1"></i> Avg Resolution Time</span>
                            <span class="fw-bold text-success">6.8 hrs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-emoji-smile me-1"></i> Customer Satisfaction</span>
                            <span class="fw-bold text-warning">4.7 ★</span>
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