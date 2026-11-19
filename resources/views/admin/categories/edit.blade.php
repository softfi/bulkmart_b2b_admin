
@extends('layouts.admin')

@section('active', 'categories')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Product Category</h3>
            <p class="text-muted mb-0">
                Update {{ $category->name }} and its configurable product attributes.
            </p>
        </div>

        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            Back to Categories
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 mb-4">
            <div class="card-header py-3">
                <h5 class="fw-bold mb-0">Category Information</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category Name *</label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name) }}"
                               maxlength="100"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number"
                               name="sort_order"
                               class="form-control"
                               min="0"
                               max="65535"
                               value="{{ old('sort_order', $category->sort_order) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   class="form-check-input"
                                   id="category-active"
                                   @checked(old('is_active', $category->is_active) == '1')>
                            <label class="form-check-label" for="category-active">
                                Active category
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  rows="3"
                                  maxlength="2000"
                                  class="form-control">{{ old('description', $category->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">Category Attributes</h5>
                    <p class="small text-muted mb-0">
                        Manage the fields vendors must complete for this category.
                    </p>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm" id="add-attribute">
                    + Add Attribute
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-warning small">
                    Removing an attribute can affect existing product and variant data.
                    Avoid removing attributes already used by submitted products until
                    the data migration safeguards are implemented.
                </div>

                <div id="attributes-container"></div>

                <div id="empty-attributes" class="text-center text-muted py-4">
                    No attributes configured. Click <strong>Add Attribute</strong> to add one.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.categories.index') }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection


@php
    $existingAttributes = $category->attributes->map(function ($attribute) {
        return [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'input_type' => $attribute->input_type,
            'options_text' => implode("\n", $attribute->options ?? []),
            'is_required' => (bool) $attribute->is_required,
            'is_variant_attribute' => (bool) $attribute->is_variant_attribute,
            'sort_order' => $attribute->sort_order,
        ];
    })->values()->all();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('attributes-container');
    const addButton = document.getElementById('add-attribute');
    const emptyMessage = document.getElementById('empty-attributes');

    if (!container || !addButton) {
        return;
    }

    const existingAttributes = @json($existingAttributes);
    const oldAttributes = @json(old('attributes', []));

    let attributeIndex = 0;

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

    function updateEmptyMessage() {
        if (!emptyMessage) {
            return;
        }

        const hasAttributes = container.querySelectorAll('.attribute-row').length > 0;
        emptyMessage.style.display = hasAttributes ? 'none' : '';
    }

    function updateOptionsVisibility(row) {
        const typeSelect = row.querySelector('.attribute-input-type');
        const optionsGroup = row.querySelector('.attribute-options-group');

        if (!typeSelect || !optionsGroup) {
            return;
        }

        optionsGroup.style.display =
            typeSelect.value === 'select' ? '' : 'none';
    }

    function createAttributeRow(attribute = {}) {
        const index = attributeIndex++;

        const id = attribute.id ?? '';
        const name = attribute.name ?? '';
        const inputType = attribute.input_type ?? 'text';
        const optionsText = attribute.options_text ?? '';
        const isRequired = Boolean(attribute.is_required);
        const isVariant = Boolean(attribute.is_variant_attribute);
        const sortOrder = attribute.sort_order ?? index;

        const row = document.createElement('div');
        row.className = 'attribute-row border rounded p-3 mb-3';

        row.innerHTML = `
            <input
                type="hidden"
                name="attributes[${index}][id]"
                value="${escapeHtml(id)}"
            >

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Attribute Name</label>
                    <input
                        type="text"
                        name="attributes[${index}][name]"
                        class="form-control"
                        value="${escapeHtml(name)}"
                        placeholder="e.g. Material, Size, Color"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Input Type</label>
                    <select
                        name="attributes[${index}][input_type]"
                        class="form-select attribute-input-type"
                        required
                    >
                        <option value="text" ${inputType === 'text' ? 'selected' : ''}>Text</option>
                        <option value="number" ${inputType === 'number' ? 'selected' : ''}>Number</option>
                        <option value="select" ${inputType === 'select' ? 'selected' : ''}>Select</option>
                    </select>
                </div>

                <div class="col-md-5 attribute-options-group">
                    <label class="form-label">Options</label>
                    <textarea
                        name="attributes[${index}][options_text]"
                        class="form-control"
                        rows="2"
                        placeholder="One option per line"
                    >${escapeHtml(optionsText)}</textarea>
                    <small class="text-muted">
                        Required only for Select. Enter one option per line.
                    </small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sort Order</label>
                    <input
                        type="number"
                        name="attributes[${index}][sort_order]"
                        class="form-control"
                        min="0"
                        value="${escapeHtml(sortOrder)}"
                    >
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input
                            type="checkbox"
                            name="attributes[${index}][is_required]"
                            class="form-check-input"
                            value="1"
                            ${isRequired ? 'checked' : ''}
                        >
                        <label class="form-check-label">Required</label>
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input
                            type="checkbox"
                            name="attributes[${index}][is_variant_attribute]"
                            class="form-check-input"
                            value="1"
                            ${isVariant ? 'checked' : ''}
                        >
                        <label class="form-check-label">Variant Attribute</label>
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-danger remove-attribute mb-1"
                    >
                        Remove Attribute
                    </button>
                </div>
            </div>
        `;

        const typeSelect = row.querySelector('.attribute-input-type');

        typeSelect.addEventListener('change', function () {
            updateOptionsVisibility(row);
        });

        row.querySelector('.remove-attribute').addEventListener('click', function () {
            row.remove();
            updateEmptyMessage();
        });

        container.appendChild(row);
        updateOptionsVisibility(row);
        updateEmptyMessage();
    }

    addButton.addEventListener('click', function () {
        createAttributeRow();
    });

    // Restore existing attributes when the page first loads.
    if (Array.isArray(oldAttributes) && oldAttributes.length > 0) {
        oldAttributes.forEach(function (attribute) {
            createAttributeRow(attribute);
        });
    } else if (Array.isArray(existingAttributes)) {
        existingAttributes.forEach(function (attribute) {
            createAttributeRow(attribute);
        });
    }

    updateEmptyMessage();
});
</script>
@endpush

