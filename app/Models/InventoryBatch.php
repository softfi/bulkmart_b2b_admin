<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    protected $fillable = [
        'goods_receipt_item_id',
        'product_id',
        'product_variant_id',
        'vendor_profile_id',
        'vendor_purchase_id',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
        'received_at',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_remaining' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function receiptItem(): BelongsTo
    {
        return $this->belongsTo(
            GoodsReceiptItem::class,
            'goods_receipt_item_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function vendorPurchase(): BelongsTo
    {
        return $this->belongsTo(VendorPurchase::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class)
            ->with('administrator')
            ->latest();
    }
}
