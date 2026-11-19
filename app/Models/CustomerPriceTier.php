<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_listing_id',
        'product_variant_id',
        'minimum_quantity',
        'maximum_quantity',
        'unit_price',
    ];

    protected $casts = [
        'minimum_quantity' => 'decimal:3',
        'maximum_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
    ];

    public function customerListing(): BelongsTo
    {
        return $this->belongsTo(CustomerListing::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
