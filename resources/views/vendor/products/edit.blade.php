
@extends('layouts.vendor')

@section('title', 'Edit Product')

@section('content')
<style>
    .vendor-form-heading {
        margin-bottom: 22px;
    }

    .vendor-form-heading h2 {
        margin-top: 15px;
        margin-bottom: 7px;
        color: #f1f5f9;
        font-size: 25px;
        font-weight: 700;
    }

    .vendor-form-heading p {
        color: #aab7c8;
        font-size: 14px;
    }

    .vendor-back-link {
        color: #d6b574;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .vendor-back-link:hover {
        color: #e5c98f;
        text-decoration: underline;
    }

    .vendor-form-section-title {
        margin-bottom: 22px;
        padding-bottom: 14px;
        border-bottom: 1px solid #26364a;
        color: #f1f5f9;
        font-size: 16px;
        font-weight: 700;
    }

    .vendor-review-notice {
        margin-bottom: 20px;
        padding: 13px 15px;
        background: #142f45;
        border: 1px solid #285777;
        border-radius: 8px;
        color: #bae6fd;
        font-size: 13px;
    }
</style>

<div class="vendor-form-heading">
    <a href="{{ route('vendor.products.index') }}" class="vendor-back-link">
        ← Back to products
    </a>

    <h2>Edit Product</h2>

    <p class="mb-0">
        Update your wholesale product information.
    </p>
</div>

<div class="vendor-review-notice">
    <strong>Review notice:</strong>
    Changes to your product will be submitted for admin review again.
</div>

<div class="vendor-card p-4">
    <h5 class="vendor-form-section-title">Product Information</h5>

    <form action="{{ route('vendor.products.update', $product) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('vendor.products._form', ['product' => $product])

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-dark px-4">
                Save and Resubmit for Review
            </button>

            <a href="{{ route('vendor.products.index') }}"
               class="btn btn-outline-secondary px-4">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
