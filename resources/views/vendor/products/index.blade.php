
@extends('layouts.vendor')

@section('title', 'My Products')

@section('content')
<style>
    .vendor-page-title {
        margin-bottom: 6px;
        color: #f1f5f9;
        font-size: 25px;
        font-weight: 700;
    }

    .vendor-page-subtitle {
        color: #aab7c8;
        font-size: 14px;
    }

    .vendor-table-product {
        color: #f1f5f9;
        font-weight: 600;
    }

    .vendor-table-meta {
        margin-top: 4px;
        color: #aab7c8;
        font-size: 12px;
    }

    .vendor-product-status {
        display: inline-block;
        padding: 6px 10px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .vendor-product-status.pending {
        background: #3a2d16;
        border-color: #80612b;
        color: #fde68a;
    }

    .vendor-product-status.approved {
        background: #123326;
        border-color: #276749;
        color: #bbf7d0;
    }

    .vendor-product-status.rejected {
        background: #3b1d27;
        border-color: #783746;
        color: #fecaca;
    }

    .vendor-product-status.suspended {
        background: #263244;
        border-color: #526176;
        color: #d1d9e4;
    }

    .vendor-product-note {
        max-width: 230px;
        margin-top: 6px;
        color: #fcd58b;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .vendor-empty-state {
        padding: 55px 20px;
        text-align: center;
    }

    .vendor-empty-state h5 {
        color: #f1f5f9;
        font-weight: 700;
    }

    .vendor-empty-state p {
        color: #aab7c8;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="vendor-page-title">My Products</h2>
        <p class="vendor-page-subtitle mb-0">
            Manage your wholesale product listings and review status.
        </p>
    </div>

    <a href="{{ route('vendor.products.create') }}" class="btn btn-dark">
        + Add Product
    </a>
</div>

@if($products->count())
    <div class="vendor-card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-3">Product</th>
                        <th>Category</th>
                        <th>Price / Unit</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th class="text-end px-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($products as $product)
                        @php
                            $statusClass = [
                                'pending' => 'pending',
                                'approved' => 'approved',
                                'rejected' => 'rejected',
                                'suspended' => 'suspended',
                            ][$product->status] ?? 'suspended';
                        @endphp

                        <tr>
                            <td class="px-3">
                                <div class="vendor-table-product">
                                    {{ $product->name }}
                                </div>

                                <div class="vendor-table-meta">
                                    MOQ: {{ $product->minimum_order_quantity }}
                                    {{ $product->unit }}
                                </div>
                            </td>

                            <td>{{ $product->category }}</td>

                            <td>
                                ₹{{ number_format((float) $product->price_per_unit, 2) }}
                            </td>

                            <td>
                                {{ $product->available_quantity }}
                                {{ $product->unit }}
                            </td>

                            <td>
                                <span class="vendor-product-status {{ $statusClass }}">
                                    {{ ucfirst($product->status) }}
                                </span>

                                @if($product->admin_note)
                                    <div class="vendor-product-note">
                                        Admin note: {{ $product->admin_note }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-end px-3">
                                @if($product->status !== 'suspended')
                                    <a href="{{ route('vendor.products.edit', $product) }}"
                                       class="btn btn-sm btn-primary mb-1">
                                        Edit
                                    </a>
                                @endif

                                <form action="{{ route('vendor.products.destroy', $product) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger mb-1">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@else
    <div class="vendor-card vendor-empty-state">
        <h5>No products listed yet</h5>

        <p class="mb-3">
            Add your first wholesale product to submit it for admin review.
        </p>

        <a href="{{ route('vendor.products.create') }}" class="btn btn-dark">
            Add Your First Product
        </a>
    </div>
@endif
@endsection
