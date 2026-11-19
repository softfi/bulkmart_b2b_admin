
@extends('layouts.admin')

@section('title', 'Purchase Details')
@section('active', 'purchases')

@section('content')
@php
    $statusStyles = [
        'ordered' => 'primary',
        'partially_received' => 'warning',
        'received' => 'success',
        'cancelled' => 'danger',
    ];

    $paymentStyles = [
        'unpaid' => 'danger',
        'partially_paid' => 'warning',
        'paid' => 'success',
    ];

    $statusLabel = ucfirst(str_replace('_', ' ', $purchase->status));
    $paymentLabel = ucfirst(str_replace('_', ' ', $purchase->payment_status));

    $canCancel = $purchase->status === 'ordered';
@endphp

<style>

.purchase-page {
    color: #e7edf7;
}

.purchase-page .purchase-header a {
    color: #b9c9df !important;
}

.purchase-title {
    color: #f5f7fb;
    font-size: 27px;
    font-weight: 800;
    letter-spacing: -.7px;
    margin-bottom: 5px;
}

.purchase-subtitle,
.muted-note {
    color: #91a2b9 !important;
    font-size: 12px;
}

.purchase-card {
    background: #111e30;
    border: 1px solid #2a3b52;
    border-radius: 13px;
    margin-bottom: 20px;
    overflow: hidden;
    color: #e7edf7;
}

.purchase-card-header {
    padding: 17px 20px;
    border-bottom: 1px solid #2a3b52;
    background: #17263b;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.purchase-card-header h5 {
    color: #f1c879;
    font-size: 15px;
    font-weight: 750;
    margin: 0;
}

.purchase-card-body {
    padding: 20px;
}

.detail-label {
    font-size: 11px;
    font-weight: 700;
    color: #91a5c0;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 6px;
}

.detail-value {
    font-size: 14px;
    font-weight: 650;
    color: #f0f4fa;
    overflow-wrap: anywhere;
}

.detail-item {
    margin-bottom: 20px;
}

.purchase-number {
    display: inline-block;
    background: #20334d;
    color: #f1c879;
    padding: 7px 12px;
    border: 1px solid #3b506b;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 750;
}

.purchase-total {
    background: #172a44;
    border: 1px solid #344b69;
    color: #fff;
    border-radius: 12px;
    padding: 22px;
}

.purchase-total .amount {
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -.7px;
    color: #f1c879;
}

.purchase-total .caption {
    color: #b8c8dc;
    font-size: 12px;
}

.purchase-table {
    --bs-table-bg: transparent;
    --bs-table-color: #e7edf7;
    --bs-table-border-color: #2a3b52;
    margin-bottom: 0;
}

.purchase-table th {
    background: #17263b;
    color: #a9bad0;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 13px 15px;
    border-color: #2a3b52;
    white-space: nowrap;
}

.purchase-table td {
    background: #111e30;
    color: #e7edf7;
    padding: 15px;
    font-size: 13px;
    vertical-align: middle;
    border-color: #2a3b52;
}

.purchase-table .text-muted {
    color: #91a2b9 !important;
}

.purchase-page .border {
    border-color: #34465e !important;
}

.purchase-page hr {
    border-color: #34465e;
    opacity: 1;
}

.purchase-actions {
    background: #111e30;
    border: 1px solid #2a3b52;
    border-radius: 12px;
    padding: 20px;
}

.purchase-actions h5 {
    color: #f1c879;
}

.purchase-page .form-label {
    color: #b7c6d9;
}

.purchase-page .form-control,
.purchase-page .form-select {
    background-color: #0b1524;
    color: #edf3fb;
    border: 1px solid #354963;
}

.purchase-page .form-control:focus,
.purchase-page .form-select:focus {
    background-color: #0b1524;
    color: #fff;
    border-color: #d8b56d;
    box-shadow: 0 0 0 .2rem rgba(216,181,109,.15);
}

.purchase-page .form-select option {
    background: #111e30;
    color: #fff;
}

.purchase-page .btn-dark {
    background: #d8b56d;
    border-color: #d8b56d;
    color: #101a29;
    font-weight: 700;
}

.purchase-page .btn-dark:hover {
    background: #edca88;
    border-color: #edca88;
}

