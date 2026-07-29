@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Support Ticket Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Support</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ticket Details</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</button>
            <button class="btn btn-outline-info btn-sm"><i class="bi bi-person-plus me-1"></i>Assign</button>
            <button class="btn btn-primary btn-sm"><i class="bi bi-reply me-1"></i>Reply</button>
            <button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Resolve</button>
            <button class="btn btn-secondary btn-sm"><i class="bi bi-x-lg me-1"></i>Close</button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-xl-8">
            
            <!-- Ticket Information -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between border-bottom pb-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Unable to book a parking slot</h5>
                            <span class="text-muted small">Ticket ID: <span class="fw-medium text-primary">#TKT-4210</span></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill border border-primary border-opacity-25">Technical</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Critical</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Open</span>
                        </div>
                    </div>
                    <div class="row g-3 text-muted small">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar3 me-2 fs-6"></i>
                                <span>Created: <span class="text-dark fw-medium">29 Jul, 2026 09:15 AM</span></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock-history me-2 fs-6"></i>
                                <span>Last Updated: <span class="text-dark fw-medium">29 Jul, 2026 11:30 AM</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Assigned Agent Row -->
            <div class="row g-4 mb-4">
                <!-- Customer Information -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-person-circle me-2"></i>Customer Information</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <span class="text-secondary fw-bold fs-5">RS</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Rahul Sharma</h6>
                                    <span class="text-muted small">rahul.s@email.com</span>
                                </div>
                            </div>
                            <div class="vstack gap-2 small text-muted border-top pt-3">
                                <div><i class="bi bi-telephone me-2"></i> +91 98765 43210</div>
                                <div><i class="bi bi-geo-alt me-2"></i> Mumbai, Maharashtra</div>
                                <div><i class="bi bi-award me-2"></i> Membership: <span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Gold</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Agent -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i>Assigned Agent</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <span class="text-primary fw-bold fs-5">JS</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Jane Smith</h6>
                                    <span class="text-muted small">Senior Support Agent</span>
                                </div>
                            </div>
                            <div class="vstack gap-2 small text-muted border-top pt-3">
                                <div><i class="bi bi-envelope me-2"></i> jane.s@company.com</div>
                                <div><i class="bi bi-circle-fill text-success me-2" style="font-size: 0.5rem;"></i> Currently Available</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issue Description -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-chat-left-text me-2"></i>Issue Description</h6>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-secondary">"I have been trying to book a parking slot at the Downtown Mall Parking location for the past 2 days. Every time I select the time and proceed to payment, I get a 'Parking Unavailable' error. However, the dashboard shows availability for those slots. Please help me resolve this issue urgently as I need to park there daily for work."</p>
                </div>
            </div>

            <!-- Conversation Timeline -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2"></i>Conversation Timeline</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-4">
                        <!-- Customer Message -->
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                <span class="text-secondary small fw-bold">RS</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-light bg-opacity-50 p-3 rounded-3 border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-medium small">Rahul Sharma <span class="text-muted fw-normal">(Customer)</span></span>
                                        <span class="text-muted small">29 Jul, 2026 09:15 AM</span>
                                    </div>
                                    <p class="mb-0 small text-secondary">I have been trying to book a slot but it's failing with a 'Parking Unavailable' error.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Support Reply -->
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                <span class="text-primary small fw-bold">JS</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 border border-primary border-opacity-25">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-medium small">Jane Smith <span class="text-muted fw-normal">(Support Agent)</span></span>
                                        <span class="text-muted small">29 Jul, 2026 10:00 AM</span>
                                    </div>
                                    <p class="mb-0 small text-secondary">Hello Rahul, I apologize for the inconvenience. We are currently investigating a caching issue with the Downtown Mall location. I will update you as soon as it's fixed.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Follow-up -->
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                <span class="text-secondary small fw-bold">RS</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-light bg-opacity-50 p-3 rounded-3 border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-medium small">Rahul Sharma <span class="text-muted fw-normal">(Customer)</span></span>
                                        <span class="text-muted small">29 Jul, 2026 11:30 AM</span>
                                    </div>
                                    <p class="mb-0 small text-secondary">Thank you, Jane. Please let me know if you need any screenshots or additional info from my side.</p>
                                    <div class="mt-2 border rounded-2 p-2 bg-white d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-image text-primary"></i>
                                        <span class="small text-muted">screenshot_error.png</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolution Section -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-check2-circle me-2"></i>Resolution & Notes</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-semibold text-muted">Resolution Notes</label>
                            <textarea class="form-control" rows="3" placeholder="Enter resolution details...">Fixed the caching bug for Downtown Mall location. Slots are now updating in real-time.</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-semibold text-muted">Internal Notes (Private)</label>
                            <textarea class="form-control" rows="3" placeholder="Internal notes for team...">Deployed hotfix v1.2.3 to production. Monitor for 24 hours.</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-semibold text-muted">Resolution Date</label>
                            <input type="datetime-local" class="form-control" value="2026-07-29T14:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-semibold text-muted">Customer Feedback</label>
                            <select class="form-select">
                                <option value="">Pending Feedback</option>
                                <option value="satisfied">Satisfied</option>
                                <option value="neutral">Neutral</option>
                                <option value="unsatisfied">Unsatisfied</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-filetype-pdf me-1"></i>Download PDF</button>
                            <button class="btn btn-outline-info btn-sm"><i class="bi bi-share me-1"></i>Forward</button>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-reply me-1"></i>Reply</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-xl-4">
            <!-- Quick Statistics Sidebar -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Quick Statistics</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-hourglass-top me-1"></i> Ticket Age</span>
                            <span class="fw-bold text-warning">2 hours 15 mins</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-clock me-1"></i> Response Time</span>
                            <span class="fw-bold text-success">45 mins</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted small fw-medium"><i class="bi bi-flag me-1"></i> Priority</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill border border-danger border-opacity-25">Critical</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-medium"><i class="bi bi-arrow-up-circle me-1"></i> Escalation Status</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning fw-normal rounded-pill border border-warning border-opacity-25">Escalated</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions / Attachments Sidebar -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-paperclip me-2"></i>Attachments</h6>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        <div class="d-flex align-items-center p-2 border rounded-3 bg-light bg-opacity-50">
                            <i class="bi bi-file-earmark-image text-primary fs-5 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="small fw-medium text-truncate" style="max-width: 180px;">screenshot_error.png</div>
                                <div class="text-muted small">2.4 MB</div>
                            </div>
                            <a href="#" class="text-muted small"><i class="bi bi-download"></i></a>
                        </div>
                        <div class="d-flex align-items-center p-2 border rounded-3 bg-light bg-opacity-50">
                            <i class="bi bi-file-earmark-text text-danger fs-5 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="small fw-medium text-truncate" style="max-width: 180px;">booking_logs.txt</div>
                                <div class="text-muted small">12 KB</div>
                            </div>
                            <a href="#" class="text-muted small"><i class="bi bi-download"></i></a>
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-3"><i class="bi bi-cloud-upload me-1"></i>Upload New</button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    .fs-7 { font-size: 0.75rem; }
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection