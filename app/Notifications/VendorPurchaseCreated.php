<?php

namespace App\Notifications;

use App\Models\VendorPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VendorPurchaseCreated extends Notification
{
    use Queueable;

    protected array $purchaseData;

    public function __construct(VendorPurchase $purchase)
    {
        $purchase->loadMissing(['product', 'productVariant']);

        $this->purchaseData = [
            'purchase_id' => $purchase->id,
            'purchase_number' => $purchase->purchase_number,
            'product_name' => $purchase->product?->name ?? 'Product',
            'variant' => $purchase->productVariant?->sku,
            'quantity' => (string) $purchase->quantity,
            'unit_price' => (string) $purchase->unit_price,
            'subtotal' => (string) $purchase->subtotal,
            'expected_delivery_date' => $purchase->expected_delivery_date?->format('d M Y'),
            'message' => 'Your business has received a new purchase from the admin.',
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->purchaseData;
    }
}