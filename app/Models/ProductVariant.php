<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price_per_unit',
        'available_quantity',
        'minimum_order_quantity',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price_per_unit' => 'decimal:2',
        'available_quantity' => 'decimal:3',
        'minimum_order_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
