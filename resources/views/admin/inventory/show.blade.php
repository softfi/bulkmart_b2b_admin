
@extends('layouts.admin')

@section('title', 'Inventory Details')
@section('active', 'inventory')

@section('content')
@php
    $totalReceived = (float) ($product->total_received_quantity ?? 0);
    $totalRemaining = (float) ($product->total_remaining_quantity ?? 0);
    $totalDepletedOrAdjusted = max(0, $totalReceived - $totalRemaining);

    $adjustments = $product->inventoryBatches
        ->flatMap(fn ($batch) => $batch->adjustments)
        ->sortByDesc('created_at');
@endphp

<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="text-decoration-none">
                ← Back to inventory
            </a>

            <h2 class="fw-bold mt-3 mb-1">{{ $product->name }}</h2>

            <p class="text-muted mb-0">
                {{ $product->vendorProfile?->business_name ?? 'Vendor unavailable' }}
                · {{ $product->category ?: 'Uncategorised' }}
                · Unit: {{ $product->unit }}
            </p>

            @if($product->trashed())
                <span class="badge bg-secondary mt-2">Archived product</span>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

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

{{-- Inventory summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">Total received</div>
                <div class="fs-3 fw-bold">
                    {{ number_format($totalReceived, 3) }}
                </div>
                <div class="small text-muted">{{ $product->unit }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">Remaining stock</div>
                <div class="fs-3 fw-bold text-success">
                    {{ number_format($totalRemaining, 3) }}
                </div>
                <div class="small text-muted">{{ $product->unit }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">Depleted or adjusted</div>
                <div class="fs-3 fw-bold">
                    {{ number_format($totalDepletedOrAdjusted, 3) }}
                </div>
                <div class="small text-muted">{{ $product->unit }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Inventory batches --}}
<div class="card border-0 mb-4">
    <div class="card-header py-3">
        <h5 class="mb-1 fw-bold">Inventory batches</h5>
        <div class="small text-muted">
            Each batch represents stock recorded through a goods receipt.
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Received date</th>
                    <th>Receipt number</th>
                    <th>Purchase number</th>
                    <th class="text-end">Unit cost</th>
                    <th class="text-end">Received</th>
                    <th class="text-end">Remaining</th>
                    <th style="min-width: 280px;">Stock adjustment</th>
                </tr>
            </thead>

            <tbody>
                @forelse($product->inventoryBatches as $batch)
                    <tr>
                        <td>
                            {{ $batch->received_at?->format('d M Y, h:i A') ?? '—' }}
                        </td>

                        <td>
                            <span class="fw-semibold">
                                {{ $batch->receiptItem?->goodsReceipt?->receipt_number ?? '—' }}
                            </span>
                        </td>

                        <td>
                            {{ $batch->vendorPurchase?->purchase_number ?? '—' }}
                        </td>

                        <td class="text-end">
                            ₹{{ number_format((float) $batch->unit_cost, 2) }}
                        </td>

                        <td class="text-end">
                            {{ number_format((float) $batch->quantity_received, 3) }}
                            {{ $product->unit }}
                        </td>

                        <td class="text-end fw-semibold">
                            {{ number_format((float) $batch->quantity_remaining, 3) }}
                            {{ $product->unit }}
                        </td>

                        <td>
                            <form method="POST"
                                  action="{{ route('admin.inventory.adjustments.store', $batch) }}">
                                @csrf

                                <select name="adjustment_type"
                                        class="form-select form-select-sm mb-2"
                                        required>
                                    <option value="decrease">Decrease stock</option>
                                    <option value="increase">Increase stock</option>
                                </select>

                                <input
                                    type="number"
                                    name="quantity"
                                    class="form-control form-control-sm mb-2"
                                    min="0.001"
                                    max="999999999"
                                    step="0.001"
                                    placeholder="Quantity"
                                    required
                                >

                                <input
                                    type="text"
                                    name="reason"
                                    class="form-control form-control-sm mb-2"
                                    maxlength="2000"
                                    placeholder="Reason for adjustment"
                                    required
                                >

                                <button type="submit"
                                        class="btn btn-sm btn-outline-primary w-100"
                                        onclick="return confirm('Are you sure you want to adjust this inventory batch?')">
                                    Record adjustment
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No inventory batches found for this product.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Adjustment audit history --}}
<div class="card border-0">
    <div class="card-header py-3">
        <h5 class="mb-1 fw-bold">Adjustment audit history</h5>
        <div class="small text-muted">
            Complete history of manual stock increases and decreases.
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Adjustment type</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Before</th>
                    <th class="text-end">After</th>
                    <th>Administrator</th>
                    <th>Reason</th>
                </tr>
            </thead>

            <tbody>
                @forelse($adjustments as $adjustment)
                    <tr>
                        <td>
                            {{ $adjustment->created_at?->format('d M Y, h:i A') ?? '—' }}
                        </td>

                        <td>#{{ $adjustment->inventory_batch_id }}</td>

                        <td>
                            @if($adjustment->adjustment_type === 'increase')
                                <span class="badge bg-success">Increase</span>
                            @else
                                <span class="badge bg-warning">Decrease</span>
                            @endif
                        </td>

                        <td class="text-end">
                            {{ number_format((float) $adjustment->quantity, 3) }}
                            {{ $product->unit }}
                        </td>

                        <td class="text-end">
                            {{ number_format((float) $adjustment->quantity_before, 3) }}
                        </td>

                        <td class="text-end">
                            {{ number_format((float) $adjustment->quantity_after, 3) }}
                        </td>

                        <td>
                            {{ $adjustment->administrator?->name ?? 'Unknown' }}
                        </td>

                        <td>{{ $adjustment->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No stock adjustments recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
