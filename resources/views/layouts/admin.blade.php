
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | Wholesale</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        :root {
            --admin-bg: #0b1220;
            --admin-sidebar: #080e19;
            --admin-surface: #111c2c;
            --admin-surface-hover: #172438;
            --admin-surface-raised: #1c2a3b;
            --admin-border: #304258;
            --admin-border-soft: #26364a;

            --admin-gold: #d6b574;
            --admin-gold-hover: #e5c98f;

            --admin-text: #f1f5f9;
            --admin-text-soft: #dce4ee;
            --admin-muted: #aab7c8;
            --admin-muted-dark: #8190a5;

            --admin-success: #86efac;
            --admin-warning: #fde68a;
            --admin-danger: #fecaca;
            --admin-info: #bae6fd;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--admin-bg);
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--admin-bg);
            color: var(--admin-text);
            font-family: 'DM Sans', Arial, sans-serif;
            font-size: 14px;
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: 'DM Sans', Arial, sans-serif;
        }

        a {
            color: var(--admin-gold);
        }

        a:hover {
            color: var(--admin-gold-hover);
        }

        /* Sidebar */

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            width: 258px;
            height: 100vh;
            padding: 25px 16px;
            overflow-y: auto;
            background: var(--admin-sidebar);
            border-right: 1px solid var(--admin-border-soft);
            color: var(--admin-text);
        }

        .admin-brand {
            display: block;
            padding: 5px 10px 25px;
            color: var(--admin-text);
            font-family: 'Manrope', sans-serif;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -.7px;
            text-decoration: none;
        }

        .admin-brand:hover {
            color: #fff;
        }

        .admin-brand span {
            color: var(--admin-gold);
        }

        .sidebar-label {
            margin: 23px 12px 10px;
            color: var(--admin-muted-dark);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            margin-bottom: 5px;
            padding: 12px 13px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #c2ccda;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .sidebar-link:hover {
            background: var(--admin-surface-hover);
            color: #fff;
        }

        .sidebar-link.active {
            background: #202b3b;
            border-color: #3b4656;
            box-shadow: inset 3px 0 var(--admin-gold);
            color: var(--admin-gold-hover);
        }

        .sidebar-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            flex-shrink: 0;
            color: var(--admin-gold);
            font-size: 16px;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding: 18px 10px 0;
            border-top: 1px solid var(--admin-border-soft);
        }

        .admin-avatar {
            display: grid;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            place-items: center;
            background: #26364a;
            border: 1px solid #40536b;
            border-radius: 9px;
            color: var(--admin-gold-hover);
            font-weight: 800;
        }

        .admin-sidebar .text-white {
            color: var(--admin-text) !important;
        }

        .admin-sidebar .text-secondary {
            color: var(--admin-muted) !important;
        }

        .admin-sidebar form {
            margin: 0;
        }

        .sidebar-logout {
            cursor: pointer;
        }

        /* Main content */

        .admin-main {
            min-height: 100vh;
            margin-left: 258px;
            padding: 32px 34px 50px;
            background: var(--admin-bg);
        }

        .admin-main > .container,
        .admin-main > .container-fluid {
            max-width: 100%;
        }

        /* Typography */

        .admin-main h1,
        .admin-main h2,
        .admin-main h3,
        .admin-main h4,
        .admin-main h5,
        .admin-main h6 {
            color: var(--admin-text);
        }

        .admin-main p {
            color: var(--admin-text-soft);
        }

        .admin-main .text-dark {
            color: var(--admin-text) !important;
        }

        .admin-main .text-body {
            color: var(--admin-text-soft) !important;
        }

        .admin-main .text-secondary,
        .admin-main .text-muted {
            color: var(--admin-muted) !important;
        }

        .admin-main .small,
        .admin-main small {
            color: var(--admin-muted);
        }

        /* Cards and panels */

        .admin-main .card,
        .admin-main .content-card,
        .admin-main .stat-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            color: var(--admin-text);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
        }

        .admin-main .card-header,
        .admin-main .card-footer {
            background: #172438;
            border-color: var(--admin-border-soft);
            color: var(--admin-text);
        }

        .admin-main .card-body {
            color: var(--admin-text-soft);
        }

        .admin-main .card-title {
            color: var(--admin-text);
        }

        .admin-main .card-subtitle {
            color: var(--admin-muted);
        }

        /* Forms */

        .admin-main .form-label {
            margin-bottom: 7px;
            color: #dce4ee;
            font-size: 13px;
            font-weight: 600;
        }

        .admin-main .form-control,
        .admin-main .form-select {
            min-height: 42px;
            background-color: #0c1625;
            border: 1px solid #35465b;
            border-radius: 7px;
            color: var(--admin-text);
            font-size: 13px;
        }

        .admin-main textarea.form-control {
            min-height: 100px;
        }

        .admin-main .form-control::placeholder {
            color: #8d9bad;
            opacity: 1;
        }

        .admin-main .form-control:focus,
        .admin-main .form-select:focus {
            background-color: #0c1625;
            border-color: var(--admin-gold);
            box-shadow: 0 0 0 3px rgba(214, 181, 116, .13);
            color: var(--admin-text);
        }

        .admin-main .form-control:disabled,
        .admin-main .form-select:disabled {
            background: #172438;
            color: #91a0b4;
            opacity: 1;
        }

        .admin-main .form-text {
            color: var(--admin-muted);
        }

        .admin-main input[type="file"].form-control {
            padding: 7px;
            color: var(--admin-muted);
        }

        .admin-main input[type="file"]::file-selector-button {
            margin: -4px 10px -4px -4px;
            padding: 7px 11px;
            background: #26364a;
            border: 0;
            border-radius: 5px;
            color: #f1f5f9;
        }

        .admin-main .form-check-input {
            background-color: #0c1625;
            border-color: #526176;
        }

        .admin-main .form-check-input:checked {
            background-color: var(--admin-gold);
            border-color: var(--admin-gold);
        }

        .admin-main .form-check-label {
            color: var(--admin-text-soft);
        }

        .admin-main .is-invalid {
            border-color: #e58b8b;
        }

        .admin-main .invalid-feedback {
            color: #fca5a5;
        }

        .admin-main .is-valid {
            border-color: #71c99a;
        }

        /* Buttons */

        .admin-main .btn {
            border-radius: 7px;
            font-size: 12px;
            font-weight: 650;
        }

        .admin-main .btn-primary,
        .admin-main .btn-dark {
            background: var(--admin-gold);
            border-color: var(--admin-gold);
            color: #17202d;
        }

        .admin-main .btn-primary:hover,
        .admin-main .btn-dark:hover {
            background: var(--admin-gold-hover);
            border-color: var(--admin-gold-hover);
            color: #111827;
        }

        .admin-main .btn-secondary {
            background: #35465b;
            border-color: #526176;
            color: #f1f5f9;
        }

        .admin-main .btn-secondary:hover {
            background: #40536b;
            border-color: #64748b;
            color: #fff;
        }

        .admin-main .btn-outline-primary,
        .admin-main .btn-outline-dark {
            background: transparent;
            border-color: #8b7955;
            color: var(--admin-gold-hover);
        }

        .admin-main .btn-outline-primary:hover,
        .admin-main .btn-outline-dark:hover {
            background: #26364a;
            border-color: var(--admin-gold);
            color: #fff;
        }

        .admin-main .btn-outline-secondary {
            background: transparent;
            border-color: #526176;
            color: #dce4ee;
        }

        .admin-main .btn-outline-secondary:hover {
            background: #26364a;
            border-color: #64748b;
            color: #fff;
        }

        .admin-main .btn-outline-danger {
            border-color: #a34d5a;
            color: #fca5a5;
        }

        .admin-main .btn-outline-danger:hover {
            background: #3b1d27;
            color: #fecaca;
        }

        /* Tables */

        .admin-main .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e5ebf3;
            --bs-table-border-color: #2b3a4d;
            --bs-table-striped-bg: #142236;
            --bs-table-striped-color: #e5ebf3;
            --bs-table-hover-bg: #1b2b40;
            --bs-table-hover-color: #fff;

            margin-bottom: 0;
            color: #e5ebf3;
        }

        .admin-main .table > :not(caption) > * > * {
            background-color: transparent;
            border-bottom-color: #2b3a4d;
            color: inherit;
            box-shadow: none;
        }

        .admin-main .table thead th {
            padding: 13px 14px;
            background: #172438 !important;
            color: #dce4ee !important;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-main .table tbody td {
            padding: 13px 14px;
            color: #dce4ee;
            vertical-align: middle;
        }

        .admin-main .table tbody tr:hover > * {
            background: #172438 !important;
            color: #fff;
        }

        .admin-main .table-light,
        .admin-main .table-light > th,
        .admin-main .table-light > td {
            --bs-table-bg: #172438;
            --bs-table-color: #e5ebf3;
            --bs-table-border-color: #2b3a4d;
            color: #e5ebf3 !important;
        }

        .admin-main .table-responsive {
            border-radius: 9px;
        }

        /* Badges */

        .admin-main .badge {
            padding: 6px 9px;
            border: 1px solid transparent;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .admin-main .bg-success {
            background: #123326 !important;
            border-color: #276749;
            color: #bbf7d0 !important;
        }

        .admin-main .bg-warning {
            background: #3a2d16 !important;
            border-color: #80612b;
            color: #fde68a !important;
        }

        .admin-main .bg-danger {
            background: #3b1d27 !important;
            border-color: #783746;
            color: #fecaca !important;
        }

        .admin-main .bg-info {
            background: #142f45 !important;
            border-color: #285777;
            color: #bae6fd !important;
        }

        .admin-main .bg-secondary {
            background: #263244 !important;
            border-color: #526176;
            color: #d1d9e4 !important;
        }

        /* Alerts */

        .admin-main .alert {
            border-radius: 8px;
            font-size: 13px;
        }

        .admin-main .alert-success {
            background: #123326;
            border-color: #276749;
            color: #bbf7d0;
        }

        .admin-main .alert-danger {
            background: #3b1d27;
            border-color: #783746;
            color: #fecaca;
        }

        .admin-main .alert-warning {
            background: #3a2d16;
            border-color: #80612b;
            color: #fde68a;
        }

        .admin-main .alert-info {
            background: #142f45;
            border-color: #285777;
            color: #bae6fd;
        }

        .admin-main .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Pagination */

        .admin-main .pagination {
            gap: 4px;
        }

        .admin-main .page-link {
            background: #111c2c;
            border-color: #35465b;
            color: #dce4ee;
            font-size: 12px;
        }

        .admin-main .page-link:hover {
            background: #26364a;
            border-color: #526176;
            color: #fff;
        }

        .admin-main .page-item.active .page-link {
            background: var(--admin-gold);
            border-color: var(--admin-gold);
            color: #17202d;
        }

        .admin-main .page-item.disabled .page-link {
            background: #0c1625;
            border-color: #26364a;
            color: #718096;
        }

        /* Other Bootstrap elements */

        .admin-main .dropdown-menu {
            background: #111c2c;
            border: 1px solid var(--admin-border);
        }

        .admin-main .dropdown-item {
            color: var(--admin-text-soft);
            font-size: 13px;
        }

        .admin-main .dropdown-item:hover {
            background: #26364a;
            color: #fff;
        }

        .admin-main .list-group-item {
            background: #111c2c;
            border-color: #2b3a4d;
            color: var(--admin-text-soft);
        }

        .admin-main .modal-content {
            background: #111c2c;
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
        }

        .admin-main .modal-header,
        .admin-main .modal-footer {
            border-color: var(--admin-border);
        }

        .admin-main hr {
            border-color: var(--admin-border);
            opacity: 1;
        }

        /* Responsive */

        @media (max-width: 1100px) {
            .admin-sidebar {
                width: 78px;
                padding: 22px 9px;
            }

            .admin-brand {
                padding: 5px 0 25px;
                font-size: 0;
                text-align: center;
            }

            .admin-brand span {
                font-size: 21px;
            }

            .sidebar-label,
            .sidebar-text,
            .admin-text {
                display: none;
            }

            .sidebar-link {
                justify-content: center;
                padding: 13px 5px;
            }

            .admin-main {
                margin-left: 78px;
                padding: 28px 22px 40px;
            }

            .sidebar-bottom {
                padding-right: 0;
                padding-left: 0;
            }
        }

        @media (max-width: 650px) {
            .admin-sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: auto;
                padding: 12px;
                border-right: 0;
                border-bottom: 1px solid var(--admin-border);
            }

            .admin-brand {
                padding: 5px 8px 12px;
                font-size: 19px;
                text-align: left;
            }

            .admin-brand span {
                font-size: inherit;
            }

            .sidebar-label,
            .sidebar-text,
            .admin-text {
                display: block;
            }

            .sidebar-label {
                margin: 12px 10px 8px;
            }

            .sidebar-link {
                justify-content: flex-start;
                padding: 9px 10px;
            }

            .sidebar-bottom {
                margin-top: 10px;
                padding-top: 12px;
            }

            .admin-main {
                margin-left: 0;
                padding: 21px 13px 35px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <x-admin.sidebar :active="View::yieldContent('active')" />

    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>
