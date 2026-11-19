<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPurchaseStatusHistory extends Model
{
    protected $fillable = [
        'vendor_purchase_id',
        'changed_by',
        'previous_status',
        'new_status',
        'previous_payment_status',
        'new_payment_status',
        'notes',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(VendorPurchase::class, 'vendor_purchase_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}