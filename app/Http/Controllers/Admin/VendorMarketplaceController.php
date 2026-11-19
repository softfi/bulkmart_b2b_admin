<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VendorMarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'vendor' => ['nullable', 'integer', 'min:1'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,suspended'],
            'sort' => ['nullable', 'in:price_low,price_high,newest,oldest'],
        ]);

        $productsQuery = Product::query()
            ->with([
                'vendorProfile.user',
                'productCategory',
                'variants' => fn ($query) => $query
                    ->orderBy('id'),
            ])
            ->whereHas('vendorProfile', fn (Builder $query) =>
                $query->where('approval_status', 'approved')
            );

        $this->applyFilters($productsQuery, $filters);

        $sort = $filters['sort'] ?? 'newest';

        if ($sort === 'oldest') {
            $productsQuery->oldest();
        } else {
            $productsQuery->latest();
        }

        /*
         * Product pagination is performed in SQL. We then expand only the
         * current page into product/variant offers.
         */
        $productsPage = $productsQuery
            ->paginate(25)
            ->withQueryString();

        $pageOffers = $this->expandProducts($productsPage->getCollection());

        /*
         * Comparison candidates are fetched across all approved vendors,
         * not just the current vendor filter. This ensures a vendor filter
         * cannot hide a cheaper competing offer.
         */
        $candidateProducts = $this->comparisonCandidates($productsPage);

        $comparisonOffers = $this->expandProducts($candidateProducts);

        $lowestPrices = $comparisonOffers
            ->groupBy('comparison_key')
            ->map(fn (Collection $offers) =>
                (float) $offers->min('unit_price')
            );

        $pageOffers = $pageOffers->map(function (array $offer) use ($lowestPrices) {
            $lowest = $lowestPrices->get($offer['comparison_key']);

            $offer['lowest_price'] = $lowest !== null
                && abs((float) $offer['unit_price'] - (float) $lowest) < 0.0001;

            $offer['lowest_price_value'] = $lowest;

            return $offer;
        });

        if ($sort === 'price_low') {
            $pageOffers = $pageOffers->sortBy('unit_price')->values();
        } elseif ($sort === 'price_high') {
            $pageOffers = $pageOffers->sortByDesc('unit_price')->values();
        }

        $offers = new LengthAwarePaginator(
            $pageOffers,
            $productsPage->total(),
            $productsPage->perPage(),
            $productsPage->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $vendors = VendorProfile::query()
            ->where('approval_status', 'approved')
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        $categories = Product::query()
            ->whereHas('vendorProfile', fn (Builder $query) =>
                $query->where('approval_status', 'approved')
            )
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $summary = [
            'total_products' => Product::query()
                ->whereHas('vendorProfile', fn (Builder $query) =>
                    $query->where('approval_status', 'approved')
                )
                ->count(),

            'approved_products' => Product::query()
                ->where('status', 'approved')
                ->whereHas('vendorProfile', fn (Builder $query) =>
                    $query->where('approval_status', 'approved')
                )
                ->count(),

            'pending_products' => Product::query()
                ->where('status', 'pending')
                ->whereHas('vendorProfile', fn (Builder $query) =>
                    $query->where('approval_status', 'approved')
                )
                ->count(),

            'vendor_count' => $vendors->count(),
        ];

        return view('admin.marketplace.index', compact(
            'offers',
            'vendors',
            'categories',
            'summary',
            'filters'
        ));
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function (Builder $searchQuery) use ($term) {
                $searchQuery
                    ->where('name', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('unit', 'like', '%' . $term . '%')
                    ->orWhereHas('vendorProfile', fn (Builder $vendorQuery) =>
                        $vendorQuery->where('business_name', 'like', '%' . $term . '%')
                    )
                    ->orWhereHas('variants', fn (Builder $variantQuery) =>
                        $variantQuery->where('sku', 'like', '%' . $term . '%')
                    );
            });
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor_profile_id', $filters['vendor']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    private function comparisonCandidates($productsPage): Collection
    {
        $names = $productsPage->getCollection()
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();

        $categories = $productsPage->getCollection()
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty() || $categories->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->with([
                'vendorProfile',
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('id'),
            ])
            ->where('status', 'approved')
            ->whereIn('name', $names)
            ->whereIn('category', $categories)
            ->whereHas('vendorProfile', fn (Builder $query) =>
                $query->where('approval_status', 'approved')
            )
            ->get();
    }

    private function expandProducts(Collection $products): Collection
    {
        $offers = collect();

        foreach ($products as $product) {
            $vendor = $product->vendorProfile;

            if (!$vendor) {
                continue;
            }

            $activeVariants = $product->variants
                ->where('is_active', true)
                ->values();

            if ($activeVariants->isEmpty()) {
                $offers->push($this->makeOffer($product, null));
                continue;
            }

            foreach ($activeVariants as $variant) {
                $offers->push($this->makeOffer($product, $variant));
            }
        }

        return $offers;
    }

    private function makeOffer(Product $product, ?ProductVariant $variant): array
    {
        $attributes = $variant?->attributes ?? [];
        $attributes = is_array($attributes) ? $attributes : [];

        $normalizedAttributes = $this->normalizeAttributes($attributes);

        $category = trim((string) ($product->category ?? ''));
        $name = trim((string) $product->name);

        $comparisonKey = implode('|', [
            mb_strtolower($name),
            mb_strtolower($category),
            json_encode($normalizedAttributes, JSON_UNESCAPED_UNICODE),
        ]);

        $attributeLabel = collect($attributes)
            ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', (string) $key)) . ': ' . $value)
            ->implode(' · ');

        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'product_name' => $product->name,
            'category' => $product->category,
            'unit' => $product->unit,
            'description' => $product->description,
            'vendor_id' => $product->vendor_profile_id,
            'vendor_name' => $product->vendorProfile?->business_name ?? 'Vendor',
            'vendor_email' => $product->vendorProfile?->user?->email,
            'vendor_phone' => $product->vendorProfile?->phone,
            'vendor_city' => $product->vendorProfile?->city,
            'vendor_state' => $product->vendorProfile?->state,
            'variant_sku' => $variant?->sku,
            'variant_attributes' => $attributes,
            'variant_label' => $attributeLabel ?: ($variant?->sku ?: 'Standard product'),
            'unit_price' => (float) ($variant?->price_per_unit ?? $product->price_per_unit),
            'available_quantity' => (float) ($variant?->available_quantity ?? $product->available_quantity),
            'minimum_order_quantity' => (float) ($variant?->minimum_order_quantity ?? $product->minimum_order_quantity),
            'status' => $product->status,
            'listing_date' => $product->created_at,
            'comparison_key' => $comparisonKey,
            'can_purchase' => $product->status === 'approved'
                && $product->vendorProfile?->approval_status === 'approved'
                && (!$variant || $variant->is_active)
                && (float) ($variant?->available_quantity ?? $product->available_quantity) > 0,
        ];
    }

    private function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = $this->normalizeAttributes($value);
            } elseif (is_string($value)) {
                $value = mb_strtolower(trim($value));
            }

            $normalized[(string) $key] = $value;
        }

        ksort($normalized);

        return $normalized;
    }
}