@extends('layouts.vendor')

@section('title', 'Purchase Details')

@section('content')
<div class="vendor-page-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1>Purchase Details</h1>
        <p class="muted-text mb-0">{{ $purchase->purchase_number }}</p>
    </div>

    <a href="{{ route('vendor.purchases.index') }}"
       class="btn btn-outline-secondary">
        Back to purchases
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="vendor-card p-4 h-100">
            <div class="muted-text small mb-2">Purchase information</div>
            <h5>{{ $purchase->product?->name ?? 'Product unavailable' }}</h5>

            @if($purchase->productVariant)
                <p class="muted-text mb-2">
                    Variant SKU: {{ $purchase->productVariant->sku }}
                </p>
            @endif

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span class="muted-text">Purchase number</span>
                <strong>{{ $purchase->purchase_number }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="muted-text">Purchase date</span>
                <strong>{{ $purchase->purchase_date?->format('d M Y') }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="muted-text">Expected delivery</span>
                <strong>
                    {{ $purchase->expected_delivery_date?->format('d M Y') ?? 'Not specified' }}
                </strong>
            </div>

            <div class="d-flex justify-content-between">
                <span class="muted-text">Purchase status</span>
                <strong>{{ ucwords(str_replace('_', ' ', $purchase->status)) }}</strong>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="vendor-card p-4 h-100">
            <div class="muted-text small mb-2">Quantity and payment summary</div>

            <div class="d-flex justify-content-between mb-3">
                <span class="muted-text">Ordered quantity</span>
                <strong>{{ number_format((float) $purchase->quantity, 3) }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <span class="muted-text">Received quantity</span>
                <strong>{{ number_format($receivedQuantity, 3) }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <span class="muted-text">Remaining quantity</span>
                <strong>{{ number_format($remainingQuantity, 3) }}</strong>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span class="muted-text">Unit price</span>
                <strong>₹{{ number_format((float) $purchase->unit_price, 2) }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="muted-text">Subtotal</span>
                <strong>₹{{ number_format((float) $purchase->subtotal, 2) }}</strong>
            </div>

            <div class="d-flex justify-content-between">
                <span class="muted-text">Payment status</span>
                <strong>{{ ucwords(str_replace('_', ' ', $purchase->payment_status)) }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="vendor-card p-4 mb-4">
    <h5 class="mb-3">Goods receiving history</h5>

    @if($purchase->goodsReceipts->isEmpty())
        <p class="muted-text mb-0">No goods have been recorded as received yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Receipt number</th>
                        <th>Received date</th>
                        <th>Quantity received</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->goodsReceipts as $receipt)
                        @foreach($receipt->items as $item)
                            <tr>
                                <td>{{ $receipt->receipt_number }}</td>
                                <td>{{ $receipt->received_at?->format('d M Y') ?? '—' }}</td>
                                <td>{{ number_format((float) $item->quantity_received, 3) }}</td>
                                <td>{{ $receipt->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="vendor-card p-4">
    <h5 class="mb-3">Purchase status history</h5>

    @if($purchase->statusHistories->isEmpty())
        <p class="muted-text mb-0">No status history is available.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Previous status</th>
                        <th>New status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->statusHistories as $history)
                        <tr>
                            <td>{{ $history->created_at?->format('d M Y, h:i A') }}</td>
                            <td>
                                {{ $history->previous_status
                                    ? ucwords(str_replace('_', ' ', $history->previous_status))
                                    : '—' }}
                            </td>
                            <td>
                                {{ ucwords(str_replace('_', ' ', $history->new_status)) }}
                            </td>
                            <td>{{ $history->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection