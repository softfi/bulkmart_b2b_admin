@php
    $units = ['kg', 'g', 'litre', 'ml', 'piece', 'box', 'bag', 'ton'];

    $selectedCategoryId = old(
        'category_id',
        $product?->category_id
    );

    $selectedCategory = $categories->firstWhere('id', (int) $selectedCategoryId);

    $productAttributeValues = old(
        'attribute_values',
        $product?->attribute_values ?? []
    );

    $savedVariants = old('variants');

    if ($savedVariants === null) {
        $savedVariants = $product
            ? $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'attributes' => collect($variant->attributes ?? [])
                        ->mapWithKeys(function ($value, $key) {
                            return [$key => $value];
                        })->all(),
                    'price_per_unit' => $variant->price_per_unit,
                    'available_quantity' => $variant->available_quantity,
                    'minimum_order_quantity' => $variant->minimum_order_quantity,
                ];
            })->values()->all()
            : [];
    }
@endphp

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label">Product Name *</label>
        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $product?->name) }}"
            maxlength="150"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Category *</label>
        <select
            name="category_id"
            id="product-category"
            class="form-select @error('category_id') is-invalid @enderror"
            required
        >
            <option value="">Select category</option>

            @foreach($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected((int) $selectedCategoryId === $category->id)
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea
            name="description"
            rows="4"
            maxlength="5000"
            class="form-control @error('description') is-invalid @enderror"
        >{{ old('description', $product?->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Price per Unit (₹) *</label>
        <input
            type="number"
            name="price_per_unit"
            step="0.01"
            min="0.01"
            class="form-control @error('price_per_unit') is-invalid @enderror"
            value="{{ old('price_per_unit', $product?->price_per_unit) }}"
            required
        >
        @error('price_per_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Unit *</label>
        <select
            name="unit"
            class="form-select @error('unit') is-invalid @enderror"
            required
        >
            <option value="">Select unit</option>
            @foreach($units as $unit)
                <option
                    value="{{ $unit }}"
                    @selected(old('unit', $product?->unit) === $unit)
                >
                    {{ strtoupper($unit) }}
                </option>
            @endforeach
        </select>
        @error('unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Available Quantity *</label>
        <input
            type="number"
            name="available_quantity"
            step="0.001"
            min="0.001"
            class="form-control @error('available_quantity') is-invalid @enderror"
            value="{{ old('available_quantity', $product?->available_quantity) }}"
            required
        >
        @error('available_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Minimum Order Quantity (MOQ) *</label>
        <input
            type="number"
            name="minimum_order_quantity"
            step="0.001"
            min="0.001"
            class="form-control @error('minimum_order_quantity') is-invalid @enderror"
            value="{{ old('minimum_order_quantity', $product?->minimum_order_quantity) }}"
            required
        >
        @error('minimum_order_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Product Image</label>
        <input
            type="file"
            name="image"
            accept=".jpg,.jpeg,.png,.webp"
            class="form-control @error('image') is-invalid @enderror"
        >
        <div class="form-text">JPG, PNG or WebP. Maximum 5 MB.</div>

        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($product?->image_path)
            <p class="small muted-text mt-2 mb-0">
                An image is already uploaded. Choose a new image only if you want to replace it.
            </p>
        @endif
    </div>

    <div class="col-12">
        <hr>
        <h5 class="fw-bold">Category Attributes</h5>
        <p class="text-muted small">
            Complete the additional information required for the selected category.
        </p>

        <div id="category-attributes">
            <p class="text-muted small mb-0">
                Select a category to view its attributes.
            </p>
        </div>
    </div>

    <div class="col-12" id="variants-section">
        <hr>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1">Product Variants</h5>
                <p class="text-muted small mb-0">
                    Add variants such as size, colour, pack size or model.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-outline-dark btn-sm"
                id="add-variant"
            >
                + Add Variant
            </button>
        </div>

        <div id="variants-container"></div>

        @error('variants')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>

</div>

@php
    $categoryAttributeData = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'attributes' => $category->attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'input_type' => $attribute->input_type,
                    'options' => $attribute->options ?? [],
                    'is_required' => (bool) $attribute->is_required,
                    'is_variant_attribute' => (bool) $attribute->is_variant_attribute,
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categories = @json($categoryAttributeData);
    const initialValues = @json($productAttributeValues);
    const initialVariants = @json($savedVariants);

    const categorySelect = document.getElementById('product-category');
    const attributeContainer = document.getElementById('category-attributes');
    const variantContainer = document.getElementById('variants-container');
    const addVariantButton = document.getElementById('add-variant');

    if (!categorySelect || !attributeContainer || !variantContainer) {
        return;
    }

    let variantIndex = 0;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }

    function selectedCategory() {
        return categories.find(function (category) {
            return String(category.id) === String(categorySelect.value);
        });
    }

    function attributeInput(attribute, value, name) {
        const required = attribute.is_required ? 'required' : '';
        const safeValue = escapeHtml(value);

        if (attribute.input_type === 'select') {
            let options = '<option value="">Select ' +
                escapeHtml(attribute.name) + '</option>';

            (attribute.options || []).forEach(function (option) {
                const selected = String(option) === String(value)
                    ? 'selected'
                    : '';

                options += '<option value="' + escapeHtml(option) + '" ' +
                    selected + '>' + escapeHtml(option) + '</option>';
            });

            return '<select name="' + name + '" class="form-select" ' +
                required + '>' + options + '</select>';
        }

        const type = attribute.input_type === 'number' ? 'number' : 'text';

        return '<input type="' + type + '" name="' + name +
            '" class="form-control" value="' + safeValue + '" ' +
            (type === 'number' ? 'step="any"' : '') +
            ' maxlength="1000" ' + required + '>';
    }

    function renderCategoryAttributes() {
        const category = selectedCategory();

        attributeContainer.innerHTML = '';

        if (!category) {
            attributeContainer.innerHTML =
                '<p class="text-muted small">Select a category to view its attributes.</p>';

            renderVariants();
            return;
        }

        const attributes = category.attributes.filter(function (attribute) {
            return !attribute.is_variant_attribute;
        });

        if (attributes.length === 0) {
            attributeContainer.innerHTML =
                '<p class="text-muted small">No additional product attributes are configured for this category.</p>';
        }

        attributes.forEach(function (attribute) {
            const value = initialValues[attribute.slug] ??
                initialValues[String(attribute.id)] ?? '';

            const wrapper = document.createElement('div');
            wrapper.className = 'mb-3';

            wrapper.innerHTML =
                '<label class="form-label">' +
                escapeHtml(attribute.name) +
                (attribute.is_required ? ' *' : '') +
                '</label>' +
                attributeInput(
                    attribute,
                    value,
                    'attribute_values[' + attribute.id + ']'
                );

            attributeContainer.appendChild(wrapper);
        });

        renderVariants();
    }

    function renderVariantAttributeFields(index, values) {
        const category = selectedCategory();

        if (!category) {
            return '<p class="text-muted small">Select a category first.</p>';
        }

        const attributes = category.attributes.filter(function (attribute) {
            return attribute.is_variant_attribute;
        });

        if (attributes.length === 0) {
            return '<p class="text-muted small">This category has no variant attributes configured.</p>';
        }

        return attributes.map(function (attribute) {
            const value = values[attribute.slug] ??
                values[String(attribute.id)] ?? '';

            return `
                <div class="col-md-6">
                    <label class="form-label">
                        ${escapeHtml(attribute.name)}
                        ${attribute.is_required ? '*' : ''}
                    </label>
                    ${attributeInput(
                        attribute,
                        value,
                        'variants[' + index + '][attributes][' + attribute.id + ']'
                    )}
                </div>
            `;
        }).join('');
    }

    function addVariant(variant = {}) {
        const index = variantIndex++;

        const attributes = variant.attributes || {};

        const card = document.createElement('div');
        card.className = 'border rounded p-3 mb-3 variant-card';

        card.innerHTML = `
            <input type="hidden"
                name="variants[${index}][id]"
                value="${escapeHtml(variant.id ?? '')}">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Variant ${index + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-variant">
                    Remove
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">SKU</label>
                    <input type="text"
                        name="variants[${index}][sku]"
                        class="form-control"
                        maxlength="100"
                        value="${escapeHtml(variant.sku ?? '')}">
                </div>

                <div class="col-12">
                    <div class="row g-3 variant-attributes">
                        ${renderVariantAttributeFields(index, attributes)}
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price per Unit (₹) *</label>
                    <input type="number"
                        name="variants[${index}][price_per_unit]"
                        class="form-control"
                        min="0.01"
                        step="0.01"
                        required
                        value="${escapeHtml(variant.price_per_unit ?? '')}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Available Quantity *</label>
                    <input type="number"
                        name="variants[${index}][available_quantity]"
                        class="form-control"
                        min="0.001"
                        step="0.001"
                        required
                        value="${escapeHtml(variant.available_quantity ?? '')}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Minimum Order Quantity *</label>
                    <input type="number"
                        name="variants[${index}][minimum_order_quantity]"
                        class="form-control"
                        min="0.001"
                        step="0.001"
                        required
                        value="${escapeHtml(variant.minimum_order_quantity ?? '')}">
                </div>
            </div>
        `;

        card.querySelector('.remove-variant').addEventListener('click', function () {
            card.remove();
        });

        variantContainer.appendChild(card);
    }

    function renderVariants() {
        const currentVariants = Array.from(
            variantContainer.querySelectorAll('.variant-card')
        );

        if (currentVariants.length === 0) {
            variantContainer.innerHTML =
                '<p class="text-muted small mb-0">No variants added.</p>';
        }
    }

    addVariantButton.addEventListener('click', function () {
        const category = selectedCategory();

        if (!category) {
            alert('Please select a category before adding variants.');
            return;
        }

        if (!category.attributes.some(function (attribute) {
            return attribute.is_variant_attribute;
        })) {
            alert('This category has no variant attributes configured.');
            return;
        }

        const emptyMessage = variantContainer.querySelector('p');

        if (emptyMessage) {
            emptyMessage.remove();
        }

        addVariant();
    });

    categorySelect.addEventListener('change', function () {
        // A category change means old variant attribute values may no
        // longer match the new category, so clear existing variant rows.
        variantContainer.innerHTML = '';
        variantIndex = 0;
        renderCategoryAttributes();
    });

    renderCategoryAttributes();

    if (Array.isArray(initialVariants) && initialVariants.length > 0) {
        initialVariants.forEach(function (variant) {
            addVariant(variant);
        });
    } else {
        renderVariants();
    }
});
</script>
@endpush
