@extends('layouts.admin')

@section('title', 'Create Vendor Purchase')
@section('active', 'purchases')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.purchases.index') }}" class="text-decoration-none">
        ← Back to purchases
    </a>
    <h2 class="fw-bold mt-3">Create Vendor Purchase</h2>
    <p class="text-muted">
        Select an approved vendor product, choose its variant if applicable,
        and record the purchase.
    </p>
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

@php
    $variantData = [];

    foreach ($vendors as $vendor) {
        foreach ($vendor->products as $product) {
            $variantData[$product->id] = $product->variants
                ->where('is_active', true)
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'label' => collect($variant->attributes ?? [])
                            ->map(fn ($value, $key) =>
                                ucfirst(str_replace('_', ' ', $key)) . ': ' . $value
                            )
                            ->implode(' / '),
                        'sku' => $variant->sku,
                        'price' => $variant->price_per_unit,
                        'quantity' => $variant->available_quantity,
                        'minimum_order_quantity' => $variant->minimum_order_quantity,
                    ];
                })
                ->values()
                ->all();
        }
    }
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.purchases.store') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Approved vendor product *</label>

                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Select product</option>

                    @foreach($vendors as $vendor)
                        <optgroup label="{{ $vendor->business_name }}">
                            @foreach($vendor->products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    data-vendor="{{ $vendor->business_name }}"
                                    data-category="{{ $product->productCategory?->name ?? $product->category ?? 'Uncategorised' }}"
                                    data-price="{{ $product->price_per_unit }}"
                                    data-quantity="{{ $product->available_quantity }}"
                                    data-minimum="{{ $product->minimum_order_quantity }}"
                                    data-unit="{{ $product->unit }}"
                                    @selected((string) $selectedProductId === (string) $product->id)
                                >
                                    {{ $product->name }} —
                                    ₹{{ number_format((float) $product->price_per_unit, 2) }}/{{ $product->unit }}
                                    (Available: {{ $product->available_quantity }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <small class="text-muted">
                    Only approved products from approved vendors are listed.
                </small>
            </div>

            <div id="product_details" class="product-details mb-4" hidden>
                <div class="product-details-heading">Selected product details</div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <small>Vendor</small>
                        <div id="detail_vendor">—</div>
                    </div>

                    <div class="col-md-4">
                        <small>Category</small>
                        <div id="detail_category">—</div>
                    </div>

                    <div class="col-md-4">
                        <small>Unit</small>
                        <div id="detail_unit">—</div>
                    </div>

                    <div class="col-md-4">
                        <small>Available quantity</small>
                        <div id="detail_quantity">—</div>
                    </div>

                    <div class="col-md-4">
                        <small>Minimum order quantity</small>
                        <div id="detail_minimum">—</div>
                    </div>

                    <div class="col-md-4">
                        <small>Indicative unit price</small>
                        <div id="detail_price">—</div>
                    </div>
                </div>
            </div>

            <div class="mb-4" id="variant_wrapper" hidden>
                <label class="form-label fw-semibold">Product variant *</label>

                <select
                    name="product_variant_id"
                    id="product_variant_id"
                    class="form-select"
                    disabled
                >
                    <option value="">Select variant</option>
                </select>

                <small class="text-muted" id="variant_help">
                    Select a variant before entering the purchase quantity.
                </small>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Purchase quantity *</label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        step="0.001"
                        min="0.001"
                        value="{{ old('quantity') }}"
                        class="form-control"
                        required
                    >
                    <small class="text-muted" id="quantity_help"></small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Agreed unit price (₹) *</label>
                    <input
                        type="number"
                        name="unit_price"
                        id="unit_price"
                        step="0.01"
                        min="0.01"
                        value="{{ old('unit_price') }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purchase date *</label>
                    <input
                        type="date"
                        name="purchase_date"
                        value="{{ old('purchase_date', now()->toDateString()) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Expected delivery date</label>
                    <input
                        type="date"
                        name="expected_delivery_date"
                        value="{{ old('expected_delivery_date') }}"
                        class="form-control"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea
                        name="notes"
                        rows="3"
                        maxlength="5000"
                        class="form-control"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-3">
                The purchase total is calculated on the server. Inventory is updated
                separately after goods are received and verified.
            </div>

            <button class="btn btn-dark">Create Purchase</button>

            <a href="{{ route('admin.purchases.index') }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>
        </form>
    </div>
</div>

<style>
.product-details {
    padding: 18px;
    background: #111c2d;
    border: 1px solid #34445b;
    border-radius: 10px;
    color: #edf2f8;
}

.product-details-heading {
    margin-bottom: 15px;
    color: #dfc18a;
    font-size: 13px;
    font-weight: 750;
}

.product-details small {
    display: block;
    margin-bottom: 5px;
    color: #9eacc0;
    font-size: 11px;
}

.product-details .row > div > div {
    font-size: 13px;
    font-weight: 650;
    overflow-wrap: anywhere;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantsByProduct = @json($variantData);

    const productSelect = document.getElementById('product_id');
    const variantWrapper = document.getElementById('variant_wrapper');
    const variantSelect = document.getElementById('product_variant_id');
    const variantHelp = document.getElementById('variant_help');
    const quantityInput = document.getElementById('quantity');
    const quantityHelp = document.getElementById('quantity_help');
    const priceInput = document.getElementById('unit_price');
    const productDetails = document.getElementById('product_details');

    const initialVariantId = @json($selectedVariantId);

    function setDetail(id, value) {
        document.getElementById(id).textContent = value || '—';
    }

    function resetVariant() {
        variantSelect.innerHTML = '<option value="">Select variant</option>';
        variantSelect.disabled = true;
        variantSelect.required = false;
        variantWrapper.hidden = true;

        variantHelp.textContent =
            'Select a variant before entering the purchase quantity.';
    }

    function updateProduct() {
        const selectedProduct = productSelect.selectedOptions[0];
        const productId = productSelect.value;

        resetVariant();

        if (!productId || !selectedProduct || !selectedProduct.value) {
            productDetails.hidden = true;
            quantityInput.removeAttribute('max');
            quantityHelp.textContent = '';
            return;
        }

        productDetails.hidden = false;

        setDetail('detail_vendor', selectedProduct.dataset.vendor);
        setDetail('detail_category', selectedProduct.dataset.category);
        setDetail('detail_unit', selectedProduct.dataset.unit);
        setDetail(
            'detail_quantity',
            selectedProduct.dataset.quantity + ' ' + selectedProduct.dataset.unit
        );
        setDetail(
            'detail_minimum',
            selectedProduct.dataset.minimum + ' ' + selectedProduct.dataset.unit
        );
        setDetail(
            'detail_price',
            '₹' + Number(selectedProduct.dataset.price).toFixed(2)
        );

        const variants = variantsByProduct[productId] || [];

        if (variants.length > 0) {
            variantWrapper.hidden = false;
            variantSelect.disabled = false;
            variantSelect.required = true;

            variants.forEach(function (variant) {
                const option = document.createElement('option');
                option.value = variant.id;

                const label = variant.label || 'Variant #' + variant.id;
                const sku = variant.sku ? ' — SKU: ' + variant.sku : '';

                option.textContent =
                    label + sku +
                    ' — ₹' + Number(variant.price).toFixed(2) +
                    ' (Available: ' + variant.quantity + ')';

                option.dataset.price = variant.price;
                option.dataset.quantity = variant.quantity;
                option.dataset.minimum = variant.minimum_order_quantity;

                if (String(initialVariantId) === String(variant.id)) {
                    option.selected = true;
                }

                variantSelect.appendChild(option);
            });

            variantHelp.textContent =
                'Choose the exact variant you want to purchase.';
        } else {
            quantityInput.max = selectedProduct.dataset.quantity;
            quantityHelp.textContent =
                'Available vendor stock: ' + selectedProduct.dataset.quantity +
                ' ' + selectedProduct.dataset.unit;

            if (!priceInput.value) {
                priceInput.value = Number(selectedProduct.dataset.price).toFixed(2);
            }
        }

        updateVariant();
    }

    function updateVariant() {
        const selectedProduct = productSelect.selectedOptions[0];
        const selectedVariant = variantSelect.selectedOptions[0];

        if (!selectedProduct || !productSelect.value) {
            return;
        }

        if (variantWrapper.hidden) {
            quantityInput.max = selectedProduct.dataset.quantity;

            quantityHelp.textContent =
                'Available vendor stock: ' + selectedProduct.dataset.quantity +
                ' ' + selectedProduct.dataset.unit;

            return;
        }

        if (!variantSelect.value || !selectedVariant) {
            quantityInput.removeAttribute('max');
            quantityHelp.textContent = 'Select a variant first.';
            return;
        }

        quantityInput.max = selectedVariant.dataset.quantity;

        quantityHelp.textContent =
            'Available stock for this variant: ' +
            selectedVariant.dataset.quantity + ' ' +
            selectedProduct.dataset.unit +
            '. Minimum order: ' +
            selectedVariant.dataset.minimum;

        priceInput.value =
            Number(selectedVariant.dataset.price).toFixed(2);

        setDetail(
            'detail_quantity',
            selectedVariant.dataset.quantity + ' ' + selectedProduct.dataset.unit
        );

        setDetail(
            'detail_minimum',
            selectedVariant.dataset.minimum + ' ' + selectedProduct.dataset.unit
        );

        setDetail(
            'detail_price',
            '₹' + Number(selectedVariant.dataset.price).toFixed(2)
        );
    }

    productSelect.addEventListener('change', function () {
        variantSelect.value = '';
        quantityInput.value = '';
        priceInput.value = '';
        updateProduct();
    });

    variantSelect.addEventListener('change', function () {
        quantityInput.value = '';
        updateVariant();
    });

    updateProduct();
});
</script>
@endpush