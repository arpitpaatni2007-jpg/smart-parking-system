@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Notifications Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
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
                                <i class="bi bi-bell fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Notifications</h6>
                            <h5 class="mb-0 fw-bold">2,458</h5>
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
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Unread Notifications</h6>
                            <h5 class="mb-0 fw-bold">152</h5>
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
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Scheduled</h6>
                            <h5 class="mb-0 fw-bold">34</h5>
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
                                <i class="bi bi-x-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Failed</h6>
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
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-phone fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Push Sent</h6>
                            <h5 class="mb-0 fw-bold">1,874</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Notifications</h6>
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="searchNotif" class="form-label fs-7 fw-semibold text-muted">Search</label>
                    <input type="text" class="form-control" id="searchNotif" placeholder="Title or ID...">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterNotifType" class="form-label fs-7 fw-semibold text-muted">Notification Type</label>
                    <select class="form-select" id="filterNotifType">
                        <option value="">All Types</option>
                        <option value="push">Push</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="inapp">In-App</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterAudience" class="form-label fs-7 fw-semibold text-muted">Audience</label>
                    <select class="form-select" id="filterAudience">
                        <option value="">All</option>
                        <option value="all">All Users</option>
                        <option value="owners">Owners</option>
                        <option value="customers">Customers</option>
                        <option value="admins">Admins</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterDeliveryStatus" class="form-label fs-7 fw-semibold text-muted">Delivery Status</label>
                    <select class="form-select" id="filterDeliveryStatus">
                        <option value="">All</option>
                        <option value="sent">Sent</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="draft">Draft</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
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

    <!-- Create Notification & Sidebar Row -->
    <div class="row g-4 mb-4">
        <!-- Create Notification Card -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Notification</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="notifTitle" class="form-label fs-7 fw-semibold text-muted">Notification Title</label>
                            <input type="text" class="form-control" id="notifTitle" placeholder="Enter notification title">
                        </div>
                        <div class="col-md-6">
                            <label for="notifType" class="form-label fs-7 fw-semibold text-muted">Notification Type</label>
                            <select class="form-select" id="notifType">
                                <option value="push">Push</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="inapp">In-App</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="notifMessage" class="form-label fs-7 fw-semibold text-muted">Notification Message</label>
                            <textarea class="form-control" id="notifMessage" rows="3" placeholder="Type your message here..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="notifAudience" class="form-label fs-7 fw-semibold text-muted">Audience</label>
                            <select class="form-select" id="notifAudience">
                                <option value="all">All Users</option>
                                <option value="owners">Owners</option>
                                <option value="customers">Customers</option>
                                <option value="admins">Admins</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="notifPriority" class="form-label fs-7 fw-semibold text-muted">Priority</label>
                            <select class="form-select" id="notifPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="notifSchedule" class="form-label fs-7 fw-semibold text-muted">Schedule Date & Time</label>
                            <input type="datetime-local" class="form-control" id="notifSchedule">
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-7 fw-semibold text-muted">Attachment</label>
                            <div class="border rounded-3 p-3 bg-light bg-opacity-25 text-center cursor-pointer" style="border-style: dashed !important;">
                                <i class="bi bi-paperclip fs-5 text-muted d-block mb-1"></i>
                                <span class="small text-muted">Click to upload or drag file here</span>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm">Preview</button>
                            <button type="button" class="btn btn-success btn-sm"><i class="bi bi-send me-1"></i>Send Now</button>
                            <button type="button" class="btn btn-primary btn-sm"><i class="bi bi-clock me-1"></i>Schedule</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Sidebar -->
        <div class="col-xl-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-calendar-day me-1"></i> Today's Notifications</span>
                            <span class="fw-bold">86</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-check2-circle me-1"></i> Delivery Success %</span>
                            <span class="fw-bold text-success">98.4%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-envelope-open me-1"></i> Open Rate</span>
                            <span class="fw-bold text-primary">42.6%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-mouse2 me-1"></i> Click Rate</span>
                            <span class="fw-bold text-warning">18.2%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications Table -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Notifications</h6>
            <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="ps-3">Notif ID</th>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Type</th>
                            <th>Sent Date</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#NTF-984</span></td>
                            <td>Weekly Booking Reminder</td>
                            <td>All Users</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">Push</span></td>
                            <td>29 Jul, 2026</td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Sent</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resend"><i class="bi bi-send"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#NTF-983</span></td>
                            <td>Monthly Invoice Summary</td>
                            <td>Owners</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Email</span></td>
                            <td>29 Jul, 2026</td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Scheduled</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resend"><i class="bi bi-send"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#NTF-982</span></td>
                            <td>Parking Slot Release Alert</td>
                            <td>Customers</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">SMS</span></td>
                            <td>28 Jul, 2026</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">Draft</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resend"><i class="bi bi-send"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">#NTF-981</span></td>
                            <td>System Maintenance Alert</td>
                            <td>Admins</td>
                            <td><span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25">In-App</span></td>
                            <td>27 Jul, 2026</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Failed</span></td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Resend"><i class="bi bi-send"></i></button>
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
    .cursor-pointer { cursor: pointer; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection