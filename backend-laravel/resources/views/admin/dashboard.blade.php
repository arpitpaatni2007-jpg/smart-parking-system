{{-- ============================================================
     Dashboard
     ============================================================
     Extends:  layouts/admin
     Section:  content
     Purpose:  Entry page after login. Content widgets will be
               added here in subsequent phases.
     ============================================================ --}}

@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- ── Page heading ─────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-700" style="color:#0D1B2A; font-weight:700;">
                Dashboard
            </h4>
            <p class="mb-0" style="color:#5A6A7A; font-size:.875rem;">
                Welcome back, Admin. Here's what's happening today.
            </p>
        </div>
    </div>

    {{-- ── Placeholder card ────────────────────────────────── --}}
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div
                class="card border text-center"
                style="
                    border-color:  #e2e8ee !important;
                    border-radius: 14px;
                    padding:       3rem 2rem;
                    background:    #fff;
                    box-shadow:    0 2px 12px rgba(15,61,86,.06);
                "
            >
                <div
                    class="mx-auto mb-4 d-flex align-items-center justify-content-center"
                    style="
                        width:         64px;
                        height:        64px;
                        background:    rgba(46,204,113,.12);
                        border-radius: 16px;
                    "
                >
                    <i
                        class="bi bi-speedometer2"
                        style="font-size:1.75rem; color:#2ECC71;"
                    ></i>
                </div>

                <h5
                    class="mb-2 fw-700"
                    style="color:#0D1B2A; font-weight:700;"
                >
                    Dashboard Content Coming Soon
                </h5>

                <p
                    class="mb-0"
                    style="color:#5A6A7A; font-size:.875rem; line-height:1.6;"
                >
                    Stats, charts and summary widgets will appear here
                    once the data layer is connected.
                </p>
            </div>
        </div>
    </div>

@endsection