@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Configuration Dashboard</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">System Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Export Status Report</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh System</button>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">System Status</h6>
                        <h5 class="mb-0 fw-bold text-success">Operational</h5>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">Server Status</h6>
                        <h5 class="mb-0 fw-bold text-success">Running</h5>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-hdd-network fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">Database Status</h6>
                        <h5 class="mb-0 fw-bold text-success">Connected</h5>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-database-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">API Status</h6>
                        <h5 class="mb-0 fw-bold text-success">Healthy</h5>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-cloud-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">Storage Usage</h6>
                        <h5 class="mb-0 fw-bold text-warning">45.8 GB / 100 GB</h5>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-hdd-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">Memory Usage</h6>
                        <h5 class="mb-0 fw-bold text-info">4.2 GB / 8 GB</h5>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-memory fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fs-7 fw-normal mb-1">CPU Usage</h6>
                        <h5 class="mb-0 fw-bold text-primary">32%</h5>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-cpu fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-xl-8">
            
            <!-- Application Information -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Application Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Application Name</small>
                                <span class="fw-medium">Smart Parking System</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Version</small>
                                <span class="fw-medium">v3.2.1</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Environment</small>
                                <span class="fw-medium text-success">Production</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Laravel Version</small>
                                <span class="fw-medium">v12.0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">PHP Version</small>
                                <span class="fw-medium">v8.3.0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Database Engine</small>
                                <span class="fw-medium">MySQL 8.0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Server Time</small>
                                <span class="fw-medium">11:45 AM (UTC+5:30)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light bg-opacity-50 p-2 rounded-2 border">
                                <small class="text-muted d-block mb-1 fs-7">Timezone</small>
                                <span class="fw-medium">Asia/Kolkata</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Status -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-server me-2"></i>Service Status</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Authentication</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Email Service</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">SMS Service</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Payment Gateway</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Google Maps</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25 small">Offline</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Notification Service</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">File Storage</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-25">
                                <span class="small fw-medium">Cron Jobs</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Online</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Overview -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-shield-shaded me-2"></i>Security Overview</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded-3 bg-light bg-opacity-25 h-100">
                                <div class="text-success mb-2"><i class="bi bi-lock-fill fs-4"></i></div>
                                <h6 class="fw-medium mb-0 small">SSL Status</h6>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small mt-1">Active</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded-3 bg-light bg-opacity-25 h-100">
                                <div class="text-primary mb-2"><i class="bi bi-shield-check fs-4"></i></div>
                                <h6 class="fw-medium mb-0 small">Firewall</h6>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small mt-1">Enabled</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded-3 bg-light bg-opacity-25 h-100">
                                <div class="text-info mb-2"><i class="bi bi-shield-lock fs-4"></i></div>
                                <h6 class="fw-medium mb-0 small">2FA Status</h6>
                                <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small mt-1">Enabled</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded-3 bg-light bg-opacity-25 h-100">
                                <div class="text-danger mb-2"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                                <h6 class="fw-medium mb-0 small">Failed Login Attempts</h6>
                                <span class="fw-bold text-danger small mt-1">8 (Last 24h)</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 p-3 rounded-3 border border-warning border-opacity-25">
                            <div>
                                <span class="fw-medium small text-warning"><i class="bi bi-bell-fill me-1"></i>Recent Security Alert</span>
                                <p class="small text-muted mb-0 mt-1">Multiple failed login attempts detected from IP 192.168.1.10</p>
                            </div>
                            <span class="text-muted small">1 min ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Performance -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2"></i>System Performance</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">CPU Usage</span>
                                <span class="small fw-bold text-primary">32%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 32%;" aria-valuenow="32" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">Memory Usage</span>
                                <span class="small fw-bold text-info">52.5%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 52.5%;" aria-valuenow="52.5" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">Disk Usage</span>
                                <span class="small fw-bold text-warning">45.8%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 45.8%;" aria-valuenow="45.8" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-medium text-muted">Network Usage</span>
                                <span class="small fw-bold text-success">12.4%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 12.4%;" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent System Events Timeline -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent System Events</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3 position-relative ps-3" style="border-left: 2px solid #e9ecef;">
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-primary rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">New Backup Created</span>
                                <span class="text-muted small d-block">Automated backup completed successfully.</span>
                                <span class="text-muted small">30 Jul, 2026 02:00 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-success rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">System Update Deployed</span>
                                <span class="text-muted small d-block">Applied security patch v3.2.1 to the application.</span>
                                <span class="text-muted small">29 Jul, 2026 10:15 PM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-warning rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">Maintenance Window Scheduled</span>
                                <span class="text-muted small d-block">Planned downtime for 31 Jul, 2026 02:00 AM - 04:00 AM.</span>
                                <span class="text-muted small">29 Jul, 2026 09:00 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-info rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">Cache Cleared</span>
                                <span class="text-muted small d-block">System cache was cleared successfully.</span>
                                <span class="text-muted small">28 Jul, 2026 06:30 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installed Modules -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-boxes me-2"></i>Installed Modules</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-speedometer2 fs-4 text-primary mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Dashboard</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-pin-map fs-4 text-success mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Parking</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-calendar-check fs-4 text-warning mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Bookings</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-credit-card fs-4 text-danger mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Payments</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-graph-up fs-4 text-info mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Reports</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-file-earmark-text fs-4 text-secondary mb-2"></i>
                                <span class="small fw-medium d-block text-dark">CMS</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-headset fs-4 text-primary mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Support</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-people fs-4 text-success mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Users</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-person-badge fs-4 text-warning mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Roles</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-shield-check fs-4 text-danger mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Permissions</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-gear fs-4 text-info mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Settings</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-clock-history fs-4 text-secondary mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Activity Logs</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light bg-opacity-50 rounded-3 p-3 text-center h-100 border">
                                <i class="bi bi-person-circle fs-4 text-primary mb-2"></i>
                                <span class="small fw-medium d-block text-dark">Profile</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Buttons -->
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                <button class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
                <button class="btn btn-outline-primary"><i class="bi bi-tools me-1"></i>Run Diagnostics</button>
                <button class="btn btn-primary"><i class="bi bi-download me-1"></i>Download Report</button>
            </div>

        </div>

        <!-- Right Column: Sidebar -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Quick System Info</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock me-1"></i> Current Uptime</span>
                            <span class="fw-bold text-success small">14 days, 6 hrs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd me-1"></i> Last Backup</span>
                            <span class="fw-bold text-secondary small">30 Jul, 02:00 AM</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-calendar-check me-1"></i> Next Backup</span>
                            <span class="fw-bold text-secondary small">31 Jul, 02:00 AM</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd-stack me-1"></i> Total Storage</span>
                            <span class="fw-bold text-secondary small">100 GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd-network me-1"></i> Available Storage</span>
                            <span class="fw-bold text-success small">54.2 GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-geo-alt me-1"></i> Server Location</span>
                            <span class="fw-bold text-secondary small">Mumbai, India</span>
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
    .bg-light { background-color: #f8f9fa !important; }
    .form-switch .form-check-input { cursor: pointer; }
</style>
@endsection