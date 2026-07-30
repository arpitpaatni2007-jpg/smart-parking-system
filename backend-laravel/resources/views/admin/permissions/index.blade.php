@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Permissions Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Roles & Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Export</button>
            <button class="btn btn-outline-info btn-sm"><i class="bi bi-shield-plus me-1"></i>Assign Permissions</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Create Permission</button>
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
                                <i class="bi bi-shield-check fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Total Permissions</h6>
                            <h5 class="mb-0 fw-bold">120</h5>
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
                                <i class="bi bi-check-all fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Assigned</h6>
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
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-dash-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Unassigned</h6>
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
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-gear-fill fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">System</h6>
                            <h5 class="mb-0 fw-bold">35</h5>
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
                                <i class="bi bi-pencil-fill fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 fs-7 fw-normal">Custom</h6>
                            <h5 class="mb-0 fw-bold">85</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Role Selector Section -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="searchPermission" class="form-label fs-7 fw-semibold text-muted">Search Permission</label>
                    <input type="text" class="form-control" id="searchPermission" placeholder="Permission name...">
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="roleSelector" class="form-label fs-7 fw-semibold text-muted">Select Role</label>
                    <select class="form-select" id="roleSelector">
                        <option value="superadmin">Super Admin</option>
                        <option value="admin" selected>Admin</option>
                        <option value="owner">Parking Owner</option>
                        <option value="agent">Support Agent</option>
                        <option value="finance">Finance Manager</option>
                        <option value="ops">Operations Manager</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterCategory" class="form-label fs-7 fw-semibold text-muted">Category</label>
                    <select class="form-select" id="filterCategory">
                        <option value="">All Categories</option>
                        <option value="dashboard">Dashboard</option>
                        <option value="users">Users</option>
                        <option value="owners">Parking Owners</option>
                        <option value="parking">Parking</option>
                        <option value="bookings">Bookings</option>
                        <option value="payments">Payments</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="filterStatus" class="form-label fs-7 fw-semibold text-muted">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1">Reset Filters</button>
                    <button type="button" class="btn btn-primary flex-grow-1">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Matrix & Summary Sidebar Row -->
    <div class="row g-4">
        <!-- Left Column: Matrix -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Permissions Matrix <span class="badge bg-light text-dark fw-normal rounded-pill ms-2">Admin Role</span></h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        
                        <!-- Dashboard -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View Dashboard</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Analytics</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-success mb-0"><i class="bi bi-people me-2"></i>Users</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View Users</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Create Users</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Edit Users</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Delete Users</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parking Owners -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-warning mb-0"><i class="bi bi-house-door me-2"></i>Parking Owners</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Create</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Edit</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Delete</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Approve</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parking -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-info mb-0"><i class="bi bi-pin-map me-2"></i>Parking</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Create</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Edit</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Delete</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Assign</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bookings -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-calendar-check me-2"></i>Bookings</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Approve</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Cancel</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Refund</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payments -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-danger mb-0"><i class="bi bi-credit-card me-2"></i>Payments</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Verify</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Refund</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Export</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reports -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up me-2"></i>Reports</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Generate</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Download</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Support -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-headset me-2"></i>Support</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View Tickets</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Assign Tickets</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Reply</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Resolve</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CMS -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-success mb-0"><i class="bi bi-file-earmark-text me-2"></i>CMS</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Create</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Edit</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Delete</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Publish</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-gear me-2"></i>Settings</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">View</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                        <span class="small fw-medium">Edit</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- Bottom Action Buttons -->
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                <button class="btn btn-outline-secondary">Cancel</button>
                <button class="btn btn-outline-danger"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Permissions</button>
                <button class="btn btn-primary"><i class="bi bi-floppy me-1"></i>Save Permissions</button>
            </div>
        </div>

        <!-- Right Column: Summary Sidebar -->
        <div class="col-xl-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clipboard-data me-2"></i>Permission Summary</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-person-badge me-1"></i> Selected Role</span>
                            <span class="fw-bold text-primary small">Admin</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-grid-3x3 me-1"></i> Total Modules</span>
                            <span class="fw-bold text-secondary small">10</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-check-circle text-success me-1"></i> Enabled Permissions</span>
                            <span class="fw-bold text-success small">78</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-dash-circle text-danger me-1"></i> Disabled Permissions</span>
                            <span class="fw-bold text-danger small">42</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock-history me-1"></i> Last Updated</span>
                            <span class="fw-bold text-secondary small">30 Jul, 2026</span>
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
    .form-switch .form-check-input { cursor: pointer; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection