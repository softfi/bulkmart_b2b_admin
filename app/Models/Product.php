<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\CustomerListing;



class Product extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_profile_id',
        'category_id',
        'name',
        'slug',
        'description',
        'category',
        'attribute_values',
        'unit',
        'price_per_unit',
        'available_quantity',
        'minimum_order_quantity',
        'image_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'attribute_values' => 'array',
        'price_per_unit' => 'decimal:2',
        'available_quantity' => 'decimal:3',
        'minimum_order_quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'reviewed_at' => 'datetime',
    ];

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'product_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProductStatusHistory::class)
            ->with(['reviewer', 'actor'])
            ->latest();
    }

    public function customerListing(): HasOne
    {
        return $this->hasOne(CustomerListing::class, 'product_id');
    }
}
