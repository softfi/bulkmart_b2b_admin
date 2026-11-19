
@extends('layouts.admin')

@section('active', 'catalog')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Customer Listing</h3>
        <span class="badge {{ $listing->is_active ? 'bg-success' : 'bg-secondary' }}">
            {{ $listing->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        Source product: <strong>{{ $listing->product->name }}</strong> |
        Current inventory:
        <strong>{{ number_format($stockRemaining, 3) }} {{ $listing->product->unit }}</strong>
    </div>

    <form method="POST"
          action="{{ route('admin.catalog.update', $listing) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.catalog._form', [
            'listing' => $listing,
            'products' => collect(),
            'tiers' => old('price_tiers', $listing->priceTiers->map(fn($tier) => [
                'product_variant_id' => $tier->product_variant_id,
                'minimum_quantity' => $tier->minimum_quantity,
                'maximum_quantity' => $tier->maximum_quantity,
                'unit_price' => $tier->unit_price,
            ])->toArray()),
        ])

        <button class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.catalog.index') }}" class="btn btn-outline-secondary">Back</a>
    </form>
</div>
@endsection