.purchase-page .btn-outline-secondary {
    color: #d8e2ef;
    border-color: #52657e;
}

.purchase-page .btn-outline-secondary:hover {
    background: #263950;
    color: #fff;
}

.purchase-page .text-muted {
    color: #91a2b9 !important;
}

@media(max-width: 767px) {
    .purchase-title {
        font-size: 22px;
    }

    .purchase-card-body {
        padding: 15px;
    }
}


/* Keep purchase title and actions aligned */
.purchase-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 18px;
    width: 100%;
}

.purchase-header > div:first-child {
    flex: 1 1 350px;
    min-width: 0;
}

.purchase-header > div:last-child {
    display: flex;
    flex: 0 1 auto;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
}

/* Prevent cards and Bootstrap columns from forcing horizontal overflow */
.purchase-page,
.purchase-page .row,
.purchase-page [class*="col-"] {
    min-width: 0;
}

.purchase-page {
    max-width: 100%;
    overflow-x: hidden;
}

.purchase-card,
.purchase-total,
.purchase-actions {
    max-width: 100%;
    min-width: 0;
}

.purchase-page .table-responsive {
    max-width: 100%;
}

@media (max-width: 767px) {
    .purchase-header > div:last-child {
        justify-content: flex-start;
        width: 100%;
    }
}
/* Record Goods Receipt button */
.purchase-page .btn-primary {
    background: #e2bd72 !important;
    border: 1px solid #e2bd72 !important;
    color: #000000 !important;
    font-weight: 750;
    border-radius: 8px;
    padding: 10px 17px;
    box-shadow: none;
}

.purchase-page .btn-primary:hover {
    background: #f0ce8b !important;
    border-color: #f0ce8b !important;
    color: #101a29 !important;
}

/* Keep secondary button readable */
.purchase-page .btn-outline-secondary {
    background: #17263b;
    color: #e7edf7 !important;
    border: 1px solid #52657e !important;
}

.purchase-page .btn-outline-secondary:hover {
    background: #293d57 !important;
    color: #fff !important;
}


/* =========================================
   PURCHASE PAGE - COLOR CONSISTENCY FIX
========================================= */

/* Status badges */
.purchase-page .badge {
    font-weight: 700;
    padding: 7px 12px;
    border-radius: 6px;
    letter-spacing: .2px;
}

.purchase-page .badge.bg-primary {
    background: #244c78 !important;
    color: #dcecff !important;
}

.purchase-page .badge.bg-success {
    background: #164c3a !important;
    color: #a7f3d0 !important;
}

.purchase-page .badge.bg-warning {
    background: #59451f !important;
    color: #ffe3a3 !important;
}

.purchase-page .badge.bg-danger {
    background: #592b35 !important;
    color: #ffc2cc !important;
}

.purchase-page .badge.bg-secondary {
    background: #34465e !important;
    color: #e0e8f2 !important;
}

/* Receipt count badge */
.purchase-page .badge.bg-light {
    background: #263b55 !important;
    color: #e4edfa !important;
    border: 1px solid #405773;
}

/* Primary buttons */
.purchase-page .btn-primary,
.purchase-page .btn-dark {
    background: #d8b56d !important;
    border: 1px solid #d8b56d !important;
    color: #172235 !important;
    font-weight: 750;
    border-radius: 8px;
    transition: all .2s ease;
}

.purchase-page .btn-primary:hover,
.purchase-page .btn-dark:hover {
    background: #edca88 !important;
    border-color: #edca88 !important;
    color: #101a29 !important;
    transform: translateY(-1px);
}

/* Secondary buttons */
.purchase-page .btn-outline-secondary {
    background: #22344b !important;
    color: #e2eaf5 !important;
    border: 1px solid #435a76 !important;
    border-radius: 8px;
    font-weight: 650;
    transition: all .2s ease;
}

.purchase-page .btn-outline-secondary:hover {
    background: #344b66 !important;
    border-color: #647d9b !important;
    color: #ffffff !important;
}

/* Receipt record sections */
.purchase-page .purchase-card-body > .border.rounded-3 {
    background: #17263b !important;
    border: 1px solid #344b67 !important;
    color: #e7edf7 !important;
}

