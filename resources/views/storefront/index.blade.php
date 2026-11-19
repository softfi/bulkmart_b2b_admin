
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wholesale Store | Curated Business Essentials</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #171512;
            --ink-soft: #211e19;
            --ink-card: #29251f;
            --cream: #f3efe7;
            --paper: #eae4d9;
            --muted: #a49a8b;
            --gold: #c5a46d;
            --gold-light: #dfc797;
            --line: rgba(197, 164, 109, .22);
            --white: #f8f5ef;
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
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .announcement span { color: var(--gold-light); }

        .store-nav {
            background: var(--ink);
            border-bottom: 1px solid var(--line);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-inner {
            min-height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .brand {
            color: var(--white);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 190px;
        }

        .brand-mark {
            width: 39px;
            height: 39px;
            border: 1px solid var(--gold);
            color: var(--gold-light);
            display: grid;
            place-items: center;
            font-family: 'Playfair Display', serif;
            font-size: 23px;
        }

        .brand-name {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            line-height: 1.1;
            letter-spacing: .3px;
        }

        .brand-caption {
            display: block;
            color: #a99d8b;
            font-size: 8px;
            letter-spacing: 2.3px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .nav-search {
            display: flex;
            flex: 1;
            max-width: 550px;
            height: 43px;
            border: 1px solid #494238;
            background: #211e19;
        }

        .nav-search select {
            width: 145px;
            min-width: 120px;
            border: 0;
            border-right: 1px solid #494238;
            outline: 0;
            background: #29251f;
            color: var(--gold-light);
            padding: 0 10px;
            font-size: 11px;
            cursor: pointer;
        }

        .nav-search input {
            min-width: 0;
            flex: 1;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--white);
            padding: 0 15px;
            font-size: 13px;
        }

        .nav-search input::placeholder { color: #948a7d; }

        .nav-search button {
            border: 0;
            background: var(--gold);
            color: #211a10;
            padding: 0 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: background .2s ease;
        }

        .nav-search button:hover { background: var(--gold-light); }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 39px;
            padding: 0 14px;
            color: #e9e1d4;
            border: 1px solid #4a4338;
            font-size: 11px;
            letter-spacing: .5px;
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
            color: #1d1811;
            font-weight: 700;
        }

        .nav-link-btn.gold:hover {
            background: var(--gold-light);
            color: #17130e;
            transform: translateY(-1px);
        }

        .category-bar {
            background: #211e19;
            border-bottom: 1px solid var(--line);
        }

        .category-inner {
            display: flex;
            align-items: center;
            gap: 28px;
            min-height: 44px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .category-inner::-webkit-scrollbar { display: none; }

        .category-inner a {
            color: #c5b9a7;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            white-space: nowrap;
            transition: color .2s ease;
        }

        .category-inner a:hover { color: var(--gold-light); }

        .hero {
            min-height: 355px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            background:
                radial-gradient(ellipse at 80% 45%, rgba(164, 126, 69, .22), transparent 38%),
                linear-gradient(110deg, #171512 0%, #262119 58%, #342a1e 100%);
            color: var(--white);
            margin-top: 30px;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 390px;
            height: 390px;
            border: 1px solid rgba(197, 164, 109, .22);
            border-radius: 50%;
            right: 7%;
            top: -15px;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 285px;
            height: 285px;
            border: 1px solid rgba(197, 164, 109, .17);
            border-radius: 50%;
            right: 11%;
            top: 38px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 650px;
            padding: 55px 0;
        }

        .eyebrow {
            color: var(--gold-light);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 5vw, 62px);
            font-weight: 500;
            line-height: 1.08;
            letter-spacing: -.8px;
            margin-bottom: 20px;
        }

        .hero h1 em {
            color: var(--gold-light);
            font-weight: 500;
        }

        .hero p {
            max-width: 480px;
            color: #c2b8a9;
            font-size: 14px;
            line-height: 1.9;
            margin-bottom: 28px;
        }

        .gold-button {
            min-height: 47px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 24px;
            background: linear-gradient(135deg, #dec795, #b89155);
            color: #211a10;
            border: 1px solid #e2c99b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 5px 0 #80633a, 0 10px 24px rgba(0,0,0,.18);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }

        .gold-button:hover {
            color: #17130e;
            filter: brightness(1.06);
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #80633a, 0 14px 28px rgba(0,0,0,.22);
        }

        .gold-button:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #80633a;
        }

        .benefits {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            background: #e8e1d5;
            border: 1px solid #d9cfbf;
            margin: 22px 0 48px;
        }

        .benefit {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 13px;
            border-right: 1px solid #d6cbbb;
        }

        .benefit:last-child { border-right: 0; }

        .benefit-icon {
            color: #9d7944;
            font-family: 'Playfair Display', serif;
            font-size: 25px;
        }

        .benefit strong {
            display: block;
            font-size: 11px;
            letter-spacing: .4px;
        }

        .benefit small {
            display: block;
            color: #81786b;
            font-size: 10px;
            margin-top: 3px;
        }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
        }

        .section-kicker {
            color: #9b7949;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .section-heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 600;
            margin: 0;
            color: #211d17;
        }

        .product-count {
            color: #82796d;
            font-size: 11px;
            letter-spacing: .5px;
            padding-bottom: 5px;
        }

        .filter-panel {
            background: #fffdf8;
            border: 1px solid #ded6c9;
            padding: 18px;
            margin-bottom: 28px;
            box-shadow: 0 8px 22px rgba(40, 31, 18, .035);
        }

        .filter-panel .form-label {
            color: #51483b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .filter-panel .form-control,
        .filter-panel .form-select {
            min-height: 44px;
            border-radius: 0;
            border-color: #d8d0c3;
            background-color: #fffdf8;
            color: #27221b;
            font-size: 12px;
            box-shadow: none;
        }

        .filter-panel .form-control:focus,
        .filter-panel .form-select:focus {
            border-color: #a98754;
            box-shadow: 0 0 0 3px rgba(169, 135, 84, .12);
        }

        .filter-submit {
            min-height: 44px;
            width: 100%;
            border: 1px solid #211d17;
            background: #211d17;
            color: #f6f0e5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            transition: .2s ease;
        }

        .filter-submit:hover {
            background: #9e7b4c;
            border-color: #9e7b4c;
            color: white;
        }

        .reset-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            color: #62584a;
            font-size: 11px;
            padding: 0 12px;
            border: 1px solid #d8d0c3;
            white-space: nowrap;
        }

        .reset-link:hover { color: #9e7b4c; border-color: #9e7b4c; }

        .product-card {
            height: 100%;
            border: 1px solid #ddd5c8;
            border-radius: 0;
            overflow: hidden;
            background: #fffdf8;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            border-color: #b89a6d;
            box-shadow: 0 18px 36px rgba(38, 29, 17, .12);
        }

        .product-image {
            height: 235px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9e2d6;
            border-bottom: 1px solid #ded5c8;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .55s cubic-bezier(.2,.7,.2,1);
        }

        .product-card:hover .product-image img { transform: scale(1.055); }

        .image-fallback {
            color: #a08c6d;
            font-family: 'Playfair Display', serif;
            font-size: 50px;
        }

        .image-label {
            position: absolute;
            left: 13px;
            top: 13px;
            background: rgba(25, 22, 17, .88);
            color: #e4d2ae;
            padding: 7px 10px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .product-card .card-body { padding: 20px; }

        .product-category {
            color: #9a794b;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            margin-bottom: 9px;
        }

        .product-title {
            color: #211d17;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 9px;
        }

        .product-description {
            min-height: 38px;
            color: #82796d;
            font-size: 11px;
            line-height: 1.7;
            margin-bottom: 17px;
        }

        .price {
            color: #8b693d;
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            font-weight: 600;
            line-height: 1.2;
        }

        .price-unit {
            color: #82796d;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 400;
        }

        .price-note {
            color: #8b8174;
            font-size: 10px;
            margin-top: 5px;
        }

        .product-meta {
            border-top: 1px solid #e8e0d4;
            margin-top: 16px;
            padding-top: 13px;
        }

        .meta-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            color: #82796d;
            font-size: 10px;
            margin-bottom: 7px;
        }

        .meta-line strong {
            color: #3b352c;
            font-weight: 700;
            text-align: right;
        }

        .stock-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #7c8b5c;
            margin-right: 5px;
            vertical-align: 1px;
        }

        .view-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 45px;
            margin-top: 17px;
            padding: 0 15px;
            background: #211d17;
            color: #f5efe5;
            border: 1px solid #211d17;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 3px 0 #0e0d0b;
            transition: .2s ease;
        }

        .view-button span:last-child {
            color: var(--gold-light);
            font-size: 16px;
        }

        .view-button:hover {
            color: #211a10;
            background: var(--gold);
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: 0 5px 0 #8a6b3e;
        }

        .view-button:hover span:last-child { color: #211a10; }

        .empty-state {
            background: #fffdf8;
            border: 1px solid #ded6c9;
            padding: 65px 20px;
            text-align: center;
        }

        .empty-state .symbol {
            color: #a88a5d;
            font-family: 'Playfair Display', serif;
            font-size: 48px;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 25px;
        }

        .empty-state p { color: #82796d; font-size: 12px; }

        .pagination { gap: 5px; }

        .pagination .page-link {
            border-radius: 0 !important;
            border-color: #d9d0c2;
            background: #fffdf8;
            color: #42392d;
            font-size: 11px;
            box-shadow: none;
        }

        .pagination .active .page-link {
            background: #211d17;
            border-color: #211d17;
            color: #f6efdf;
        }

        .store-footer {
            background: #171512;
            color: #a99d8b;
            margin-top: 75px;
            padding: 45px 0 25px;
            border-top: 2px solid #9b7949;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            color: #f4eee3;
            font-size: 22px;
        }

        .footer-note {
            color: #8e8374;
            font-size: 11px;
            line-height: 1.8;
            max-width: 390px;
        }

        .footer-bottom {
            border-top: 1px solid #3a3329;
            margin-top: 30px;
            padding-top: 18px;
            color: #817668;
            font-size: 9px;
            letter-spacing: 1px;
        }

        @media (max-width: 991px) {
            .nav-inner { flex-wrap: wrap; padding: 15px 0; }
            .nav-search { order: 3; max-width: none; flex-basis: 100%; }
            .brand { min-width: auto; }
            .hero { min-height: 320px; }
        }

        @media (max-width: 575px) {
            .nav-search select {
                width: 105px;
                min-width: 105px;
                padding: 0 5px;
                font-size: 9px;
            }

            .nav-search input {
                padding: 0 8px;
                font-size: 11px;
            }

            .nav-search button {
                padding: 0 12px;
                font-size: 9px;
            }

            .announcement { font-size: 8px; letter-spacing: 1.2px; }
            .nav-actions { gap: 6px; }
            .nav-link-btn { padding: 0 9px; font-size: 9px; }
            .hero { margin-top: 16px; min-height: 300px; }
            .hero-content { padding: 40px 20px; }
            .hero::before { right: -190px; }
            .hero::after { right: -140px; }
            .benefits { grid-template-columns: 1fr; margin-bottom: 38px; }
            .benefit { border-right: 0; border-bottom: 1px solid #d6cbbb; }
            .benefit:last-child { border-bottom: 0; }
            .section-heading h2 { font-size: 28px; }
            .product-image { height: 215px; }
        }
    </style>
</head>

<body>

<div class="announcement">
    A better way to source for business <span>·</span> Wholesale pricing, transparent quantities
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

        <form class="nav-search" method="GET" action="{{ route('storefront.index') }}">
            <select name="category" aria-label="Select category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}"
                        @selected(($validated['category'] ?? '') === $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <input type="search" name="search"
                   placeholder="Search products, categories and supplies..."
                   value="{{ $validated['search'] ?? '' }}">
            <button type="submit">Search</button>
        </form>

        <div class="nav-actions">
            @if(auth()->check() && auth()->user()->role === 'customer')
                <a href="{{ route('customer.account') }}" class="nav-link-btn">My Account</a>
                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link-btn">Sign Out</button>
                </form>
            @else
                <a href="{{ route('customer.login') }}" class="nav-link-btn gold">Sign In</a>
            @endif

            <a href="{{ route('vendor.login') }}" class="nav-link-btn">Vendor</a>
        </div>
    </div>
</header>

<nav class="category-bar">
    <div class="container category-inner">
        <a href="{{ route('storefront.index') }}">All products</a>
        @foreach($categories as $category)
            <a href="{{ route('storefront.index', ['category' => $category]) }}">
                {{ $category }}
            </a>
        @endforeach
    </div>
</nav>

<main class="container">

    <section class="hero">
        <div class="container hero-content">
            <div class="eyebrow">A considered way to buy wholesale</div>
            <h1>Source better.<br><em>Grow further.</em></h1>
            <p>
                Discover quality products for your business, explore transparent
                quantity-based pricing and source with confidence.
            </p>
            <a href="#catalog" class="gold-button">Explore the collection <span class="ms-3">→</span></a>
        </div>
    </section>

    <section class="benefits" aria-label="Store benefits">
        <div class="benefit">
            <span class="benefit-icon">✦</span>
            <div>
                <strong>Curated product selection</strong>
                <small>Products approved for our marketplace</small>
            </div>
        </div>
        <div class="benefit">
            <span class="benefit-icon">◇</span>
            <div>
                <strong>Transparent wholesale pricing</strong>
                <small>Quantity-based prices shown upfront</small>
            </div>
        </div>
        <div class="benefit">
            <span class="benefit-icon">⌑</span>
            <div>
                <strong>Stock visibility</strong>
                <small>See available quantities before you buy</small>
            </div>
        </div>
    </section>

    <section id="catalog" class="mb-5">
        <div class="section-heading">
            <div>
                <div class="section-kicker">The marketplace</div>
                <h2>Shop the collection</h2>
            </div>
            <div class="product-count">{{ $listings->total() }} products</div>
        </div>

        <div class="row g-4">
            @forelse($listings as $listing)
                @php
                    $product = $listing->product;
                    $firstTier = $listing->priceTiers->first();
                    $displayName = $listing->display_name ?: $product->name;
                    $hasImage = !empty($listing->image_path) || !empty($product->image_path);
                @endphp

                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <article class="card product-card">
                        <div class="product-image">
                            @if($hasImage)
                                <img
                                    src="{{ route('storefront.products.image', $listing->product_id) }}"
                                    alt="{{ $displayName }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                >
                                <span class="image-fallback" style="display:none;">W.</span>
                            @else
                                <span class="image-fallback">W.</span>
                            @endif
                            <span class="image-label">Wholesale selection</span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="product-category">
                                {{ $product->category ?: 'Selected product' }}
                            </div>

                            <h3 class="product-title">{{ $displayName }}</h3>

                            <p class="product-description">
                                @if($listing->customer_description)
                                    {{ \Illuminate\Support\Str::limit($listing->customer_description, 85) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($product->description, 85) }}
                                @endif
                            </p>

                            <div class="mt-auto">
                                @if($firstTier)
                                    <div class="price">
                                        ₹{{ number_format((float) $firstTier->unit_price, 2) }}
                                        <span class="price-unit">/ {{ $product->unit }}</span>
                                    </div>
                                    <div class="price-note">
                                        Starting price · From
                                        {{ number_format((float) $firstTier->minimum_quantity, 3) }}
                                        {{ $product->unit }}
                                    </div>
                                @endif

                                <div class="product-meta">
                                    <div class="meta-line">
                                        <span>Availability</span>
                                        <strong><span class="stock-dot"></span>
                                            {{ number_format((float) $product->stock_remaining, 3) }}
                                            {{ $product->unit }}
                                        </strong>
                                    </div>
                                    <div class="meta-line">
                                        <span>Minimum order</span>
                                        <strong>
                                            {{ number_format((float) $listing->minimum_order_quantity, 3) }}
                                            {{ $product->unit }}
                                        </strong>
                                    </div>
                                </div>

                                <a href="{{ route('storefront.show', $listing) }}" class="view-button">
                                    <span>View product details</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        <div class="symbol">W.</div>
                        <h3>No products found</h3>
                        <p>There are currently no products matching your search.</p>
                        <a href="{{ route('storefront.index') }}" class="gold-button">
                            Browse all products
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if($listings->hasPages())
            <div class="mt-5 d-flex justify-content-center">
                {{ $listings->links() }}
            </div>
        @endif
    </section>
</main>

<footer class="store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-7">
                <div class="footer-brand">Wholesale Store</div>
                <p class="footer-note mt-2 mb-0">
                    A business-to-business marketplace built around considered
                    sourcing, transparent wholesale pricing and dependable supply.
                </p>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="eyebrow mb-2">For business buyers</div>
                <div class="small">Quality · Value · Supply</div>
            </div>
        </div>
        <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2">
            <span>WHOLESALE STORE</span>
            <span>BUSINESS-TO-BUSINESS MARKETPLACE</span>
        </div>
    </div>
</footer>

</body>
</html>
