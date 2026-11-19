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

@if($purchase->vendor_response_status === 'pending' && $purchase->status === 'ordered')
    <div class="vendor-card p-4 mb-4">
        <h5 class="mb-2">Purchase Confirmation Required</h5>

        <p class="muted-text">
            The admin has requested the full quantity shown above.
            Please confirm whether you can fulfil this purchase.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <form method="POST"
                  action="{{ route('vendor.purchases.respond', $purchase) }}"
                  onsubmit="return confirm('Confirm that you can supply the full requested quantity?')">
                @csrf

                <input type="hidden" name="decision" value="accepted">

                <button type="submit" class="btn btn-success">
                    Accept Purchase
                </button>
            </form>

            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="collapse"
                    data-bs-target="#rejectPurchaseForm">
                Reject Purchase
            </button>
        </div>

        <div class="collapse mt-3" id="rejectPurchaseForm">
            <form method="POST"
                  action="{{ route('vendor.purchases.respond', $purchase) }}"
                  onsubmit="return confirm('Reject this purchase? The reserved quantity will be released.')">
                @csrf

                <input type="hidden" name="decision" value="rejected">

                <label class="form-label" for="rejectionReason">
                    Reason for rejection
                </label>

                <textarea
                    name="reason"
                    id="rejectionReason"
                    class="form-control"
                    rows="3"
                    minlength="5"
                    maxlength="2000"
                    required
                    placeholder="Explain why you cannot fulfil this purchase..."
                ></textarea>

                @error('reason')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-danger mt-3">
                    Confirm Rejection
                </button>
            </form>
        </div>
    </div>
@else
    <div class="vendor-card p-4 mb-4">
        <h5 class="mb-2">Vendor Response</h5>

        @if($purchase->vendor_response_status === 'accepted')
            <span class="badge bg-success">Accepted</span>
            <p class="muted-text mt-2 mb-0">
                You confirmed the full requested quantity
                @if($purchase->vendor_responded_at)
                    on {{ $purchase->vendor_responded_at->format('d M Y, h:i A') }}.
                @endif
            </p>
        @elseif($purchase->vendor_response_status === 'rejected')
            <span class="badge bg-danger">Rejected</span>
            <p class="muted-text mt-2 mb-1">Your rejection reason:</p>
            <p class="mb-0">{{ $purchase->vendor_response_reason }}</p>
        @elseif($purchase->status === 'cancelled')
            <span class="badge bg-secondary">Cancelled by admin</span>
            <p class="muted-text mt-2 mb-0">
                This purchase is no longer active.
            </p>
        @endif
    </div>
@endif

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