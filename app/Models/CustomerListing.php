<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'display_name',
        'customer_description',
        'image_path',
        'minimum_order_quantity',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'minimum_order_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(CustomerPriceTier::class)
            ->orderBy('minimum_quantity');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the applicable unit price for a quantity.
     *
     * Variant-specific tiers take priority. If no matching
     * variant tier exists, product-level tiers are used.
     */
    public function priceForQuantity(
        $quantity,
        ?int $productVariantId = null
    ): ?string {
        $query = $this->priceTiers()
            ->where('minimum_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('maximum_quantity')
                    ->orWhere('maximum_quantity', '>=', $quantity);
            });

        if ($productVariantId !== null) {
            $query->where(function ($query) use ($productVariantId) {
                $query->where('product_variant_id', $productVariantId)
                    ->orWhereNull('product_variant_id');
            });

            $query->orderByRaw(
                'CASE WHEN product_variant_id = ? THEN 0 ELSE 1 END',
                [$productVariantId]
            );
        } else {
            $query->whereNull('product_variant_id');
        }

        $tier = $query
            ->orderBy('minimum_quantity', 'desc')
            ->first();

        return $tier?->unit_price;
    }
}
