@props(['active' => ''])

@php
    $vendorUser = auth()->user();
    $vendorProfile = $vendorUser?->vendorProfile;
    $vendorApproved = $vendorProfile?->approval_status === 'approved';
@endphp

<aside class="vendor-sidebar">
    <a href="{{ route('vendor.dashboard') }}" class="vendor-brand">
        WHOLESALE<span>.</span>
        <small>VENDOR PORTAL</small>
    </a>

    <div class="vendor-sidebar-label">WORKSPACE</div>
    <nav class="vendor-navigation" aria-label="Vendor navigation">
        <a href="{{ route('vendor.dashboard') }}" class="vendor-nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
            <span class="vendor-nav-icon">DB</span><span class="vendor-nav-text">Dashboard</span>
        </a>
        @if($vendorApproved)
            <a href="{{ route('vendor.products.index') }}" class="vendor-nav-link {{ $active === 'products' ? 'active' : '' }}">
                <span class="vendor-nav-icon">PR</span><span class="vendor-nav-text">My Products</span>
            </a>
            <a href="{{ route('vendor.products.create') }}" class="vendor-nav-link {{ $active === 'create-product' ? 'active' : '' }}">
                <span class="vendor-nav-icon">ADD</span><span class="vendor-nav-text">Add Product</span>
            </a>
        @endif
    </nav>

    <div class="vendor-sidebar-label vendor-account-label">ACCOUNT</div>
    <form action="{{ route('vendor.logout') }}" method="POST" class="vendor-logout-form">
        @csrf
        <button type="submit" class="vendor-nav-link vendor-logout">
            <span class="vendor-nav-icon">OUT</span><span class="vendor-nav-text">Sign out</span>
        </button>
    </form>

    <div class="vendor-sidebar-bottom">
        <div class="vendor-user-avatar">{{ strtoupper(substr($vendorUser?->name ?? 'V', 0, 1)) }}</div>
        <div class="vendor-user-info">
            <strong>{{ $vendorUser?->name ?? 'Vendor' }}</strong>
            <small>{{ $vendorProfile?->business_name ?? 'Vendor Account' }}</small>
        </div>
    </div>
</aside>
