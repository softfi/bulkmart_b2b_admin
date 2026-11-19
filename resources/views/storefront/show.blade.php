
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $listing->display_name ?: $product->name }} | Wholesale Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #171512;
            --ink-soft: #211e19;
            --cream: #f3efe7;
            --paper: #fffdf8;
            --muted: #82796d;
            --gold: #b99560;
            --gold-light: #dfc797;
            --line: #ded5c7;
            --green: #718357;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--ink);
            font-family: 'DM Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; }

        .announcement {
            background: #100f0d;
            color: #d6c6a9;
            text-align: center;
            padding: 9px 15px;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .announcement span { color: var(--gold-light); }

        .store-nav {
            background: var(--ink);
            border-bottom: 1px solid rgba(197, 164, 109, .22);
        }

        .nav-inner {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .brand {
            color: #f8f5ef;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border: 1px solid var(--gold);
            color: var(--gold-light);
            display: grid;
            place-items: center;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
        }

        .brand-name {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            line-height: 1.1;
        }

        .brand-caption {
            display: block;
            color: #a99d8b;
            font-size: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .nav-actions {
            display: flex;
            gap: 9px;
            align-items: center;
        }

        .nav-link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            color: #e9e1d4;
            border: 1px solid #4a4338;
            background: transparent;
            font-size: 10px;
            letter-spacing: .6px;
            transition: .2s ease;
            white-space: nowrap;
        }

        .nav-link-btn:hover {
            color: var(--gold-light);
            border-color: var(--gold);
        }

        .nav-link-btn.gold {
            background: var(--gold);
            border-color: var(--gold);
            color: #211a10;
            font-weight: 700;
        }

        .nav-link-btn.gold:hover {
            background: var(--gold-light);
            color: #17130e;
            transform: translateY(-1px);
        }

        .page-wrap { padding-top: 32px; }

        .breadcrumb {
            margin-bottom: 25px;
            font-size: 10px;
            letter-spacing: .4px;
        }

        .breadcrumb a { color: #947346; }
        .breadcrumb-item.active { color: #84796b; }
        .breadcrumb-item + .breadcrumb-item::before { color: #a99c89; }

        .product-panel,
        .info-panel {
            background: var(--paper);
            border: 1px solid var(--line);
            box-shadow: 0 8px 25px rgba(40, 31, 18, .035);
        }

        .gallery-panel {
            padding: 14px;
        }

        .product-image {
            min-height: 490px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background:
                radial-gradient(ellipse at center, #f8f3e9 0%, #e8dfd0 100%);
        }

        .product-image img {
            display: block;
            width: 100%;
            height: 490px;
            object-fit: contain;
            transition: transform .5s ease;
        }

        .product-image:hover img { transform: scale(1.035); }

        .image-fallback {
            color: #a58a60;
            font-family: 'Playfair Display', serif;
            font-size: 85px;
        }

        .gallery-caption {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 13px 5px 2px;
            color: #8c8172;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .category-label {
            color: #9b7949;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .product-title {
            color: #211d17;
            font-family: 'Playfair Display', serif;
            font-size: clamp(31px, 4vw, 43px);
            font-weight: 600;
            letter-spacing: -.5px;
            line-height: 1.15;
        }

        .description {
            color: #81776a;
            font-size: 12px;
            line-height: 1.9;
        }

        .gold-rule {
            width: 52px;
            height: 2px;
            background: var(--gold);
            margin: 21px 0;
        }

        .price-caption {
            color: #82796d;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .price-highlight {
            color: #927044;
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 600;
            line-height: 1.3;
        }

        .price-highlight span {
            color: #82796d;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 400;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 25px 0;
        }

        .info-tile {
            padding: 17px;
            background: #f0eadf;
            border: 1px solid #e0d6c7;
        }

        .info-tile small {
            display: block;
            color: #82796d;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .info-tile strong {
            display: block;
            color: #28231c;
            font-size: 16px;
            font-weight: 700;
        }

        .stock-status {
            color: var(--green) !important;
        }

        .detail-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid #e6ded2;
            padding: 13px 0;
            color: #82796d;
            font-size: 11px;
        }

        .detail-line strong {
            color: #383127;
            text-align: right;
        }

        .section-title {
            color: #211d17;
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            font-weight: 600;
        }

        .section-subtitle {
            color: #82796d;
            font-size: 11px;
            line-height: 1.7;
        }

        .tier-table {
            margin: 0;
            --bs-table-bg: transparent;
            --bs-table-color: #393329;
            --bs-table-border-color: #e5ddd0;
        }

        .tier-table th {
            background: #211e19;
            color: #dfc797;
            border-color: #494033;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .tier-table td,
        .tier-table th {
            padding: 15px 13px;
            vertical-align: middle;
        }

        .tier-table td { font-size: 11px; }

        .tier-table tbody tr:hover { background: #f4eee4; }

        .tier-price {
            color: #8d6a3c;
            font-weight: 700;
        }

        .estimate-panel {
            background: #211e19;
            color: #f4eee3;
            border: 1px solid #3a3329;
            padding: 24px;
        }

        .estimate-heading {
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            color: #f4eee3;
        }

        .estimate-copy {
            color: #b6aa98;
            font-size: 10px;
            line-height: 1.7;
        }

        .estimate-panel .form-label {
            color: #dfc797;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .estimate-panel .form-control {
            height: 49px;
            border-radius: 0;
            border: 1px solid #5a4e3b;
            background: #302b23;
            color: #fff8eb;
            font-size: 16px;
            box-shadow: none;
        }

        .estimate-panel .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(197, 164, 109, .12);
        }

        .estimate-panel .form-text {
            color: #a99d8b;
            font-size: 9px;
            line-height: 1.7;
        }

        .estimate-result {
            background: #302b23;
            border: 1px solid #514431;
            padding: 17px;
            margin-top: 20px;
        }

        .estimate-result .result-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #c0b4a2;
            font-size: 11px;
            margin-bottom: 12px;
        }

        .estimate-result .result-line:last-child { margin-bottom: 0; }

        .estimate-result strong {
            color: #f6efe3;
            font-weight: 700;
        }

        .estimate-total {
            padding-top: 13px;
            border-top: 1px solid #554936;
        }

        .estimate-total strong {
            color: var(--gold-light);
            font-family: 'Playfair Display', serif;
            font-size: 22px;
        }

        .estimate-panel .alert-danger {
            background: #4a2924;
            color: #f1d4cc;
            border: 1px solid #71443a;
            border-radius: 0;
            font-size: 10px;
        }

        .fine-print {
            color: #a99d8b;
            font-size: 9px;
            line-height: 1.8;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #725b3b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: .2s ease;
        }

        .back-link:hover { color: #a17d49; gap: 13px; }

        .store-footer {
            background: #171512;
            color: #a99d8b;
            margin-top: 70px;
            padding: 35px 0 22px;
            border-top: 2px solid #9b7949;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            color: #f4eee3;
            font-size: 20px;
        }

        .footer-bottom {
            border-top: 1px solid #3a3329;
            margin-top: 23px;
            padding-top: 16px;
            color: #817668;
            font-size: 9px;
            letter-spacing: 1px;
        }

        @media (max-width: 991px) {
            .product-image,
            .product-image img { min-height: 380px; height: 380px; }
        }

        @media (max-width: 575px) {
            .announcement { font-size: 8px; letter-spacing: 1px; }
            .nav-inner { min-height: 70px; flex-wrap: wrap; padding: 12px 0; }
            .nav-actions { gap: 6px; }
            .nav-link-btn { min-height: 34px; padding: 0 9px; font-size: 9px; }
            .page-wrap { padding-top: 20px; }
            .product-image,
            .product-image img { min-height: 290px; height: 290px; }
            .info-grid { grid-template-columns: 1fr 1fr; }
            .estimate-panel { padding: 19px; }
            .section-title { font-size: 22px; }
        }
    </style>
</head>

<body>

<div class="announcement">
    Wholesale sourcing, considered differently <span>·</span> Transparent quantity pricing
</div>

<header class="store-nav">
    <div class="container nav-inner">
        <a class="brand" href="{{ route('storefront.index') }}">
            <span class="brand-mark">W</span>
            <span>
                <span class="brand-name">Wholesale Store</span>
                <span class="brand-caption">Trade · Supply · Quality</span>
            </span>
        </a>

        <div class="nav-actions">
            @if(auth()->check() && auth()->user()->role === 'customer')
                <a href="{{ route('customer.account') }}" class="nav-link-btn">My Account</a>
            @else
                <a href="{{ route('customer.login') }}" class="nav-link-btn gold">Customer Sign In</a>
            @endif

            <a href="{{ route('storefront.index') }}" class="nav-link-btn">Back to shop</a>
        </div>
    </div>
</header>

<main class="container page-wrap">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('storefront.index') }}">Shop</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $listing->display_name ?: $product->name }}
            </li>
        </ol>
    </nav>

    <div class="row g-4 align-items-stretch">

        <div class="col-lg-6">
            <section class="product-panel gallery-panel h-100">
                <div class="product-image">
                    @if(!empty($listing->image_path) || !empty($product->image_path))
                        <img
                            src="{{ route('storefront.products.image', $listing->product_id) }}"
                            alt="{{ $listing->display_name ?: $product->name }}"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                        >
                        <span class="image-fallback" style="display:none;">W.</span>
                    @else
                        <span class="image-fallback">W.</span>
                    @endif
                </div>

                <div class="gallery-caption">
                    <span>Wholesale collection</span>
                    <span>Product detail</span>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="product-panel p-4 p-lg-5 h-100">

                <div class="category-label mb-3">
                    {{ $product->category ?: 'Selected product' }}
                </div>

                <h1 class="product-title mb-3">
                    {{ $listing->display_name ?: $product->name }}
                </h1>

                @if($listing->customer_description)
                    <p class="description mb-0">{{ $listing->customer_description }}</p>
                @elseif($product->description)
                    <p class="description mb-0">{{ $product->description }}</p>
                @endif

                <div class="gold-rule"></div>

                <div class="price-caption mb-2">Starting wholesale price</div>

                @if($listing->priceTiers->first())
                    <div class="price-highlight">
                        ₹{{ number_format((float) $listing->priceTiers->first()->unit_price, 2) }}
                        <span>/ {{ $product->unit }}</span>
                    </div>
                    <p class="fine-print mt-1 mb-0">
                        Final unit price depends on the selected order quantity.
                    </p>
                @else
                    <div class="price-highlight">Price unavailable</div>
                @endif

                <div class="info-grid">
                    <div class="info-tile">
                        <small>Available inventory</small>
                        <strong class="stock-status">
                            {{ number_format((float) $stockRemaining, 3) }}
                            {{ $product->unit }}
                        </strong>
                    </div>
                    <div class="info-tile">
                        <small>Minimum order</small>
                        <strong>
                            {{ number_format((float) $listing->minimum_order_quantity, 3) }}
                            {{ $product->unit }}
                        </strong>
                    </div>
                </div>

                <div class="detail-line">
                    <span>Product unit</span>
                    <strong>{{ $product->unit }}</strong>
                </div>
                <div class="detail-line">
                    <span>Product category</span>
                    <strong>{{ $product->category ?: 'General' }}</strong>
                </div>
                <div class="detail-line">
                    <span>Pricing structure</span>
                    <strong>Quantity-based wholesale</strong>
                </div>

            </section>
        </div>
    </div>

    <div class="row g-4 mt-2">

        <div class="col-lg-7">
            <section class="info-panel p-4 p-lg-5 h-100">
                <div class="category-label mb-2">Wholesale pricing</div>
                <h2 class="section-title mb-2">Quantity-based pricing</h2>
                <p class="section-subtitle mb-4">
                    Larger quantities may qualify for different unit prices.
                    Review the available price breaks below.
                </p>

                <div class="table-responsive">
                    <table class="table tier-table align-middle">
                        <thead>
                            <tr>
                                <th>Minimum quantity</th>
                                <th>Maximum quantity</th>
                                <th>Price per {{ $product->unit }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listing->priceTiers as $tier)
                                <tr>
                                    <td>
                                        {{ number_format((float) $tier->minimum_quantity, 3) }}
                                        {{ $product->unit }}
                                    </td>
                                    <td>
                                        @if($tier->maximum_quantity !== null)
                                            {{ number_format((float) $tier->maximum_quantity, 3) }}
                                            {{ $product->unit }}
                                        @else
                                            No upper limit
                                        @endif
                                    </td>
                                    <td class="tier-price">
                                        ₹{{ number_format((float) $tier->unit_price, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        Pricing information is not available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="estimate-panel h-100">
                <div class="category-label mb-2">Plan your purchase</div>
                <h2 class="estimate-heading mb-2">Order estimate</h2>
                <p class="estimate-copy mb-4">
                    Enter your required quantity to calculate the applicable
                    wholesale price and estimated subtotal.
                </p>

                <div class="mb-3">
                    <label for="quantity" class="form-label">
                        Order quantity · {{ $product->unit }}
                    </label>

                    <input
                        type="number"
                        id="quantity"
                        class="form-control"
                        min="{{ $listing->minimum_order_quantity }}"
                        max="{{ $stockRemaining }}"
                        step="0.001"
                        value="{{ $listing->minimum_order_quantity }}"
                        required
                    >

                    <div class="form-text">
                        Minimum order:
                        {{ number_format((float) $listing->minimum_order_quantity, 3) }}
                        {{ $product->unit }}.
                        Available:
                        {{ number_format((float) $stockRemaining, 3) }}
                        {{ $product->unit }}.
                    </div>
                </div>

                <div id="quantity-error"
                     class="alert alert-danger py-2 d-none"
                     role="alert"></div>

                <div class="estimate-result">
                    <div class="result-line">
                        <span>Applicable unit price</span>
                        <strong id="estimated-unit-price">—</strong>
                    </div>

                    <div class="result-line estimate-total">
                        <span>Estimated subtotal</span>
                        <strong id="estimated-subtotal">—</strong>
                    </div>
                </div>

                <p class="fine-print mt-3 mb-0">
                    This is an estimate only. Inventory and applicable pricing
                    will be validated again when an order is submitted.
                </p>
            </section>
        </div>

    </div>

    <div class="mt-4">
        <a href="{{ route('storefront.index') }}" class="back-link">
            <span>←</span> Continue browsing
        </a>
    </div>

</main>

<footer class="store-footer">
    <div class="container">
        <div class="footer-brand">Wholesale Store</div>
        <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2">
            <span>WHOLESALE STORE</span>
            <span>BUSINESS-TO-BUSINESS MARKETPLACE</span>
        </div>
    </div>
</footer>

@php
    $pricingTiers = $listing->priceTiers->map(function ($tier) {
        return [
            'minimum' => (float) $tier->minimum_quantity,
            'maximum' => $tier->maximum_quantity === null
                ? null
                : (float) $tier->maximum_quantity,
            'price' => (float) $tier->unit_price,
        ];
    })->values()->all();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInput = document.getElementById('quantity');
    const errorBox = document.getElementById('quantity-error');
    const unitPriceOutput = document.getElementById('estimated-unit-price');
    const subtotalOutput = document.getElementById('estimated-subtotal');

    const minimumQuantity = Number(@json((float) $listing->minimum_order_quantity));
    const availableStock = Number(@json($stockRemaining));
    const tiers = @json($pricingTiers);

    const currency = new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function updateEstimate() {
        const quantity = Number(quantityInput.value);

        errorBox.classList.add('d-none');
        errorBox.textContent = '';

        unitPriceOutput.textContent = '—';
        subtotalOutput.textContent = '—';

        if (
            !Number.isFinite(quantity) ||
            quantity <= 0 ||
            quantity < minimumQuantity ||
            quantity > availableStock
        ) {
            errorBox.textContent = quantity > availableStock
                ? 'The requested quantity exceeds available inventory.'
                : 'Enter a quantity equal to or greater than the minimum order quantity.';

            errorBox.classList.remove('d-none');
            return;
        }

        const matchingTier = tiers.find(function (tier) {
            return quantity >= tier.minimum &&
                (tier.maximum === null || quantity <= tier.maximum);
        });

        if (!matchingTier) {
            errorBox.textContent = 'No price tier applies to this quantity. Please contact the administrator.';
            errorBox.classList.remove('d-none');
            return;
        }

        const subtotal = quantity * matchingTier.price;

        unitPriceOutput.textContent = currency.format(matchingTier.price);
        subtotalOutput.textContent = currency.format(subtotal);
    }

    quantityInput.addEventListener('input', updateEstimate);
    updateEstimate();
});
</script>

</body>
</html>
