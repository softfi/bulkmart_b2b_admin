<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorPurchase;
use Illuminate\Http\Request;

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

        // A vendor must never access another vendor's purchase.
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
            round(
                (float) $purchase->quantity - $receivedQuantity,
                3
            )
        );

        return view('vendor.purchases.show', compact(
            'purchase',
            'receivedQuantity',
            'remainingQuantity'
        ));
    }
}