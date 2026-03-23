<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ currentBusiness()->business_name ?? config('app.name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- DataTables --}}
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    {{-- Flatpickr --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    {{-- Toastr --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
            --primary-light: #EFF6FF;
            --secondary: #64748B;
            --success: #16A34A;
            --danger: #DC2626;
            --warning: #D97706;
            --info: #0891B2;
            --sidebar-width: 260px;
            --sidebar-bg: #0F172A;
            --sidebar-text: #94A3B8;
            --sidebar-active: #2563EB;
            --topbar-height: 60px;
            --body-bg: #F1F5F9;
            --card-bg: #FFFFFF;
            --border-color: #E2E8F0;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --font-main: 'Inter', sans-serif;
        }

        [data-theme="dark"] {
            --body-bg: #0F172A;
            --card-bg: #1E293B;
            --border-color: #334155;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
        }

        * { box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            background: var(--body-bg);
            color: var(--text-primary);
            margin: 0;
            overflow-x: hidden;
        }

        /* === SIDEBAR === */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }

        .sidebar-brand {
            padding: 20px 20px;
            border-bottom: 1px solid #1E293B;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: white; font-size: 16px;
        }

        .sidebar-brand .brand-name {
            color: white;
            font-weight: 700;
            font-size: 15px;
            line-height: 1.2;
        }
        .sidebar-brand .brand-name small {
            display: block;
            color: #64748B;
            font-size: 10px;
            font-weight: 400;
        }

        .sidebar-section {
            padding: 16px 12px 8px;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0 8px;
            margin-bottom: 6px;
        }

        .sidebar-nav { list-style: none; margin: 0; padding: 0; }

        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 1px;
        }

        .sidebar-nav a i {
            width: 18px;
            font-size: 16px;
            flex-shrink: 0;
        }

        .sidebar-nav a:hover {
            background: #1E293B;
            color: #E2E8F0;
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
        }

        .sidebar-nav a .badge {
            margin-left: auto;
            font-size: 10px;
        }

        .sidebar-nav .has-submenu > a::after {
            content: '\F282';
            font-family: 'Bootstrap Icons';
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.2s;
        }

        .sidebar-nav .has-submenu.open > a::after { transform: rotate(90deg); }

        .submenu {
            display: none;
            padding-left: 28px;
        }
        .submenu.show { display: block; }
        .submenu a {
            font-size: 13px;
            padding: 7px 12px;
            color: #64748B;
        }

        /* === MAIN CONTENT === */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky; top: 0;
            height: var(--topbar-height);
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center;
            padding: 0 24px;
            gap: 16px;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .topbar-toggle {
            display: none;
            background: none; border: none;
            font-size: 20px; cursor: pointer;
            color: var(--text-secondary);
        }

        .topbar-breadcrumb {
            flex: 1;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .topbar-breadcrumb .page-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .topbar-actions { display: flex; align-items: center; gap: 12px; }

        .business-switcher {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            background: var(--card-bg);
            transition: all 0.2s;
        }
        .business-switcher:hover { border-color: var(--primary); }

        .topbar-icon-btn {
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            position: relative;
            transition: all 0.2s;
        }
        .topbar-icon-btn:hover { border-color: var(--primary); color: var(--primary); }

        .notification-dot {
            position: absolute; top: 6px; right: 6px;
            width: 7px; height: 7px;
            background: #EF4444;
            border-radius: 50%;
            border: 2px solid var(--card-bg);
        }

        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: var(--primary);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 14px;
            cursor: pointer;
        }

        /* === PAGE CONTENT === */
        .page-content { padding: 24px; flex: 1; }

        .page-header {
            display: flex; align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .page-header .page-actions { margin-left: auto; display: flex; gap: 8px; }

        /* === CARDS === */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: none;
        }

        .card-header {
            background: none;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .card-body { padding: 20px; }

        /* === STAT CARDS === */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-content .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-content .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
            margin: 4px 0;
        }

        .stat-content .stat-change {
            font-size: 12px;
            font-weight: 500;
        }

        /* === TABLES === */
        .table { color: var(--text-primary); }
        .table > :not(caption) > * > * { background: var(--card-bg); color: var(--text-primary); }
        .table thead th {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: middle; font-size: 14px; }
        .table-hover tbody tr:hover > * { background: var(--primary-light); }

        /* === FORMS === */
        .form-label { font-size: 13px; font-weight: 500; color: var(--text-secondary); margin-bottom: 6px; }
        .form-control, .form-select {
            font-size: 14px;
            border-color: var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 8px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: var(--card-bg);
            color: var(--text-primary);
        }

        /* === BUTTONS === */
        .btn { border-radius: 8px; font-size: 13.5px; font-weight: 500; padding: 8px 16px; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }

        /* === BADGES === */
        .status-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.7;
        }
        .badge-paid { background: #DCFCE7; color: #16A34A; }
        .badge-unpaid { background: #FEE2E2; color: #DC2626; }
        .badge-partial { background: #FEF3C7; color: #D97706; }
        .badge-overdue { background: #FEE2E2; color: #DC2626; }
        .badge-draft { background: #F1F5F9; color: #64748B; }

        /* === INVOICE FORM === */
        .invoice-items-table { width: 100%; }
        .invoice-items-table thead th { background: #F8FAFC; font-size: 12px; }
        .item-row td { vertical-align: top; }
        .item-row .form-control { border: 1px solid #E2E8F0; font-size: 13px; }

        /* === RESPONSIVE === */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .topbar-toggle { display: block; }
        }

        /* === SCROLLBAR === */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }

        /* === UTILITIES === */
        .text-currency { font-family: 'JetBrains Mono', monospace; }
        .rupee::before { content: '₹'; }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<nav class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">G</div>
        <div class="brand-name">
            GST ERP
            <small>{{ currentBusiness()->business_name ?? 'Select Business' }}</small>
        </div>
    </a>

    <div class="sidebar-section">
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Sales</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Invoices
                </a>
            </li>
            <li>
                <a href="{{ route('invoices.index', ['type' => 'credit_note']) }}">
                    <i class="bi bi-file-minus"></i> Credit Notes
                </a>
            </li>
            <li>
                <a href="{{ route('invoices.index', ['type' => 'proforma_invoice']) }}">
                    <i class="bi bi-file-earmark-text"></i> Proforma
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Purchase</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i> Purchase Bills
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Parties</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('parties.index', ['type' => 'customer']) }}">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li>
                <a href="{{ route('parties.index', ['type' => 'supplier']) }}">
                    <i class="bi bi-building"></i> Suppliers
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Inventory</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Products
                    @if($lowStockCount ?? 0 > 0)
                        <span class="badge bg-danger">{{ $lowStockCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Finance</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('payments.index') }}">
                    <i class="bi bi-cash-stack"></i> Payments
                </a>
            </li>
            <li>
                <a href="{{ route('expenses.index') }}">
                    <i class="bi bi-wallet2"></i> Expenses
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Reports & GST</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('reports.sales') }}">
                    <i class="bi bi-graph-up"></i> Sales Report
                </a>
            </li>
            <li>
                <a href="{{ route('reports.profit-loss') }}">
                    <i class="bi bi-bar-chart"></i> Profit & Loss
                </a>
            </li>
            <li>
                <a href="{{ route('reports.gstr1') }}">
                    <i class="bi bi-file-earmark-ruled"></i> GSTR-1
                </a>
            </li>
            <li>
                <a href="{{ route('reports.gstr3b') }}">
                    <i class="bi bi-file-earmark-check"></i> GSTR-3B
                </a>
            </li>
            <li>
                <a href="{{ route('reports.stock') }}">
                    <i class="bi bi-archive"></i> Stock Report
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Settings</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </div>
</nav>

{{-- MAIN WRAPPER --}}
<div class="main-wrapper">

    {{-- TOPBAR --}}
    <div class="topbar">
        <button class="topbar-toggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-breadcrumb">
            <div class="page-title">@yield('page-title', 'Dashboard')</div>
        </div>

        <div class="topbar-actions">
            {{-- Business Switcher --}}
            @if(auth()->user()->businesses()->count() > 1)
            <div class="dropdown">
                <button class="business-switcher dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-building text-primary"></i>
                    {{ Str::limit(currentBusiness()->business_name, 20) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(auth()->user()->businesses as $biz)
                    <li>
                        <a class="dropdown-item" href="{{ route('business.switch', $biz) }}">
                            {{ $biz->business_name }}
                            @if($biz->is_default) <i class="bi bi-check-circle-fill text-success ms-auto"></i> @endif
                        </a>
                    </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('business.create') }}"><i class="bi bi-plus"></i> Add Business</a></li>
                </ul>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="dropdown">
                <button class="topbar-icon-btn" data-bs-toggle="dropdown" title="Quick Actions">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('invoices.create') }}"><i class="bi bi-receipt me-2"></i>New Invoice</a></li>
                    <li><a class="dropdown-item" href="{{ route('purchases.create') }}"><i class="bi bi-cart-plus me-2"></i>New Purchase</a></li>
                    <li><a class="dropdown-item" href="{{ route('parties.create') }}"><i class="bi bi-person-plus me-2"></i>Add Party</a></li>
                    <li><a class="dropdown-item" href="{{ route('products.create') }}"><i class="bi bi-box me-2"></i>Add Product</a></li>
                    <li><a class="dropdown-item" href="{{ route('expenses.create') }}"><i class="bi bi-wallet me-2"></i>Add Expense</a></li>
                </ul>
            </div>

            {{-- Dark Mode Toggle --}}
            <button class="topbar-icon-btn" onclick="toggleDarkMode()" title="Toggle dark mode">
                <i class="bi bi-moon-stars" id="darkModeIcon"></i>
            </button>

            {{-- Notifications --}}
            <div class="dropdown">
                <button class="topbar-icon-btn" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="notification-dot"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" style="width:320px">
                    <div class="p-3 border-bottom fw-semibold">Notifications</div>
                    <div class="p-3 text-center text-muted small">No new notifications</div>
                </div>
            </div>

            {{-- User Menu --}}
            <div class="dropdown">
                <div class="user-avatar dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;list-style:none">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- PAGE CONTENT --}}
    <div class="page-content">
        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Sidebar overlay for mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999"></div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // CSRF setup for AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Toastr config
    toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };

    // Dark mode
    function toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        document.getElementById('darkModeIcon').className = isDark ? 'bi bi-moon-stars' : 'bi bi-sun';
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    }

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (savedTheme === 'dark') document.getElementById('darkModeIcon').className = 'bi bi-sun';

    // Sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show');
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    }

    // Init Flatpickr for date inputs
    document.querySelectorAll('[data-date]').forEach(el => {
        flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
    });

    // Init Select2
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

    // Number formatting
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
    }

    // Show flash messages via toastr
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>

@stack('scripts')
</body>
</html>
