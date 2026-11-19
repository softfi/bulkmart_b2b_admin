
@extends('layouts.admin')

@section('active', 'categories')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Product Categories</h3>
            <p class="text-muted mb-0">
                Manage product categories and their configurable specifications and variant attributes.
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            + Create Category
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-2">Total Categories</div>
                    <div class="fs-3 fw-bold">{{ $categories->total() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-2">Categories on This Page</div>
                    <div class="fs-3 fw-bold">{{ $categories->count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-2">Active Categories on This Page</div>
                    <div class="fs-3 fw-bold">
                        {{ $categories->where('is_active', true)->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold">All Categories</h5>
            <span class="small text-muted">
                {{ $categories->total() }} records
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Category</th>
                        <th>Specifications</th>
                        <th>Products</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $category->name }}</div>
                                <div class="small text-muted">{{ $category->slug }}</div>

                                @if($category->description)
                                    <div class="small text-muted mt-1">
                                        {{ \Illuminate\Support\Str::limit($category->description, 90) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ $category->attributes_count }}
                                    {{ \Illuminate\Support\Str::plural('field', $category->attributes_count) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($category->products_count) }}
                            </td>

                            <td>{{ $category->sort_order }}</td>

                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.categories.status', $category) }}"
                                          onsubmit="return confirm('Are you sure you want to {{ $category->is_active ? 'deactivate' : 'activate' }} this category?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm {{ $category->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="fw-semibold mb-2">No categories created yet</div>
                                <p class="text-muted small mb-3">
                                    Create your first category and configure its product attributes.
                                </p>
                                <a href="{{ route('admin.categories.create') }}"
                                   class="btn btn-primary btn-sm">
                                    Create First Category
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="card-footer">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
