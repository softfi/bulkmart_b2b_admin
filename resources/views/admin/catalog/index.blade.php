
@extends('layouts.admin')

@section('active', 'catalog')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">Customer Catalog</h3>
            <p class="text-muted mb-0">
                Manage customer-facing products, selling prices and listing availability.
            </p>
        </div>

        <a href="{{ route('admin.catalog.create') }}" class="btn btn-primary px-4">
            + Create Listing
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="catalog-stat">
                <div class="stat-label">Total Listings</div>
                <div class="stat-value">{{ $listings->total() }}</div>
                <div class="stat-description">Customer catalog records</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="catalog-stat">
                <div class="stat-label">Current Page</div>
                <div class="stat-value">{{ $listings->count() }}</div>
                <div class="stat-description">Listings displayed below</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="catalog-stat">
                <div class="stat-label">Page</div>
                <div class="stat-value">{{ $listings->currentPage() }}</div>
                <div class="stat-description">
                    Of {{ $listings->lastPage() }} pages
                </div>
            </div>
        </div>
    </div>

    <div class="card catalog-card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">Product Listings</h5>
                <div class="small text-muted">
                    Review listing details, pricing and activation status.
                </div>
            </div>

            <span class="small text-muted">
                {{ $listings->firstItem() ?? 0 }}–{{ $listings->lastItem() ?? 0 }}
                of {{ $listings->total() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table catalog-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Vendor</th>
                        <th>Minimum Order</th>
                        <th>Customer Price Tiers</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($listings as $listing)
                        <tr>
                            <td>
                                <div class="fw-semibold product-name">
                                    {{ $listing->display_name ?: $listing->product->name }}
                                </div>

                                <div class="small text-muted mt-1">
                                    {{ $listing->product->category }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-medium">
                                    {{ $listing->product->vendorProfile->business_name }}
                                </div>
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    {{ number_format($listing->minimum_order_quantity, 3) }}
                                </span>

                                <span class="text-muted">
                                    {{ $listing->product->unit }}
                                </span>
                            </td>

                            <td>
                                @forelse($listing->priceTiers as $tier)
                                    <div class="price-tier-row">
                                        <span>
                                            {{ number_format($tier->minimum_quantity, 3) }}
                                            –
                                            {{ $tier->maximum_quantity !== null
                                                ? number_format($tier->maximum_quantity, 3)
                                                : 'Unlimited' }}
                                        </span>

                                        <strong>
                                            ₹{{ number_format($tier->unit_price, 2) }}
                                        </strong>
                                        <span class="text-muted">/ unit</span>
                                    </div>
                                @empty
                                    <span class="text-muted small">
                                        No pricing configured
                                    </span>
                                @endforelse
                            </td>

                            <td>
                                @if($listing->is_active)
                                    <span class="catalog-status active">
                                        Active
                                    </span>
                                @else
                                    <span class="catalog-status inactive">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                    <a
                                        href="{{ route('admin.catalog.edit', $listing) }}"
                                        class="btn btn-sm btn-outline-gold"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.catalog.status', $listing) }}"
                                        onsubmit="return confirm('Are you sure you want to {{ $listing->is_active ? 'deactivate' : 'activate' }} this listing?');"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $listing->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        >
                                            {{ $listing->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-title">No Customer Listings Yet</div>
                                    <p class="text-muted mb-3">
                                        Create a listing from an approved product with available inventory.
                                    </p>

                                    <a
                                        href="{{ route('admin.catalog.create') }}"
                                        class="btn btn-primary"
                                    >
                                        Create First Listing
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($listings->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-muted">
                    Showing {{ $listings->firstItem() }}–{{ $listings->lastItem() }}
                    of {{ $listings->total() }} listings
                </span>

                {{ $listings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .catalog-stat {
        height: 100%;
        padding: 20px;
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
    }

    .stat-label {
        color: #aeb9c8;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }

    .stat-value {
        color: #f1f4f8;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-description {
        color: #98a7ba;
        font-size: 0.8rem;
        margin-top: 8px;
    }

    .catalog-card {
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
        overflow: hidden;
    }

    .catalog-card .card-header,
    .catalog-card .card-footer {
        background: #111c2c;
        border-color: #2a394d;
        padding: 18px 20px;
        color: #edf2f8;
    }

    .catalog-card .card-header h5 {
        color: #edf2f8;
        font-weight: 600;
    }

    .catalog-table {
        color: #e8edf5;
        border-color: #2a394d;
    }

    .catalog-table thead th {
        background: #0b1422;
        color: #d6b574;
        border-color: #34445a;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 15px 16px;
        white-space: nowrap;
    }

    .catalog-table tbody td {
        background: transparent;
        color: #e8edf5;
        border-color: #26354a;
        padding: 16px;
    }

    .catalog-table tbody tr:hover td {
        background: #172437;
    }

    .product-name {
        color: #f1f4f8;
    }

    .price-tier-row {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
        font-size: 0.82rem;
        margin-bottom: 5px;
    }

    .price-tier-row:last-child {
        margin-bottom: 0;
    }

    .price-tier-row strong {
        color: #e2c58c;
        margin-left: 5px;
    }

    .catalog-status {
        display: inline-block;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .catalog-status.active {
        background: #164b39;
        color: #c6f4df;
        border: 1px solid #267453;
    }

    .catalog-status.inactive {
        background: #354257;
        color: #e1e8f2;
        border: 1px solid #52627a;
    }

    .btn-outline-gold {
        color: #e2c58c;
        border-color: #b99a5e;
    }

    .btn-outline-gold:hover {
        color: #101827;
        background: #d6b574;
        border-color: #d6b574;
    }

    .empty-title {
        color: #edf2f8;
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 8px;
    }

    .catalog-card .pagination {
        margin-bottom: 0;
    }

    .catalog-card .page-link {
        background: #0b1422;
        border-color: #34445a;
        color: #dce3ed;
    }

    .catalog-card .page-item.active .page-link {
        background: #b99a5e;
        border-color: #b99a5e;
        color: #101827;
    }

    .catalog-card .page-item.disabled .page-link {
        background: #172437;
        color: #7f8da1;
    }

    @media (max-width: 767px) {
        .catalog-table {
            min-width: 900px;
        }
    }
</style>
@endpush
