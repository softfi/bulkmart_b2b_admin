
@extends('layouts.admin')

@section('title', 'Vendor Purchases')
@section('active', 'purchases')

@push('styles')
<style>
    .purchases-page {
        color: #edf2f8;
    }

    .purchases-page .page-title {
        color: #f3f5f8;
        font-size: 27px;
        font-weight: 750;
        letter-spacing: -.6px;
    }

    .purchases-page .page-description,
    .purchases-page .muted {
        color: #a2afc0 !important;
    }

    .purchases-page .purchase-card {
        background: #111c2c;
        border: 1px solid #26364a;
        border-radius: 13px;
    }

    .purchases-page .form-label {
        color: #dce4ed;
        font-size: 13px;
        font-weight: 600;
    }

    .purchases-page .form-control,
    .purchases-page .form-select {
        min-height: 43px;
        background: #0b1422;
        color: #edf2f8;
        border: 1px solid #34445a;
        border-radius: 8px;
    }

    .purchases-page .form-control::placeholder {
        color: #8998aa;
        opacity: 1;
    }

    .purchases-page .form-control:focus,
    .purchases-page .form-select:focus {
        background: #0b1422;
        color: #fff;
        border-color: #c6a66b;
        box-shadow: 0 0 0 .2rem rgba(198,166,107,.14);
    }

    .purchases-page .form-select option {
        background: #111c2c;
        color: #edf2f8;
    }

    .purchases-page .btn-gold {
        background: #c6a66b;
        border: 1px solid #c6a66b;
        color: #111827;
        font-weight: 700;
        border-radius: 8px;
        padding: 9px 15px;
    }

    .purchases-page .btn-gold:hover {
        background: #d7ba82;
        border-color: #d7ba82;
        color: #111827;
    }

    .purchases-page .btn-outline-muted {
        border: 1px solid #526176;
        color: #dce4ed;
        border-radius: 8px;
        padding: 9px 15px;
    }

    .purchases-page .btn-outline-muted:hover {
        background: #26364a;
        color: #fff;
    }

    .purchases-page .table {
        color: #e5ebf3;
        margin: 0;
    }

    .purchases-page .table thead th {
        background: #172438;
        color: #b9c5d4;
        border-bottom: 1px solid #34445a;
        border-top: 0;
        padding: 15px 13px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .purchases-page .table tbody td {
        background: #111c2c;
        color: #e5ebf3;
        border-color: #26364a;
        padding: 15px 13px;
        vertical-align: middle;
    }

    .purchases-page .table tbody tr:hover td {
        background: #172438;
    }

    .purchases-page .purchase-number {
        color: #e2c58e;
        font-weight: 700;
        white-space: nowrap;
    }

    .purchases-page .primary-text {
        color: #f3f5f8;
        font-weight: 600;
    }

    .purchases-page .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .purchases-page .status-ordered {
        color: #f4d28a;
        background: rgba(190,142,48,.17);
        border: 1px solid rgba(190,142,48,.35);
    }

    .purchases-page .status-partially_received {
        color: #a9c9f5;
        background: rgba(65,115,180,.17);
        border: 1px solid rgba(65,115,180,.35);
    }

    .purchases-page .status-received {
        color: #91d8ad;
        background: rgba(46,139,87,.17);
        border: 1px solid rgba(46,139,87,.35);
    }

    .purchases-page .status-cancelled {
        color: #f0a0a0;
        background: rgba(180,65,65,.17);
        border: 1px solid rgba(180,65,65,.35);
    }

    .purchases-page .status-default {
        color: #c3cbd6;
        background: rgba(120,135,155,.17);
        border: 1px solid rgba(120,135,155,.35);
    }

    .purchases-page .view-link {
        display: inline-block;
        background: rgba(198,166,107,.12);
        border: 1px solid rgba(198,166,107,.4);
        color: #e2c58e;
        padding: 7px 12px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .purchases-page .view-link:hover {
        background: #c6a66b;
        color: #111827;
    }

    .purchases-page .empty-state {
        color: #aab6c5;
        padding: 38px 15px;
        text-align: center;
    }

    .purchases-page .pagination {
        margin-bottom: 0;
    }

    .purchases-page .page-link {
        background: #0b1422;
        border-color: #34445a;
        color: #dce4ed;
    }

    .purchases-page .page-item.active .page-link {
        background: #c6a66b;
        border-color: #c6a66b;
        color: #111827;
    }

    .purchases-page .page-item.disabled .page-link {
        background: #111c2c;
        color: #748399;
        border-color: #26364a;
    }

    .purchases-page .alert-success {
        background: rgba(46,139,87,.16);
        color: #a7e2bb;
        border: 1px solid rgba(46,139,87,.35);
    }

    @media (max-width: 767px) {
        .purchases-page .page-title {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')
<div class="purchases-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1">Vendor Purchases</h2>
            <p class="page-description mb-0">
                Manage procurement orders and track goods received from approved vendors.
            </p>
        </div>

        <a href="{{ route('admin.purchases.create') }}" class="btn btn-gold">
            + Create Purchase
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search and status filters --}}
    <div class="purchase-card p-3 p-lg-4 mb-4">
        <form method="GET" action="{{ route('admin.purchases.index') }}">
            <div class="row g-3">

                <div class="col-md-5">
                    <label for="purchase-search" class="form-label">
                        Search purchases
                    </label>

                    <input
                        id="purchase-search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Purchase number, vendor or product"
                    >
                </div>

                <div class="col-md-3">
                    <label for="purchase-status" class="form-label">
                        Purchase status
                    </label>

                    <select
                        id="purchase-status"
                        name="status"
                        class="form-select"
                    >
                        <option value="">All statuses</option>

                        @foreach(['ordered', 'partially_received', 'received', 'cancelled'] as $status)
                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-gold">
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('admin.purchases.index') }}"
                        class="btn btn-outline-muted"
                    >
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Purchases table --}}
    <div class="purchase-card overflow-hidden">

        <div class="p-3 p-lg-4 border-bottom" style="border-color:#26364a !important;">
            <h5 class="mb-1 fw-bold">Procurement Records</h5>
            <p class="muted small mb-0">
                Purchase orders, vendor details, quantities and receiving status.
            </p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Purchase No.</th>
                        <th>Vendor</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchases as $purchase)
                        @php
                            $status = $purchase->status ?? 'unknown';

                            $statusClass = [
                                'ordered' => 'status-ordered',
                                'partially_received' => 'status-partially_received',
                                'received' => 'status-received',
                                'cancelled' => 'status-cancelled',
                            ][$status] ?? 'status-default';
                        @endphp

                        <tr>
                            <td class="ps-3">
                                <div class="purchase-number">
                                    {{ $purchase->purchase_number }}
                                </div>
                            </td>

                            <td>
                                <div class="primary-text">
                                    {{ $purchase->vendorProfile->business_name ?? 'Unknown Vendor' }}
                                </div>
                            </td>

                            <td>
                                <div class="primary-text">
                                    {{ $purchase->product->name ?? 'Product unavailable' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                {{ number_format((float) $purchase->quantity, 3) }}
                                {{ $purchase->product->unit ?? '' }}
                            </td>

                            <td class="text-nowrap fw-semibold">
                                ₹{{ number_format((float) $purchase->subtotal, 2) }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                {{ $purchase->purchase_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="text-end pe-3">
                                <a
                                    href="{{ route('admin.purchases.show', $purchase) }}"
                                    class="view-link"
                                >
                                    View Details
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="fw-semibold mb-1">
                                        No vendor purchases found
                                    </div>
                                    <div class="small">
                                        Create a purchase or adjust your search filters.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-3 p-lg-4 border-top" style="border-color:#26364a !important;">
                {{ $purchases->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
