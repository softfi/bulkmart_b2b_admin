
@extends('layouts.admin')

@section('title', 'Inventory')
@section('active', 'inventory')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Inventory</h2>
            <p class="text-muted mb-0">
                Monitor stock received through vendor purchases and goods receipts.
            </p>
        </div>
    </div>

    {{-- Search and filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.inventory.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-6">
                        <label for="search" class="form-label">Search inventory</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Product, category or vendor"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="stock" class="form-label">Stock status</label>
                        <select name="stock" id="stock" class="form-select">
                            <option value="all" @selected(request('stock', 'all') === 'all')>
                                All stock
                            </option>
                            <option value="in_stock" @selected(request('stock') === 'in_stock')>
                                In stock
                            </option>
                            <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>
                                Out of stock
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Apply Filters
                        </button>

                        <a href="{{ route('admin.inventory.index') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Inventory table --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div>
                <h5 class="fw-bold mb-1">Stock Overview</h5>
                <small class="text-muted">
                    Products with recorded goods receipts
                </small>
            </div>

            <span class="badge bg-secondary">
                {{ $products->total() }} products
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th class="ps-3">Product</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th class="text-end">Total Received</th>
                        <th class="text-end">Remaining Stock</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)

                        @php
                            $received = (float) ($product->total_received_quantity ?? 0);
                            $remaining = (float) ($product->total_remaining_quantity ?? 0);
                        @endphp

                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">
                                    {{ $product->name }}
                                </div>

                                <small class="text-muted">
                                    Unit: {{ $product->unit }}
                                </small>

                                @if($product->trashed())
                                    <div class="mt-1">
                                        <span class="badge bg-secondary">
                                            Archived product
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $product->vendorProfile?->business_name ?? '—' }}
                            </td>

                            <td>
                                {{ $product->category ?: '—' }}
                            </td>

                            <td class="text-end">
                                <span class="fw-medium">
                                    {{ number_format($received, 3) }}
                                </span>
                                <small class="text-muted">
                                    {{ $product->unit }}
                                </small>
                            </td>

                            <td class="text-end">
                                <span class="fw-bold">
                                    {{ number_format($remaining, 3) }}
                                </span>
                                <small class="text-muted">
                                    {{ $product->unit }}
                                </small>
                            </td>

                            <td>
                                @if($remaining > 0)
                                    <span class="badge bg-success">
                                        In Stock
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Out of Stock
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                <a
                                    href="{{ route('admin.inventory.show', $product->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View Batches
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="fw-semibold mb-1">
                                    No inventory found
                                </div>
                                <div class="text-muted small">
                                    Inventory will appear here after goods receipts are recorded.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer border-0 py-3">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
