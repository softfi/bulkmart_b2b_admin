<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerListing;
use App\Models\CustomerPriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CustomerCatalogController extends Controller
{
    public function index()
    {
        $listings = CustomerListing::with([
            'product.vendorProfile',
            'priceTiers.productVariant',
        ])->latest()->paginate(20);

        return view('admin.catalog.index', compact('listings'));
    }

    public function create()
    {
        $products = Product::query()
            ->where('status', 'approved')
            ->whereHas('inventoryBatches', fn ($q) => $q->where('quantity_remaining', '>', 0))
            ->with(['vendorProfile', 'variants'])
            ->withSum('inventoryBatches as stock_remaining', 'quantity_remaining')
            ->whereDoesntHave('customerListing')
            ->orderBy('name')
            ->get();

        return view('admin.catalog.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateListing($request);

        $listing = DB::transaction(function () use ($data, $request) {
            $product = Product::whereKey($data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensurePublishable($product);

            if ($product->customerListing()->exists()) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product already has a customer listing.',
                ]);
            }

            $this->validateVariantTiers($product, $data['price_tiers']);

            $listing = CustomerListing::create([
                'product_id' => $product->id,
                'display_name' => $data['display_name'] ?? null,
                'customer_description' => $data['customer_description'] ?? null,
                'image_path' => $request->file('image')
                    ? $request->file('image')->store('customer-listing-images', 'local')
                    : null,
                'minimum_order_quantity' => $data['minimum_order_quantity'],
                'is_active' => false,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->saveTiers($listing, $data['price_tiers']);

            return $listing;
        });

        return redirect()->route('admin.catalog.edit', $listing)
            ->with('success', 'Customer listing created. Review it and activate when ready.');
    }

    public function edit(CustomerListing $listing)
    {
        $listing->load([
            'product.vendorProfile',
            'product.variants',
            'priceTiers.productVariant',
        ]);

        $stockRemaining = $listing->product->inventoryBatches()
            ->sum('quantity_remaining');

        $variantStock = $listing->product->variants
            ->mapWithKeys(function ($variant) use ($listing) {
                $stock = $listing->product->inventoryBatches()
                    ->where('product_variant_id', $variant->id)
                    ->sum('quantity_remaining');

                return [$variant->id => $stock];
            });

        return view('admin.catalog.edit', compact(
            'listing',
            'stockRemaining',
            'variantStock'
        ));
    }

    public function update(Request $request, CustomerListing $listing)
    {
        $data = $this->validateListing($request, false);

        DB::transaction(function () use ($data, $request, $listing) {
            $lockedListing = CustomerListing::whereKey($listing->id)
                ->lockForUpdate()
                ->firstOrFail();

            $product = Product::whereKey($lockedListing->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedListing->is_active) {
                $this->ensurePublishable($product);
            }

            $this->validateVariantTiers($product, $data['price_tiers']);

            $imagePath = $lockedListing->image_path;

            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')
                    ->store('customer-listing-images', 'local');

                if ($newImagePath === false) {
                    throw ValidationException::withMessages([
                        'image' => 'The listing image could not be saved.',
                    ]);
                }

                $imagePath = $newImagePath;
            }

            $lockedListing->update([
                'display_name' => $data['display_name'] ?? null,
                'customer_description' => $data['customer_description'] ?? null,
                'image_path' => $imagePath,
                'minimum_order_quantity' => $data['minimum_order_quantity'],
                'updated_by' => $request->user()->id,
            ]);

            $lockedListing->priceTiers()->delete();

            $this->saveTiers($lockedListing, $data['price_tiers']);
        });

        return redirect()->route('admin.catalog.edit', $listing)
            ->with('success', 'Customer listing and price tiers updated.');
    }

    public function toggleStatus(CustomerListing $listing)
    {
        DB::transaction(function () use ($listing) {
            $lockedListing = CustomerListing::whereKey($listing->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedListing->is_active) {
                $product = Product::whereKey($lockedListing->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensurePublishable($product);

                if (! $lockedListing->priceTiers()->exists()) {
                    throw ValidationException::withMessages([
                        'listing' => 'Add valid customer price tiers before activation.',
                    ]);
                }
            }

            $lockedListing->update([
                'is_active' => ! $lockedListing->is_active,
                'updated_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Customer listing status updated.');
    }

    private function validateListing(
        Request $request,
        bool $requireProduct = true
    ): array {
        $rules = [
            'display_name' => ['nullable', 'string', 'max:150'],
            'customer_description' => ['nullable', 'string', 'max:5000'],
            'minimum_order_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'price_tiers' => ['required', 'array', 'min:1', 'max:150'],
            'price_tiers.*.product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],
            'price_tiers.*.minimum_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'price_tiers.*.maximum_quantity' => [
                'nullable',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'price_tiers.*.unit_price' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:9999999999',
            ],
        ];

        if ($requireProduct) {
            $rules['product_id'] = [
                'required',
                'integer',
                'exists:products,id',
            ];
        }

        $data = $request->validate($rules);

        $data['price_tiers'] = collect($data['price_tiers'])
            ->map(fn ($tier) => [
                'product_variant_id' => ($tier['product_variant_id'] ?? '') === ''
                    ? null
                    : (int) $tier['product_variant_id'],
                'minimum_quantity' => (float) $tier['minimum_quantity'],
                'maximum_quantity' => ($tier['maximum_quantity'] ?? '') === ''
                    ? null
                    : (float) $tier['maximum_quantity'],
                'unit_price' => $tier['unit_price'],
            ])
            ->values()
            ->all();

        return $data;
    }

    private function validateVariantTiers(Product $product, array $tiers): void
    {
        $variantIds = collect($tiers)
            ->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values();

        if ($variantIds->isNotEmpty()) {
            $validIds = $product->variants()
                ->whereIn('id', $variantIds)
                ->pluck('id');

            if ($validIds->count() !== $variantIds->count()) {
                throw ValidationException::withMessages([
                    'price_tiers' => 'One or more selected variants do not belong to this product.',
                ]);
            }
        }

        $groups = collect($tiers)->groupBy(
            fn ($tier) => $tier['product_variant_id'] ?? 'product'
        );

        foreach ($groups as $variantKey => $group) {
            $minimumOrder = $variantKey === 'product'
                ? (float) request()->input('minimum_order_quantity')
                : (float) $product->variants()
                    ->whereKey((int) $variantKey)
                    ->value('minimum_order_quantity');

            if ($minimumOrder <= 0) {
                throw ValidationException::withMessages([
                    'price_tiers' => 'A variant has an invalid minimum order quantity.',
                ]);
            }

            $sorted = $group->sortBy('minimum_quantity')->values();
            $previousMaximum = null;

            foreach ($sorted as $index => $tier) {
                $minimum = (int) round($tier['minimum_quantity'] * 1000);
                $maximum = $tier['maximum_quantity'] === null
                    ? null
                    : (int) round($tier['maximum_quantity'] * 1000);

                $expectedMinimum = (int) round($minimumOrder * 1000);

                if ($maximum !== null && $maximum < $minimum) {
                    throw ValidationException::withMessages([
                        'price_tiers' => 'A tier maximum must be greater than or equal to its minimum.',
                    ]);
                }

                if ($index === 0 && $minimum !== $expectedMinimum) {
                    throw ValidationException::withMessages([
                        'price_tiers' => 'The first tier in each pricing group must start at its minimum order quantity.',
                    ]);
                }

                if ($previousMaximum === null && $index > 0) {
                    throw ValidationException::withMessages([
                        'price_tiers' => 'No tier may follow a tier with an unlimited maximum.',
                    ]);
                }

                if ($index > 0 && $minimum !== $previousMaximum + 1) {
                    throw ValidationException::withMessages([
                        'price_tiers' => 'Tiers must be consecutive with no gaps or overlaps (quantity precision: 0.001).',
                    ]);
                }

                if ($maximum === null && $index !== $sorted->count() - 1) {
                    throw ValidationException::withMessages([
                        'price_tiers' => 'Only the final tier in each pricing group may have an unlimited maximum.',
                    ]);
                }

                $previousMaximum = $maximum;
            }

            if ($previousMaximum !== null) {
                throw ValidationException::withMessages([
                    'price_tiers' => 'The final tier in each pricing group must have an unlimited maximum.',
                ]);
            }
        }
    }

    private function ensurePublishable(Product $product): void
    {
        if ($product->trashed() || $product->status !== 'approved') {
            throw ValidationException::withMessages([
                'product_id' => 'Only active, approved vendor products can be listed.',
            ]);
        }

        if ((float) $product->inventoryBatches()->sum('quantity_remaining') <= 0) {
            throw ValidationException::withMessages([
                'product_id' => 'This product has no available inventory.',
            ]);
        }
    }

    private function saveTiers(CustomerListing $listing, array $tiers): void
    {
        foreach ($tiers as $tier) {
            CustomerPriceTier::create([
                'customer_listing_id' => $listing->id,
                'product_variant_id' => $tier['product_variant_id'],
                'minimum_quantity' => $tier['minimum_quantity'],
                'maximum_quantity' => $tier['maximum_quantity'],
                'unit_price' => $tier['unit_price'],
            ]);
        }
    }
public function showImage(CustomerListing $listing)
{
    abort_unless($listing->image_path, 404);

    $disk = Storage::disk('local');

    abort_unless($disk->exists($listing->image_path), 404);

    $path = $disk->path($listing->image_path);
    $mimeType = mime_content_type($path);

    abort_unless(
        in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true),
        415
    );

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'private, max-age=3600',
    ]);
}
    }

    