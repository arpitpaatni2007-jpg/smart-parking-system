@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Users Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Users</h6>
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
                                <i class="bi bi-check-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Active Users</h6>
                            <h5 class="mb-0 fw-bold">10,245</h5>
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
                                <i class="bi bi-slash-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Blocked Users</h6>
                            <h5 class="mb-0 fw-bold">1,253</h5>
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
                                <i class="bi bi-person-plus fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">New Today</h6>
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
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-star-fill fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Premium Members</h6>
                            <h5 class="mb-0 fw-bold">3,102</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            
            <!-- Filter Section -->
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label for="searchUser" class="form-label fs-7 fw-semibold text-muted">Search User</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchUser" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="filterId" class="form-label fs-7 fw-semibold text-muted">User ID</label>
                    <input type="text" class="form-control" id="filterId" placeholder="e.g. USR001">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="filterCity" class="form-label fs-7 fw-semibold text-muted">City</label>
                    <input type="text" class="form-control" id="filterCity" placeholder="Enter city">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="filterRegDate" class="form-label fs-7 fw-semibold text-muted">Reg. Date</label>
                    <input type="date" class="form-control" id="filterRegDate">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Account Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                        <option value="pending">Pending Verification</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filterMembership" class="form-label fs-7 fw-semibold text-muted">Membership Type</label>
                    <select class="form-select" id="filterMembership">
                        <option value="">All Memberships</option>
                        <option value="basic">Basic</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset</button>
                    <button type="button" class="btn btn-primary flex-grow-1">Apply Filters</button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 80px;">User ID</th>
                            <th style="width: 50px;">Img</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Reg. Date</th>
                            <th class="text-center" style="width: 90px;">Bookings</th>
                            <th style="width: 100px;">Membership</th>
                            <th style="width: 120px;">Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">USR001</span></td>
                            <td>
                                <div class="avatar-xs d-inline-block rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <span class="text-secondary fw-bold fs-6">RM</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">Rahul Sharma</span>
                                    <span class="text-muted fs-7">+91 98765 43210</span>
                                </div>
                            </td>
                            <td>rahul.s@email.com</td>
                            <td>+91 98765 43210</td>
                            <td>Mumbai</td>
                            <td>25 Mar, 2026</td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">14</span></td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Gold</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Block"><i class="bi bi-slash-circle"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">USR024</span></td>
                            <td>
                                <div class="avatar-xs d-inline-block rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <span class="text-secondary fw-bold fs-6">PS</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">Priya Singh</span>
                                    <span class="text-muted fs-7">+91 99887 76655</span>
                                </div>
                            </td>
                            <td>priya.s@email.com</td>
                            <td>+91 99887 76655</td>
                            <td>Delhi</td>
                            <td>18 Mar, 2026</td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">8</span></td>
                            <td><span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill border border-info border-opacity-25">Silver</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Active</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Block"><i class="bi bi-slash-circle"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <!-- Row 3 -->
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">USR005</span></td>
                            <td>
                                <div class="avatar-xs d-inline-block rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <span class="text-secondary fw-bold fs-6">AK</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">Amit Kumar</span>
                                    <span class="text-muted fs-7">+91 99887 76655</span>
                                </div>
                            </td>
                            <td>amit.k@email.com</td>
                            <td>+91 99887 76655</td>
                            <td>Bangalore</td>
                            <td>15 Feb, 2026</td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">2</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Basic</span></td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal rounded-pill border border-secondary border-opacity-25">Inactive</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Block"><i class="bi bi-slash-circle"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <!-- Row 4 -->
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">USR112</span></td>
                            <td>
                                <div class="avatar-xs d-inline-block rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <span class="text-secondary fw-bold fs-6">SM</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">Sneha Mehta</span>
                                    <span class="text-muted fs-7">+91 98765 12345</span>
                                </div>
                            </td>
                            <td>sneha.m@email.com</td>
                            <td>+91 98765 12345</td>
                            <td>Pune</td>
                            <td>10 Jan, 2026</td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">22</span></td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Premium</span></td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Blocked</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-success border-0 p-1" title="Unblock"><i class="bi bi-check-circle"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        <!-- Row 5 -->
                        <tr>
                            <td class="ps-3"><span class="fw-medium text-primary">USR045</span></td>
                            <td>
                                <div class="avatar-xs d-inline-block rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <span class="text-secondary fw-bold fs-6">VP</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">Vikram Patel</span>
                                    <span class="text-muted fs-7">+91 99887 11223</span>
                                </div>
                            </td>
                            <td>vikram.p@email.com</td>
                            <td>+91 99887 11223</td>
                            <td>Chennai</td>
                            <td>05 Jan, 2026</td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill">0</span></td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Gold</span></td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Pending Verification</span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary border-0 p-1" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-1" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Block"><i class="bi bi-slash-circle"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="text-muted small">
                    Showing 1 to 5 of 100 entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">...</a></li>
                        <li class="page-item"><a class="page-link" href="#">10</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            
        </div>
    </div>
</div>

<style>
    .fs-7 { font-size: 0.75rem; }
    .avatar-xs { width: 36px; height: 36px; }
    .table th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 1px solid #e9ecef; }
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; }
    .pagination .page-link { font-size: 0.875rem; }
</style>
@endsection