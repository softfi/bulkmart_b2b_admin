
@extends('layouts.admin')

@section('title', 'Vendor Marketplace')
@section('active', 'marketplace')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ];
@endphp

<style>
    .market-page {
        color: #e8edf5;
    }

    .market-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .market-heading h1 {
        margin: 0;
        color: #f4f6fa;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .market-heading p {
        margin: 7px 0 0;
        color: #9ba9bc;
        font-size: 13px;
    }

    .market-heading .btn {
        background: #d6b574;
        border-color: #d6b574;
        color: #101827;
        font-weight: 750;
    }

    .market-heading .btn:hover {
        background: #e5c98f;
        border-color: #e5c98f;
    }

    .market-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .market-stat {
        background: #111c2d;
        border: 1px solid #2b3b51;
        border-radius: 13px;
        padding: 17px 18px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, .12);
    }

    .market-stat small {
        display: block;
        color: #9ba9bc;
        font-size: 11px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .7px;
    }

    .market-stat strong {
        display: block;
        margin-top: 8px;
        color: #e5c98f;
        font-size: 25px;
        font-weight: 850;
    }

    .market-filter {
        background: #111c2d;
        border: 1px solid #2b3b51;
        border-radius: 13px;
        padding: 18px;
        margin-bottom: 17px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, .10);
    }

    .market-filter label {
        display: block;
        margin-bottom: 6px;
        color: #c3cedd;
        font-size: 11px;
        font-weight: 750;
    }

    .market-filter .form-control,
    .market-filter .form-select {
        min-height: 41px;
        background-color: #0b1422;
        border: 1px solid #3a4a60;
        border-radius: 7px;
        color: #edf2f8;
        font-size: 13px;
    }

    .market-filter .form-control::placeholder {
        color: #7f8da1;
    }

    .market-filter .form-control:focus,
    .market-filter .form-select:focus {
        background-color: #0b1422;
        border-color: #d6b574;
        color: #fff;
        box-shadow: 0 0 0 .2rem rgba(214, 181, 116, .12);
    }

    .market-filter .form-select option {
        background: #111c2d;
        color: #edf2f8;
    }

    .market-filter .btn-dark {
        background: #d6b574;
        border-color: #d6b574;
        color: #101827;
        font-weight: 750;
    }

    .market-filter .btn-dark:hover {
        background: #e5c98f;
        border-color: #e5c98f;
    }

    .market-filter .btn-outline-secondary {
        border-color: #53647b;
        color: #c4cedc;
    }

    .market-filter .btn-outline-secondary:hover {
        background: #26364a;
        border-color: #718096;
        color: #fff;
    }

    
.market-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    background: #111c2d;
    border: 1px solid #2b3b51;
    border-radius: 13px;
    box-shadow: 0 5px 18px rgba(0, 0, 0, .10);
}

.market-table {
    width: 100%;
    min-width: 0;
    table-layout: fixed;
    border-collapse: collapse;
}

.market-table th {
    padding: 12px 8px;
    background: #19273a;
    color: #b2bfd0;
    font-size: 9px;
    font-weight: 850;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: normal;
    overflow-wrap: anywhere;
    border-bottom: 1px solid #35465c;
}

.market-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #29394e;
    vertical-align: middle;
    font-size: 11px;
    color: #d0d9e5;
    overflow-wrap: anywhere;
    word-break: normal;
}

.market-table th:nth-child(1),
.market-table td:nth-child(1) { width: 18%; }

.market-table th:nth-child(2),
.market-table td:nth-child(2) { width: 15%; }

.market-table th:nth-child(3),
.market-table td:nth-child(3) { width: 15%; }

.market-table th:nth-child(4),
.market-table td:nth-child(4) { width: 10%; }

.market-table th:nth-child(5),
.market-table td:nth-child(5) { width: 10%; }

.market-table th:nth-child(6),
.market-table td:nth-child(6) { width: 11%; }

.market-table th:nth-child(7),
.market-table td:nth-child(7) { width: 10%; }

.market-table th:nth-child(8),
.market-table td:nth-child(8) { width: 11%; }

