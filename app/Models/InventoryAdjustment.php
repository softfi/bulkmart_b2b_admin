<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_batch_id',
        'adjusted_by',
        'adjustment_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
    ];

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    public function administrator()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
