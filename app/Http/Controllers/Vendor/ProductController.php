<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $this->approvedVendor($request);

        $products = $vendor->products()
            ->with(['productCategory', 'variants'])
            ->latest()
            ->paginate(15);

        return view('vendor.products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $this->approvedVendor($request);

        $categories = ProductCategory::where('is_active', true)
            ->with('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $vendor = $this->approvedVendor($request);
        $validated = $this->validateProduct($request);

        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('product-images', 'local');

                if (!$imagePath) {
                    throw new RuntimeException('Product image upload failed.');
                }
            }

            DB::transaction(function () use (
                $vendor,
                $validated,
                $imagePath
            ) {
                $category = ProductCategory::with('attributes')
                    ->where('is_active', true)
                    ->findOrFail($validated['category_id']);

                $product = $vendor->products()->create([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug($validated['name']),
                    'description' => $validated['description'] ?? null,
                    'category' => $category->name,
                    'category_id' => $category->id,
                    'attribute_values' => $this->normalizeProductAttributes(
                        $validated['attribute_values'] ?? [],
                        $category
                    ),
                    'unit' => $validated['unit'],
                    'price_per_unit' => $validated['price_per_unit'],
                    'available_quantity' => $validated['available_quantity'],
                    'minimum_order_quantity' => $validated['minimum_order_quantity'],
                    'image_path' => $imagePath,
                    'status' => 'pending',
                ]);

                $this->syncVariants($product, $category, $validated);
            });
        } catch (\Throwable $exception) {
            if (
                $imagePath &&
                !Storage::disk('local')->delete($imagePath)
            ) {
                Log::warning('Failed to clean up product image after creation failure.', [
                    'image_path' => $imagePath,
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('success', 'Product submitted for admin review.');
    }

    public function edit(Request $request, Product $product)
    {
        $vendor = $this->approvedVendor($request);
        $product = $this->ownedProduct($vendor, $product);

        if ($product->status === 'suspended') {
            return redirect()
                ->route('vendor.products.index')
                ->with('error', 'This product has been suspended by the admin and cannot be edited.');
        }

        $product->load(['variants', 'productCategory']);

        $categories = ProductCategory::where('is_active', true)
            ->with('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = $this->approvedVendor($request);
        $product = $this->ownedProduct($vendor, $product);

        if ($product->status === 'suspended') {
            return redirect()
                ->route('vendor.products.index')
                ->with('error', 'This product has been suspended by the admin and cannot be edited.');
        }

        $validated = $this->validateProduct($request);

        $newImagePath = null;
        $oldImagePath = $product->image_path;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')
                    ->store('product-images', 'local');

                if (!$newImagePath) {
                    throw new RuntimeException('Product image upload failed.');
                }
            }

            DB::transaction(function () use (
                $request,
                $product,
                $validated,
                $newImagePath
            ) {
                $lockedProduct = Product::whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedProduct->status === 'suspended') {
                    throw ValidationException::withMessages([
                        'product' => 'This product has been suspended and cannot be edited.',
                    ]);
                }

                $reservedQuantity = (float) $lockedProduct->reserved_quantity;
                $newAvailableQuantity = (float) $validated['available_quantity'];

                if ($newAvailableQuantity < $reservedQuantity) {
                    throw ValidationException::withMessages([
                        'available_quantity' =>
                            'Available quantity cannot be less than the quantity reserved for admin purchases (' .
                            number_format($reservedQuantity, 3) . ').',
                    ]);
                }

                $category = ProductCategory::with('attributes')
                    ->where('is_active', true)
                    ->findOrFail($validated['category_id']);

                $previousStatus = $lockedProduct->status;

                $lockedProduct->update([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug(
                        $validated['name'],
                        $lockedProduct->id
                    ),
                    'description' => $validated['description'] ?? null,
                    'category' => $category->name,
                    'category_id' => $category->id,
                    'attribute_values' => $this->normalizeProductAttributes(
                        $validated['attribute_values'] ?? [],
                        $category
                    ),
                    'unit' => $validated['unit'],
                    'price_per_unit' => $validated['price_per_unit'],
                    'available_quantity' => $validated['available_quantity'],
                    'minimum_order_quantity' => $validated['minimum_order_quantity'],
                    'image_path' => $newImagePath ?? $lockedProduct->image_path,
                    'status' => 'pending',
                    'admin_note' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);

                $this->syncVariants($lockedProduct, $category, $validated);

                $lockedProduct->statusHistories()->create([
                    'reviewed_by' => null,
                    'changed_by' => $request->user()->id,
                    'previous_status' => $previousStatus,
                    'new_status' => 'pending',
                    'reason' => 'Vendor edited and resubmitted the product for admin review.',
                ]);
            });
        } catch (\Throwable $exception) {
            if (
                $newImagePath &&
                !Storage::disk('local')->delete($newImagePath)
            ) {
                Log::warning('Failed to clean up replacement product image after update failure.', [
                    'image_path' => $newImagePath,
                    'product_id' => $product->id,
                ]);
            }

            throw $exception;
        }

        if (
            $newImagePath &&
            $oldImagePath &&
            !Storage::disk('local')->delete($oldImagePath)
        ) {
            Log::warning('Could not delete the previous product image.', [
                'image_path' => $oldImagePath,
                'product_id' => $product->id,
            ]);
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('success', 'Product updated and submitted for review.');
    }

    public function destroy(Request $request, Product $product)
    {
        $vendor = $this->approvedVendor($request);
        $product = $this->ownedProduct($vendor, $product);

        DB::transaction(function () use ($product) {
            $lockedProduct = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $lockedProduct->reserved_quantity > 0) {
                throw ValidationException::withMessages([
                    'product' =>
                        'This product cannot be archived because stock is reserved for admin purchases.',
                ]);
            }

            $lockedProduct->delete();
        });

        return redirect()
            ->route('vendor.products.index')
            ->with('success', 'Product archived successfully.');
    }

    private function approvedVendor(Request $request): VendorProfile
    {
        $vendor = $request->user()->vendorProfile;

        abort_unless(
            $vendor && $vendor->approval_status === 'approved',
            403,
            'Only approved vendors can manage products.'
        );

        return $vendor;
    }

    private function ownedProduct(
        VendorProfile $vendor,
        Product $product
    ): Product {
        return $vendor->products()
            ->whereKey($product->id)
            ->firstOrFail();
    }

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'unit' => [
                'required',
                'in:kg,g,litre,ml,piece,box,bag,ton',
            ],

            'price_per_unit' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:9999999999',
            ],

            'available_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],

            'minimum_order_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],

            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['nullable', 'string', 'max:1000'],

            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer', 'distinct'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.attributes' => ['required_with:variants', 'array'],
            'variants.*.attributes.*' => ['nullable', 'string', 'max:1000'],

            'variants.*.price_per_unit' => [
                'required_with:variants',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:9999999999',
            ],

            'variants.*.available_quantity' => [
                'required_with:variants',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],

            'variants.*.minimum_order_quantity' => [
                'required_with:variants',
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
        ]);

        $category = ProductCategory::with('attributes')
            ->where('is_active', true)
            ->find($validated['category_id']);

        if (!$category) {
            throw ValidationException::withMessages([
                'category_id' => 'Please select an active product category.',
            ]);
        }

        if (
            (float) $validated['minimum_order_quantity'] >
            (float) $validated['available_quantity']
        ) {
            throw ValidationException::withMessages([
                'minimum_order_quantity' =>
                    'Minimum order quantity cannot exceed available quantity.',
            ]);
        }

        $productAttributes = $category->attributes
            ->where('is_variant_attribute', false)
            ->values();

        $this->validateAttributeValues(
            $validated['attribute_values'] ?? [],
            $productAttributes,
            'attribute_values'
        );

        $this->validateVariantAttributes(
            $validated['variants'] ?? [],
            $category->attributes
        );

        return $validated;
    }

    private function validateAttributeValues(
        array $values,
        $definitions,
        string $fieldPrefix
    ): void {
        foreach ($definitions as $definition) {
            $key = (string) $definition->id;
            $value = $values[$key] ?? null;

            if (
                $definition->is_required &&
                ($value === null || trim($value) === '')
            ) {
                throw ValidationException::withMessages([
                    "{$fieldPrefix}.{$key}" =>
                        "{$definition->name} is required for this category.",
                ]);
            }

            if ($value === null || trim($value) === '') {
                continue;
            }

            if ($definition->input_type === 'number' && !is_numeric($value)) {
                throw ValidationException::withMessages([
                    "{$fieldPrefix}.{$key}" =>
                        "{$definition->name} must be a number.",
                ]);
            }

            if (
                $definition->input_type === 'select' &&
                !in_array($value, $definition->options ?? [], true)
            ) {
                throw ValidationException::withMessages([
                    "{$fieldPrefix}.{$key}" =>
                        "Select a valid option for {$definition->name}.",
                ]);
            }
        }

        foreach (array_keys($values) as $attributeId) {
            if (!$definitions->contains('id', (int) $attributeId)) {
                throw ValidationException::withMessages([
                    $fieldPrefix =>
                        'The submitted data contains an attribute that does not belong to this category.',
                ]);
            }
        }
    }

    private function validateVariantAttributes(
        array $variants,
        $definitions
    ): void {
        $variantDefinitions = $definitions
            ->where('is_variant_attribute', true)
            ->values();

        foreach ($variants as $index => $variant) {
            $attributes = $variant['attributes'] ?? [];

            foreach ($variantDefinitions as $definition) {
                $key = (string) $definition->id;
                $value = $attributes[$key] ?? null;

                if (
                    $definition->is_required &&
                    ($value === null || trim($value) === '')
                ) {
                    throw ValidationException::withMessages([
                        "variants.{$index}.attributes.{$key}" =>
                            "{$definition->name} is required for each variant.",
                    ]);
                }

                if ($value === null || trim($value) === '') {
                    continue;
                }

                if (
                    $definition->input_type === 'number' &&
                    !is_numeric($value)
                ) {
                    throw ValidationException::withMessages([
                        "variants.{$index}.attributes.{$key}" =>
                            "{$definition->name} must be a number.",
                    ]);
                }

                if (
                    $definition->input_type === 'select' &&
                    !in_array($value, $definition->options ?? [], true)
                ) {
                    throw ValidationException::withMessages([
                        "variants.{$index}.attributes.{$key}" =>
                            "Select a valid option for {$definition->name}.",
                    ]);
                }
            }

            foreach (array_keys($attributes) as $attributeId) {
                if (!$variantDefinitions->contains('id', (int) $attributeId)) {
                    throw ValidationException::withMessages([
                        "variants.{$index}.attributes" =>
                            'A variant contains an attribute that is not configured for this category.',
                    ]);
                }
            }

            if (
                isset(
                    $variant['minimum_order_quantity'],
                    $variant['available_quantity']
                ) &&
                (float) $variant['minimum_order_quantity'] >
                (float) $variant['available_quantity']
            ) {
                throw ValidationException::withMessages([
                    "variants.{$index}.minimum_order_quantity" =>
                        'Variant minimum order quantity cannot exceed its available quantity.',
                ]);
            }
        }
    }

    private function normalizeProductAttributes(
        array $values,
        ProductCategory $category
    ): array {
        $definitions = $category->attributes
            ->where('is_variant_attribute', false);

        $normalized = [];

        foreach ($definitions as $definition) {
            $value = $values[(string) $definition->id] ?? null;

            if ($value !== null && trim($value) !== '') {
                $normalized[$definition->slug] = trim($value);
            }
        }

        return $normalized;
    }

    private function syncVariants(
        Product $product,
        ProductCategory $category,
        array $validated
    ): void {
        $variantDefinitions = $category->attributes
            ->where('is_variant_attribute', true);

        $submittedVariants = $validated['variants'] ?? [];

        $existingVariants = $product->variants()
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $submittedIds = collect($submittedVariants)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        if ($submittedIds->count() !== $submittedIds->unique()->count()) {
            throw ValidationException::withMessages([
                'variants' => 'A variant was submitted more than once.',
            ]);
        }

        $unknownIds = $submittedIds->diff($existingVariants->keys());

        if ($unknownIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'variants' => 'One or more variants do not belong to this product.',
            ]);
        }

        $retainedIds = [];

        foreach ($submittedVariants as $index => $variantData) {
            $variantId = isset($variantData['id'])
                ? (int) $variantData['id']
                : null;

            $sku = isset($variantData['sku']) &&
                trim($variantData['sku']) !== ''
                    ? trim($variantData['sku'])
                    : null;

            if ($sku !== null) {
                $duplicateInRequest = collect($submittedVariants)
                    ->filter(function ($other, $otherIndex) use ($sku, $index) {
                        return $otherIndex !== $index &&
                            isset($other['sku']) &&
                            trim($other['sku']) !== '' &&
                            Str::lower(trim($other['sku'])) === Str::lower($sku);
                    })
                    ->isNotEmpty();

                if ($duplicateInRequest) {
                    throw ValidationException::withMessages([
                        'variants' => 'Each variant must have a unique SKU.',
                    ]);
                }

                $skuExists = ProductVariant::where('sku', $sku)
                    ->when(
                        $variantId,
                        fn ($query) => $query->where('id', '!=', $variantId)
                    )
                    ->exists();

                if ($skuExists) {
                    throw ValidationException::withMessages([
                        "variants.{$index}.sku" =>
                            "The SKU {$sku} is already in use.",
                    ]);
                }
            }

            $attributes = [];

            foreach ($variantDefinitions as $definition) {
                $value = $variantData['attributes'][(string) $definition->id] ?? null;

                if ($value !== null && trim($value) !== '') {
                    $attributes[$definition->slug] = trim($value);
                }
            }

            $data = [
                'sku' => $sku,
                'attributes' => $attributes,
                'price_per_unit' => $variantData['price_per_unit'],
                'available_quantity' => $variantData['available_quantity'],
                'minimum_order_quantity' => $variantData['minimum_order_quantity'],
                'is_active' => true,
            ];

            if ($variantId) {
                $variant = $existingVariants->get($variantId);
                $variant->update($data);
            } else {
                $variant = $product->variants()->create($data);
            }

            $retainedIds[] = $variant->id;
        }

        $product->variants()
            ->whereNotIn('id', $retainedIds ?: [0])
            ->delete();
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug ?: 'product';
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $counter++;
            $slug = ($baseSlug ?: 'product') . '-' . $counter;
        }

        return $slug;
    }
}
