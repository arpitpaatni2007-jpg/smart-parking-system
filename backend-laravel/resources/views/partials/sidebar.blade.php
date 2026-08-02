<nav id="sidebar">

    {{-- ── Brand / Logo ──────────────────────────────────────── --}}
    <div class="d-flex align-items-center px-3 py-0" style="
        height:          64px;
        flex-shrink:     0;
        border-bottom:   1px solid var(--divider);
        gap:             0.625rem;
    ">
        <div style="
            width:            36px;
            height:           36px;
            background:       var(--sidebar-accent);
            border-radius:    9px;
            display:          flex;
            align-items:      center;
            justify-content:  center;
            flex-shrink:      0;
        ">
            <i class="bi bi-p-square-fill" style="font-size:1.2rem; color:#0F3D56;"></i>
        </div>
        <div>
            <div style="color:#fff; font-weight:700; font-size:.95rem; line-height:1.2;">Smart Parking</div>
            <div style="color:var(--group-label); font-size:.7rem; letter-spacing:.4px;">Admin Panel</div>
        </div>
    </div>

    {{-- ── Navigation ─────────────────────────────────────────── --}}
    <div class="flex-grow-1 py-2">
        <ul class="list-unstyled mb-0 px-2">

            {{-- ─ Main ──────────────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">Main</div>

            <li>
                <a href="{{ route('dashboard') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- ─ Management ───────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">Management</div>

            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Users</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.parking-owners.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.parking-owners.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Parking Owners</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.parkings.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.parkings.*') ? 'active' : '' }}">
                    <i class="bi bi-signpost-2" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Parkings</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.bookings.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Bookings</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                    <i class="bi bi-car-front" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Vehicles</span>
                </a>
            </li>

            {{-- ─ Finance ──────────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">Finance</div>

            <li>
                <a href="{{ route('admin.payments.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.earnings.dashboard') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.earnings.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Earnings</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}">
                    <i class="bi bi-send" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Payouts</span>
                </a>
            </li>

            {{-- ─ Communication ────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">Communication</div>

            <li>
                <a href="{{ route('admin.notifications.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.support.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                    <i class="bi bi-headset" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Support</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.cms.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.cms.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>CMS</span>
                </a>
            </li>

            {{-- ─ Analytics ────────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">Analytics</div>

            <li>
                <a href="{{ route('admin.reports.dashboard') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Reports</span>
                </a>
            </li>

            {{-- ─ System ───────────────────────────────────── --}}
            <div style="
                color:          var(--group-label);
                font-size:      .65rem;
                font-weight:    700;
                letter-spacing: .8px;
                text-transform: uppercase;
                padding:        1.1rem 0.75rem 0.35rem;
            ">System</div>

            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Settings</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.system-users.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-person" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>System Users</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.roles.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-key" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Roles &amp; Permissions</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.activity.index') }}"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-code" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>Logs</span>
                </a>
            </li>

            <li>
                <a href="#"
                   class="d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('admin.app-version.*') ? 'active' : '' }}">
                    <i class="bi bi-phone" style="font-size:1rem; width:18px; flex-shrink:0;"></i>
                    <span>App Version</span>
                </a>
            </li>

        </ul>
    </div>

    {{-- ── Admin profile (bottom) ──────────────────────────────── --}}
    <div style="
        border-top:  1px solid var(--divider);
        padding:     0.875rem 1rem;
        flex-shrink: 0;
    ">
        <a href="{{ route('admin.profile.index') }}"
           class="d-flex align-items-center gap-2 text-decoration-none
                  {{ request()->routeIs('admin.profile.*') ? 'sidebar-active' : '' }}"
           style="
               padding:       0.5rem 0.625rem;
               border-radius: 8px;
               transition:    background 0.18s;
               background:    {{ request()->routeIs('admin.profile.*') ? 'var(--sidebar-active-bg)' : 'transparent' }};
           "
           onmouseover="if(!this.classList.contains('sidebar-active')) this.style.background='var(--sidebar-hover-bg)'"
           onmouseout="if(!this.classList.contains('sidebar-active'))  this.style.background='transparent'"
        >
            <div style="
                width:           36px;
                height:          36px;
                border-radius:   50%;
                background:      rgba(255,255,255,0.15);
                display:         flex;
                align-items:     center;
                justify-content: center;
                flex-shrink:     0;
            ">
                <i class="bi bi-person-circle" style="font-size:1.2rem; color:var(--sidebar-text-active);"></i>
            </div>
            <div style="overflow:hidden;">
                <div style="
                    color:         var(--sidebar-text-active);
                    font-size:     .84rem;
                    font-weight:   600;
                    white-space:   nowrap;
                    overflow:      hidden;
                    text-overflow: ellipsis;
                ">Admin Profile</div>
                <div style="
                    color:       var(--group-label);
                    font-size:   .7rem;
                    white-space: nowrap;
                ">View &amp; Edit Profile</div>
            </div>
        </a>
    </div>

</nav>

<style>
/* Sidebar nav links */
#sidebar ul li a {
    display:         flex;
    align-items:     center;
    gap:             0.5rem;
    padding:         0.5rem 0.75rem;
    border-radius:   8px;
    margin-bottom:   2px;
    color:           var(--sidebar-text);
    text-decoration: none;
    font-size:       .875rem;
    border-left:     3px solid transparent;
    transition:      background 0.18s, color 0.18s;
}

#sidebar ul li a:hover {
    background: var(--sidebar-hover-bg);
    color:      var(--sidebar-text-active);
}

#sidebar ul li a.active {
    background:  var(--sidebar-active-bg);
    color:       var(--sidebar-text-active);
    font-weight: 600;
    border-left: 3px solid var(--sidebar-active-border);
}
</style>