.market-product {
    display: block;
    color: #f1f5f9;
    font-weight: 800;
    font-size: 12px;
    overflow-wrap: anywhere;
}

.market-sub {
    display: block;
    margin-top: 4px;
    color: #9ba9bc;
    font-size: 10px;
    overflow-wrap: anywhere;
}

.market-price {
    color: #e5c98f;
    font-size: 12px;
    font-weight: 850;
    white-space: normal;
}

.market-lowest {
    display: inline-block;
    margin-top: 5px;
    padding: 4px 6px;
    border-radius: 20px;
    background: #173c31;
    border: 1px solid #28634c;
    color: #8ce0b4;
    font-size: 8px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: .2px;
    white-space: normal;
}

.market-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    max-width: 100%;
    padding: 7px 8px;
    border: 1px solid #d6b574;
    border-radius: 7px;
    background: #d6b574;
    color: #101827;
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    white-space: normal;
    text-align: center;
    overflow-wrap: anywhere;
}

@media (max-width: 900px) {
    .market-table th,
    .market-table td {
        padding: 9px 5px;
        font-size: 10px;
    }

    .market-table {
        font-size: 10px;
    }
}


    .status-approved {
        background: #173c31;
        border: 1px solid #28634c;
        color: #8ce0b4;
    }

    .status-pending {
        background: #44351b;
        border: 1px solid #725a2a;
        color: #f0cd7e;
    }

    .status-rejected,
    .status-suspended {
        background: #472626;
        border: 1px solid #743d3d;
        color: #ffaaaa;
    }

    .market-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 11px;
        border: 1px solid #d6b574;
        border-radius: 7px;
        background: #d6b574;
        color: #101827;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
        transition: background .15s ease;
    }

    .market-action:hover {
        background: #e5c98f;
        border-color: #e5c98f;
        color: #101827;
    }

    .market-action.disabled {
        border-color: #3b4a5e;
        background: #253449;
        color: #aab7c8;
        pointer-events: none;
    }

    .market-empty {
        padding: 50px 20px;
        text-align: center;
        color: #9ba9bc;
        background: #111c2d;
    }

    .market-empty strong {
        display: block;
        margin-bottom: 7px;
        color: #f1f5f9;
        font-size: 15px;
    }

    .market-pagination {
        padding: 15px;
        background: #111c2d;
        border-top: 1px solid #29394e;
    }

    .market-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    .market-pagination .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .market-pagination .page-link {
        background: #19273a;
        border-color: #35465c;
        color: #d5deea;
    }

    .market-pagination .page-item.active .page-link {
        background: #d6b574;
        border-color: #d6b574;
        color: #101827;
    }

    .market-pagination .page-item.disabled .page-link {
        background: #111c2d;
        border-color: #29394e;
        color: #718096;
    }

    .market-note {
        margin: 0 0 15px;
        padding: 12px 15px;
        border: 1px solid #35465c;
        border-radius: 9px;
        background: #19273a;
        color: #b9c5d4;
        font-size: 11px;
    }

    .market-note strong {
        color: #e5c98f;
    }

    @media(max-width: 900px) {
        .market-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 480px) {
        .market-stats {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .market-stat {
            padding: 13px;
        }

        .market-stat strong {
            font-size: 21px;
        }

        .market-heading h1 {
            font-size: 21px;
        }
    }
</style>

<div class="market-page">

    <div class="market-heading">
        <div>
            <h1>Vendor Product Marketplace</h1>
            <p>Review approved vendors' offers, compare wholesale prices and choose products for procurement.</p>
        </div>

        <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm px-3 py-2">
            View Vendor Purchases
        </a>
    </div>

    <div class="market-stats">
        <div class="market-stat">
            <small>Vendor Products</small>
            <strong>{{ number_format($summary['total_products']) }}</strong>
        </div>

        <div class="market-stat">
            <small>Approved Products</small>
            <strong>{{ number_format($summary['approved_products']) }}</strong>
        </div>

        <div class="market-stat">
            <small>Pending Products</small>
            <strong>{{ number_format($summary['pending_products']) }}</strong>
        </div>

        <div class="market-stat">
            <small>Approved Vendors</small>
            <strong>{{ number_format($summary['vendor_count']) }}</strong>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.marketplace.index') }}" class="market-filter">
        <div class="row g-3 align-items-end">

            <div class="col-lg-4 col-md-6">
                <label for="search">Search products, SKU, vendor</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    class="form-control"
                    placeholder="Product, vendor, category or SKU"
                    value="{{ $filters['search'] ?? '' }}"
                >
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="vendor">Vendor</label>
                <select name="vendor" id="vendor" class="form-select">
                    <option value="">All vendors</option>
                    @foreach($vendors as $vendor)
                        <option
                            value="{{ $vendor->id }}"
                            @selected(($filters['vendor'] ?? '') == $vendor->id)
                        >
                            {{ $vendor->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category }}"
                            @selected(($filters['category'] ?? '') === $category)
                        >
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="status">Product status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statusLabels as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(($filters['status'] ?? '') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="sort">Sort</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: low to high</option>
                    <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: high to low</option>
                </select>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dark btn-sm px-4">Apply Filters</button>
                <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary btn-sm px-4">Clear</a>
            </div>
        </div>
    </form>

    <p class="market-note">
        <strong>Price comparison:</strong>
        The lowest price badge compares approved offers for the same product name, category and variant attributes across approved vendors.
        It is a comparison reference only; the admin makes the final purchasing decision.
    </p>

    <div class="market-table-wrap ">
        @if($offers->count())
            <table class="market-table">
                <thead>
                    <tr>
                        <th>Product / Variant</th>
                        <th>Vendor</th>
                        <th>Wholesale Price</th>
                        <th>Available Qty</th>
                        <th>Minimum Order</th>
                        <th>Product Status</th>
                        <th>Listing Date</th>
                        <th>Procurement</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($offers as $offer)
                        <tr>
                            <td>
                                <span class="market-product">{{ $offer['product_name'] }}</span>
                                <span class="market-sub">{{ $offer['category'] ?: 'Uncategorised' }} · {{ $offer['unit'] }}</span>
                                <span class="market-sub">{{ $offer['variant_label'] }}</span>

                                @if($offer['variant_sku'])
                                    <span class="market-sub">SKU: {{ $offer['variant_sku'] }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="market-product">{{ $offer['vendor_name'] }}</span>
                                @if($offer['vendor_city'] || $offer['vendor_state'])
                                    <span class="market-sub">
                                        {{ collect([$offer['vendor_city'], $offer['vendor_state']])->filter()->implode(', ') }}
                                    </span>
                                @endif
                                @if($offer['vendor_phone'])
                                    <span class="market-sub">{{ $offer['vendor_phone'] }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="market-price">₹{{ number_format($offer['unit_price'], 2) }}</span>
                                <span class="market-sub">per {{ $offer['unit'] }}</span>

                                @if($offer['lowest_price'])
                                    <span class="market-lowest">Lowest comparable price</span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ number_format($offer['available_quantity'], 3) }}</strong>
                                <span class="market-sub">{{ $offer['unit'] }}</span>
                            </td>

                            <td>
                                <strong>{{ number_format($offer['minimum_order_quantity'], 3) }}</strong>
                                <span class="market-sub">{{ $offer['unit'] }}</span>
                            </td>

                            <td>
                                <span class="market-status status-{{ $offer['status'] }}">
                                    {{ $statusLabels[$offer['status']] ?? ucfirst($offer['status']) }}
                                </span>
                            </td>

                            <td>
                                {{ $offer['listing_date']?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                @if($offer['can_purchase'])
                                    <a
                                        href="{{ route('admin.purchases.create', [
                                            'product_id' => $offer['product_id'],
                                            'product_variant_id' => $offer['variant_id'],
                                        ]) }}"
                                        class="market-action"
                                    >
                                        Create Purchase
                                    </a>
                                @else
                                    <span class="market-action disabled">
                                        Not purchasable
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="market-pagination">
                {{ $offers->links() }}
            </div>
        @else
            <div class="market-empty">
                <strong>No vendor offers found</strong>
                Try changing your search or filters. Only products belonging to approved vendors appear in this marketplace.
            </div>
        @endif
    </div>
</div>
@endsection
