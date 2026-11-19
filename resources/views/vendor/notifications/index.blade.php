@extends('layouts.vendor')

@section('title', 'Notifications')

@section('content')
<div class="vendor-page-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1>Notifications</h1>
        <p class="muted-text mb-0">Updates about purchases and your vendor account.</p>
    </div>

    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('vendor.notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-secondary">
                Mark all as read
            </button>
        </form>
    @endif
</div>

<div class="vendor-card p-3 p-md-4">
    @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $purchaseId = $data['purchase_id'] ?? null;
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 py-3
                    {{ !$loop->last ? 'border-bottom' : '' }}"
             style="border-color: var(--vendor-border) !important;">

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <strong>{{ $data['purchase_number'] ?? 'Vendor notification' }}</strong>

                    @if(!$notification->read_at)
                        <span class="badge bg-warning text-dark">New</span>
                    @else
                        <span class="badge bg-secondary">Read</span>
                    @endif
                </div>

                <p class="mb-1">
                    {{ $data['message'] ?? 'You have a new notification.' }}
                </p>

                @if(!empty($data['product_name']))
                    <div class="muted-text small">
                        Product: {{ $data['product_name'] }}
                    </div>
                @endif

                @if(isset($data['quantity']))
                    <div class="muted-text small">
                        Quantity: {{ number_format((float) $data['quantity'], 3) }}
                    </div>
                @endif

                <div class="muted-text small mt-2">
                    {{ $notification->created_at?->format('d M Y, h:i A') }}
                </div>

                @if($purchaseId)
                    <a href="{{ route('vendor.purchases.show', $purchaseId) }}"
                       class="btn btn-sm btn-outline-secondary mt-2">
                        View purchase
                    </a>
                @endif
            </div>

            @if(!$notification->read_at)
                <form method="POST"
                      action="{{ route('vendor.notifications.read', $notification->id) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Mark as read
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="text-center py-5">
            <h5>No notifications</h5>
            <p class="muted-text mb-0">New purchase updates will appear here.</p>
        </div>
    @endforelse

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection