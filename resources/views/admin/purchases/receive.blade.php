
@extends('layouts.admin')

@section('title', 'Receive Vendor Purchase')
@section('active', 'purchases')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Receive Vendor Purchase</h3>
            <p class="text-muted mb-0">{{ $purchase->purchase_number }}</p>
        </div>

        <a href="{{ route('admin.purchases.show', $purchase) }}"
           class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Vendor</strong>
                    <p>{{ $purchase->vendorProfile->business_name ?? 'Unavailable' }}</p>
                </div>

                <div class="col-md-4">
                    <strong>Product</strong>
                    <p>{{ $purchase->product->name ?? 'Unavailable' }}</p>
                </div>

                <div class="col-md-4">
                    <strong>Variant</strong>
                    @if($purchase->productVariant)
                        <p class="mb-0">
                            {{ collect($purchase->productVariant->attributes ?? [])
                                ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)) . ': ' . $value)
                                ->implode(' / ') ?: 'Variant #' . $purchase->productVariant->id }}
                        </p>

                        @if($purchase->productVariant->sku)
                            <small class="text-muted">
                                SKU: {{ $purchase->productVariant->sku }}
                            </small>
                        @endif
                    @else
                        <p class="text-muted">Product-level purchase</p>
                    @endif
                </div>

                <div class="col-md-4">
                    <strong>Ordered quantity</strong>
                    <p>
                        {{ number_format((float) $purchase->quantity, 3) }}
                        {{ $purchase->product->unit ?? '' }}
                    </p>
                </div>

                <div class="col-md-4">
                    <strong>Outstanding quantity</strong>
                    <p class="fw-bold text-primary">
                        {{ number_format($remaining, 3) }}
                        {{ $purchase->product->unit ?? '' }}
                    </p>
                </div>

                <div class="col-md-4">
                    <strong>Agreed unit cost</strong>
                    <p>₹{{ number_format((float) $purchase->unit_price, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Record Goods Received</h5>

            <form method="POST"
                  action="{{ route('admin.purchases.receive.store', $purchase) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Quantity actually received *</label>

                    <input
                        type="number"
                        name="quantity_received"
                        class="form-control"
                        min="0.001"
                        max="{{ $remaining }}"
                        step="0.001"
                        value="{{ old('quantity_received') }}"
                        required
                    >

                    <small class="text-muted">
                        Maximum outstanding:
                        {{ number_format($remaining, 3) }}
                        {{ $purchase->product->unit ?? '' }}
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Received date and time *</label>

                    <input
                        type="datetime-local"
                        name="received_at"
                        class="form-control"
                        value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Receiving notes</label>

                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                        maxlength="5000"
                    >{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Confirm Goods Receipt
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
