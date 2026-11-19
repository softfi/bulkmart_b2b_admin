<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorPurchase;
use App\Models\VendorProfile;
use App\Models\VendorPurchaseStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\VendorPurchaseCreated;

class VendorPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = VendorPurchase::with([
            'vendorProfile',
            'product',
            'productVariant',
            'creator',
        ])
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->search;

                $q->where(function ($sub) use ($term) {
                    $sub->where('purchase_number', 'like', "%{$term}%")
                        ->orWhereHas('vendorProfile', fn ($v) =>
                            $v->where('business_name', 'like', "%{$term}%"))
                        ->orWhereHas('product', fn ($p) =>
                            $p->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('productVariant', fn ($v) =>
                            $v->where('sku', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.purchases.index', compact('purchases'));
    }

   public function create(Request $request)
{
    $vendors = VendorProfile::where('approval_status', 'approved')
        ->whereHas('products', fn ($q) => $q->where('status', 'approved'))
        ->with([
            'products' => fn ($q) => $q
                ->where('status', 'approved')
                ->with(['variants', 'productCategory'])
                ->orderBy('name'),
        ])
        ->orderBy('business_name')
        ->get();

    // Accept marketplace preselection only when the product belongs
    // to an approved vendor and is an approved product.
    $selectedProductId = $request->query('product_id');
    $selectedVariantId = $request->query('product_variant_id');

    $selectedProduct = null;

    if ($selectedProductId && ctype_digit((string) $selectedProductId)) {
        $selectedProduct = $vendors
            ->flatMap(fn ($vendor) => $vendor->products)
            ->firstWhere('id', (int) $selectedProductId);
    }

    // A variant must belong to the selected product and be active.
    if ($selectedProduct && $selectedVariantId) {
        $validVariant = $selectedProduct->variants
            ->first(fn ($variant) =>
                $variant->is_active &&
                (string) $variant->id === (string) $selectedVariantId
            );

        $selectedVariantId = $validVariant?->id;
    } else {
        $selectedVariantId = null;
    }

    return view('admin.purchases.create', [
        'vendors' => $vendors,
        'selectedProductId' => old('product_id', $selectedProduct?->id),
        'selectedVariantId' => old('product_variant_id', $selectedVariantId),
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'unit_price' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:9999999999',
            ],
            'purchase_date' => ['required', 'date'],
            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:purchase_date',
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $purchase = DB::transaction(function () use ($validated, $request) {
            $product = Product::with('vendorProfile')
                ->whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $product->status !== 'approved' ||
                !$product->vendorProfile ||
                $product->vendorProfile->approval_status !== 'approved'
            ) {
                throw ValidationException::withMessages([
                    'product_id' => 'Only products from approved vendors can be purchased.',
                ]);
            }

            $variant = null;

            $hasVariants = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->exists();

            if ($hasVariants && empty($validated['product_variant_id'])) {
                throw ValidationException::withMessages([
                    'product_variant_id' =>
                        'Select a variant before purchasing this product.',
                ]);
            }

            if (!empty($validated['product_variant_id'])) {
                $variant = ProductVariant::whereKey(
                    $validated['product_variant_id']
                )
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'product_variant_id' =>
                            'The selected variant is invalid or inactive for this product.',
                    ]);
                }
            }

            $quantity = (float) $validated['quantity'];
            $available = (float) $product->available_quantity;
            $reserved = (float) $product->reserved_quantity;

            $remaining = round($available - $reserved, 3);

            if ($quantity > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Requested quantity exceeds the remaining unreserved product stock.',
                ]);
            }

            /*
             * Variant quantities are reserved independently.
             * Existing partially received purchases reserve only
             * their outstanding quantity.
             */
            if ($variant) {
                $variantPurchases = VendorPurchase::where(
                    'product_variant_id',
                    $variant->id
                )
                    ->whereIn('status', ['ordered', 'partially_received'])
                    ->with('goodsReceipts.items')
                    ->get();

                $variantReserved = $variantPurchases->sum(
                    function ($existingPurchase) {
                        $received = $existingPurchase->goodsReceipts
                            ->flatMap(fn ($receipt) => $receipt->items)
                            ->sum('quantity_received');

                        return max(
                            0,
                            round(
                                (float) $existingPurchase->quantity -
                                (float) $received,
                                3
                            )
                        );
                    }
                );

                $variantRemaining = round(
                    (float) $variant->available_quantity - $variantReserved,
                    3
                );

                if ($quantity > $variantRemaining) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'Requested quantity exceeds the remaining unreserved stock for this variant. Remaining: ' .
                            number_format($variantRemaining, 3) . '.',
                    ]);
                }
            }

            do {
                $number = 'VP-' . now()->format('Ymd') . '-' .
                    Str::upper(Str::random(6));
            } while (
                VendorPurchase::where('purchase_number', $number)->exists()
            );

            $subtotal = round(
                $quantity * (float) $validated['unit_price'],
                2
            );

            // Preserve aggregate product-level reservation accounting.
            $product->increment('reserved_quantity', $quantity);

            $purchase = VendorPurchase::create([
                'purchase_number' => $number,
                'vendor_profile_id' => $product->vendor_profile_id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'created_by' => $request->user()->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'],
                'subtotal' => $subtotal,
                'status' => 'ordered',
                'payment_status' => 'unpaid',
                'purchase_date' => $validated['purchase_date'],
                'expected_delivery_date' =>
                    $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            VendorPurchaseStatusHistory::create([
                'vendor_purchase_id' => $purchase->id,
                'changed_by' => $request->user()->id,
                'previous_status' => null,
                'new_status' => 'ordered',
                'previous_payment_status' => null,
                'new_payment_status' => 'unpaid',
                'notes' => $variant
                    ? 'Purchase created for variant ' .
                        ($variant->sku ?: '#' . $variant->id) .
                        ' and vendor stock reserved.'
                    : 'Purchase created and vendor stock reserved.',
            ]);

            $vendor = VendorProfile::with('user')
                ->findOrFail($purchase->vendor_profile_id);

            if ($vendor->user) {
                $vendor->user->notify(new VendorPurchaseCreated($purchase));
            }

            return $purchase;
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with(
                'success',
                'Vendor purchase created and stock reserved successfully.'
            );
    }

    public function show(VendorPurchase $purchase)
    {
        $purchase->load([
            'vendorProfile.user',
            'product',
            'productVariant',
            'creator',
            'goodsReceipts.receiver',
            'goodsReceipts.items.product',
            'goodsReceipts.items.productVariant',
        ]);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function updateStatus(Request $request, VendorPurchase $purchase)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'ordered',
                    'partially_received',
                    'received',
                    'cancelled',
                ]),
            ],
            'payment_status' => [
                'required',
                Rule::in(['unpaid', 'partially_paid', 'paid']),
            ],
        ]);

        DB::transaction(function () use ($validated, $request, $purchase) {
            $lockedPurchase = VendorPurchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $lockedPurchase->status;
            $oldPaymentStatus = $lockedPurchase->payment_status;
            $newStatus = $validated['status'];
            $newPaymentStatus = $validated['payment_status'];

            // Receipt statuses can only be changed by GoodsReceiptController.
            if (
                $newStatus !== $oldStatus &&
                $newStatus !== 'cancelled'
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Receipt status can only be updated by recording a goods receipt.',
                ]);
            }

            // A partially received purchase must be reconciled first.
            if (
                $oldStatus === 'partially_received' &&
                $newStatus === 'cancelled'
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'A partially received purchase must be reconciled before cancellation.',
                ]);
            }

            if (
                $oldStatus === 'received' &&
                $newStatus === 'cancelled'
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'A fully received purchase cannot be cancelled.',
                ]);
            }

            if (
                $oldStatus === 'cancelled' &&
                $newPaymentStatus !== 'unpaid'
            ) {
                throw ValidationException::withMessages([
                    'payment_status' =>
                        'A cancelled purchase cannot be marked as paid.',
                ]);
            }

            // Release the reserved vendor quantity exactly once on cancellation.
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $product = Product::withTrashed()
                    ->whereKey($lockedPurchase->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $receivedQuantity = (float) $lockedPurchase->goodsReceipts()
                    ->with('items')
                    ->get()
                    ->flatMap(fn ($receipt) => $receipt->items)
                    ->sum('quantity_received');

                $outstandingQuantity = round(
                    (float) $lockedPurchase->quantity - $receivedQuantity,
                    3
                );

                $reservedQuantity = (float) $product->reserved_quantity;

                if (
                    $outstandingQuantity <= 0 ||
                    $reservedQuantity < $outstandingQuantity
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Reserved stock is inconsistent. Please investigate before cancelling.',
                    ]);
                }

                $product->decrement(
                    'reserved_quantity',
                    $outstandingQuantity
                );
            }

            $lockedPurchase->update([
                'status' => $newStatus,
                'payment_status' => $newPaymentStatus,
            ]);

            if (
                $oldStatus !== $newStatus ||
                $oldPaymentStatus !== $newPaymentStatus
            ) {
                VendorPurchaseStatusHistory::create([
                    'vendor_purchase_id' => $lockedPurchase->id,
                    'changed_by' => $request->user()->id,
                    'previous_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'previous_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $newPaymentStatus,
                    'notes' => $newStatus === 'cancelled'
                        ? 'Purchase cancelled and outstanding reserved vendor stock released.'
                        : 'Payment status updated.',
                ]);
            }
        });

        return back()->with('success', 'Purchase updated successfully.');
    }
}
