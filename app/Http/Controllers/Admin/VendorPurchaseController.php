<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\VendorPurchase;
use App\Models\VendorPurchaseStatusHistory;
use App\Notifications\VendorPurchaseResponded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->vendorProfile;

        abort_unless($profile, 403);

        $purchases = VendorPurchase::with([
            'product',
            'productVariant',
            'goodsReceipts.items',
        ])
            ->where('vendor_profile_id', $profile->id)
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(15);

        return view('vendor.purchases.index', compact('purchases'));
    }

    public function show(Request $request, VendorPurchase $purchase)
    {
        $profile = $request->user()->vendorProfile;

        abort_unless(
            $profile &&
            (int) $purchase->vendor_profile_id === (int) $profile->id,
            404
        );

        $purchase->load([
            'product',
            'productVariant',
            'goodsReceipts.items',
            'statusHistories.actor',
        ]);

        $receivedQuantity = $purchase->goodsReceipts
            ->flatMap(fn ($receipt) => $receipt->items)
            ->sum(fn ($item) => (float) $item->quantity_received);

        $remainingQuantity = max(
            0,
            round((float) $purchase->quantity - $receivedQuantity, 3)
        );

        return view('vendor.purchases.show', compact(
            'purchase',
            'receivedQuantity',
            'remainingQuantity'
        ));
    }

    public function respond(Request $request, VendorPurchase $purchase)
    {
        $profile = $request->user()->vendorProfile;

        abort_unless(
            $profile &&
            (int) $purchase->vendor_profile_id === (int) $profile->id,
            404
        );

        $validated = $request->validate([
            'decision' => ['required', 'in:accepted,rejected'],
            'reason' => [
                'required_if:decision,rejected',
                'nullable',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use ($request, $purchase, $validated) {
            $lockedPurchase = VendorPurchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedPurchase->vendor_profile_id !==
                $request->user()->vendorProfile->id
            ) {
                abort(404);
            }

            if (
                $lockedPurchase->vendor_response_status !== 'pending' ||
                $lockedPurchase->status !== 'ordered'
            ) {
                throw ValidationException::withMessages([
                    'decision' => 'This purchase has already been processed or is no longer awaiting a response.',
                ]);
            }

            $oldStatus = $lockedPurchase->status;
            $decision = $validated['decision'];

            if ($decision === 'rejected') {
                $product = Product::withTrashed()
                    ->whereKey($lockedPurchase->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // A pending purchase cannot have received goods.
                $receivedQuantity = (float) $lockedPurchase->goodsReceipts()
                    ->with('items')
                    ->get()
                    ->flatMap(fn ($receipt) => $receipt->items)
                    ->sum('quantity_received');

                if ($receivedQuantity > 0) {
                    throw ValidationException::withMessages([
                        'decision' => 'This purchase already has received goods and cannot be rejected.',
                    ]);
                }

                $reservedQuantity = (float) $product->reserved_quantity;
                $orderedQuantity = (float) $lockedPurchase->quantity;

                if ($reservedQuantity < $orderedQuantity) {
                    throw ValidationException::withMessages([
                        'decision' => 'Reserved stock is inconsistent. Please contact the administrator.',
                    ]);
                }

                // Release the product-level reservation exactly once.
                $product->decrement('reserved_quantity', $orderedQuantity);

                $lockedPurchase->update([
                    'status' => 'cancelled',
                    'vendor_response_status' => 'rejected',
                    'vendor_response_reason' => $validated['reason'],
                    'vendor_responded_at' => now(),
                ]);
            } else {
                $lockedPurchase->update([
                    'vendor_response_status' => 'accepted',
                    'vendor_response_reason' => null,
                    'vendor_responded_at' => now(),
                ]);
            }

            VendorPurchaseStatusHistory::create([
                'vendor_purchase_id' => $lockedPurchase->id,
                'changed_by' => $request->user()->id,
                'previous_status' => $oldStatus,
                'new_status' => $lockedPurchase->status,
                'previous_payment_status' => $lockedPurchase->payment_status,
                'new_payment_status' => $lockedPurchase->payment_status,
                'notes' => $decision === 'accepted'
                    ? 'Vendor accepted the full purchase quantity.'
                    : 'Vendor rejected the purchase. Reason: ' .
                        $validated['reason'] .
                        ' Outstanding reserved stock released.',
            ]);

            $lockedPurchase->load([
                'creator',
                'product',
                'vendorProfile',
            ]);

            if ($lockedPurchase->creator) {
                $lockedPurchase->creator->notify(
                    new VendorPurchaseResponded($lockedPurchase)
                );
            }
        });

        return redirect()
            ->route('vendor.purchases.show', $purchase)
            ->with(
                'success',
                $validated['decision'] === 'accepted'
                    ? 'Purchase accepted successfully.'
                    : 'Purchase rejected. The reserved quantity has been released.'
            );
    }
}