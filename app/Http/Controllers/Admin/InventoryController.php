<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'stock' => ['nullable', 'in:all,in_stock,out_of_stock'],
        ]);

        $products = Product::withTrashed()
            ->with('vendorProfile')
            ->withSum('inventoryBatches as total_received_quantity', 'quantity_received')
            ->withSum('inventoryBatches as total_remaining_quantity', 'quantity_remaining')
            ->whereHas('inventoryBatches')
            ->when(!empty($validated['search']), function ($query) use ($validated) {
                $term = $validated['search'];

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhereHas('vendorProfile', function ($vendorQuery) use ($term) {
                            $vendorQuery->where('business_name', 'like', "%{$term}%");
                        });
                });
            })
            ->when(($validated['stock'] ?? 'all') === 'in_stock', function ($query) {
                $query->whereHas('inventoryBatches', function ($batchQuery) {
                    $batchQuery->where('quantity_remaining', '>', 0);
                });
            })
            ->when(($validated['stock'] ?? 'all') === 'out_of_stock', function ($query) {
                $query->whereDoesntHave('inventoryBatches', function ($batchQuery) {
                    $batchQuery->where('quantity_remaining', '>', 0);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.index', compact('products'));
    }

    public function show($product)
    {
        $product = Product::withTrashed()
            ->with([
                'vendorProfile',
                'inventoryBatches' => function ($query) {
                    $query->with([
                        'vendorPurchase',
                        'receiptItem.goodsReceipt',
                        'adjustments.administrator',
                    ])->latest('received_at');
                },
            ])
            ->withSum('inventoryBatches as total_received_quantity', 'quantity_received')
            ->withSum('inventoryBatches as total_remaining_quantity', 'quantity_remaining')
            ->findOrFail($product);

        return view('admin.inventory.show', compact('product'));
    }
}
