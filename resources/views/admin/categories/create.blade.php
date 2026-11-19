
@extends('layouts.admin')

@section('active', 'categories')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Create Product Category</h3>
            <p class="text-muted mb-0">
                Create a category and configure its product specifications.
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

    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

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
                               value="{{ old('name') }}"
                               maxlength="100"
                               placeholder="e.g. Sarees, T-Shirts, Rice"
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
                               value="{{ old('sort_order', 0) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   class="form-check-input"
                                   id="category-active"
                                   @checked(old('is_active', '1') == '1')>
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
                                  class="form-control"
                                  placeholder="Describe the products that belong to this category">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">Category Attributes</h5>
                    <p class="small text-muted mb-0">
                        Define the fields vendors must complete when submitting products.
                    </p>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm" id="add-attribute">
                    + Add Attribute
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info small">
                    <strong>Example:</strong> A Sarees category might have Fabric and Type as
                    general specifications, while Color and Size can be variant attributes.
                    A variant attribute is entered separately for each product variant.
                </div>

                <div id="attributes-container"></div>

                <div id="empty-attributes" class="text-center text-muted py-4">
                    No attributes added yet. Click <strong>Add Attribute</strong> to configure fields.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.categories.index') }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Category
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('attributes-container');
    const addButton = document.getElementById('add-attribute');
    const emptyMessage = document.getElementById('empty-attributes');

    const oldAttributes = @json(old('attributes', []));

    let nextIndex = 0;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);
    }

    function updateEmptyMessage() {
        emptyMessage.style.display = container.children.length ? 'none' : 'block';
    }

    function addAttribute(data = {}) {
        const index = nextIndex++;
        const card = document.createElement('div');

        card.className = 'border rounded p-3 mb-3 attribute-card';

        const optionsText = data.options_text ?? '';

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Attribute ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-attribute">
                    Remove
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Attribute Name *</label>
                    <input type="text"
                           name="attributes[${index}][name]"
                           value="${escapeHtml(data.name ?? '')}"
                           maxlength="100"
                           class="form-control"
                           placeholder="e.g. Fabric, Size, Model"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Input Type *</label>
                    <select name="attributes[${index}][input_type]"
                            class="form-select attribute-type">
                        <option value="text" ${data.input_type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="number" ${data.input_type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="select" ${data.input_type === 'select' ? 'selected' : ''}>Dropdown</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sort Order</label>
                    <input type="number"
                           name="attributes[${index}][sort_order]"
                           value="${escapeHtml(data.sort_order ?? 0)}"
                           min="0"
                           max="65535"
                           class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox"
                               name="attributes[${index}][is_required]"
                               value="1"
                               class="form-check-input"
                               id="required-${index}"
                               ${data.is_required ? 'checked' : ''}>
                        <label class="form-check-label" for="required-${index}">
                            Required
                        </label>
                    </div>
                </div>

                <div class="col-12 options-container" style="display:none">
                    <label class="form-label">Dropdown Options *</label>
                    <textarea name="attributes[${index}][options_text]"
                              rows="3"
                              class="form-control options-input"
                              placeholder="Enter one option per line&#10;Example:&#10;Cotton&#10;Silk&#10;Georgette">${escapeHtml(optionsText)}</textarea>
                    <div class="form-text">Enter one selectable option per line.</div>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox"
                               name="attributes[${index}][is_variant_attribute]"
                               value="1"
                               class="form-check-input"
                               id="variant-${index}"
                               ${data.is_variant_attribute ? 'checked' : ''}>
                        <label class="form-check-label" for="variant-${index}">
                            Use this attribute for product variants
                        </label>
                    </div>
                    <div class="form-text">
                        Enable for attributes such as size, color, pack size or model
                        that can have separate pricing and stock.
                    </div>
                </div>
            </div>
        `;

        const typeSelect = card.querySelector('.attribute-type');
        const optionsContainer = card.querySelector('.options-container');
        const optionsInput = card.querySelector('.options-input');

        function updateOptionsVisibility() {
            const isSelect = typeSelect.value === 'select';
            optionsContainer.style.display = isSelect ? 'block' : 'none';
            optionsInput.required = isSelect;
        }

        typeSelect.addEventListener('change', updateOptionsVisibility);

        card.querySelector('.remove-attribute').addEventListener('click', function () {
            card.remove();
            updateEmptyMessage();
        });

        container.appendChild(card);
        updateOptionsVisibility();
        updateEmptyMessage();
    }

    addButton.addEventListener('click', function () {
        addAttribute();
    });

    oldAttributes.forEach(attribute => addAttribute(attribute));

    updateEmptyMessage();
});
</script>
@endpush
