@extends('layouts.vendor')

@section('title', 'Vendor Purchases')

@section('content')
<div class="vendor-page-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1>Purchases</h1>
        <p class="muted-text mb-0">Purchase orders placed by the admin for your products.</p>
    </div>
</div>

<div class="vendor-card p-3 p-md-4">
    @if($purchases->isEmpty())
        <div class="text-center py-5">
            <h5>No purchases yet</h5>
            <p class="muted-text mb-0">New admin purchase orders will appear here.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Purchase</th>
                        <th>Product</th>
                        <th>Ordered</th>
                        <th>Received</th>
                        <th>Purchase date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                        @php
                            $received = $purchase->goodsReceipts
                                ->flatMap(fn ($receipt) => $receipt->items)
                                ->sum(fn ($item) => (float) $item->quantity_received);

                            $statusClass = match($purchase->status) {
                                'received' => 'success',
                                'partially_received' => 'warning text-dark',
                                'cancelled' => 'danger',
                                default => 'info text-dark',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $purchase->purchase_number }}</strong>
                            </td>
                            <td>
                                {{ $purchase->product?->name ?? 'Product unavailable' }}
                                @if($purchase->productVariant)
                                    <div class="small muted-text">
                                        Variant: {{ $purchase->productVariant->sku }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ number_format((float) $purchase->quantity, 3) }}</td>
                            <td>{{ number_format($received, 3) }}</td>
                            <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucwords(str_replace('_', ' ', $purchase->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendor.purchases.show', $purchase) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $purchases->links() }}
        </div>
    @endif
</div>
@endsection