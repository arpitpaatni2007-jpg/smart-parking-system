{{-- ============================================================
     Top Navbar
     ============================================================
     Included by: layouts/admin.blade.php
     Provides:
       - Hamburger toggle (mobile only)
       - Dynamic page title via @yield / $pageTitle
       - Notification icon button
       - Admin profile dropdown
     ============================================================ --}}

<nav id="top-navbar">

    {{-- ── Left: Hamburger (mobile) + Page Title ────────────────── --}}
    <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width:0;">

        {{-- Hamburger — visible only on mobile (≤991px) --}}
        <button
            class="btn btn-sm d-flex align-items-center justify-content-center d-lg-none"
            onclick="openSidebar()"
            style="
                width:        38px;
                height:       38px;
                padding:      0;
                border:       1px solid #e2e8ee;
                border-radius:8px;
                color:        #5A6A7A;
                background:   #fff;
                flex-shrink:  0;
            "
            aria-label="Open sidebar"
        >
            <i class="bi bi-list" style="font-size:1.25rem;"></i>
        </button>

        {{-- Page title --}}
        <h6
            class="mb-0 fw-600 text-truncate"
            style="
                color:          #0D1B2A;
                font-size:      1rem;
                font-weight:    600;
                letter-spacing: .01em;
            "
        >
            @yield('page-title', 'Dashboard')
        </h6>

    </div>

    {{-- ── Right: Notification + Admin Dropdown ─────────────────── --}}
    <div class="d-flex align-items-center gap-2">

        {{-- Notification button --}}
        <button
            class="btn btn-sm position-relative d-flex align-items-center justify-content-center"
            style="
                width:        38px;
                height:       38px;
                padding:      0;
                border:       1px solid #e2e8ee;
                border-radius:8px;
                color:        #5A6A7A;
                background:   #fff;
            "
            aria-label="Notifications"
        >
            <i class="bi bi-bell" style="font-size:1rem;"></i>
            {{-- Unread badge --}}
            <span
                class="position-absolute"
                style="
                    top:           6px;
                    right:         6px;
                    width:         7px;
                    height:        7px;
                    background:    #E53935;
                    border-radius: 50%;
                    border:        1.5px solid #fff;
                "
            ></span>
        </button>

        {{-- Admin profile dropdown --}}
        <div class="dropdown">
            <button
                class="btn btn-sm d-flex align-items-center gap-2 dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                style="
                    height:        38px;
                    padding:       0 0.75rem;
                    border:        1px solid #e2e8ee;
                    border-radius: 8px;
                    background:    #fff;
                    color:         #0D1B2A;
                    font-size:     .875rem;
                    font-weight:   600;
                "
            >
                {{-- Avatar circle --}}
                <div style="
                    width:           28px;
                    height:          28px;
                    border-radius:   50%;
                    background:      #0F3D56;
                    display:         flex;
                    align-items:     center;
                    justify-content: center;
                    flex-shrink:     0;
                ">
                    <i class="bi bi-person-fill" style="font-size:.8rem; color:#fff;"></i>
                </div>
                <span class="d-none d-sm-inline">Admin</span>
            </button>

            <ul
                class="dropdown-menu dropdown-menu-end shadow-sm"
                style="
                    min-width:     180px;
                    border:        1px solid #e2e8ee;
                    border-radius: 10px;
                    padding:       0.375rem;
                    font-size:     .875rem;
                "
            >
                <li>
                    <a
                        class="dropdown-item d-flex align-items-center gap-2"
                        href="#"
                        style="border-radius:7px; padding:.45rem .75rem;"
                    >
                        <i class="bi bi-person-circle" style="font-size:1rem; color:#5A6A7A;"></i>
                        My Profile
                    </a>
                </li>
                <li>
                    <a
                        class="dropdown-item d-flex align-items-center gap-2"
                        href="#"
                        style="border-radius:7px; padding:.45rem .75rem;"
                    >
                        <i class="bi bi-gear" style="font-size:1rem; color:#5A6A7A;"></i>
                        Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider" style="margin:.375rem 0;"></li>
                <li>
                    <form method="POST" action="#">
                        @csrf
                        <button
                            type="submit"
                            class="dropdown-item d-flex align-items-center gap-2"
                            style="
                                border-radius: 7px;
                                padding:       .45rem .75rem;
                                color:         #E53935;
                            "
                        >
                            <i class="bi bi-box-arrow-right" style="font-size:1rem;"></i>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>

</nav>