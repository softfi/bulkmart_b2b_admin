<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount(['attributes', 'products'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        DB::transaction(function () use ($validated) {
            $category = ProductCategory::create([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            $this->saveAttributes(
                $category,
                $validated['attributes'] ?? []
            );
        });

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $category)
    {
        $category->load('attributes');

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $this->validateCategory($request, $category);

        DB::transaction(function () use ($category, $validated) {
            $lockedCategory = ProductCategory::whereKey($category->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldCategoryName = $lockedCategory->name;
            $newCategoryName = $validated['name'];

            $lockedCategory->update([
                'name' => $newCategoryName,
                'slug' => $this->uniqueSlug(
                    $newCategoryName,
                    $lockedCategory->id
                ),
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            /*
             * Keep the legacy products.category field synchronized
             * with the renamed category.
             *
             * This ensures admin and vendor product tables display
             * the latest category name.
             */
            if ($oldCategoryName !== $newCategoryName) {
                DB::table('products')
                    ->where('category_id', $lockedCategory->id)
                    ->update([
                        'category' => $newCategoryName,
                    ]);
            }

            $this->saveAttributes(
                $lockedCategory,
                $validated['attributes'] ?? []
            );
        });

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Product category updated successfully. Linked products have also been synchronized.');
    }

    public function toggleStatus(ProductCategory $category)
    {
        $category->update([
            'is_active' => !$category->is_active,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                $category->is_active
                    ? 'Category activated successfully.'
                    : 'Category deactivated successfully.'
            );
    }

    private function validateCategory(
        Request $request,
        ?ProductCategory $category = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],
            'attributes' => [
                'nullable',
                'array',
                'max:50',
            ],
            'attributes.*.id' => [
                'nullable',
                'integer',
                'distinct',
            ],
            'attributes.*.name' => [
                'required',
                'string',
                'max:100',
            ],
            'attributes.*.input_type' => [
                'required',
                Rule::in(['text', 'number', 'select']),
            ],
            'attributes.*.options_text' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'attributes.*.is_required' => [
                'nullable',
                'boolean',
            ],
            'attributes.*.is_variant_attribute' => [
                'nullable',
                'boolean',
            ],
            'attributes.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],
        ]);
    }

    private function saveAttributes(
        ProductCategory $category,
        array $attributes
    ): void {
        $existingAttributes = $category->attributes()
            ->get()
            ->keyBy('id');

        $submittedIds = collect($attributes)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $unknownIds = $submittedIds->diff(
            $existingAttributes->keys()
        );

        if ($unknownIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'attributes' =>
                    'One or more submitted attributes do not belong to this category. Refresh the page and try again.',
            ]);
        }

        $hasProducts = $category->products()->exists();

        $removedIds = $existingAttributes->keys()
            ->diff($submittedIds);

        if ($hasProducts && $removedIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'attributes' =>
                    'Attributes cannot be removed after products have been assigned to this category. Deactivate or stop using the attribute instead.',
            ]);
        }

        foreach ($attributes as $attributeData) {
            $name = trim($attributeData['name']);
            $inputType = $attributeData['input_type'];

            $options = null;

            if ($inputType === 'select') {
                $options = collect(
                    preg_split(
                        '/\r\n|\r|\n/',
                        $attributeData['options_text'] ?? ''
                    )
                )
                    ->map(fn ($option) => trim($option))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($options)) {
                    throw ValidationException::withMessages([
                        'attributes' =>
                            "Please provide options for the {$name} attribute.",
                    ]);
                }
            }

            $attributeId = $attributeData['id'] ?? null;

            if ($attributeId) {
                $attribute = $existingAttributes->get((int) $attributeId);

                if (!$attribute) {
                    throw ValidationException::withMessages([
                        'attributes' =>
                            'An invalid category attribute was submitted.',
                    ]);
                }

                $configurationChanged =
                    $attribute->input_type !== $inputType ||
                    $this->normalizeOptions($attribute->options) !==
                        $this->normalizeOptions($options);

                if ($hasProducts && $configurationChanged) {
                    throw ValidationException::withMessages([
                        'attributes' =>
                            "The type or options for '{$attribute->name}' cannot be changed after products have been assigned to this category.",
                    ]);
                }

                // Preserve the existing slug because product and variant
                // attribute values may already use it as a JSON key.
                $attribute->update([
                    'name' => $name,
                    'input_type' => $inputType,
                    'options' => $options,
                    'is_required' =>
                        $attributeData['is_required'] ?? false,
                    'is_variant_attribute' =>
                        $attributeData['is_variant_attribute'] ?? false,
                    'sort_order' => $attributeData['sort_order'] ?? 0,
                ]);
            } else {
                $slug = Str::slug($name);

                if ($slug === '') {
                    $slug = 'attribute-' . Str::lower(Str::random(8));
                }

                $category->attributes()->create([
                    'name' => $name,
                    'slug' => $this->uniqueAttributeSlug(
                        $category,
                        $slug
                    ),
                    'input_type' => $inputType,
                    'options' => $options,
                    'is_required' =>
                        $attributeData['is_required'] ?? false,
                    'is_variant_attribute' =>
                        $attributeData['is_variant_attribute'] ?? false,
                    'sort_order' => $attributeData['sort_order'] ?? 0,
                ]);
            }
        }

        if (!$hasProducts && $removedIds->isNotEmpty()) {
            $category->attributes()
                ->whereIn('id', $removedIds)
                ->delete();
        }
    }

    private function normalizeOptions(?array $options): array
    {
        return collect($options ?? [])
            ->map(fn ($option) => trim((string) $option))
            ->values()
            ->all();
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($name);
        $slug = $base ?: 'category';

        $counter = 1;

        while (
            ProductCategory::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $counter++;
            $slug = ($base ?: 'category') . '-' . $counter;
        }

        return $slug;
    }

    private function uniqueAttributeSlug(
        ProductCategory $category,
        string $baseSlug,
        ?int $ignoreId = null
    ): string {
        $slug = $baseSlug;
        $counter = 1;

        while (
            $category->attributes()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }
}
