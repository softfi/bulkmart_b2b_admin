<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorPurchase;
use App\Models\VendorPurchaseStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    public function create(VendorPurchase $purchase)
    {
        $purchase->load(['vendorProfile', 'product']);

        abort_unless(
            $purchase->vendor_response_status === 'accepted',
            422,
            'The vendor must accept this purchase before goods can be received.'
        );

        if ($purchase->product_variant_id) {
            $purchase->setRelation(
                'productVariant',
                ProductVariant::whereKey($purchase->product_variant_id)
                    ->where('product_id', $purchase->product_id)
                    ->firstOrFail()
            );
        }

        abort_if(
            in_array($purchase->status, ['cancelled', 'received'], true),
            422,
            'This purchase cannot receive any more stock.'
        );

        $received = (float) $purchase->goodsReceipts()
            ->with('items')
            ->get()
            ->flatMap(fn ($receipt) => $receipt->items)
            ->sum('quantity_received');

        $remaining = round((float) $purchase->quantity - $received, 3);

        return view('admin.purchases.receive', compact(
            'purchase',
            'remaining'
        ));
    }

    public function store(Request $request, VendorPurchase $purchase)
    {
        $validated = $request->validate([
            'quantity_received' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $receipt = DB::transaction(function () use (
            $request,
            $purchase,
            $validated
        ) {
            $lockedPurchase = VendorPurchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->vendor_response_status !== 'accepted') {
                throw ValidationException::withMessages([
                    'quantity_received' =>
                        'The vendor must accept this purchase before goods can be received.',
                ]);
            }

            if (in_array($lockedPurchase->status, ['cancelled', 'received'], true)) {
                throw ValidationException::withMessages([
                    'quantity_received' => 'This purchase is not open for receiving.',
                ]);
            }

            $receivedSoFar = (float) GoodsReceiptItem::whereHas(
                'goodsReceipt',
                fn ($query) => $query->where(
                    'vendor_purchase_id',
                    $lockedPurchase->id
                )
            )->sum('quantity_received');

            $ordered = (float) $lockedPurchase->quantity;
            $newQuantity = (float) $validated['quantity_received'];
            $outstanding = round($ordered - $receivedSoFar, 3);

            if ($newQuantity > $outstanding) {
                throw ValidationException::withMessages([
                    'quantity_received' =>
                        'Cannot receive more than the outstanding quantity of ' .
                        number_format($outstanding, 3) . '.',
                ]);
            }

            $product = Product::withTrashed()
                ->whereKey($lockedPurchase->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Resolve and lock the exact variant ordered.
             * A variant must belong to the purchased product.
             */
            $variant = null;

            if ($lockedPurchase->product_variant_id) {
                $variant = ProductVariant::whereKey(
                    $lockedPurchase->product_variant_id
                )
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'quantity_received' =>
                            'The purchased product variant no longer exists or does not belong to this product.',
                    ]);
                }
            }

            $reserved = (float) $product->reserved_quantity;

            if ($reserved < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity_received' =>
                        'Reserved vendor stock is insufficient. Investigate stock records before receiving.',
                ]);
            }

            $receiptNumber = 'GR-' . now()->format('Ymd') . '-' .
                Str::upper(Str::random(8));

            $receipt = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'vendor_purchase_id' => $lockedPurchase->id,
                'received_by' => $request->user()->id,
                'received_at' => $validated['received_at'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $unitCost = (float) $lockedPurchase->unit_price;
            $subtotal = round($newQuantity * $unitCost, 2);

            $item = GoodsReceiptItem::create([
                'goods_receipt_id' => $receipt->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity_received' => $validated['quantity_received'],
                'unit_cost' => $lockedPurchase->unit_price,
                'subtotal' => $subtotal,
            ]);

            InventoryBatch::create([
                'goods_receipt_item_id' => $item->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'vendor_profile_id' => $lockedPurchase->vendor_profile_id,
                'vendor_purchase_id' => $lockedPurchase->id,
                'quantity_received' => $validated['quantity_received'],
                'quantity_remaining' => $validated['quantity_received'],
                'unit_cost' => $lockedPurchase->unit_price,
                'received_at' => $validated['received_at'],
            ]);

            // Release only the quantity physically received.
            $product->decrement('reserved_quantity', $newQuantity);

            $totalReceived = round($receivedSoFar + $newQuantity, 3);

            $newStatus = $totalReceived >= $ordered
                ? 'received'
                : 'partially_received';

            $oldStatus = $lockedPurchase->status;

            $lockedPurchase->update([
                'status' => $newStatus,
            ]);

            VendorPurchaseStatusHistory::create([
                'vendor_purchase_id' => $lockedPurchase->id,
                'changed_by' => $request->user()->id,
                'previous_status' => $oldStatus,
                'new_status' => $newStatus,
                'previous_payment_status' => $lockedPurchase->payment_status,
                'new_payment_status' => $lockedPurchase->payment_status,
                'notes' => 'Goods receipt ' . $receiptNumber .
                    ' recorded. Quantity received: ' .
                    number_format($newQuantity, 3),
            ]);

            return $receipt;
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with(
                'success',
                'Goods received and added to inventory. Receipt: ' .
                    $receipt->receipt_number
            );
    }
}
