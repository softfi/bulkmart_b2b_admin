
@extends('layouts.admin')

@section('title', 'Vendor Management')
@section('active', 'vendors')

@push('styles')
<style>
    .vendor-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 25px;
    }

    .vendor-page-title {
        margin: 0 0 6px;
        color: #f1f5f9;
        font-size: 27px;
        font-weight: 800;
        letter-spacing: -.7px;
    }

    .vendor-page-subtitle {
        margin: 0;
        color: #aab7c8;
        font-size: 13px;
    }

    .vendor-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .vendor-stat-card {
        min-width: 0;
        padding: 19px;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 10px;
    }

    .vendor-stat-label {
        margin-bottom: 11px;
        color: #aab7c8;
        font-size: 12px;
        font-weight: 600;
    }

    .vendor-stat-value {
        color: #f1f5f9;
        font-size: 27px;
        font-weight: 800;
        letter-spacing: -.6px;
    }

    .vendor-stat-note {
        margin-top: 5px;
        color: #8190a5;
        font-size: 11px;
    }

    .vendor-content-card {
        overflow: hidden;
        background: #111c2c;
        border: 1px solid #304258;
        border-radius: 10px;
    }

    .vendor-filter-section {
        padding: 19px;
        border-bottom: 1px solid #304258;
    }

    .vendor-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .vendor-filter-field {
        flex: 1;
        min-width: 210px;
    }

    .vendor-filter-field.status-field {
        flex: 0 0 190px;
    }

    .vendor-filter-label {
        display: block;
        margin-bottom: 7px;
        color: #dce4ee;
        font-size: 12px;
        font-weight: 600;
    }

    .vendor-filter-input,
    .vendor-filter-select {
        width: 100%;
        height: 42px;
        padding: 0 12px;
        background: #0c1625;
        border: 1px solid #35465b;
        border-radius: 7px;
        color: #f1f5f9;
        font-size: 13px;
        outline: none;
    }

    .vendor-filter-input::placeholder {
        color: #8d9bad;
        opacity: 1;
    }

    .vendor-filter-input:focus,
    .vendor-filter-select:focus {
        border-color: #d6b574;
        box-shadow: 0 0 0 3px rgba(214, 181, 116, .12);
    }

    .vendor-filter-select option {
        background: #111c2c;
        color: #f1f5f9;
    }

    .vendor-filter-actions {
        display: flex;
        gap: 8px;
    }

    .vendor-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .vendor-action-primary {
        background: #d6b574;
        border: 1px solid #d6b574;
        color: #17202d;
    }

    .vendor-action-primary:hover {
        background: #e5c98f;
        border-color: #e5c98f;
        color: #111827;
    }

    .vendor-action-secondary {
        background: transparent;
        border: 1px solid #526176;
        color: #dce4ee;
    }

    .vendor-action-secondary:hover {
        background: #26364a;
        color: #fff;
    }

    .vendor-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .vendor-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .vendor-table th {
        padding: 14px 17px;
        background: #172438;
        border-bottom: 1px solid #304258;
        color: #aab7c8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .8px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .vendor-table td {
        padding: 15px 17px;
        border-bottom: 1px solid #26364a;
        color: #dce4ee;
        font-size: 12px;
        vertical-align: middle;
    }

    .vendor-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .vendor-table tbody tr:hover {
        background: #172438;
    }

    .vendor-profile {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 190px;
    }

    .vendor-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        background: #26364a;
        border: 1px solid #40536b;
        border-radius: 9px;
        color: #e5c98f;
        font-size: 12px;
        font-weight: 800;
    }

    .vendor-name {
        margin-bottom: 4px;
        color: #f1f5f9;
        font-size: 12px;
        font-weight: 700;
    }

    .vendor-email,
    .vendor-muted {
        color: #aab7c8;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .vendor-business-name {
        margin-bottom: 4px;
        color: #e5ebf3;
        font-weight: 650;
    }

    .vendor-status {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 750;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .vendor-status-pending {
        background: #3a2d16;
        border-color: #80612b;
        color: #fde68a;
    }

    .vendor-status-under_review {
        background: #142f45;
        border-color: #285777;
        color: #bae6fd;
    }

    .vendor-status-approved {
        background: #123326;
        border-color: #276749;
        color: #bbf7d0;
    }

    .vendor-status-rejected {
        background: #3b1d27;
        border-color: #783746;
        color: #fecaca;
    }

    .vendor-status-suspended {
        background: #302443;
        border-color: #594477;
        color: #e9d5ff;
    }

    .vendor-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 33px;
        padding: 0 11px;
        background: transparent;
        border: 1px solid #8b7955;
        border-radius: 6px;
        color: #e5c98f;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .vendor-view-link:hover {
        background: #26364a;
        border-color: #d6b574;
        color: #fff;
    }

    .vendor-empty-state {
        padding: 48px 20px;
        color: #aab7c8;
        text-align: center;
    }

    .vendor-empty-state strong {
        display: block;
        margin-bottom: 7px;
        color: #f1f5f9;
        font-size: 15px;
    }

    .vendor-table-footer {
        padding: 16px 19px;
        border-top: 1px solid #304258;
    }

    .vendor-table-footer nav {
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 1000px) {
        .vendor-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .vendor-page-title {
            font-size: 23px;
        }

        .vendor-stat-grid {
            gap: 10px;
        }

        .vendor-stat-card {
            padding: 14px;
        }

        .vendor-stat-value {
            font-size: 22px;
        }

        .vendor-filter-field,
        .vendor-filter-field.status-field {
            flex: 1 1 100%;
        }

        .vendor-filter-actions {
            width: 100%;
        }

        .vendor-filter-actions > * {
            flex: 1;
        }
    }

    .vendor-avatar-profile {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #263d5b, #172438);
    border: 1px solid #52657d;
    color: #e6c58d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
}
</style>
@endpush

