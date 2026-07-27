<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Parking — @yield('title', 'Admin Panel')</title>

    {{-- Bootstrap 5 CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- Bootstrap Icons --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        /* ── Root variables ───────────────────────────────────── */
        :root {
            --sidebar-width:        260px;
            --sidebar-bg:           #0F3D56;
            --sidebar-text:         rgba(255, 255, 255, 0.75);
            --sidebar-text-active:  #ffffff;
            --sidebar-accent:       #2ECC71;
            --sidebar-hover-bg:     rgba(255, 255, 255, 0.08);
            --sidebar-active-bg:    rgba(46, 204, 113, 0.15);
            --sidebar-active-border:#2ECC71;
            --navbar-height:        60px;
            --navbar-bg:            #ffffff;
            --navbar-border:        #e2e8ee;
            --body-bg:              #f8f9fa;
            --text-primary:         #0D1B2A;
            --text-secondary:       #5A6A7A;
            --divider:              rgba(255, 255, 255, 0.12);
            --group-label:          rgba(255, 255, 255, 0.38);
        }

        /* ── Base ────────────────────────────────────────────── */
        *,
        *::before,
        *::after { box-sizing: border-box; }

        body {
            margin:          0;
            background:      var(--body-bg);
            font-family:     'Segoe UI', system-ui, -apple-system, sans-serif;
            color:           var(--text-primary);
            min-height:      100vh;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        #sidebar {
            position:   fixed;
            top:        0;
            left:       0;
            width:      var(--sidebar-width);
            height:     100vh;
            background: var(--sidebar-bg);
            display:    flex;
            flex-direction: column;
            z-index:    1040;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
            transition: transform 0.28s ease;
        }

        #sidebar::-webkit-scrollbar        { width: 4px; }
        #sidebar::-webkit-scrollbar-track  { background: transparent; }
        #sidebar::-webkit-scrollbar-thumb  { background: rgba(255,255,255,0.15); border-radius: 4px; }

        /* ── Main wrapper ────────────────────────────────────── */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height:  100vh;
            display:     flex;
            flex-direction: column;
            transition:  margin-left 0.28s ease;
        }

        /* ── Navbar ──────────────────────────────────────────── */
        #top-navbar {
            position:   sticky;
            top:        0;
            height:     var(--navbar-height);
            background: var(--navbar-bg);
            border-bottom: 1px solid var(--navbar-border);
            z-index:    1030;
            display:    flex;
            align-items: center;
            padding:    0 1.5rem;
            gap:        1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* ── Content area ────────────────────────────────────── */
        #page-content {
            flex:    1;
            padding: 1.75rem 1.75rem 2.5rem;
        }

        /* ── Responsive — collapse sidebar on mobile ─────────── */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #main-wrapper {
                margin-left: 0;
            }

            /* Backdrop when sidebar is open on mobile */
            #sidebar-backdrop {
                display:    none;
                position:   fixed;
                inset:      0;
                background: rgba(0, 0, 0, 0.45);
                z-index:    1039;
            }

            #sidebar-backdrop.show {
                display: block;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Sidebar ──────────────────────────────────────────────── --}}
@include('partials.sidebar')

{{-- ── Mobile sidebar backdrop ────────────────────────────────── --}}
<div id="sidebar-backdrop" onclick="closeSidebar()"></div>

{{-- ── Main wrapper ─────────────────────────────────────────────── --}}
<div id="main-wrapper">

    {{-- Top navbar --}}
    @include('partials.navbar')

    {{-- Page content --}}
    <main id="page-content">
        @yield('content')
    </main>

</div>{{-- /#main-wrapper --}}

{{-- Bootstrap 5 JS (Bundle includes Popper) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ── Mobile sidebar toggle ──────────────────────────────────
    function openSidebar() {
        document.getElementById('sidebar').classList.add('show');
        document.getElementById('sidebar-backdrop').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebar-backdrop').classList.remove('show');
        document.body.style.overflow = '';
    }
</script>

@stack('scripts')
</body>
</html>