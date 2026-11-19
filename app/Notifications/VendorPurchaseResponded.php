<?php

namespace App\Notifications;

use App\Models\VendorPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VendorPurchaseResponded extends Notification
{
    use Queueable;

    protected array $data;

    public function __construct(VendorPurchase $purchase)
    {
        $this->data = [
            'purchase_id' => $purchase->id,
            'purchase_number' => $purchase->purchase_number,
            'product_name' => $purchase->product?->name ?? 'Product',
            'vendor_name' => $purchase->vendorProfile?->business_name ?? 'Vendor',
            'response_status' => $purchase->vendor_response_status,
            'reason' => $purchase->vendor_response_reason,
            'message' => $purchase->vendor_response_status === 'accepted'
                ? 'The vendor accepted the purchase.'
                : 'The vendor rejected the purchase.',
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->data;
    }
}