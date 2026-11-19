
@extends('layouts.vendor')

@section('title', 'Dashboard')

@section('content')
@php
    $profile = $vendor->vendorProfile;
    $status = $profile->approval_status;

    $statusLabels = [
        'approved' => 'Approved',
        'pending' => 'Pending',
        'under_review' => 'Under Review',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ];

    $statusClasses = [
        'approved' => 'vendor-status-approved',
        'pending' => 'vendor-status-pending',
        'under_review' => 'vendor-status-review',
        'rejected' => 'vendor-status-rejected',
        'suspended' => 'vendor-status-suspended',
    ];
@endphp

<style>
    .vendor-heading {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 25px;
    }

    .vendor-heading h2 {
        margin-bottom: 7px;
        color: #f1f5f9;
        font-size: 25px;
        font-weight: 700;
    }

    .vendor-heading p {
        margin: 0;
        color: #aab7c8;
        font-size: 14px;
    }

    .vendor-status {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .vendor-status-approved {
        background: #123326;
        border-color: #276749;
        color: #bbf7d0;
    }

    .vendor-status-pending {
        background: #3a2d16;
        border-color: #80612b;
        color: #fde68a;
    }

    .vendor-status-review {
        background: #142f45;
        border-color: #285777;
        color: #bae6fd;
    }

    .vendor-status-rejected {
        background: #3b1d27;
        border-color: #783746;
        color: #fecaca;
    }

    .vendor-status-suspended {
        background: #263244;
        border-color: #526176;
        color: #d1d9e4;
    }

    .vendor-stat-label {
        margin-bottom: 12px;
        color: #aab7c8;
        font-size: 13px;
        font-weight: 600;
    }

    .vendor-stat-value {
        color: #f1f5f9;
        font-size: 31px;
        font-weight: 700;
        line-height: 1.2;
    }

    .vendor-stat-card {
        height: 100%;
        padding: 22px;
    }

    .vendor-section-title {
        margin-bottom: 8px;
        color: #f1f5f9;
        font-size: 17px;
        font-weight: 700;
    }

    .vendor-business-label {
        margin-bottom: 5px;
        color: #91a0b4;
        font-size: 12px;
    }

    .vendor-business-value {
        overflow-wrap: anywhere;
        color: #e5ebf3;
        font-size: 14px;
        font-weight: 600;
    }

    .vendor-business-item {
        min-height: 58px;
    }

    .vendor-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 9px 15px;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .vendor-action-primary {
        background: #d6b574;
        color: #17202d;
    }

    .vendor-action-primary:hover {
        background: #e5c98f;
        color: #111827;
    }

    .vendor-action-secondary {
        border: 1px solid #526176;
        color: #e2e8f0;
    }

    .vendor-action-secondary:hover {
        background: #26364a;
        color: #fff;
    }
</style>

<div class="vendor-heading">
    <div>
        <h2>Dashboard</h2>
        <p>Welcome back, {{ $vendor->name }}.</p>
    </div>

    <span class="vendor-status {{ $statusClasses[$status] ?? 'vendor-status-suspended' }}">
        {{ $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status)) }}
    </span>
</div>

@if($status === 'approved')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="vendor-card vendor-stat-card">
                <div class="vendor-stat-label">Total Products</div>
                <div class="vendor-stat-value">
                    {{ $productStats['total'] ?? 0 }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vendor-card vendor-stat-card">
                <div class="vendor-stat-label">Pending Review</div>
                <div class="vendor-stat-value" style="color:#fcd34d;">
                    {{ $productStats['pending'] ?? 0 }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="vendor-card vendor-stat-card">
                <div class="vendor-stat-label">Approved Products</div>
                <div class="vendor-stat-value" style="color:#86efac;">
                    {{ $productStats['approved'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>

    <div class="vendor-card p-4 mb-4">
        <h5 class="vendor-section-title">Product Management</h5>

        <p class="muted-text mb-3">
            Submit wholesale products and track their review status.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('vendor.products.create') }}"
               class="vendor-action-link vendor-action-primary">
                + Add Product
            </a>

            <a href="{{ route('vendor.products.index') }}"
               class="vendor-action-link vendor-action-secondary">
                My Products
            </a>
        </div>
    </div>

@elseif($status === 'rejected')
    <div class="alert alert-danger mb-4">
        <h5 class="mb-2">Vendor application rejected</h5>
        <p class="mb-0">
            {{ $profile->rejection_reason ?: 'Please contact the administrator for details.' }}
        </p>
    </div>

@elseif($status === 'suspended')
    <div class="alert alert-danger mb-4">
        <strong>Vendor account suspended.</strong>
        Product management is unavailable. Please contact the administrator.
    </div>

@else
    <div class="alert alert-warning mb-4">
        <h5 class="mb-2">Verification in progress</h5>
        Your registration has been received. The administrator must approve your
        business before you can submit products.
    </div>
@endif

<div class="vendor-card p-4">
    <h5 class="vendor-section-title mb-4">Business Information</h5>

    <div class="row g-4">
        <div class="col-md-6 vendor-business-item">
            <div class="vendor-business-label">Business Name</div>
            <div class="vendor-business-value">
                {{ $profile->business_name ?: 'Not provided' }}
            </div>
        </div>

        <div class="col-md-6 vendor-business-item">
            <div class="vendor-business-label">Email</div>
            <div class="vendor-business-value">
                {{ $vendor->email }}
            </div>
        </div>

        <div class="col-md-6 vendor-business-item">
            <div class="vendor-business-label">Phone</div>
            <div class="vendor-business-value">
                {{ $profile->phone ?: 'Not provided' }}
            </div>
        </div>

        <div class="col-md-6 vendor-business-item">
            <div class="vendor-business-label">GST Number</div>
            <div class="vendor-business-value">
                {{ $profile->gst_number ?: 'Not provided' }}
            </div>
        </div>

        <div class="col-12 vendor-business-item">
            <div class="vendor-business-label">Business Address</div>
            <div class="vendor-business-value">
                {{ $profile->business_address ?: 'Not provided' }}
                @if($profile->city || $profile->state || $profile->pincode)
                    , {{ $profile->city }}
                    @if($profile->state), {{ $profile->state }}@endif
                    @if($profile->pincode) - {{ $profile->pincode }}@endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
