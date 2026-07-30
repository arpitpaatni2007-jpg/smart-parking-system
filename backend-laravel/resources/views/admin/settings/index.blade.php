@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Application Settings</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Settings</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-floppy me-1"></i>Save Changes</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Left Column (Tabs) -->
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-4 border-bottom-0 gap-2 flex-wrap" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-medium" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true"><i class="bi bi-house me-1"></i>General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab" aria-controls="company" aria-selected="false"><i class="bi bi-building me-1"></i>Company</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false"><i class="bi bi-envelope me-1"></i>Email</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms" type="button" role="tab" aria-controls="sms" aria-selected="false"><i class="bi bi-chat-dots me-1"></i>SMS</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false"><i class="bi bi-credit-card me-1"></i>Payment</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="maps-tab" data-bs-toggle="tab" data-bs-target="#maps" type="button" role="tab" aria-controls="maps" aria-selected="false"><i class="bi bi-geo-alt me-1"></i>Maps</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false"><i class="bi bi-shield-lock me-1"></i>Security</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false"><i class="bi bi-bell me-1"></i>Notifications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false"><i class="bi bi-hdd-stack me-1"></i>Backup</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab" aria-controls="maintenance" aria-selected="false"><i class="bi bi-tools me-1"></i>Maintenance</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="settingsTabsContent">
                        
                        <!-- 1. General -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">General Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="appName" class="form-label fs-7 fw-semibold text-muted">Application Name</label>
                                    <input type="text" class="form-control" id="appName" value="Smart Parking System">
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
                                    <label for="language" class="form-label fs-7 fw-semibold text-muted">Language</label>
                                    <select class="form-select" id="language">
                                        <option value="en" selected>English</option>
                                        <option value="es">Español</option>
                                        <option value="fr">Français</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency" class="form-label fs-7 fw-semibold text-muted">Currency</label>
                                    <select class="form-select" id="currency">
                                        <option value="USD">USD ($)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="INR" selected>INR (₹)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="dateFormat" class="form-label fs-7 fw-semibold text-muted">Date Format</label>
                                    <select class="form-select" id="dateFormat">
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="d-m-Y" selected>DD-MM-YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-semibold text-muted">Application Logo</label>
                                    <div class="border rounded-3 p-3 bg-light bg-opacity-25 text-center cursor-pointer" style="border-style: dashed !important;">
                                        <i class="bi bi-upload fs-4 text-muted d-block mb-1"></i>
                                        <span class="small text-muted">Click to upload (120x40px)</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-semibold text-muted">Favicon</label>
                                    <div class="border rounded-3 p-3 bg-light bg-opacity-25 text-center cursor-pointer" style="border-style: dashed !important;">
                                        <i class="bi bi-upload fs-4 text-muted d-block mb-1"></i>
                                        <span class="small text-muted">Click to upload (32x32px)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Company -->
                        <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Company Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="companyName" class="form-label fs-7 fw-semibold text-muted">Company Name</label>
                                    <input type="text" class="form-control" id="companyName" value="Parking Solutions Pvt. Ltd.">
                                </div>
                                <div class="col-md-6">
                                    <label for="ownerName" class="form-label fs-7 fw-semibold text-muted">Owner Name</label>
                                    <input type="text" class="form-control" id="ownerName" value="John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label for="supportEmail" class="form-label fs-7 fw-semibold text-muted">Support Email</label>
                                    <input type="email" class="form-control" id="supportEmail" value="support@smartparking.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="supportPhone" class="form-label fs-7 fw-semibold text-muted">Support Phone</label>
                                    <input type="text" class="form-control" id="supportPhone" value="+91 98765 43210">
                                </div>
                                <div class="col-md-6">
                                    <label for="website" class="form-label fs-7 fw-semibold text-muted">Website</label>
                                    <input type="url" class="form-control" id="website" value="https://smartparking.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="gstNumber" class="form-label fs-7 fw-semibold text-muted">GST Number</label>
                                    <input type="text" class="form-control" id="gstNumber" value="22AAAAA0000A1Z5">
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

                        <!-- 3. Email -->
                        <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Email Configuration</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="smtpHost" class="form-label fs-7 fw-semibold text-muted">SMTP Host</label>
                                    <input type="text" class="form-control" id="smtpHost" value="smtp.gmail.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpPort" class="form-label fs-7 fw-semibold text-muted">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtpPort" value="587">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpUsername" class="form-label fs-7 fw-semibold text-muted">SMTP Username</label>
                                    <input type="text" class="form-control" id="smtpUsername" value="admin@smartparking.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpPassword" class="form-label fs-7 fw-semibold text-muted">SMTP Password</label>
                                    <input type="password" class="form-control" id="smtpPassword" value="********">
                                </div>
                                <div class="col-md-6">
                                    <label for="encryption" class="form-label fs-7 fw-semibold text-muted">Encryption</label>
                                    <select class="form-select" id="encryption">
                                        <option value="tls" selected>TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="senderEmail" class="form-label fs-7 fw-semibold text-muted">Sender Email</label>
                                    <input type="email" class="form-control" id="senderEmail" value="no-reply@smartparking.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="senderName" class="form-label fs-7 fw-semibold text-muted">Sender Name</label>
                                    <input type="text" class="form-control" id="senderName" value="Smart Parking System">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-send me-1"></i>Test Email</button>
                                </div>
                            </div>
                        </div>

                        <!-- 4. SMS -->
                        <div class="tab-pane fade" id="sms" role="tabpanel" aria-labelledby="sms-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">SMS Configuration</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="smsProvider" class="form-label fs-7 fw-semibold text-muted">Provider</label>
                                    <select class="form-select" id="smsProvider">
                                        <option value="twilio">Twilio</option>
                                        <option value="msg91" selected>MSG91</option>
                                        <option value="africas_talking">Africa's Talking</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="smsApiKey" class="form-label fs-7 fw-semibold text-muted">API Key</label>
                                    <input type="text" class="form-control" id="smsApiKey" value="API-KEY-XXXX-XXXX">
                                </div>
                                <div class="col-md-6">
                                    <label for="smsSenderId" class="form-label fs-7 fw-semibold text-muted">Sender ID</label>
                                    <input type="text" class="form-control" id="smsSenderId" value="SMARTPK">
                                </div>
                                <div class="col-md-6">
                                    <label for="smsCountry" class="form-label fs-7 fw-semibold text-muted">Country</label>
                                    <select class="form-select" id="smsCountry">
                                        <option value="IN" selected>India</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enableSms" checked>
                                        <label class="form-check-label fw-medium" for="enableSms">Enable SMS Notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Payment -->
                        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Payment Gateways</h6>
                            <div class="vstack gap-4">
                                <div class="border rounded-3 p-3 bg-light bg-opacity-25">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">Stripe</h6>
                                            <small class="text-muted">Process credit card payments via Stripe.</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="stripeToggle" checked>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Publishable Key</label>
                                            <input type="text" class="form-control form-control-sm" value="pk_test_XXXX">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Secret Key</label>
                                            <input type="password" class="form-control form-control-sm" value="sk_test_XXXX">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Webhook URL</label>
                                            <input type="text" class="form-control form-control-sm" value="https://api.smartparking.com/stripe/webhook">
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-3 p-3 bg-light bg-opacity-25">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">Razorpay</h6>
                                            <small class="text-muted">Popular Indian payment gateway.</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="razorpayToggle" checked>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <label class="fs-7 text-muted">Key ID</label>
                                            <input type="text" class="form-control form-control-sm" value="rzp_live_XXXX">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fs-7 text-muted">Key Secret</label>
                                            <input type="password" class="form-control form-control-sm" value="rzp_secret_XXXX">
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-3 p-3 bg-light bg-opacity-25">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">PayPal</h6>
                                            <small class="text-muted">Global payment processing solution.</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="paypalToggle">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Client ID</label>
                                            <input type="text" class="form-control form-control-sm" value="Ae1_XXXX">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Secret</label>
                                            <input type="password" class="form-control form-control-sm" value="Ee1_XXXX">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fs-7 text-muted">Webhook ID</label>
                                            <input type="text" class="form-control form-control-sm" value="wh_XXXX">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Maps -->
                        <div class="tab-pane fade" id="maps" role="tabpanel" aria-labelledby="maps-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Map Configuration</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="googleMapsKey" class="form-label fs-7 fw-semibold text-muted">Google Maps API Key</label>
                                    <input type="text" class="form-control" id="googleMapsKey" value="AIzaSyXXXXXXXXXXXXXXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label for="mapLat" class="form-label fs-7 fw-semibold text-muted">Default Latitude</label>
                                    <input type="text" class="form-control" id="mapLat" value="19.0760">
                                </div>
                                <div class="col-md-6">
                                    <label for="mapLng" class="form-label fs-7 fw-semibold text-muted">Default Longitude</label>
                                    <input type="text" class="form-control" id="mapLng" value="72.8777">
                                </div>
                                <div class="col-md-6">
                                    <label for="mapZoom" class="form-label fs-7 fw-semibold text-muted">Default Zoom</label>
                                    <input type="number" class="form-control" id="mapZoom" value="12">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="liveTracking" checked>
                                        <label class="form-check-label fw-medium" for="liveTracking">Enable Live Tracking</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Security -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Security Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="passwordLength" class="form-label fs-7 fw-semibold text-muted">Minimum Password Length</label>
                                    <input type="number" class="form-control" id="passwordLength" value="8">
                                </div>
                                <div class="col-md-6">
                                    <label for="sessionTimeout" class="form-label fs-7 fw-semibold text-muted">Session Timeout (Minutes)</label>
                                    <input type="number" class="form-control" id="sessionTimeout" value="60">
                                </div>
                                <div class="col-md-6">
                                    <label for="loginAttempts" class="form-label fs-7 fw-semibold text-muted">Max Login Attempts</label>
                                    <input type="number" class="form-control" id="loginAttempts" value="5">
                                </div>
                                <div class="col-md-6 pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorToggle" checked>
                                        <label class="form-check-label fw-medium" for="twoFactorToggle">Enable 2FA (Two-Factor Authentication)</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="captchaToggle" checked>
                                        <label class="form-check-label fw-medium" for="captchaToggle">Enable Google reCAPTCHA on Login</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 8. Notifications -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Notification Preferences</h6>
                            <div class="vstack gap-3">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <div>
                                        <h6 class="fw-medium mb-0">Email Notifications</h6>
                                        <small class="text-muted">Send system alerts via email.</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <div>
                                        <h6 class="fw-medium mb-0">SMS Notifications</h6>
                                        <small class="text-muted">Send booking alerts via SMS.</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="smsNotif" checked>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <div>
                                        <h6 class="fw-medium mb-0">Push Notifications</h6>
                                        <small class="text-muted">Send real-time push notifications to mobile apps.</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="pushNotif">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-medium mb-0">Admin Alerts</h6>
                                        <small class="text-muted">Notify admins on critical system errors.</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="adminAlerts" checked>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 9. Backup -->
                        <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Backup Management</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoBackup" checked>
                                        <label class="form-check-label fw-medium" for="autoBackup">Enable Automatic Backups</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="backupFrequency" class="form-label fs-7 fw-semibold text-muted">Backup Frequency</label>
                                    <select class="form-select" id="backupFrequency">
                                        <option value="daily">Daily</option>
                                        <option value="weekly" selected>Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="backupRetention" class="form-label fs-7 fw-semibold text-muted">Retention Period (Days)</label>
                                    <input type="number" class="form-control" id="backupRetention" value="30">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i>Download Latest Backup</button>
                                </div>
                            </div>
                        </div>

                        <!-- 10. Maintenance -->
                        <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Maintenance Mode</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenanceToggle">
                                        <label class="form-check-label fw-medium text-danger" for="maintenanceToggle">Enable Maintenance Mode</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="maintenanceMessage" class="form-label fs-7 fw-semibold text-muted">Maintenance Message</label>
                                    <textarea class="form-control" id="maintenanceMessage" rows="3">We are currently performing scheduled maintenance. Please check back in a few hours.</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="allowedIps" class="form-label fs-7 fw-semibold text-muted">Allowed IP Addresses (One per line)</label>
                                    <textarea class="form-control" id="allowedIps" rows="2">192.168.1.1
