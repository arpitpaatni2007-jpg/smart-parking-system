@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">User Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-arrow-left me-1"></i>Back</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit User</button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-xl-8">
            <!-- Profile & Personal Info Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-4 border-end-lg">
                            <div class="text-center text-lg-start">
                                <div class="position-relative d-inline-block mb-3">
                                    <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto mx-lg-0" style="width: 120px; height: 120px;">
                                        <span class="text-secondary fw-bold display-6">RM</span>
                                    </div>
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 18px; height: 18px;"></span>
                                </div>
                                <h5 class="fw-bold mb-1">Rahul Sharma</h5>
                                <p class="text-muted mb-3"><small>USR001</small></p>
                                <div class="d-flex flex-wrap gap-2 mb-3 justify-content-center justify-content-lg-start">
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Gold</span>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span>
                                </div>
                                <div class="vstack gap-2 text-muted small">
                                    <div><i class="bi bi-calendar3 me-2"></i>Joined: 25 Mar, 2026</div>
                                    <div><i class="bi bi-clock-history me-2"></i>Last Login: 29 Jul, 2026 10:45 AM</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <h6 class="fw-bold mb-3 text-uppercase text-muted small">Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Full Name</small>
                                        <span class="fw-medium">Rahul Sharma</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Email</small>
                                        <span class="fw-medium">rahul.s@email.com</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Phone Number</small>
                                        <span class="fw-medium">+91 98765 43210</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Gender</small>
                                        <span class="fw-medium">Male</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Date of Birth</small>
                                        <span class="fw-medium">15 Aug, 1995</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">City</small>
                                        <span class="fw-medium">Mumbai</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">State</small>
                                        <span class="fw-medium">Maharashtra</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Country</small>
                                        <span class="fw-medium">India</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                        <small class="text-muted d-block mb-1">Address</small>
                                        <span class="fw-medium">123, MG Road, Andheri East, Mumbai - 400093</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-car-front-fill me-2"></i>Vehicle Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                <small class="text-muted d-block mb-1">Vehicle Number</small>
                                <span class="fw-medium">MH-01-AB-1234</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                <small class="text-muted d-block mb-1">Vehicle Type</small>
                                <span class="fw-medium">Sedan</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                <small class="text-muted d-block mb-1">Vehicle Brand</small>
                                <span class="fw-medium">Hyundai</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                <small class="text-muted d-block mb-1">Vehicle Model</small>
                                <span class="fw-medium">Verna</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2">
                                <small class="text-muted d-block mb-1">Colour</small>
                                <span class="fw-medium">White</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking History Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>Booking History</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3">Booking ID</th>
                                    <th>Parking Name</th>
                                    <th>Entry Time</th>
                                    <th>Exit Time</th>
                                    <th>Amount</th>
                                    <th class="pe-3 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#BK-7861</span></td>
                                    <td>Downtown Mall Parking</td>
                                    <td>29 Jul, 10:30 AM</td>
                                    <td>29 Jul, 02:45 PM</td>
                                    <td><span class="fw-medium">₹250</span></td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Completed</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#BK-7740</span></td>
                                    <td>City Center Garage</td>
                                    <td>28 Jul, 08:00 PM</td>
                                    <td>28 Jul, 11:00 PM</td>
                                    <td><span class="fw-medium">₹180</span></td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Completed</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#BK-7601</span></td>
                                    <td>Airport Parking Plaza</td>
                                    <td>25 Jul, 06:00 AM</td>
                                    <td>25 Jul, 12:00 PM</td>
                                    <td><span class="fw-medium">₹500</span></td>
                                    <td class="pe-3 text-end"><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Cancelled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>Payment History</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3">Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Date</th>
                                    <th class="pe-3 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TXN-987654</span></td>
                                    <td><span class="fw-medium">₹250</span></td>
                                    <td>Credit Card</td>
                                    <td>29 Jul, 2026</td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Success</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TXN-984521</span></td>
                                    <td><span class="fw-medium">₹180</span></td>
                                    <td>UPI</td>
                                    <td>28 Jul, 2026</td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Success</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><span class="fw-medium text-primary">#TXN-980112</span></td>
                                    <td><span class="fw-medium">₹500</span></td>
                                    <td>Credit Card</td>
                                    <td>25 Jul, 2026</td>
                                    <td class="pe-3 text-end"><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Failed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-xl-4">
            <!-- Account Statistics Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Account Statistics</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-6 border-end">
                            <h5 class="fw-bold mb-0 text-primary">14</h5>
                            <small class="text-muted">Total Bookings</small>
                        </div>
                        <div class="col-6">
                            <h5 class="fw-bold mb-0 text-success">12</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-6 border-end border-top pt-3">
                            <h5 class="fw-bold mb-0 text-danger">2</h5>
                            <small class="text-muted">Cancelled</small>
                        </div>
                        <div class="col-6 border-top pt-3">
                            <h5 class="fw-bold mb-0 text-warning">₹4,850</h5>
                            <small class="text-muted">Amount Spent</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Overview Sidebar Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4 bg-light bg-opacity-25">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-person-badge me-2"></i>Account Overview</h6>
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-award me-1"></i> Membership</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Gold</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-shield-check me-1"></i> Verification</span>
                            <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-wallet2 me-1"></i> Wallet Balance</span>
                            <span class="fw-bold text-success">₹240</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-stars me-1"></i> Reward Points</span>
                            <span class="fw-bold text-primary">1,250</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock-history me-1"></i> Last Active</span>
                            <span class="small fw-medium">10 mins ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone Action Buttons -->
            <div class="card shadow-sm border-0 rounded-3 border border-danger border-opacity-25">
                <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 py-3">
                    <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-danger btn-sm fw-medium"><i class="bi bi-slash-circle me-2"></i>Block User</button>
                        <button class="btn btn-outline-danger btn-sm fw-medium"><i class="bi bi-trash3 me-2"></i>Delete User</button>
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
    .bg-light { background-color: #f8f9fa !important; }
    .border-end-lg { 
        border-right: 1px solid #e9ecef; 
    }
    @media (max-width: 991.98px) {
        .border-end-lg { 
            border-right: none !important; 
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 2rem;
            margin-bottom: 1rem;
        }
    }
</style>
@endsection