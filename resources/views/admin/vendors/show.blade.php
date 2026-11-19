
@extends('layouts.admin')

@section('title', 'Vendor Details')
@section('active', 'vendors')

@php
    $vendorName = $vendor->user->name ?? 'Unknown Vendor';

    $initials = collect(explode(' ', trim($vendorName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');

    $status = $vendor->approval_status ?? 'pending';

    $statusLabels = [
        'pending' => 'Pending Review',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ];

    $statusClasses = [
        'pending' => 'pending',
        'under_review' => 'review',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'suspended' => 'suspended',
    ];

    $reviewedDocuments = $vendor->documents->filter(
        fn ($document) => !empty($document->admin_note)
    );
@endphp

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('admin.vendors.index') }}" class="vendor-back-link">
                ← Back to Vendor Management
            </a>

            <h3 class="mb-1 mt-2">Vendor Details</h3>
            <p class="text-muted mb-0">
                Review supplier information and submitted verification documents.
            </p>
        </div>

        <span class="vendor-status {{ $statusClasses[$status] ?? 'suspended' }}">
            {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Vendor Profile --}}
    <div class="vendor-profile-card mb-4">
        <div class="vendor-avatar">
            {{ $initials ?: 'V' }}
        </div>

        <div class="flex-grow-1">
            <h4 class="vendor-profile-name">{{ $vendorName }}</h4>

            <div class="vendor-profile-email">
                {{ $vendor->user->email ?? 'No email available' }}
            </div>

            <div class="vendor-profile-meta">
                Vendor ID: #{{ $vendor->id }}
                <span class="meta-separator">·</span>
                Registered {{ $vendor->created_at?->format('d M Y') ?? '—' }}
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-7">

            {{-- Business Information --}}
            <div class="card vendor-card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Business Information</h5>
                    <p class="text-muted small mb-0">
                        Registered business and contact details
                    </p>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <div class="detail-label">Business Name</div>
                            <div class="detail-value">
                                {{ $vendor->business_name ?: '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">Contact Person</div>
                            <div class="detail-value">{{ $vendorName }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value">
                                {{ $vendor->user->email ?? '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value">
                                {{ $vendor->phone ?: '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">GST Number</div>
                            <div class="detail-value">
                                {{ $vendor->gst_number ?: 'Not Provided' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">City</div>
                            <div class="detail-value">{{ $vendor->city ?: '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">State</div>
                            <div class="detail-value">{{ $vendor->state ?: '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-label">Pincode</div>
                            <div class="detail-value">{{ $vendor->pincode ?: '—' }}</div>
                        </div>

                        <div class="col-12">
                            <div class="detail-label">Business Address</div>
                            <div class="detail-value">
                                {{ $vendor->business_address ?: '—' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Verification Documents --}}
            <div class="card vendor-card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">Verification Documents</h5>
                        <p class="text-muted small mb-0">
                            Documents submitted by this vendor
                        </p>
                    </div>

                    <span class="document-count">
                        {{ $vendor->documents->count() }} Documents
                    </span>
                </div>

                <div class="card-body p-4">

                    @forelse($vendor->documents as $document)
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>

                            <div class="document-information flex-grow-1">
                                <div class="document-name">
                                    {{ ucwords(str_replace('_', ' ', $document->document_type)) }}
                                </div>

                                <div class="document-meta">
                                    {{ $document->original_filename }}

                                    @if($document->file_size)
                                        · {{ number_format($document->file_size / 1024, 1) }} KB
                                    @endif
                                </div>

                                <div class="document-review-status">
                                    {{ ucwords(str_replace('_', ' ', $document->review_status ?? 'pending')) }}
                                </div>

                                @if($document->admin_note)
                                    <div class="document-note mt-2">
                                        <strong>Admin Note:</strong>
                                        {{ $document->admin_note }}
                                    </div>
                                @endif
                            </div>

                            <a
                                href="{{ route('admin.vendors.documents.download', [
                                    'vendor' => $vendor->id,
                                    'document' => $document->id
                                ]) }}"
                                class="btn btn-outline-gold btn-sm"
                            >
                                <i class="bi bi-download me-1"></i>
                                Download
                            </a>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-file-earmark-x"></i>
                            <p class="mb-0 mt-2">No verification documents have been submitted.</p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Document Review Notes --}}
            @if($reviewedDocuments->isNotEmpty())
                <div class="card vendor-card">
                    <div class="card-header">
                        <h5 class="mb-1">Document Review Notes</h5>
                        <p class="text-muted small mb-0">
                            Existing administrative comments on documents
                        </p>
                    </div>

                    <div class="card-body p-4">
                        @foreach($reviewedDocuments as $document)
                            <div class="document-note-block">
                                <div class="detail-label">
                                    {{ ucwords(str_replace('_', ' ', $document->document_type)) }}
                                </div>

                                <div class="note-content">
                                    {{ $document->admin_note }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-5">

            {{-- Account Summary --}}
            <div class="card vendor-card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Account Summary</h5>
                    <p class="text-muted small mb-0">Vendor account overview</p>
                </div>

                <div class="card-body p-4">

                    <div class="summary-row">
                        <span>Vendor ID</span>
                        <strong>#{{ $vendor->id }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Approval Status</span>
                        <strong>
                            {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Account Status</span>
                        <strong class="{{ ($vendor->user?->is_active ?? false) ? 'text-success' : 'text-warning' }}">
                            {{ ($vendor->user?->is_active ?? false) ? 'Active' : 'Inactive' }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Documents Submitted</span>
                        <strong>{{ $vendor->documents->count() }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Registration Date</span>
                        <strong>{{ $vendor->created_at?->format('d M Y') ?? '—' }}</strong>
                    </div>

                </div>
            </div>

            {{-- Vendor Review --}}
            <div class="card vendor-card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Vendor Review</h5>
                    <p class="text-muted small mb-0">
                        Update this vendor's verification status
                    </p>
                </div>

                <div class="card-body p-4">

                    <div class="review-current-status mb-3">
                        <span>Current Status</span>
                        <strong>
                            {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}
                        </strong>
                    </div>

                    <form
                        action="{{ route('admin.vendors.status.update', $vendor) }}"
                        method="POST"
                        id="vendorReviewForm"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="vendorStatus" class="form-label">
                                New Status *
                            </label>

                            <select
                                name="status"
                                id="vendorStatus"
                                class="form-select"
                                required
                            >
                                <option value="">Select status</option>

                                <option value="under_review"
                                    @selected(old('status') === 'under_review')>
                                    Under Review
                                </option>

                                <option value="approved"
                                    @selected(old('status') === 'approved')>
                                    Approve Vendor
                                </option>

                                <option value="rejected"
                                    @selected(old('status') === 'rejected')>
                                    Reject Application
                                </option>

                                <option value="suspended"
                                    @selected(old('status') === 'suspended')>
                                    Suspend Vendor
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="vendorReason" class="form-label">
                                Review Reason
                            </label>

                            <textarea
                                name="reason"
                                id="vendorReason"
                                rows="4"
                                maxlength="2000"
                                class="form-control"
                                placeholder="Required when rejecting or suspending a vendor..."
                            >{{ old('reason') }}</textarea>

                            <div class="form-text">
                                A reason is required for rejection and suspension.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Update Vendor Status
                        </button>
                    </form>

                </div>
            </div>

            {{-- Review History --}}
            <div class="card vendor-card mb-4">
                <div class="card-header">
                    <h5 class="mb-1">Review History</h5>
                    <p class="text-muted small mb-0">
                        Previous vendor status changes
                    </p>
                </div>

                <div class="card-body p-4">

                    @forelse($vendor->statusHistories as $history)
                        <div class="history-item">

                            <div class="history-top">
                                <strong>
                                    {{ ucwords(str_replace('_', ' ', $history->new_status)) }}
                                </strong>

                                <span>
                                    {{ $history->created_at->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            <div class="history-meta">
                                Previous status:
                                {{ $history->previous_status
                                    ? ucwords(str_replace('_', ' ', $history->previous_status))
                                    : 'Initial status' }}
                            </div>

                            <div class="history-meta">
                                Reviewed by:
                                {{ $history->reviewer->name ?? 'Admin account unavailable' }}
                            </div>

                            @if($history->reason)
                                <div class="history-reason">
                                    {{ $history->reason }}
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="empty-state">
                            No review actions have been recorded yet.
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Rejection Information --}}
            @if($vendor->rejection_reason)
                <div class="card vendor-card">
                    <div class="card-header">
                        <h5 class="mb-1">Rejection Information</h5>
                        <p class="text-muted small mb-0">
                            Recorded rejection reason
                        </p>
                    </div>

                    <div class="card-body p-4">
                        <div class="note-content">
                            {{ $vendor->rejection_reason }}
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .vendor-back-link {
        color: #d6b574;
        text-decoration: none;
        font-size: 0.88rem;
    }

    .vendor-back-link:hover {
        color: #f0d49b;
    }

    .vendor-profile-card {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 22px;
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
    }

    .vendor-avatar {
        width: 62px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 12px;
        background: #293a51;
        color: #e2c58c;
        font-size: 21px;
        font-weight: 700;
    }

    .vendor-profile-name {
        color: #f1f4f8;
        font-size: 1.15rem;
        font-weight: 650;
        margin-bottom: 4px;
    }

    .vendor-profile-email,
    .vendor-profile-meta {
        color: #aeb9c8;
        font-size: 0.84rem;
        overflow-wrap: anywhere;
    }

    .vendor-profile-meta {
        margin-top: 8px;
        font-size: 0.78rem;
    }

    .meta-separator {
        padding: 0 7px;
        color: #d6b574;
    }

    .vendor-status {
        display: inline-flex;
        align-items: center;
        padding: 7px 13px;
        border-radius: 30px;
        font-size: 0.77rem;
        font-weight: 650;
        white-space: nowrap;
    }

    .vendor-status.pending {
        background: #59451e;
        color: #ffe4a6;
    }

    .vendor-status.review {
        background: #203f65;
        color: #d5e8ff;
    }

    .vendor-status.approved {
        background: #164b39;
        color: #c6f4df;
    }

    .vendor-status.rejected {
        background: #552631;
        color: #ffd5dc;
    }

    .vendor-status.suspended {
        background: #354257;
        color: #e1e8f2;
    }

    .vendor-card {
        background: #111c2c;
        border: 1px solid #26354a;
        border-radius: 12px;
        overflow: hidden;
    }

    .vendor-card .card-header {
        padding: 17px 20px;
        background: #0d1726;
        border-bottom: 1px solid #2a394d;
        color: #edf2f8;
    }

    .vendor-card .card-header h5 {
        color: #edf2f8;
        font-weight: 600;
    }

    .vendor-card .card-body {
        color: #e8edf5;
    }

    .vendor-card .text-muted {
        color: #aeb9c8 !important;
    }

    .detail-label {
        color: #9eacbf;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .detail-value {
        color: #edf2f8;
        font-size: 0.91rem;
        font-weight: 550;
        overflow-wrap: anywhere;
    }

    .document-count {
        background: #273950;
        border: 1px solid #40536c;
        color: #e4ebf4;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .document-item {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px;
        margin-bottom: 12px;
        background: #0d1726;
        border: 1px solid #2a394d;
        border-radius: 9px;
    }

    .document-item:last-child {
        margin-bottom: 0;
    }

    .document-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        background: #26384f;
        border-radius: 9px;
        color: #e2c58c;
        font-size: 18px;
    }

    .document-information {
        min-width: 0;
    }

    .document-name {
        color: #edf2f8;
        font-size: 0.88rem;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .document-meta {
        color: #aeb9c8;
        font-size: 0.74rem;
        margin-top: 4px;
        overflow-wrap: anywhere;
    }

    .document-review-status {
        color: #d6b574;
        font-size: 0.75rem;
        margin-top: 5px;
        text-transform: capitalize;
    }

    .document-note {
        color: #d6deea;
        background: #19283b;
        border: 1px solid #35475e;
        border-radius: 7px;
        padding: 9px;
        font-size: 0.78rem;
        overflow-wrap: anywhere;
    }

    .btn-outline-gold {
        color: #e2c58c;
        border-color: #b99a5e;
        white-space: nowrap;
    }

    .btn-outline-gold:hover {
        color: #101827;
        background: #d6b574;
        border-color: #d6b574;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #29384c;
        font-size: 0.83rem;
    }

    .summary-row:first-child {
        padding-top: 0;
    }

    .summary-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .summary-row span {
        color: #aeb9c8;
    }

    .summary-row strong {
        color: #edf2f8;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .review-current-status {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        background: #19283b;
        border: 1px solid #35475e;
        border-radius: 8px;
        font-size: 0.82rem;
    }

    .review-current-status span {
        color: #aeb9c8;
    }

    .review-current-status strong {
        color: #e2c58c;
        text-align: right;
    }

    .vendor-card .form-label {
        color: #dce3ed;
        font-size: 0.86rem;
        font-weight: 550;
    }

    .vendor-card .form-control,
    .vendor-card .form-select {
        background: #0b1422;
        color: #edf2f8;
        border: 1px solid #34445a;
        min-height: 42px;
    }

    .vendor-card textarea.form-control {
        min-height: 105px;
    }

    .vendor-card .form-control::placeholder {
        color: #9ba9bb;
        opacity: 1;
    }

    .vendor-card .form-control:focus,
    .vendor-card .form-select:focus {
        background: #0b1422;
        color: #fff;
        border-color: #d6b574;
        box-shadow: 0 0 0 0.2rem rgba(214, 181, 116, 0.14);
    }

    .vendor-card .form-text {
        color: #aeb9c8;
    }

    .history-item {
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid #29384c;
    }

    .history-item:last-child {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .history-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .history-top strong {
        color: #e2c58c;
        font-size: 0.86rem;
    }

    .history-top span,
    .history-meta {
        color: #aeb9c8;
        font-size: 0.74rem;
    }

    .history-meta {
        margin-top: 6px;
    }

    .history-reason,
    .note-content {
        margin-top: 10px;
        padding: 12px;
        background: #19283b;
        border: 1px solid #35475e;
        border-radius: 8px;
        color: #dce3ed;
        font-size: 0.82rem;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .document-note-block {
        margin-bottom: 16px;
    }

    .document-note-block:last-child {
        margin-bottom: 0;
    }

    .empty-state {
        padding: 28px 12px;
        text-align: center;
        color: #aeb9c8;
        font-size: 0.86rem;
    }

    .empty-state i {
        color: #d6b574;
        font-size: 25px;
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

    @media(max-width: 575px) {
        .vendor-profile-card {
            align-items: flex-start;
            padding: 16px;
        }

        .vendor-avatar {
            width: 48px;
            height: 48px;
            font-size: 17px;
        }

        .document-item {
            flex-wrap: wrap;
        }

        .document-information {
            flex-basis: calc(100% - 60px);
        }

        .document-item .btn {
            margin-left: 55px;
        }

        .history-top {
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('vendorStatus');
    const reasonField = document.getElementById('vendorReason');
    const reviewForm = document.getElementById('vendorReviewForm');

    if (!statusSelect || !reasonField || !reviewForm) {
        return;
    }

    function updateReasonRequirement() {
        reasonField.required = ['rejected', 'suspended'].includes(
            statusSelect.value
        );
    }

    statusSelect.addEventListener('change', updateReasonRequirement);

    reviewForm.addEventListener('submit', function (event) {
        if (statusSelect.value === '{{ $status }}') {
            event.preventDefault();
            alert('The vendor already has this status. Select a different status.');
            return;
        }

        if (
            ['rejected', 'suspended'].includes(statusSelect.value) &&
            !reasonField.value.trim()
        ) {
            event.preventDefault();
            reasonField.focus();
        }
    });

    updateReasonRequirement();
});
</script>
@endpush
