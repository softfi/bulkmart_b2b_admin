
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Vendor Portal') | B2B</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --vendor-bg: #0b1220;
            --vendor-sidebar: #080e19;
            --vendor-surface: #111c2c;
            --vendor-surface-hover: #172438;
            --vendor-border: #26364a;
            --vendor-gold: #d6b574;
            --vendor-gold-hover: #e5c98f;
            --vendor-text: #f1f5f9;
            --vendor-muted: #aab7c8;
            --vendor-danger: #fca5a5;
            --vendor-success: #86efac;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--vendor-bg);
            color: var(--vendor-text);
            font-family: Arial, Helvetica, sans-serif;
        }

        body.vendor-auth-page {
            background: var(--vendor-bg);
        }

        a {
            color: var(--vendor-gold);
        }

        a:hover {
            color: var(--vendor-gold-hover);
        }

        .vendor-shell {
            min-height: 100vh;
        }

        .vendor-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            width: 258px;
            padding: 24px 16px;
            overflow-y: auto;
            background: var(--vendor-sidebar);
            border-right: 1px solid var(--vendor-border);
        }

        .vendor-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 10px 26px;
            color: var(--vendor-text);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: .3px;
            text-decoration: none;
        }

        .vendor-brand-mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 10px;
            background: var(--vendor-gold);
            color: #111827;
            font-size: 16px;
            font-weight: 800;
        }

        .vendor-sidebar-label {
            margin: 16px 10px 9px;
            color: #8190a5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .vendor-nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .vendor-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #c2ccda;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .vendor-nav-link:hover {
            background: var(--vendor-surface-hover);
            color: #fff;
        }

        .vendor-nav-link.active {
            background: #202b3b;
            border-color: #3b4656;
            color: var(--vendor-gold-hover);
        }

        .vendor-nav-icon {
            display: inline-grid;
            width: 22px;
            place-items: center;
            color: var(--vendor-gold);
            font-size: 15px;
        }

        .vendor-sidebar-bottom {
            margin-top: auto;
            padding-top: 20px;
        }

        .vendor-user-panel {
            padding: 14px 12px;
            background: var(--vendor-surface);
            border: 1px solid var(--vendor-border);
            border-radius: 9px;
        }

        .vendor-user-name {
            overflow: hidden;
            color: var(--vendor-text);
            font-size: 13px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vendor-user-email {
            overflow: hidden;
            margin-top: 4px;
            color: var(--vendor-muted);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vendor-logout-button {
            width: 100%;
            margin-top: 12px;
            padding: 9px 10px;
            background: transparent;
            border: 1px solid #69434a;
            border-radius: 7px;
            color: #fca5a5;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
        }

        .vendor-logout-button:hover {
            background: #351d25;
            color: #fecaca;
        }

        .vendor-content {
            min-height: 100vh;
            margin-left: 258px;
            padding: 34px;
        }

        .vendor-content-inner {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
        }

        .vendor-page-heading {
            margin-bottom: 25px;
        }

        .vendor-page-heading h1,
        .vendor-page-heading h2 {
            color: var(--vendor-text);
            font-size: 25px;
            font-weight: 700;
        }

        .vendor-card {
            background: var(--vendor-surface);
            border: 1px solid var(--vendor-border);
            border-radius: 11px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
            color: var(--vendor-text);
        }

        .vendor-card h1,
        .vendor-card h2,
        .vendor-card h3,
        .vendor-card h4,
        .vendor-card h5,
        .vendor-card h6 {
            color: var(--vendor-text);
        }

        .muted-text {
            color: var(--vendor-muted) !important;
        }

        .vendor-content .text-dark {
            color: var(--vendor-text) !important;
        }

        .vendor-content .text-secondary {
            color: var(--vendor-muted) !important;
        }

        .vendor-content .text-muted {
            color: var(--vendor-muted) !important;
        }

        .vendor-content .form-label {
            margin-bottom: 7px;
            color: #dce4ee;
            font-size: 13px;
            font-weight: 600;
        }

        .vendor-content .form-control,
        .vendor-content .form-select {
            min-height: 43px;
            background-color: #0c1625;
            border: 1px solid #35465b;
            border-radius: 7px;
            color: var(--vendor-text);
        }

        .vendor-content textarea.form-control {
            min-height: 110px;
        }

        .vendor-content .form-control::placeholder {
            color: #8d9bad;
            opacity: 1;
        }

        .vendor-content .form-control:focus,
        .vendor-content .form-select:focus {
            background-color: #0c1625;
            border-color: var(--vendor-gold);
            box-shadow: 0 0 0 3px rgba(214, 181, 116, .13);
            color: var(--vendor-text);
        }

        .vendor-content input[type="file"].form-control {
            padding: 8px;
            color: var(--vendor-muted);
        }

        .vendor-content input[type="file"]::file-selector-button {
            margin: -5px 10px -5px -5px;
            padding: 7px 11px;
            background: #26364a;
            border: 0;
            border-radius: 5px;
            color: #f1f5f9;
        }

        .vendor-content .form-text,
        .vendor-content small {
            color: var(--vendor-muted);
        }

        .vendor-content .form-check-input {
            background-color: #0c1625;
            border-color: #526176;
        }

        .vendor-content .form-check-input:checked {
            background-color: var(--vendor-gold);
            border-color: var(--vendor-gold);
        }

        .vendor-content .form-check-label {
            color: #dce4ee;
        }

        .vendor-content .btn-dark {
            background: var(--vendor-gold);
            border-color: var(--vendor-gold);
            color: #17202d;
            font-weight: 700;
        }

        .vendor-content .btn-dark:hover {
            background: var(--vendor-gold-hover);
            border-color: var(--vendor-gold-hover);
            color: #111827;
        }

        .vendor-content .btn-outline-dark {
            border-color: #64748b;
            color: #e2e8f0;
        }

        .vendor-content .btn-outline-dark:hover {
            background: #26364a;
            border-color: #64748b;
            color: #fff;
        }

        .vendor-content .btn-outline-secondary {
            border-color: #526176;
            color: #d1d9e4;
        }

        .vendor-content .btn-outline-secondary:hover {
            background: #26364a;
            color: #fff;
        }

        .vendor-content .btn-primary {
            background: #345b83;
            border-color: #456f9c;
            color: #fff;
        }

        .vendor-content .btn-primary:hover {
            background: #416e9b;
            border-color: #5a83ad;
        }

        .vendor-content .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e5ebf3;
            --bs-table-border-color: #2b3a4d;
            --bs-table-striped-bg: #142236;
            --bs-table-striped-color: #e5ebf3;
            --bs-table-hover-bg: #1b2b40;
            --bs-table-hover-color: #fff;
            margin-bottom: 0;
        }

        .vendor-content .table thead th {
            padding-top: 14px;
            padding-bottom: 14px;
            background: #172438;
            color: #dce4ee;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .vendor-content .table td {
            padding-top: 14px;
            padding-bottom: 14px;
            vertical-align: middle;
        }

        .vendor-content .table-light {
            --bs-table-bg: #172438;
            --bs-table-color: #e5ebf3;
            --bs-table-border-color: #2b3a4d;
        }

        .vendor-content .pagination {
            gap: 4px;
        }

        .vendor-content .page-link {
            background: #111c2c;
            border-color: #35465b;
            color: #dce4ee;
        }

        .vendor-content .page-item.active .page-link {
            background: var(--vendor-gold);
            border-color: var(--vendor-gold);
            color: #17202d;
        }

        .vendor-content .page-item.disabled .page-link {
            background: #0c1625;
            color: #718096;
        }

        .vendor-content .alert {
            border-radius: 8px;
        }

        .vendor-content .alert-success {
            background: #123326;
            border-color: #276749;
            color: #bbf7d0;
        }

        .vendor-content .alert-danger {
            background: #3b1d27;
            border-color: #783746;
            color: #fecaca;
        }

        .vendor-content .alert-warning {
            background: #3a2d16;
            border-color: #80612b;
            color: #fde68a;
        }

        .vendor-content .alert-info {
            background: #142f45;
            border-color: #285777;
            color: #bae6fd;
        }

        .vendor-content .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .vendor-content hr {
            border-color: var(--vendor-border);
            opacity: 1;
        }

        .vendor-mobile-header {
            display: none;
        }

        @media (max-width: 991.98px) {
            .vendor-sidebar {
                width: 230px;
            }

            .vendor-content {
                margin-left: 230px;
                padding: 25px 20px;
            }
        }

        @media (max-width: 767.98px) {
            .vendor-sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
                padding: 12px;
                border-right: 0;
                border-bottom: 1px solid var(--vendor-border);
            }

            .vendor-brand {
                padding: 5px 7px 12px;
            }

            .vendor-sidebar-label {
                margin-top: 10px;
            }

            .vendor-nav {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .vendor-sidebar-bottom {
                margin-top: 12px;
                padding-top: 10px;
            }

            .vendor-content {
                margin-left: 0;
                padding: 22px 14px;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="@auth vendor-auth-page @endauth">

@auth
    <div class="vendor-shell">
        <aside class="vendor-sidebar">
            <a href="{{ route('vendor.dashboard') }}" class="vendor-brand">
                <span class="vendor-brand-mark">B2B</span>
                <span>Vendor Portal</span>
            </a>

            <div class="vendor-sidebar-label">Workspace</div>

            <nav class="vendor-nav">
                <a href="{{ route('vendor.dashboard') }}"
                   class="vendor-nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                    <span class="vendor-nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->vendorProfile?->approval_status === 'approved')
                    <a href="{{ route('vendor.products.index') }}"
                       class="vendor-nav-link {{ request()->routeIs('vendor.products.index', 'vendor.products.edit') ? 'active' : '' }}">
                        <span class="vendor-nav-icon">▤</span>
                        <span>My Products</span>
                    </a>

                    <a href="{{ route('vendor.products.create') }}"
                       class="vendor-nav-link {{ request()->routeIs('vendor.products.create') ? 'active' : '' }}">
                        <span class="vendor-nav-icon">＋</span>
                        <span>Add Product</span>
                    </a>

                    <a href="{{ route('vendor.purchases.index') }}"
                    class="vendor-nav-link {{ request()->routeIs('vendor.purchases.*') ? 'active' : '' }}">
                        <span class="vendor-nav-icon">&#128179;</span>
                        <span>Purchases</span>
                    </a>

                    <a href="{{ route('vendor.notifications.index') }}"
                    class="vendor-nav-link {{ request()->routeIs('vendor.notifications.*') ? 'active' : '' }}">
                        <span class="vendor-nav-icon">&#128276;</span>
                        <span>Notifications</span>

                        @php
                            $unreadVendorNotifications = auth()->user()->unreadNotifications()->count();
                        @endphp

                        @if($unreadVendorNotifications > 0)
                            <span class="badge rounded-pill bg-warning text-dark ms-auto">
                                {{ $unreadVendorNotifications > 99 ? '99+' : $unreadVendorNotifications }}
                            </span>
                        @endif
                    </a>
                @endif
            </nav>

            <div class="vendor-sidebar-bottom">
                <div class="vendor-user-panel">
                    <div class="vendor-user-name">
                        {{ auth()->user()->name }}
                    </div>

                    <div class="vendor-user-email">
                        {{ auth()->user()->email }}
                    </div>

                    <form action="{{ route('vendor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="vendor-logout-button">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="vendor-content">
            <div class="vendor-content-inner">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning" role="alert">
                        {{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