10.0.0.1</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- Bottom Buttons -->
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                <button class="btn btn-outline-secondary">Cancel</button>
                <button class="btn btn-primary"><i class="bi bi-floppy me-1"></i>Save Changes</button>
            </div>
        </div>

        <!-- Right Column (Sidebar) -->
        <div class="col-xl-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-file-code me-1"></i> Laravel Version</span>
                            <span class="fw-bold text-success small">v12.0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-code-square me-1"></i> PHP Version</span>
                            <span class="fw-bold text-info small">v8.3.0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-database me-1"></i> Database</span>
                            <span class="fw-bold text-primary small">MySQL 8.0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock me-1"></i> Server Time</span>
                            <span class="fw-bold small">11:45 AM (UTC+5:30)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hdd me-1"></i> Storage Used</span>
                            <span class="fw-bold text-warning small">45.8 GB / 100 GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-heart-pulse me-1"></i> System Status</span>
                            <span class="badge bg-success bg-opacity-10 text-success fw-normal rounded-pill border border-success border-opacity-25 small">Operational</span>
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
    .nav-tabs .nav-link { 
        color: #495057; 
        border: 1px solid transparent; 
        border-radius: 0.375rem; 
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover { 
        background-color: #f8f9fa; 
    }
    .nav-tabs .nav-link.active { 
        background-color: #ffffff; 
        border-color: #dee2e6 #dee2e6 #ffffff; 
        color: #0d6efd; 
        font-weight: 600;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.02);
    }
    .cursor-pointer { cursor: pointer; }
    .bg-light { background-color: #f8f9fa !important; }
    .form-switch .form-check-input { cursor: pointer; }
</style>
@endsection