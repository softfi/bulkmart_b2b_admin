@extends('layouts.vendor')

@section('title', 'Add Product')

@section('active', 'create-product')

@section('content')
<div class="mb-4">
    <a href="{{ route('vendor.products.index') }}" class="text-decoration-none">
        ← Back to products
    </a>
    <h2 class="fw-bold mt-3 mb-1">Add New Product</h2>
    <p class="muted-text">Enter accurate product and wholesale pricing details.</p>
</div>

<div class="vendor-card p-4">
    <form action="{{ route('vendor.products.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        @include('vendor.products._form', ['product' => null])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-dark">
                Submit for Review
            </button>
            <a href="{{ route('vendor.products.index') }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection