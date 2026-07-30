@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Admin Profile</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit Profile</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-floppy me-1"></i>Save Changes</button>
        </div>
    </div>

    <!-- Profile Header / Banner -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
        <div class="bg-primary bg-opacity-10" style="height: 100px;"></div>
        <div class="card-body p-4 pt-0">
            <div class="row g-4 align-items-end">
                <div class="col-md-3 text-center text-md-start" style="margin-top: -50px;">
                    <div class="bg-secondary bg-opacity-25 rounded-circle d-inline-block border border-4 border-white shadow-sm" style="width: 120px; height: 120px;">
                        <div class="d-flex align-items-center justify-content-center w-100 h-100 text-secondary fw-bold fs-1">AD</div>
                    </div>
                </div>
                <div class="col-md-6 pt-4 pt-md-0">
                    <h4 class="fw-bold mb-1">Admin User</h4>
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">Super Admin</span>
                        <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Online</span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-muted small">
                        <span><i class="bi bi-envelope me-1"></i> admin@smartparking.com</span>
                        <span><i class="bi bi-telephone me-1"></i> +91 98765 43210</span>
                        <span><i class="bi bi-calendar3 me-1"></i> Member Since: 01 Jan, 2026</span>
                    </div>
                </div>
                <div class="col-md-3 text-md-end pt-2">
                    <button class="btn btn-outline-primary btn-sm w-100 w-md-auto"><i class="bi bi-camera me-1"></i>Change Photo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-xl-8">
            
            <!-- Profile Information -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person-lines-fill me-2"></i>Profile Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label fs-7 fw-semibold text-muted">First Name</label>
                            <input type="text" class="form-control" id="firstName" value="Admin">
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label fs-7 fw-semibold text-muted">Last Name</label>
                            <input type="text" class="form-control" id="lastName" value="User">
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label fs-7 fw-semibold text-muted">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" class="form-control" id="username" value="adminuser">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fs-7 fw-semibold text-muted">Email</label>
                            <input type="email" class="form-control" id="email" value="admin@smartparking.com">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fs-7 fw-semibold text-muted">Phone</label>
                            <input type="text" class="form-control" id="phone" value="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label for="dob" class="form-label fs-7 fw-semibold text-muted">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" value="1990-01-15">
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label fs-7 fw-semibold text-muted">Gender</label>
                            <select class="form-select" id="gender">
                                <option value="male" selected>Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label fs-7 fw-semibold text-muted">Address</label>
                            <input type="text" class="form-control" id="address" value="123, MG Road, Andheri East">
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label fs-7 fw-semibold text-muted">City</label>
                            <input type="text" class="form-control" id="city" value="Mumbai">
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label fs-7 fw-semibold text-muted">State</label>
                            <input type="text" class="form-control" id="state" value="Maharashtra">
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label fs-7 fw-semibold text-muted">Country</label>
                            <input type="text" class="form-control" id="country" value="India">
                        </div>
                        <div class="col-md-4">
                            <label for="postalCode" class="form-label fs-7 fw-semibold text-muted">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" value="400093">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-gear-wide-connected me-2"></i>Account Settings</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="accUsername" class="form-label fs-7 fw-semibold text-muted">Username</label>
                            <input type="text" class="form-control" id="accUsername" value="adminuser">
                        </div>
                        <div class="col-md-6">
                            <label for="accEmail" class="form-label fs-7 fw-semibold text-muted">Primary Email</label>
                            <input type="email" class="form-control" id="accEmail" value="admin@smartparking.com">
                        </div>
                        <div class="col-md-6">
                            <label for="language" class="form-label fs-7 fw-semibold text-muted">Language</label>
                            <select class="form-select" id="language">
                                <option value="en" selected>English</option>
                                <option value="es">Español</option>
                                <option value="fr">Français</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="timezone" class="form-label fs-7 fw-semibold text-muted">Timezone</label>
                            <select class="form-select" id="timezone">
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Asia/Kolkata" selected>Asia/Kolkata</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="theme" class="form-label fs-7 fw-semibold text-muted">Theme Preference</label>
                            <select class="form-select" id="theme">
                                <option value="light" selected>Light</option>
                                <option value="dark">Dark</option>
                                <option value="system">System Default</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="notifPref" class="form-label fs-7 fw-semibold text-muted">Notification Preference</label>
                            <select class="form-select" id="notifPref">
                                <option value="email" selected>Email</option>
                                <option value="sms">SMS</option>
                                <option value="push">Push</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password & 2FA Row -->
            <div class="row g-4 mb-4">
                <!-- Change Password -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-key me-2"></i>Change Password</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="vstack gap-3">
                                <div>
                                    <label for="currentPassword" class="form-label fs-7 fw-semibold text-muted">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" value="********">
                                </div>
                                <div>
                                    <label for="newPassword" class="form-label fs-7 fw-semibold text-muted">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" value="********">
                                </div>
                                <div>
                                    <label for="confirmPassword" class="form-label fs-7 fw-semibold text-muted">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" value="********">
                                </div>
                                <div>
                                    <label class="form-label fs-7 fw-semibold text-muted">Password Strength</label>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small text-success fw-medium mt-1 d-block">Strong password</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Two-Factor Auth</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="vstack gap-3">
                                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                    <div>
                                        <h6 class="fw-medium mb-0 small">Enable 2FA</h6>
                                        <small class="text-muted">Add an extra layer of security.</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="twoFactorToggle" checked>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label fs-7 fw-semibold text-muted">Authenticator App</label>
                                    <div class="border rounded-3 p-3 bg-light bg-opacity-25 text-center">
                                        <div class="bg-white border rounded-2 p-2 d-inline-block mb-2">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <span class="fw-bold small text-muted" style="letter-spacing: 4px;">ABC1 2DEF 3GHI 4JKL</span>
                                                <i class="bi bi-clipboard text-primary cursor-pointer"></i>
                                            </div>
                                        </div>
                                        <span class="small text-muted d-block">Scan this code with your authenticator app</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label fs-7 fw-semibold text-muted">Recovery Codes</label>
                                    <div class="border rounded-3 p-3 bg-light bg-opacity-25 text-center">
                                        <span class="small text-muted">12 recovery codes available.</span>
                                        <button class="btn btn-outline-secondary btn-sm mt-1 w-100"><i class="bi bi-arrow-repeat me-1"></i>Regenerate Codes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login History -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Login History</h6>
                    <a href="#" class="text-decoration-none small fw-medium">View All <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Time</th>
                                    <th>IP Address</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>Location</th>
                                    <th class="pe-3 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3">30 Jul, 2026</td>
                                    <td>10:30 AM</td>
                                    <td>192.168.1.45</td>
                                    <td>MacBook Pro</td>
                                    <td>Chrome 115</td>
                                    <td>Mumbai, India</td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Success</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3">29 Jul, 2026</td>
                                    <td>06:15 PM</td>
                                    <td>10.0.0.1</td>
                                    <td>iPhone 14</td>
                                    <td>Safari 16</td>
                                    <td>Mumbai, India</td>
                                    <td class="pe-3 text-end"><span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25">Success</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3">28 Jul, 2026</td>
                                    <td>09:45 AM</td>
                                    <td>172.16.0.8</td>
                                    <td>Windows PC</td>
                                    <td>Firefox 120</td>
                                    <td>Mumbai, India</td>
                                    <td class="pe-3 text-end"><span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Failed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Timeline -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3 position-relative ps-3" style="border-left: 2px solid #e9ecef;">
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-primary rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">Profile Updated</span>
                                <span class="text-muted small d-block">Updated email preferences and theme settings.</span>
                                <span class="text-muted small">30 Jul, 2026 09:15 AM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-warning rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">Password Changed</span>
                                <span class="text-muted small d-block">Account password was successfully updated.</span>
                                <span class="text-muted small">29 Jul, 2026 06:10 PM</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle-x bg-success rounded-circle border border-2 border-white" style="width: 10px; height: 10px; left: -6px; margin-top: 4px;"></span>
                            <div>
                                <span class="fw-medium small text-dark">Successful Login</span>
                                <span class="text-muted small d-block">Logged in from Chrome on MacBook Pro.</span>
                                <span class="text-muted small">29 Jul, 2026 08:30 AM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Buttons -->
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <button class="btn btn-outline-secondary">Cancel</button>
                <button class="btn btn-outline-primary"><i class="bi bi-arrow-up-circle me-1"></i>Update Profile</button>
                <button class="btn btn-primary"><i class="bi bi-floppy me-1"></i>Save Changes</button>
            </div>

        </div>

        <!-- Right Column: Sidebar -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i>Profile Overview</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-medium"><i class="bi bi-check2-circle me-1"></i> Profile Completion</span>
                                <span class="fw-bold text-success small">85%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-shield-check me-1"></i> Account Status</span>
                            <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Active</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd-stack me-1"></i> Storage Used</span>
                            <span class="fw-bold text-warning small">2.8 GB / 10 GB</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock-history me-1"></i> Last Login</span>
                            <span class="fw-bold text-secondary small">30 Jul, 2026 10:30 AM</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-shield-fill-check me-1"></i> Security Score</span>
                            <span class="fw-bold text-success small">Excellent</span>
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
    .bg-light { background-color: #f8f9fa !important; }
    .cursor-pointer { cursor: pointer; }
    .form-switch .form-check-input { cursor: pointer; }
</style>
@endsection