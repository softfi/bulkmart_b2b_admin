
@php
    $variantOptions = collect();

    if ($listing) {
        $variantOptions = $listing->product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'label' => collect($variant->attributes ?? [])
                    ->map(fn ($value, $key) => $key . ': ' . $value)
                    ->implode(', ') ?: ('Variant #' . $variant->id),
            ];
        });
    } else {
        $variantOptions = $products->flatMap(function ($product) {
            return $product->variants->map(function ($variant) use ($product) {
                return [
                    'id' => $variant->id,
                    'product_id' => $product->id,
                    'label' => $product->name . ' — ' .
                        (collect($variant->attributes ?? [])
                            ->map(fn ($value, $key) => $key . ': ' . $value)
                            ->implode(', ') ?: ('Variant #' . $variant->id)),
                ];
            });
        });
    }

    $tiers = collect($tiers ?? [])->values();
@endphp

<div class="card admin-card mb-4">
    <div class="card-body p-4">
        <h5 class="mb-4">Product Details</h5>

        @if(!$listing)
            <div class="mb-3">
                <label class="form-label">Approved Product *</label>
                <select name="product_id" id="catalog-product-id" class="form-select" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            @selected(old('product_id') == $product->id)>
                            {{ $product->name }}
                            — {{ $product->vendorProfile->business_name }}
                            (Stock: {{ number_format($product->stock_remaining, 3) }} {{ $product->unit }})
                        </option>
                    @endforeach
                </select>

                @error('product_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                @if($products->isEmpty())
                    <div class="text-warning small mt-2">
                        No approved products with available inventory are eligible for listing.
                    </div>
                @endif
            </div>
        @else
            <div class="mb-3">
                <label class="form-label">Source Product</label>
                <input class="form-control" value="{{ $listing->product->name }}" disabled>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Customer-facing Product Name</label>
            <input type="text" name="display_name" class="form-control"
                   maxlength="150"
                   value="{{ old('display_name', $listing?->display_name) }}"
                   placeholder="Leave blank to use the original product name">
            @error('display_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Customer Description</label>
            <textarea name="customer_description" class="form-control"
                      rows="4" maxlength="5000"
                      placeholder="Enter the product description shown to customers">{{ old('customer_description', $listing?->customer_description) }}</textarea>
            @error('customer_description')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Minimum Order Quantity *</label>
            <input type="number" name="minimum_order_quantity"
                   id="minimum_order_quantity" class="form-control"
                   min="0.001" max="999999999" step="0.001" required
                   value="{{ old('minimum_order_quantity', $listing?->minimum_order_quantity ?? '1.000') }}">
            <div class="form-text">
                Product-wide pricing starts at this quantity. Variant pricing starts at the variant's MOQ.
            </div>
            @error('minimum_order_quantity')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
    <label class="form-label">Customer Product Image</label>

    @php
        $currentImageUrl = $listing && $listing->image_path
            ? route('admin.catalog.image', $listing)
            : null;
    @endphp

    <div class="mb-3">
        <div id="currentImageContainer"
             class="{{ $currentImageUrl ? '' : 'd-none' }}">
            <div class="small text-secondary mb-2">Current customer listing image</div>

            <img
                id="currentImagePreview"
                src="{{ $currentImageUrl ?? '' }}"
                alt="Current customer listing image"
                style="width:180px;height:150px;object-fit:contain;background:#fff;border:1px solid #334155;border-radius:10px;padding:6px;"
            >
        </div>

        <div id="noCurrentImage"
             class="small text-secondary {{ $currentImageUrl ? 'd-none' : '' }}">
            No customer-specific image uploaded yet.
        </div>
    </div>

    <label for="listingImage" class="form-label">
        {{ $listing && $listing->image_path ? 'Replace Image' : 'Upload Image' }}
    </label>

    <input
        type="file"
        name="image"
        id="listingImage"
        class="form-control"
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
    >

    <div class="form-text">
        JPG, PNG or WebP. Maximum file size: 5 MB.
        Leave empty to keep the current image.
    </div>

    @error('image')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror

    <div id="newImageContainer" class="mt-3 d-none">
        <div class="small text-secondary mb-2">New image preview</div>
        <img
            id="newImagePreview"
            alt="New customer listing image preview"
            style="width:180px;height:150px;object-fit:contain;background:#fff;border:1px solid #334155;border-radius:10px;padding:6px;"
        >
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('listingImage');
    const previewContainer = document.getElementById('newImageContainer');
    const preview = document.getElementById('newImagePreview');

    if (!input || !previewContainer || !preview) {
        return;
    }

    let previewUrl = null;

    input.addEventListener('change', function () {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = this.files && this.files[0];

        if (!file) {
            previewContainer.classList.add('d-none');
            preview.removeAttribute('src');
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            this.value = '';
            previewContainer.classList.add('d-none');
            alert('Please select a JPG, PNG or WebP image.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.value = '';
            previewContainer.classList.add('d-none');
            alert('Image size must not exceed 5 MB.');
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        previewContainer.classList.remove('d-none');
    });
});
</script>
    </div>
</div>

<div class="card admin-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="mb-1">Customer Quantity Pricing</h5>
                <p class="text-muted small mb-0">
                    Set quantity-based selling prices for the whole product or individual variants.
                </p>
            </div>
            <button type="button" class="btn btn-outline-gold btn-sm" id="add-price-tier">
                + Add Price Tier
            </button>
        </div>

        <div class="alert alert-secondary small mb-3">
            Each pricing group must have consecutive tiers without gaps or overlaps.
            Leave the final maximum quantity blank for unlimited quantity.
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle" id="price-tier-table">
                <thead>
                    <tr>
                        <th>Applies To</th>
                        <th>Minimum Quantity</th>
                        <th>Maximum Quantity</th>
                        <th>Unit Selling Price (₹)</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tiers as $index => $tier)
                        <tr>
                            <td>
                                <select class="form-select tier-variant"
                                        name="price_tiers[{{ $index }}][product_variant_id]">
                                    <option value="">Entire product</option>
                                    @foreach($variantOptions as $variant)
                                        <option value="{{ $variant['id'] }}"
                                            @selected((string)($tier['product_variant_id'] ?? '') === (string)$variant['id'])>
                                            {{ $variant['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001"
                                       max="999999999" class="form-control"
                                       name="price_tiers[{{ $index }}][minimum_quantity]"
                                       value="{{ $tier['minimum_quantity'] ?? '' }}"
                                       placeholder="Minimum" required>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001"
                                       max="999999999" class="form-control"
                                       name="price_tiers[{{ $index }}][maximum_quantity]"
                                       value="{{ $tier['maximum_quantity'] ?? '' }}"
                                       placeholder="Unlimited">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01"
                                       max="9999999999" class="form-control"
                                       name="price_tiers[{{ $index }}][unit_price]"
                                       value="{{ $tier['unit_price'] ?? '' }}"
                                       placeholder="Selling price" required>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm remove-tier">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @error('price_tiers')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror

        @foreach($errors->get('price_tiers.*') as $messages)
            @foreach($messages as $message)
                <div class="text-danger small mt-1">{{ $message }}</div>
            @endforeach
        @endforeach

        <div class="mt-3 small text-muted">
            Choose "Entire product" for default pricing. A matching variant-specific tier takes priority over the product-wide tier.
        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-card {
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
        color: #e8edf5;
    }

    .admin-card h5 {
        color: #f1f4f8;
        font-weight: 600;
    }

    .admin-card .form-label {
        color: #dce3ed;
        font-weight: 500;
    }

    .admin-card .form-control,
    .admin-card .form-select {
        background-color: #0b1422;
        border: 1px solid #34445a;
        color: #edf2f8;
        min-height: 42px;
    }

    .admin-card textarea.form-control {
        min-height: 110px;
    }

    .admin-card .form-control::placeholder {
        color: #9ba9bb;
        opacity: 1;
    }

    .admin-card .form-control:focus,
    .admin-card .form-select:focus {
        background-color: #0b1422;
        color: #fff;
        border-color: #d6b574;
        box-shadow: 0 0 0 0.2rem rgba(214, 181, 116, 0.14);
    }

    .admin-card .form-control:disabled {
        background-color: #172437;
        color: #aeb9c8;
    }

    .admin-card .form-text,
    .admin-card .text-muted {
        color: #aeb9c8 !important;
    }

    .admin-card .admin-table {
        color: #e8edf5;
        border-color: #2a394d;
        margin-bottom: 0;
    }

    .admin-card .admin-table thead th {
        color: #d6b574;
        background: #0c1625;
        border-bottom: 1px solid #34445a;
        white-space: nowrap;
        font-size: 0.85rem;
    }

    .admin-card .admin-table td {
        background: transparent;
        border-color: #2a394d;
        min-width: 150px;
    }

    .admin-card .admin-table td:last-child {
        min-width: 95px;
    }

    .admin-card .alert-secondary {
        background: #19283b;
        border: 1px solid #35475e;
        color: #d4deea;
    }

    .btn-outline-gold {
        color: #e2c58c;
        border: 1px solid #b99a5e;
        background: transparent;
    }

    .btn-outline-gold:hover {
        color: #101827;
        background: #d6b574;
        border-color: #d6b574;
    }

    .admin-card input[type="file"]::file-selector-button {
        background: #25364c;
        color: #edf2f8;
        border: 0;
        padding: 0.5rem 0.8rem;
        margin: -0.375rem 0.75rem -0.375rem -0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#price-tier-table tbody');
    const addButton = document.getElementById('add-price-tier');
    const productSelect = document.getElementById('catalog-product-id');
    const variantOptions = @json($variantOptions->values());

    if (!tbody || !addButton) return;

    function renumberRows() {
        [...tbody.querySelectorAll('tr')].forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (input) {
                input.name = `price_tiers[${index}][${input.dataset.field}]`;
            });
        });
    }

    function refreshVariantOptions(select) {
        const selected = select.value;
        const productId = productSelect ? productSelect.value : '';

        select.innerHTML = '<option value="">Entire product</option>';

        variantOptions.forEach(function (variant) {
            if (productId && variant.product_id &&
                String(variant.product_id) !== String(productId)) {
                return;
            }

            const option = document.createElement('option');
            option.value = variant.id;
            option.textContent = variant.label;
            select.appendChild(option);
        });

        if ([...select.options].some(option => option.value === selected)) {
            select.value = selected;
        }
    }

    function makeInput(field, step, min, placeholder, required) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'form-control';
        input.dataset.field = field;
        input.step = step;
        input.min = min;
        input.max = field === 'unit_price' ? '9999999999' : '999999999';
        input.placeholder = placeholder;
        input.required = required;
        return input;
    }

    function addTier() {
        if (tbody.children.length >= 30) {
            alert('A maximum of 30 price tiers is allowed.');
            return;
        }

        const row = document.createElement('tr');
        const variantCell = document.createElement('td');
        const minCell = document.createElement('td');
        const maxCell = document.createElement('td');
        const priceCell = document.createElement('td');
        const actionCell = document.createElement('td');

        const variantSelect = document.createElement('select');
        variantSelect.className = 'form-select tier-variant';
        variantSelect.dataset.field = 'product_variant_id';

        const minInput = makeInput('minimum_quantity', '0.001', '0.001', 'Minimum', true);
        const maxInput = makeInput('maximum_quantity', '0.001', '0.001', 'Unlimited', false);
        const priceInput = makeInput('unit_price', '0.01', '0.01', 'Selling price', true);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-outline-danger btn-sm remove-tier';
        remove.textContent = 'Remove';

        variantCell.appendChild(variantSelect);
        minCell.appendChild(minInput);
        maxCell.appendChild(maxInput);
        priceCell.appendChild(priceInput);
        actionCell.className = 'text-end';
        actionCell.appendChild(remove);

        row.append(variantCell, minCell, maxCell, priceCell, actionCell);
        tbody.appendChild(row);

        refreshVariantOptions(variantSelect);
        renumberRows();
        minInput.focus();
    }

    addButton.addEventListener('click', addTier);

    tbody.addEventListener('click', function (event) {
        const remove = event.target.closest('.remove-tier');
        if (!remove) return;

        if (tbody.children.length <= 1) {
            alert('At least one price tier is required.');
            return;
        }

        remove.closest('tr').remove();
        renumberRows();
    });

    if (productSelect) {
        productSelect.addEventListener('change', function () {
            tbody.querySelectorAll('.tier-variant').forEach(refreshVariantOptions);
        });
    }

    tbody.querySelectorAll('.tier-variant').forEach(refreshVariantOptions);
    renumberRows();
});
</script>
@endpush