@section('content')

@php
    $totalVendors = \App\Models\VendorProfile::count();

    $pendingVendors = \App\Models\VendorProfile::where(
        'approval_status',
        'pending'
    )->count();

    $approvedVendors = \App\Models\VendorProfile::where(
        'approval_status',
        'approved'
    )->count();

    $rejectedVendors = \App\Models\VendorProfile::where(
        'approval_status',
        'rejected'
    )->count();
@endphp

<div class="vendor-page-header">
    <div>
        <h1 class="vendor-page-title">Vendor Management</h1>
        <p class="vendor-page-subtitle">
            Review vendor applications and manage your supplier network.
        </p>
    </div>
</div>

<div class="vendor-stat-grid">

    <div class="vendor-stat-card">
        <div class="vendor-stat-label">Total Vendors</div>
        <div class="vendor-stat-value">
            {{ number_format($totalVendors) }}
        </div>
        <div class="vendor-stat-note">Registered supplier accounts</div>
    </div>

    <div class="vendor-stat-card">
        <div class="vendor-stat-label">Pending Review</div>
        <div class="vendor-stat-value">
            {{ number_format($pendingVendors) }}
        </div>
        <div class="vendor-stat-note">Awaiting admin verification</div>
    </div>

    <div class="vendor-stat-card">
        <div class="vendor-stat-label">Approved Vendors</div>
        <div class="vendor-stat-value">
            {{ number_format($approvedVendors) }}
        </div>
        <div class="vendor-stat-note">Approved supplier accounts</div>
    </div>

    <div class="vendor-stat-card">
        <div class="vendor-stat-label">Rejected Vendors</div>
        <div class="vendor-stat-value">
            {{ number_format($rejectedVendors) }}
        </div>
        <div class="vendor-stat-note">Applications not approved</div>
    </div>

</div>

<div class="vendor-content-card">

    <div class="vendor-filter-section">

        <form action="{{ route('admin.vendors.index') }}"
              method="GET"
              class="vendor-filter-form">

            <div class="vendor-filter-field">
                <label for="vendor-search" class="vendor-filter-label">
                    Search Vendors
                </label>

                <input
                    type="text"
                    id="vendor-search"
                    name="search"
                    class="vendor-filter-input"
                    placeholder="Vendor, business, email, phone..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="vendor-filter-field status-field">
                <label for="vendor-status" class="vendor-filter-label">
                    Approval Status
                </label>

                <select
                    id="vendor-status"
                    name="status"
                    class="vendor-filter-select"
                >
                    <option value="">All Statuses</option>

                    <option value="pending"
                        @selected(request('status') === 'pending')>
                        Pending
                    </option>

                    <option value="under_review"
                        @selected(request('status') === 'under_review')>
                        Under Review
                    </option>

                    <option value="approved"
                        @selected(request('status') === 'approved')>
                        Approved
                    </option>

                    <option value="rejected"
                        @selected(request('status') === 'rejected')>
                        Rejected
                    </option>

                    <option value="suspended"
                        @selected(request('status') === 'suspended')>
                        Suspended
                    </option>
                </select>
            </div>

            <div class="vendor-filter-actions">
                <button type="submit"
                        class="vendor-action-btn vendor-action-primary">
                    Apply Filters
                </button>

                <a href="{{ route('admin.vendors.index') }}"
                   class="vendor-action-btn vendor-action-secondary">
                    Reset
                </a>
            </div>

        </form>

    </div>

    <div class="vendor-table-wrap">

        <table class="vendor-table">

            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Business</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($vendors as $vendor)

                    @php
                        $vendorName = $vendor->user->name ?? 'Unknown Vendor';

                        $initials = collect(explode(' ', trim($vendorName)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                            ->implode('');

                        $status = $vendor->approval_status ?? 'pending';

                        $allowedStatuses = [
                            'pending',
                            'under_review',
                            'approved',
                            'rejected',
                            'suspended',
                        ];

                        if (!in_array($status, $allowedStatuses, true)) {
                            $status = 'pending';
                        }
                    @endphp

                    <tr>

                        <td>
                            <div class="vendor-profile">

                               <div class="vendor-avatar vendor-avatar-profile">
                                    <span>{{ $initials ?: 'V' }}</span>
                                </div>

                                <div>
                                    <div class="vendor-name">
                                        {{ $vendorName }}
                                    </div>

                                    <div class="vendor-email">
                                        {{ $vendor->user->email ?? 'No email available' }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            <div class="vendor-business-name">
                                {{ $vendor->business_name }}
                            </div>

                            <div class="vendor-muted">
                                {{ $vendor->phone ?: 'No phone provided' }}
                            </div>
                        </td>

                        <td>
                            {{ collect([
                                $vendor->city,
                                $vendor->state
                            ])->filter()->implode(', ') ?: '—' }}
                        </td>

                        <td>
                            <span class="vendor-status vendor-status-{{ $status }}">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </td>

                        <td>
                            {{ $vendor->created_at?->format('d M Y') ?? '—' }}
                        </td>

                        <td>
                            <a
                                href="{{ route('admin.vendors.show', $vendor) }}"
                                class="vendor-view-link"
                            >
                                View Vendor
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">
                            <div class="vendor-empty-state">
                                <strong>No vendors found</strong>
                                Try changing your search or filter criteria.
                            </div>
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($vendors->hasPages())
        <div class="vendor-table-footer">
            {{ $vendors->links() }}
        </div>
    @endif

</div>

@endsection
