<?php

namespace App\Http\Controllers;

use App\Models\CustomerListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontController extends Controller
{
    /**
     * Display active customer listings with available inventory.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $listingsQuery = CustomerListing::query()
            ->where('is_active', true)
            ->whereHas('priceTiers')
            ->whereHas('product', function (Builder $query) {
                $query->where('status', 'approved')
                    ->whereHas('vendorProfile', function (Builder $vendorQuery) {
                        $vendorQuery->where('approval_status', 'approved')
                            ->whereHas('user', function (Builder $userQuery) {
                                $userQuery->where('is_active', true);
                            });
                    })
                    ->whereHas('inventoryBatches', function (Builder $batchQuery) {
                        $batchQuery->where('quantity_remaining', '>', 0);
                    });
            })
            ->with([
                'product' => function ($query) {
                    $query->withSum(
                        'inventoryBatches as stock_remaining',
                        'quantity_remaining'
                    );
                },
                'priceTiers',
            ]);

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $listingsQuery->where(function (Builder $query) use ($search) {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('customer_description', 'like', "%{$search}%")
                    ->orWhereHas('product', function (Builder $productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($validated['category'])) {
            $category = $validated['category'];

            $listingsQuery->whereHas('product', function (Builder $query) use ($category) {
                $query->where('category', $category);
            });
        }

        $listings = $listingsQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = CustomerListing::query()
            ->where('is_active', true)
            ->whereHas('product', function (Builder $query) {
                $query->where('status', 'approved')
                    ->whereHas('vendorProfile', function (Builder $vendorQuery) {
                        $vendorQuery->where('approval_status', 'approved')
                            ->whereHas('user', function (Builder $userQuery) {
                                $userQuery->where('is_active', true);
                            });
                    })
                    ->whereHas('inventoryBatches', function (Builder $batchQuery) {
                        $batchQuery->where('quantity_remaining', '>', 0);
                    });
            })
            ->join('products', 'customer_listings.product_id', '=', 'products.id')
            ->whereNotNull('products.category')
            ->where('products.category', '!=', '')
            ->distinct()
            ->orderBy('products.category')
            ->pluck('products.category');

        return view('storefront.index', compact(
            'listings',
            'categories',
            'validated'
        ));
    }

    /**
     * Display one active customer listing.
     */
    public function show(CustomerListing $listing)
    {
        abort_unless($listing->is_active, 404);

        $listing->load([
            'priceTiers',
            'product' => function ($query) {
                $query->withSum(
                    'inventoryBatches as stock_remaining',
                    'quantity_remaining'
                )->with([
                    'vendorProfile.user',
                ]);
            },
        ]);

        $product = $listing->product;

        abort_if(
            ! $product ||
            $product->trashed() ||
            $product->status !== 'approved' ||
            ! $product->vendorProfile ||
            $product->vendorProfile->approval_status !== 'approved' ||
            ! $product->vendorProfile->user ||
            ! $product->vendorProfile->user->is_active ||
            (float) ($product->stock_remaining ?? 0) <= 0 ||
            $listing->priceTiers->isEmpty(),
            404
        );

        $stockRemaining = (float) $product->stock_remaining;

        return view('storefront.show', compact(
            'listing',
            'product',
            'stockRemaining'
        ));
    }

public function image(CustomerListing $listing)
{
    abort_unless($listing->is_active, 404);

    $listing->load('product.vendorProfile.user');

    $product = $listing->product;

    abort_if(
        !$product ||
        $product->trashed() ||
        $product->status !== 'approved' ||
        !$product->vendorProfile ||
        $product->vendorProfile->approval_status !== 'approved' ||
        !$product->vendorProfile->user ||
        !$product->vendorProfile->user->is_active ||
        !$product->image_path,
        404
    );

    $disk = Storage::disk('local');

    abort_unless($disk->exists($product->image_path), 404);

    return $disk->response($product->image_path);
}
}
