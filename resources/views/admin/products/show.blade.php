
@extends('layouts.admin')

@section('title', 'Product Review')
@section('active', 'products')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <a href="{{ route('admin.products.index') }}" class="back-link">
            ← Back to Products
        </a>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
            <div>
                <h3 class="mb-1">{{ $product->name }}</h3>
                <p class="text-muted mb-0">Product review and vendor information</p>
            </div>

            @php
                $statusClasses = [
                    'pending' => 'status-pending',
                    'approved' => 'status-approved',
                    'rejected' => 'status-rejected',
                    'suspended' => 'status-suspended',
                ];
            @endphp

            <span class="product-status {{ $statusClasses[$product->status] ?? 'status-suspended' }}">
                {{ ucfirst($product->status) }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        <div class="col-lg-7">

            <div class="card review-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Information</h5>
                </div>

                <div class="card-body p-4">

                    @if($product->image_path)
                        <div class="product-image-wrapper mb-4">
                            <img
                                src="{{ route('admin.products.image', $product) }}"
                                alt="{{ $product->name }}"
                                class="product-image"
                            >
                        </div>
                    @endif

                    <h4 class="product-heading">{{ $product->name }}</h4>

                    <p class="text-muted description-text">
                        {{ $product->description ?: 'No description provided.' }}
                    </p>

                    <div class="section-divider"></div>

                    <div class="row g-4">

                        <div class="col-sm-6">
                            <div class="detail-label">Category</div>
                            <div class="detail-value">{{ $product->category }}</div>
                        </div>

                        <div class="col-sm-6">
                            <div class="detail-label">Unit</div>
                            <div class="detail-value">{{ $product->unit }}</div>
                        </div>

                        <div class="col-sm-6">
                            <div class="detail-label">Vendor Price Per Unit</div>
                            <div class="detail-value">
                                ₹{{ number_format((float) $product->price_per_unit, 2) }}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="detail-label">Available Quantity</div>
                            <div class="detail-value">
                                {{ number_format((float) $product->available_quantity, 3) }}
                                {{ $product->unit }}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="detail-label">Minimum Order Quantity</div>
                            <div class="detail-value">
                                {{ number_format((float) $product->minimum_order_quantity, 3) }}
                                {{ $product->unit }}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="detail-label">Submitted On</div>
                            <div class="detail-value">
                                {{ $product->created_at->format('d M Y, h:i A') }}
                            </div>
                        </div>

                        @if($product->reviewed_at)
                            <div class="col-sm-6">
                                <div class="detail-label">Last Reviewed</div>
                                <div class="detail-value">
                                    {{ $product->reviewed_at->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        @endif

                        @if($product->reviewer)
                            <div class="col-sm-6">
                                <div class="detail-label">Reviewed By</div>
                                <div class="detail-value">
                                    {{ $product->reviewer->name }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="card review-card">
                <div class="card-header">
                    <h5 class="mb-0">Vendor Information</h5>
                </div>

                <div class="card-body p-4">
                    <h5 class="vendor-heading">
                        {{ $product->vendorProfile->business_name }}
                    </h5>

                    <div class="vendor-detail">
                        <span>Contact Person</span>
                        <strong>{{ $product->vendorProfile->user->name }}</strong>
                    </div>

                    <div class="vendor-detail">
                        <span>Email</span>
                        <strong>{{ $product->vendorProfile->user->email }}</strong>
                    </div>

                    <div class="vendor-detail">
                        <span>Phone</span>
                        <strong>{{ $product->vendorProfile->phone ?: 'Not provided' }}</strong>
                    </div>

                    <div class="vendor-detail">
                        <span>Location</span>
                        <strong>
                            {{ collect([
                                $product->vendorProfile->city,
                                $product->vendorProfile->state,
                                $product->vendorProfile->pincode
                            ])->filter()->implode(', ') ?: 'Not provided' }}
                        </strong>
                    </div>

                    <div class="vendor-detail">
                        <span>Vendor Verification</span>
                        <strong>
                            {{ ucfirst($product->vendorProfile->approval_status) }}
                        </strong>
                    </div>

                    <div class="mt-3">
                        <a
                            href="{{ route('admin.vendors.show', $product->vendorProfile) }}"
                            class="btn btn-outline-gold btn-sm"
                        >
                            View Vendor Profile
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-5">

            <div class="card review-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Review Decision</h5>
                </div>

                <div class="card-body p-4">

                    @if($product->admin_note)
                        <div class="previous-note mb-4">
                            <div class="detail-label mb-2">Previous Admin Note</div>
                            <p class="mb-0">{{ $product->admin_note }}</p>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('admin.products.status.update', $product) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="reviewStatus" class="form-label">
                                Decision *
                            </label>

                            <select
                                name="status"
                                id="reviewStatus"
                                class="form-select"
                                required
                            >
                                <option value="approved"
                                    @selected(old('status') === 'approved'
                                        || (!old('status') && $product->status === 'pending'))>
                                    Approve
                                </option>

                                <option value="rejected"
                                    @selected(old('status') === 'rejected')>
                                    Reject
                                </option>

                                <option value="suspended"
                                    @selected(old('status') === 'suspended')>
                                    Suspend
                                </option>
                            </select>
                        </div>

                        <div class="mb-3" id="reasonContainer">
                            <label for="reviewReason" class="form-label">
                                Reason / Admin Note
                            </label>

                            <textarea
                                name="reason"
                                id="reviewReason"
                                rows="4"
                                maxlength="2000"
                                class="form-control"
                                placeholder="Enter a reason when rejecting or suspending this product..."
                            >{{ old('reason') }}</textarea>

                            <div class="form-text">
                                A reason is mandatory for rejection and suspension.
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                            onclick="return confirm('Confirm this product status change?')"
                        >
                            Save Review Decision
                        </button>
                    </form>

                </div>
            </div>

            <div class="card review-card">
                <div class="card-header">
                    <h5 class="mb-0">Product Activity History</h5>
                </div>

                <div class="card-body p-4">

                    @forelse($product->statusHistories as $history)
                        <div class="history-item">
                            <div class="history-heading">
                                {{ ucfirst($history->previous_status ?? 'Submitted') }}
                                <span class="history-arrow">→</span>
                                {{ ucfirst($history->new_status) }}
                            </div>

                            <div class="history-meta">
                                @if($history->actor)
                                    By: {{ $history->actor->name }}
                                @elseif($history->reviewer)
                                    By: {{ $history->reviewer->name }}
                                @else
                                    Actor unavailable
                                @endif
                            </div>

                            <div class="history-meta">
                                {{ $history->created_at->format('d M Y, h:i A') }}
                            </div>

                            @if($history->reason)
                                <div class="history-reason">
                                    <strong>Reason / Note:</strong>
                                    <p class="mb-0 mt-1">{{ $history->reason }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">
                            No product activity recorded yet.
                        </p>
                    @endforelse

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .back-link {
        color: #d6b574;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .back-link:hover {
        color: #f0d49b;
    }

    .review-card {
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
        overflow: hidden;
    }

    .review-card .card-header {
        background: #0d1726;
        border-bottom: 1px solid #2a394d;
        color: #edf2f8;
        padding: 17px 20px;
    }

    .review-card .card-header h5 {
        font-weight: 600;
    }

    .review-card .card-body {
        color: #e8edf5;
    }

    .product-heading,
    .vendor-heading {
        color: #f1f4f8;
        font-weight: 600;
    }

    .description-text {
        line-height: 1.7;
        white-space: pre-line;
    }

    .product-image-wrapper {
        background: #0b1422;
        border: 1px solid #2a394d;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }

    .product-image {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
    }

    .section-divider {
        border-top: 1px solid #2a394d;
        margin: 22px 0;
    }

    .detail-label {
        color: #9eacbf;
        font-size: 0.8rem;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #edf2f8;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .vendor-detail {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 0;
        border-bottom: 1px solid #29384c;
    }

    .vendor-detail span {
        color: #aeb9c8;
        font-size: 0.88rem;
    }

    .vendor-detail strong {
        color: #edf2f8;
        text-align: right;
        overflow-wrap: anywhere;
        max-width: 65%;
    }

    .product-status {
        padding: 7px 13px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-pending {
        background: #59451e;
        color: #ffe4a6;
    }

    .status-approved {
        background: #164b39;
        color: #c6f4df;
    }

    .status-rejected {
        background: #552631;
        color: #ffd5dc;
    }

    .status-suspended {
        background: #354257;
        color: #e1e8f2;
    }

    .review-card .form-label {
        color: #dce3ed;
        font-weight: 500;
    }

    .review-card .form-control,
    .review-card .form-select {
        background: #0b1422;
        border: 1px solid #34445a;
        color: #edf2f8;
    }

    .review-card .form-control::placeholder {
        color: #9ba9bb;
        opacity: 1;
    }

    .review-card .form-control:focus,
    .review-card .form-select:focus {
        background: #0b1422;
        border-color: #d6b574;
        color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(214, 181, 116, 0.14);
    }

    .review-card .form-text {
        color: #aeb9c8;
    }

    .previous-note {
        background: #19283b;
        border: 1px solid #35475e;
        border-radius: 8px;
        padding: 13px;
        color: #e4ebf4;
    }

    .history-item {
        padding-bottom: 17px;
        margin-bottom: 17px;
        border-bottom: 1px solid #29384c;
    }

    .history-item:last-child {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .history-heading {
        color: #edf2f8;
        font-weight: 600;
    }

    .history-arrow {
        color: #d6b574;
        padding: 0 5px;
    }

    .history-meta {
        color: #aeb9c8;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .history-reason {
        background: #19283b;
        border-radius: 7px;
        padding: 10px;
        margin-top: 10px;
        font-size: 0.85rem;
        color: #dce3ed;
    }

    .btn-outline-gold {
        color: #e2c58c;
        border-color: #b99a5e;
    }

    .btn-outline-gold:hover {
        color: #101827;
        background: #d6b574;
        border-color: #d6b574;
    }

    .alert-success {
        background: #123c30;
        border-color: #23694f;
        color: #d8f7e9;
    }

    .alert-danger {
        background: #451f29;
        border-color: #853b4b;
        color: #ffe3e8;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('reviewStatus');
    const reasonField = document.getElementById('reviewReason');

    if (!statusSelect || !reasonField) {
        return;
    }

    function updateReasonRequirement() {
        reasonField.required = ['rejected', 'suspended'].includes(statusSelect.value);
    }

    statusSelect.addEventListener('change', updateReasonRequirement);
    updateReasonRequirement();
});
</script>
@endpush
