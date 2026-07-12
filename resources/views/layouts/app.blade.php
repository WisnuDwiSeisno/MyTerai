<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — MyTerai</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-sans: "DM Sans", system-ui, sans-serif;
            --font-mono: "DM Mono", monospace;
            --sidebar-width: 240px;
            --brand: #6f42c1;
            --brand-soft: #f1ecfb;
        }

        body {
            font-family: var(--font-sans);
            background-color: #f7f7fb;
        }

        .font-mono {
            font-family: var(--font-mono);
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #eee;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1.25rem 1rem;
        }

        .sidebar .brand {
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .sidebar .brand .badge-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--brand);
            display: inline-block;
        }

        .sidebar .nav-link {
            color: #555;
            border-radius: .5rem;
            padding: .55rem .75rem;
            margin-bottom: .15rem;
            font-size: .92rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .sidebar .nav-link.active {
            background: var(--brand-soft);
            color: var(--brand);
        }

        .sidebar .nav-link:hover:not(.active) {
            background: #f5f5f9;
            color: #333;
        }

        .sidebar .nav-section-title {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #aaa;
            margin: 1rem .75rem .35rem;
            font-weight: 700;
        }

        /* ── Main content ── */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.75rem 2rem;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .topbar h1 {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* ── Cards / Badges ── */
        .card {
            border: 1px solid #eee;
            border-radius: .85rem;
        }

        .stat-card .stat-number {
            font-size: 1.85rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .stat-card .stat-label {
            font-size: .82rem;
            color: #888;
            font-weight: 500;
        }

        .badge-status-unik {
            background: #e6f6ec;
            color: #1a7f4b;
        }

        .badge-status-duplikat {
            background: #fff3cd;
            color: #946c00;
        }

        .badge-status-tidak_terbaca {
            background: #eceef1;
            color: #5a6472;
        }

        .badge-jenis-fisik {
            background: #eef2ff;
            color: #4338ca;
        }

        .badge-jenis-elektronik {
            background: #fde9f3;
            color: #be185d;
        }

        .badge-soft {
            font-weight: 600;
            font-size: .76rem;
            padding: .35rem .65rem;
            border-radius: .5rem;
        }

        .table> :not(caption)>*>* {
            padding: .85rem .9rem;
        }

        .qr-thumb {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            cursor: pointer;
            background: #fff;
            padding: 4px;
            transition: .2s;
        }

        .qr-thumb:hover {
            transform: scale(1.1);
        }

        .qr-preview {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
        }

        /* ── Mobile ── */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .2s;
                z-index: 1050;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1.25rem;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <span class="badge-dot"></span>
            Dashboard Materai
        </div>

        <div class="nav-section-title">Menu</div>
        <nav class="nav flex-column">
            <a href="{{ route('dashboard.index') }}"
                class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Data Materai
            </a>
            <a href="{{ route('upload.create') }}"
                class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}">
                <i class="bi bi-cloud-upload"></i> Uji Coba Deteksi
            </a>
        </nav>

        <div class="nav-section-title">Pengaturan</div>
        <nav class="nav flex-column">
            <a href="{{ route('clients.index') }}"
                class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <i class="bi bi-key"></i> API Client
            </a>
        </nav>

        <div class="position-absolute bottom-0 start-0 w-100 p-3">
            <div class="d-flex align-items-center gap-2 px-2 mb-2">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                    style="width:34px;height:34px;">
                    <i class="bi bi-person-fill text-muted"></i>
                </div>
                <div class="small">
                    <div class="fw-semibold">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="main-content">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <button class="btn btn-outline-secondary d-md-none"
                onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>