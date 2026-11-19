
@extends('layouts.admin')

@section('title', 'Product Management')
@section('active', 'products')

@push('styles')
<style>

    .product-list-thumb {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #34445a;
    background: #0b1422;
    flex-shrink: 0;
}

.product-list-placeholder {
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #1b2a40;
    color: #aab6c5;
    flex-shrink: 0;
}

    .products-page {
        color: #edf2f8;
    }

    .products-page .page-heading {
        color: #f3f5f8;
        font-size: 27px;
        font-weight: 750;
        letter-spacing: -.6px;
    }

    .products-page .page-description,
    .products-page .muted {
        color: #a2afc0 !important;
    }

    .products-page .product-card {
        background: #111c2c;
        border: 1px solid #26364a;
        border-radius: 13px;
    }

    .products-page .stat-label {
        color: #aab6c5;
        font-size: 12px;
        font-weight: 600;
    }

    .products-page .stat-value {
        color: #f3f5f8;
        font-size: 29px;
        font-weight: 750;
        margin-top: 9px;
    }

    .products-page .stat-indicator {
        width: 34px;
        height: 3px;
        border-radius: 5px;
        background: #c6a66b;
        margin-top: 13px;
    }

    .products-page .form-label {
        color: #dce4ed;
        font-size: 13px;
        font-weight: 600;
    }

    .products-page .form-control,
    .products-page .form-select {
        min-height: 43px;
        background-color: #0b1422;
        color: #edf2f8;
        border: 1px solid #34445a;
        border-radius: 8px;
    }

    .products-page .form-control::placeholder {
        color: #8998aa;
        opacity: 1;
    }

    .products-page .form-control:focus,
    .products-page .form-select:focus {
        background-color: #0b1422;
        color: #fff;
        border-color: #c6a66b;
        box-shadow: 0 0 0 .2rem rgba(198,166,107,.14);
    }

    .products-page .form-select option {
        background: #111c2c;
        color: #edf2f8;
    }

    .products-page .btn-gold {
        background: #c6a66b;
        border: 1px solid #c6a66b;
        color: #111827;
        font-weight: 700;
        border-radius: 8px;
        padding: 9px 15px;
    }

    .products-page .btn-gold:hover {
        background: #d7ba82;
        border-color: #d7ba82;
        color: #111827;
    }

    .products-page .btn-outline-muted {
        border: 1px solid #526176;
        color: #dce4ed;
        border-radius: 8px;
        padding: 9px 15px;
    }

    .products-page .btn-outline-muted:hover {
        background: #26364a;
        color: #fff;
    }

    .products-page .table {
        color: #e5ebf3;
        margin: 0;
    }

    .products-page .table thead th {
        background: #172438;
        color: #b9c5d4;
        border-bottom: 1px solid #34445a;
        border-top: 0;
        padding: 15px 13px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .products-page .table tbody td {
        background: #111c2c;
        color: #e5ebf3;
        border-color: #26364a;
        padding: 15px 13px;
        vertical-align: middle;
    }

    .products-page .table tbody tr:hover td {
        background: #172438;
    }

    .products-page .product-name {
        color: #f3f5f8;
        font-weight: 650;
    }

    .products-page .vendor-name {
        color: #e5ebf3;
        font-weight: 600;
    }

    .products-page .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .products-page .status-pending {
        color: #f4d28a;
        background: rgba(190,142,48,.17);
        border: 1px solid rgba(190,142,48,.35);
    }

    .products-page .status-approved {
        color: #91d8ad;
        background: rgba(46,139,87,.17);
        border: 1px solid rgba(46,139,87,.35);
    }

    .products-page .status-rejected {
        color: #f0a0a0;
        background: rgba(180,65,65,.17);
        border: 1px solid rgba(180,65,65,.35);
    }

    .products-page .status-suspended {
        color: #c3cbd6;
        background: rgba(120,135,155,.17);
        border: 1px solid rgba(120,135,155,.35);
    }

    .products-page .review-link {
        display: inline-block;
        background: rgba(198,166,107,.12);
        border: 1px solid rgba(198,166,107,.4);
        color: #e2c58e;
        padding: 7px 12px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .products-page .review-link:hover {
        background: #c6a66b;
        color: #111827;
    }

    .products-page .empty-state {
        color: #aab6c5;
        padding: 38px 15px;
        text-align: center;
    }

    .products-page .pagination {
        margin-bottom: 0;
    }

    .products-page .page-link {
        background: #0b1422;
        border-color: #34445a;
        color: #dce4ed;
    }

    .products-page .page-item.active .page-link {
        background: #c6a66b;
        border-color: #c6a66b;
        color: #111827;
    }

    .products-page .page-item.disabled .page-link {
        background: #111c2c;
        color: #748399;
        border-color: #26364a;
    }

    @media (max-width: 767px) {
        .products-page .page-heading {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')
<div class="products-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-heading mb-1">Product Management</h2>
            <p class="page-description mb-0">
                Review vendor product submissions and manage catalogue eligibility.
            </p>
        </div>
    </div>

    {{-- Product status summary --}}
    <div class="row g-3 mb-4">
        @foreach([
            'pending' => 'Pending Review',
            'approved' => 'Approved Products',
            'rejected' => 'Rejected Products',
            'suspended' => 'Suspended Products'
        ] as $status => $label)

            <div class="col-6 col-xl-3">
                <div class="product-card h-100 p-3 p-lg-4">
                    <div class="stat-label">{{ $label }}</div>

                    <div class="stat-value">
                        {{ $counts[$status] ?? 0 }}
                    </div>

                    <div class="stat-indicator"></div>
                </div>
            </div>

        @endforeach
    </div>

    {{-- Search and filters --}}
    <div class="product-card p-3 p-lg-4 mb-4">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="product-search" class="form-label">
                        Search products or vendors
                    </label>

                    <input
                        id="product-search"
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Product name, category or business name"
                    >
                </div>

                <div class="col-md-3">
                    <label for="product-status" class="form-label">
                        Review status
                    </label>

                    <select
                        id="product-status"
                        name="status"
                        class="form-select"
                    >
                        <option value="">All statuses</option>

                        @foreach(['pending', 'approved', 'rejected', 'suspended'] as $status)
                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-gold">
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('admin.products.index') }}"
                        class="btn btn-outline-muted"
                    >
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Product submissions table --}}
    <div class="product-card overflow-hidden">

        <div class="p-3 p-lg-4 border-bottom" style="border-color:#26364a !important;">
            <h5 class="mb-1 fw-bold">Vendor Product Submissions</h5>
            <p class="muted small mb-0">
                Review submitted products, vendor pricing and available quantities.
            </p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Image</th>
                        <th>Product</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Price / Unit</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)
                        @php
                            $status = $product->status ?? 'pending';
                            $statusClass = [
                                'pending' => 'status-pending',
                                'approved' => 'status-approved',
                                'rejected' => 'status-rejected',
                                'suspended' => 'status-suspended',
                            ][$status] ?? 'status-suspended';
                        @endphp

                        <tr>
                            <td class="ps-3">
                                @if($product->image_path)
                                    <img
                                        src="{{ route('admin.products.image', $product) }}"
                                        alt="{{ $product->name }}"
                                        class="product-list-thumb"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="product-list-placeholder">📦</div>
                                @endif
                            </td>
                            <td class="ps-3">
                                <div class="product-name">
                                    {{ $product->name }}
                                </div>

                                <small class="muted">
                                    MOQ:
                                    {{ $product->minimum_order_quantity }}
                                    {{ $product->unit }}
                                </small>
                            </td>

                            <td>
                                <div class="vendor-name">
                                    {{ $product->vendorProfile->business_name ?? 'Unknown Vendor' }}
                                </div>

                                <small class="muted">
                                    {{ $product->vendorProfile->user->email ?? 'No email available' }}
                                </small>
                            </td>

                            <td>
                                {{ $product->category ?: 'Uncategorised' }}
                            </td>

                            <td class="text-nowrap">
                                ₹{{ number_format((float) $product->price_per_unit, 2) }}
                            </td>

                            <td class="text-nowrap">
                                {{ number_format((float) $product->available_quantity, 2) }}
                                {{ $product->unit }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ str_replace('_', ' ', $status) }}
                                </span>
                            </td>

                            <td class="text-end pe-3">
                                <a
                                    href="{{ route('admin.products.show', $product) }}"
                                    class="review-link"
                                >
                                    Review
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="fw-semibold mb-1">
                                        No products found
                                    </div>
                                    <div class="small">
                                        Try changing your search or status filter.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-3 p-lg-4 border-top" style="border-color:#26364a !important;">
                {{ $products->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
