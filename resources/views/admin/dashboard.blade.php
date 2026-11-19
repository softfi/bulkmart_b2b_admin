
@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('active', 'dashboard')

@push('styles')
<style>
    .admin-dashboard {
        color: #f1f5f9;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 25px;
    }

    .dashboard-title {
        margin: 0 0 7px;
        color: #f1f5f9;
        font-size: 26px;
        font-weight: 750;
    }

    .dashboard-subtitle {
        margin: 0;
        color: #aab7c8;
        font-size: 13px;
    }

    .header-date {
        padding: 10px 14px;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 8px;
        color: #dce4ee;
        font-size: 12px;
        font-weight: 600;
    }

    .welcome-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 27px;
        padding: 24px 26px;
        background: #142236;
        border: 1px solid #35465b;
        border-left: 4px solid #d6b574;
        border-radius: 10px;
    }

    .welcome-title {
        margin-bottom: 7px;
        color: #f8fafc;
        font-size: 19px;
        font-weight: 700;
    }

    .welcome-text {
        margin: 0;
        color: #b5c1d0;
        font-size: 13px;
        line-height: 1.6;
    }

    .welcome-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        background: #d6b574;
        border: 1px solid #d6b574;
        border-radius: 7px;
        color: #17202d;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .welcome-action:hover {
        background: #e5c98f;
        border-color: #e5c98f;
        color: #111827;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 12px;
        margin-bottom: 14px;
    }

    .section-title {
        margin: 0;
        color: #f1f5f9;
        font-size: 16px;
        font-weight: 700;
    }

    .section-description {
        margin-top: 5px;
        color: #9eacbd;
        font-size: 12px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 27px;
    }

    .kpi-card {
        min-width: 0;
        padding: 19px;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 10px;
    }

    .kpi-label {
        margin-bottom: 14px;
        color: #aab7c8;
        font-size: 12px;
        font-weight: 600;
    }

    .kpi-value {
        color: #f8fafc;
        font-size: 29px;
        font-weight: 750;
        line-height: 1.2;
    }

    .kpi-footer {
        margin-top: 8px;
        color: #91a0b4;
        font-size: 11px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(260px, .8fr);
        gap: 16px;
        margin-bottom: 25px;
        align-items: start;
    }

    .admin-panel {
        min-width: 0;
        overflow: hidden;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 10px;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 17px 19px;
        border-bottom: 1px solid #2b3a4d;
    }

    .panel-body {
        padding: 19px;
    }

    .status-list {
        display: grid;
        gap: 17px;
    }

    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 12px;
    }

    .status-name {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #dce4ee;
    }

    .status-count {
        color: #f8fafc;
        font-weight: 700;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .dot-pending { background: #eabf65; }
    .dot-review { background: #75b8e8; }
    .dot-approved { background: #71c99a; }
    .dot-rejected { background: #e58b8b; }
    .dot-suspended { background: #a99bd4; }

    .status-progress {
        height: 6px;
        margin-top: 8px;
        overflow: hidden;
        background: #26364a;
        border-radius: 10px;
    }

    .status-progress-fill {
        height: 100%;
        border-radius: 10px;
    }

    .fill-pending { background: #eabf65; }
    .fill-review { background: #75b8e8; }
    .fill-approved { background: #71c99a; }
    .fill-rejected { background: #e58b8b; }
    .fill-suspended { background: #a99bd4; }

    .admin-table-wrap {
        overflow-x: auto;
    }

    .admin-dashboard-table {
        width: 100%;
        min-width: 650px;
        margin: 0;
        border-collapse: collapse;
    }

    .admin-dashboard-table th {
        padding: 13px 15px;
        background: #172438;
        border-bottom: 1px solid #304258;
        color: #b5c1d0;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-dashboard-table td {
        padding: 14px 15px;
        border-bottom: 1px solid #26364a;
        color: #dce4ee;
        font-size: 12px;
        vertical-align: middle;
    }

    .admin-dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .admin-dashboard-table tbody tr:hover {
        background: #172438;
    }

    .vendor-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .vendor-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        background: #26364a;
        border: 1px solid #40536b;
        border-radius: 8px;
        color: #e5c98f;
        font-size: 11px;
        font-weight: 700;
    }

    .vendor-cell-name {
        color: #f1f5f9;
        font-size: 12px;
        font-weight: 650;
    }

    .vendor-cell-email {
        margin-top: 3px;
        color: #aab7c8;
        font-size: 10px;
    }

    .status-badge {
        display: inline-flex;
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .status-pending {
        background: #3a2d16;
        border-color: #80612b;
        color: #fde68a;
    }

    .status-under_review {
        background: #142f45;
        border-color: #285777;
        color: #bae6fd;
    }

    .status-approved {
        background: #123326;
        border-color: #276749;
        color: #bbf7d0;
    }

    .status-rejected {
        background: #3b1d27;
        border-color: #783746;
        color: #fecaca;
    }

    .status-suspended {
        background: #302744;
        border-color: #5e4b80;
        color: #ddd6fe;
    }

    .admin-text-link {
        color: #e5c98f;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .admin-text-link:hover {
        color: #f5dfad;
        text-decoration: underline;
    }

    .empty-state {
        padding: 32px 15px;
        color: #aab7c8;
        font-size: 12px;
        text-align: center;
    }

    .empty-state strong {
        display: block;
        margin-bottom: 5px;
        color: #f1f5f9;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
        margin-bottom: 25px;
    }

    .action-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 16px;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 9px;
        text-decoration: none;
    }

    .action-card:hover {
        background: #172438;
        border-color: #8d7952;
    }

    .action-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        background: #26364a;
        border-radius: 8px;
        color: #e5c98f;
        font-size: 16px;
    }

    .action-title {
        margin-bottom: 4px;
        color: #f1f5f9;
        font-size: 12px;
        font-weight: 700;
    }

    .action-description {
        color: #aab7c8;
        font-size: 10px;
        line-height: 1.5;
    }

    .account-summary {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 0;
        border-bottom: 1px solid #2b3a4d;
        font-size: 12px;
    }

    .account-summary:last-child {
        border-bottom: 0;
    }

    .account-label {
        color: #b5c1d0;
    }

    .account-value {
        color: #f8fafc;
        font-weight: 700;
    }

    @media (max-width: 1100px) {
        .kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .dashboard-title {
            font-size: 22px;
        }

        .welcome-banner {
            align-items: flex-start;
            flex-direction: column;
            padding: 19px;
        }

        .kpi-grid {
            gap: 10px;
        }

        .kpi-card {
            padding: 14px;
        }

        .kpi-value {
            font-size: 24px;
        }

        .action-grid {
            grid-template-columns: 1fr;
        }

        .panel-header,
        .panel-body {
            padding: 15px;
        }
    }
</style>
@endpush

@section('content')
@php
    $adminName = auth()->user()->name ?? 'Administrator';

    $vendorStatuses = [
        [
            'label' => 'Pending',
            'count' => $pendingVendors,
            'key' => 'pending',
        ],
        [
            'label' => 'Under Review',
            'count' => $underReviewVendors,
            'key' => 'review',
        ],
        [
            'label' => 'Approved',
            'count' => $approvedVendors,
            'key' => 'approved',
        ],
        [
            'label' => 'Rejected',
            'count' => $rejectedVendors,
            'key' => 'rejected',
        ],
        [
            'label' => 'Suspended',
            'count' => $suspendedVendors,
            'key' => 'suspended',
        ],
    ];
@endphp

<div class="admin-dashboard">

    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">Dashboard Overview</h1>
            <p class="dashboard-subtitle">
                Monitor vendor registrations and supplier account activity.
            </p>
        </div>

        <div class="header-date">
            {{ now()->format('l, d F Y') }}
        </div>
    </div>

    <div class="welcome-banner">
        <div>
            <div class="welcome-title">
                Welcome back, {{ $adminName }}
            </div>

            <p class="welcome-text">
                Your administration overview and latest vendor activity.
                There are {{ number_format($totalVendors) }} registered vendors.
            </p>
        </div>

        <a href="{{ route('admin.vendors.index') }}" class="welcome-action">
            Manage Vendors
        </a>
    </div>

    <div class="section-heading">
        <div>
            <h2 class="section-title">Platform Overview</h2>
            <div class="section-description">
                Current vendor and account statistics
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Total Vendors</div>
            <div class="kpi-value">{{ number_format($totalVendors) }}</div>
            <div class="kpi-footer">All registered suppliers</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Pending Applications</div>
            <div class="kpi-value">{{ number_format($pendingVendors) }}</div>
            <div class="kpi-footer">Applications awaiting review</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Approved Vendors</div>
            <div class="kpi-value">{{ number_format($approvedVendors) }}</div>
            <div class="kpi-footer">Approved supplier profiles</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Active Accounts</div>
            <div class="kpi-value">{{ number_format($activeVendorAccounts) }}</div>
            <div class="kpi-footer">Active vendor user accounts</div>
        </div>
    </div>

    <div class="dashboard-grid">

        <section class="admin-panel">
            <div class="panel-header">
                <div>
                    <h2 class="section-title">Vendor Approval Status</h2>
                    <div class="section-description">
                        Current application distribution
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <div class="status-list">
                    @foreach($vendorStatuses as $status)
                        @php
                            $percentage = $totalVendors > 0
                                ? ($status['count'] / $totalVendors) * 100
                                : 0;
                        @endphp

                        <div>
                            <div class="status-item">
                                <span class="status-name">
                                    <span class="status-dot dot-{{ $status['key'] }}"></span>
                                    {{ $status['label'] }}
                                </span>

                                <span class="status-count">
                                    {{ number_format($status['count']) }}
                                </span>
                            </div>

                            <div class="status-progress">
                                <div
                                    class="status-progress-fill fill-{{ $status['key'] }}"
                                    style="width: {{ $percentage }}%;"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-header">
                <div>
                    <h2 class="section-title">Vendor Account Health</h2>
                    <div class="section-description">
                        Account activation overview
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <div class="account-summary">
                    <span class="account-label">Active vendor accounts</span>
                    <span class="account-value">
                        {{ number_format($activeVendorAccounts) }}
                    </span>
                </div>

                <div class="account-summary">
                    <span class="account-label">Inactive vendor accounts</span>
                    <span class="account-value">
                        {{ number_format($inactiveVendorAccounts) }}
                    </span>
                </div>

                <div class="account-summary">
                    <span class="account-label">Rejected applications</span>
                    <span class="account-value">
                        {{ number_format($rejectedVendors) }}
                    </span>
                </div>

                <div class="account-summary">
                    <span class="account-label">Suspended vendors</span>
                    <span class="account-value">
                        {{ number_format($suspendedVendors) }}
                    </span>
                </div>
            </div>
        </section>

    </div>

    <section class="admin-panel mb-4">
        <div class="panel-header">
            <div>
                <h2 class="section-title">Recent Vendor Registrations</h2>
                <div class="section-description">
                    Latest suppliers registered on your platform
                </div>
            </div>

            <a href="{{ route('admin.vendors.index') }}" class="admin-text-link">
                View All
            </a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-dashboard-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Business Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentVendors as $vendor)
                        @php
                            $vendorName = $vendor->user->name ?? 'Unknown Vendor';

                            $initials = collect(explode(' ', trim($vendorName)))
                                ->filter()
                                ->take(2)
                                ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                ->implode('');

                            $status = $vendor->approval_status ?? 'pending';

                            $location = collect([
                                $vendor->city,
                                $vendor->state,
                            ])->filter()->implode(', ');
                        @endphp

                        <tr>
                            <td>
                                <div class="vendor-cell">
                                    <div class="vendor-avatar">
                                        {{ $initials ?: 'V' }}
                                    </div>

                                    <div>
                                        <div class="vendor-cell-name">
                                            {{ $vendorName }}
                                        </div>

                                        <div class="vendor-cell-email">
                                            {{ $vendor->user->email ?? 'Not available' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                {{ $vendor->business_name ?: 'Not provided' }}
                            </td>

                            <td>
                                {{ $location ?: 'Not provided' }}
                            </td>

                            <td>
                                <span class="status-badge status-{{ $status }}">
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>

                            <td>
                                {{ $vendor->created_at?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.vendors.show', $vendor) }}"
                                    class="admin-text-link"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <strong>No vendor registrations yet</strong>
                                    New vendor applications will appear here.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="section-heading">
        <div>
            <h2 class="section-title">Quick Actions</h2>
            <div class="section-description">
                Frequently used administration tasks
            </div>
        </div>
    </div>

    <div class="action-grid">

        <a href="{{ route('admin.vendors.index', ['status' => 'pending']) }}"
           class="action-card">
            <div class="action-icon">⌛</div>
            <div>
                <div class="action-title">Review Applications</div>
                <div class="action-description">
                    Check pending vendor registrations
                </div>
            </div>
        </a>

        <a href="{{ route('admin.vendors.index') }}"
           class="action-card">
            <div class="action-icon">♙</div>
            <div>
                <div class="action-title">Manage Vendors</div>
                <div class="action-description">
                    Search and inspect supplier profiles
                </div>
            </div>
        </a>

        <a href="{{ route('admin.vendors.index', ['status' => 'approved']) }}"
           class="action-card">
            <div class="action-icon">✓</div>
            <div>
                <div class="action-title">Approved Suppliers</div>
                <div class="action-description">
                    View approved vendor accounts
                </div>
            </div>
        </a>

    </div>

</div>
@endsection
