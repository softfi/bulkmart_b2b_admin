<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductManagementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'approved', 'rejected', 'suspended']),
            ],
        ]);

        $query = Product::with(['vendorProfile.user']);

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('vendorProfile', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('business_name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        $counts = Product::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.products.index', compact('products', 'counts'));
    }

    public function show(Product $product)
    {
        $product->load([
            'vendorProfile.user',
            'reviewer',
            'statusHistories.reviewer',
            'statusHistories.actor',
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function updateStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['approved', 'rejected', 'suspended']),
            ],
            'reason' => [
                'nullable',
                'string',
                'max:2000',
                'required_if:status,rejected,suspended',
            ],
        ]);

        DB::transaction(function () use ($request, $product, $validated) {
            $lockedProduct = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $lockedProduct->status;
            $newStatus = $validated['status'];

            if ($previousStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' => 'This product already has the selected status.',
                ]);
            }

            $lockedProduct->status = $newStatus;
            $lockedProduct->admin_note = $validated['reason'] ?? null;
            $lockedProduct->reviewed_by = $request->user()->id;
            $lockedProduct->reviewed_at = now();
            $lockedProduct->save();

            $lockedProduct->statusHistories()->create([
                'reviewed_by' => $request->user()->id,
                'changed_by' => $request->user()->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'reason' => $validated['reason'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product status updated successfully.');
    }

    public function previewImage(Product $product)
{
    abort_unless($product->image_path, 404);

    $disk = Storage::disk('local');

    abort_unless($disk->exists($product->image_path), 404);

    $path = $disk->path($product->image_path);

    return response()->file($path, [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'inline',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff',
    ]);
}
}