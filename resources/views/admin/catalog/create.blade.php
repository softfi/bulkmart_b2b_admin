
@extends('layouts.admin')

@section('active', 'catalog')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold mb-2" style="color:#d6b574; letter-spacing:.12em;">
                Customer catalog
            </div>
            <h3 class="mb-1">Create customer listing</h3>
            <p class="mb-0 text-muted">
                Configure the customer-facing details and quantity-based selling prices.
            </p>
        </div>

        <a href="{{ route('admin.catalog.index') }}"
           class="btn btn-outline-secondary">
            Back to catalog
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 mb-4" role="alert">
            <div class="fw-semibold mb-2">Please review the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($products->isEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="mb-2">No eligible products available</h5>
                <p class="text-muted mb-3">
                    A product must be approved, have received inventory remaining,
                    and not already have a customer listing.
                </p>
                <a href="{{ route('admin.products.index') }}"
                   class="btn btn-outline-primary">
                    Review vendor products
                </a>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.catalog.store') }}"
          enctype="multipart/form-data">
        @csrf

        @include('admin.catalog._form', [
            'listing' => null,
            'products' => $products,
            'tiers' => old('price_tiers', [
                [
                    'minimum_quantity' => '1.000',
                    'maximum_quantity' => '1.000',
                    'unit_price' => ''
                ],
                [
                    'minimum_quantity' => '2.000',
                    'maximum_quantity' => '',
                    'unit_price' => ''
                ],
            ]),
        ])

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
            <a href="{{ route('admin.catalog.index') }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>

            <button type="submit"
                    class="btn btn-primary"
                    @disabled($products->isEmpty())>
                Create listing
            </button>
        </div>
    </form>

</div>
@endsection