/* Nested receipt tables */
.purchase-page .purchase-card .table {
    --bs-table-bg: transparent;
    --bs-table-color: #e7edf7;
    --bs-table-border-color: #34465e;
    --bs-table-hover-bg: #20334d;
    --bs-table-hover-color: #ffffff;
    margin-bottom: 0;
}

.purchase-page .purchase-card .table thead th {
    background: #20334d !important;
    color: #c1d0e3 !important;
    border-color: #344b67 !important;
    font-size: 11px;
    font-weight: 750;
}

.purchase-page .purchase-card .table tbody td {
    background: transparent !important;
    color: #e7edf7 !important;
    border-color: #34465e !important;
}

/* Receipt text */
.purchase-page .purchase-card strong {
    color: #f0f4fa;
}

.purchase-page .purchase-card small.text-muted {
    color: #a6b7cc !important;
}

/* Form controls */
.purchase-page .form-control,
.purchase-page .form-select {
    background-color: #17263b !important;
    color: #edf3fb !important;
    border: 1px solid #405773 !important;
    border-radius: 7px;
}

.purchase-page .form-control:focus,
.purchase-page .form-select:focus {
    border-color: #d8b56d !important;
    box-shadow: 0 0 0 3px rgba(216,181,109,.16) !important;
}

/* Disabled controls */
.purchase-page .form-select:disabled,
.purchase-page .form-control:disabled {
    background-color: #26364b !important;
    color: #aab8ca !important;
    opacity: 1;
}

/* Dropdown options */
.purchase-page .form-select option {
    background: #17263b;
    color: #ffffff;
}

/* Focus visibility */
.purchase-page .btn:focus-visible {
    outline: 2px solid #f1c879;
    outline-offset: 3px;
    box-shadow: none;
}

/* Empty state */
.purchase-page .text-center.py-4 {
    background: #17263b;
    border: 1px dashed #405773;
    border-radius: 10px;
    padding: 30px 15px !important;
}

/* Smooth transitions */
.purchase-page .purchase-card,
.purchase-page .purchase-actions,
.purchase-page .purchase-total {
    transition: border-color .2s ease;
}

/* Mobile adjustments */
@media(max-width: 767px) {
    .purchase-page .btn {
        font-size: 13px;
    }

    .purchase-page .badge {
        font-size: 11px;
    }
}
/* Force Record Goods Receipt text to pure black */
.purchase-page a.btn-primary,
.purchase-page a.btn-primary:link,
.purchase-page a.btn-primary:visited,
.purchase-page a.btn-primary:hover,
.purchase-page a.btn-primary:focus,
.purchase-page a.btn-primary:active {
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
    font-weight: 800 !important;
}

</style>

<div class="container-fluid py-3 purchase-page">

    {{-- Header --}}
    <div class="purchase-header">
        <div>
            <a href="{{ route('admin.purchases.index') }}"
               class="text-decoration-none small text-muted">
                ← Back to Vendor Purchases
            </a>

            <h2 class="purchase-title mt-3">Purchase Details</h2>

            <p class="purchase-subtitle mb-2">
                View procurement information, vendor details and goods receipt progress.
            </p>

            <span class="purchase-number mb-3">
                {{ $purchase->purchase_number }}
            </span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if(!in_array($purchase->status, ['received', 'cancelled']))
                <a href="{{ route('admin.purchases.receive', $purchase) }}"
                   class="btn btn-primary">
                    Record Goods Receipt
                </a>
            @endif

            <a href="{{ route('admin.purchases.index') }}"
               class="btn btn-outline-secondary">
                All Purchases
            </a>
        </div>
    </div>

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

    {{-- Status summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="purchase-card h-100 mb-0">
                <div class="purchase-card-body">
                    <div class="detail-label">Purchase Status</div>
                    <span class="badge bg-{{ $statusStyles[$purchase->status] ?? 'secondary' }} px-3 py-2">
                        {{ $statusLabel }}
                    </span>
                    <p class="muted-note mt-3 mb-0">
                        Current goods receipt status.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="purchase-card h-100 mb-0">
                <div class="purchase-card-body">
                    <div class="detail-label">Payment Status</div>
                    <span class="badge bg-{{ $paymentStyles[$purchase->payment_status] ?? 'secondary' }} px-3 py-2">
                        {{ $paymentLabel }}
                    </span>
                    <p class="muted-note mt-3 mb-0">
                        Current vendor payment status.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="purchase-total h-100">
                <div class="caption">Total Purchase Amount</div>
                <div class="amount mt-2">
                    ₹{{ number_format((float) $purchase->subtotal, 2) }}
                </div>
                <div class="caption mt-2">
                    {{ number_format((float) $purchase->quantity, 3) }}
                    {{ $purchase->product->unit ?? 'units' }}
                </div>
            </div>
        </div>
    </div>

    
{{-- Vendor confirmation --}}
<div class="purchase-card mb-4">
    <div class="purchase-card-header">
        <h5>Vendor Confirmation</h5>
    </div>

    <div class="purchase-card-body">
        @if($purchase->vendor_response_status === 'pending')
            <span class="badge bg-warning">
                Awaiting Vendor Confirmation
            </span>

            <p class="muted-note mt-2 mb-0">
                The vendor has not responded to this purchase yet.
            </p>

        @elseif($purchase->vendor_response_status === 'accepted')
            <span class="badge bg-success">
                Accepted by Vendor
            </span>

            <p class="muted-note mt-2 mb-0">
                @if($purchase->vendor_responded_at)
                    Vendor responded on
                    {{ $purchase->vendor_responded_at->format('d M Y, h:i A') }}.
                @endif
            </p>

        @elseif($purchase->vendor_response_status === 'rejected')
            <span class="badge bg-danger">
                Rejected by Vendor
            </span>

            <div class="mt-3">
                <div class="detail-label">Vendor's rejection reason</div>
                <div class="detail-value">
                    {{ $purchase->vendor_response_reason }}
                </div>
            </div>

            <p class="muted-note mt-2 mb-0">
                Outstanding reserved stock has been released.
                Create a new purchase if you want to select another vendor.
            </p>
        @endif
    </div>
</div>

    <div class="row g-3">

        {{-- Purchase information --}}
        <div class="col-lg-8">

            <div class="purchase-card">
                <div class="purchase-card-header">
                    <h5>Purchase Information</h5>
                </div>

                <div class="purchase-card-body">
                    <div class="row">
                        <div class="col-md-4 detail-item">
                            <div class="detail-label">Purchase Number</div>
                            <div class="detail-value">
                                {{ $purchase->purchase_number }}
                            </div>
                        </div>

                        <div class="col-md-4 detail-item">
                            <div class="detail-label">Purchase Date</div>
                            <div class="detail-value">
                                {{ $purchase->purchase_date?->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="col-md-4 detail-item">
                            <div class="detail-label">Expected Delivery</div>
                            <div class="detail-value">
                                {{ $purchase->expected_delivery_date?->format('d M Y') ?? 'Not specified' }}
                            </div>
                        </div>

                        <div class="col-md-4 detail-item">
                            <div class="detail-label">Created By</div>
                            <div class="detail-value">
                                {{ $purchase->creator->name ?? '—' }}
                            </div>
                        </div>

                        <div class="col-md-4 detail-item">
                            <div class="detail-label">Created At</div>
                            <div class="detail-value">
                                {{ $purchase->created_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>
                    </div>

                    @if($purchase->notes)
                        <hr>
                        <div class="detail-label">Purchase Notes</div>
                        <p class="mb-0" style="font-size:13px;">
                            {{ $purchase->notes }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Product details --}}
            <div class="purchase-card">
                <div class="purchase-card-header">
                    <h5>Product Details</h5>
                </div>

                <div class="table-responsive">
                    <table class="table purchase-table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        {{ $purchase->product->name ?? 'Product unavailable' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $purchase->product->category ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    {{ number_format((float) $purchase->quantity, 3) }}
                                    {{ $purchase->product->unit ?? '' }}
                                </td>

                                <td>
                                    ₹{{ number_format((float) $purchase->unit_price, 2) }}
                                </td>

                                <td class="text-end fw-bold">
                                    ₹{{ number_format((float) $purchase->subtotal, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Goods receipts --}}
            <div class="purchase-card">
                <div class="purchase-card-header">
                    <h5>Goods Receipt Records</h5>
                    <span class="badge bg-light text-dark">
                        {{ $purchase->goodsReceipts->count() }} receipt(s)
                    </span>
                </div>

                <div class="purchase-card-body">
                    @forelse($purchase->goodsReceipts as $receipt)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                                <strong>Goods Receipt #{{ $receipt->id }}</strong>
                                <small class="text-muted">
                                    Received by: {{ $receipt->receiver->name ?? '—' }}
                                </small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Quantity Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receipt->items as $item)
                                            <tr>
                                                <td>{{ $item->product->name ?? 'Product' }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $item->quantity_received, 3) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="fw-semibold">No goods received yet</div>
                            <p class="muted-note mb-0 mt-1">
                                Goods receipt records will appear here after stock is received.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Vendor and payment details --}}
        <div class="col-lg-4">

            <div class="purchase-card">
                <div class="purchase-card-header">
                    <h5>Vendor Information</h5>
                </div>

                <div class="purchase-card-body">
                    <div class="detail-item">
                        <div class="detail-label">Business Name</div>
                        <div class="detail-value">
                            {{ $purchase->vendorProfile->business_name ?? '—' }}
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Vendor Email</div>
                        <div class="detail-value">
                            {{ $purchase->vendorProfile->user->email ?? '—' }}
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">
                            {{ $purchase->vendorProfile->phone ?? '—' }}
                        </div>
                    </div>

                    <div class="detail-item mb-0">
                        <div class="detail-label">Business Address</div>
                        <div class="detail-value">
                            {{ $purchase->vendorProfile->business_address ?? '—' }}
                            @if($purchase->vendorProfile)
                                {{ $purchase->vendorProfile->city }},
                                {{ $purchase->vendorProfile->state }}
                                {{ $purchase->vendorProfile->pincode }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="purchase-card">
                <div class="purchase-card-header">
                    <h5>Payment Summary</h5>
                </div>

                <div class="purchase-card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Quantity</span>
                        <strong>
                            {{ number_format((float) $purchase->quantity, 3) }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Unit Price</span>
                        <strong>
                            ₹{{ number_format((float) $purchase->unit_price, 2) }}
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <span class="fs-5 fw-bold">
                            ₹{{ number_format((float) $purchase->subtotal, 2) }}
                        </span>
                    </div>

                    <div class="mt-3">
                        <span class="text-muted small">Payment:</span>
                        <span class="badge bg-{{ $paymentStyles[$purchase->payment_status] ?? 'secondary' }}">
                            {{ $paymentLabel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Update purchase --}}
            <div class="purchase-actions">
                <h5 class="fw-bold mb-1">Update Purchase</h5>
                <p class="muted-note mb-3">
                    Update the payment status or cancel an eligible purchase.
                </p>

                <form method="POST"
                      action="{{ route('admin.purchases.status.update', $purchase) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Purchase Status
                        </label>

                        @if($canCancel)
                            <select name="status" class="form-select">
                                <option value="ordered" selected>Ordered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        @else
                            <input type="hidden"
                                   name="status"
                                   value="{{ $purchase->status }}">

                            <select class="form-select" disabled>
                                <option selected>{{ $statusLabel }}</option>
                            </select>

                            <small class="muted-note">
                                Receipt status is controlled by the goods receipt workflow.
                            </small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Payment Status
                        </label>

                        <select name="payment_status" class="form-select"
                            {{ $purchase->status === 'cancelled' ? 'disabled' : '' }}>
                            <option value="unpaid"
                                @selected($purchase->payment_status === 'unpaid')>
                                Unpaid
                            </option>
                            <option value="partially_paid"
                                @selected($purchase->payment_status === 'partially_paid')>
                                Partially Paid
                            </option>
                            <option value="paid"
                                @selected($purchase->payment_status === 'paid')>
                                Paid
                            </option>
                        </select>

                        @if($purchase->status === 'cancelled')
                            <input type="hidden" name="payment_status" value="unpaid">
                        @endif
                    </div>

                    <button type="submit" class="btn btn-dark w-100"
                        onclick="return confirm('Are you sure you want to update this purchase?')">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
