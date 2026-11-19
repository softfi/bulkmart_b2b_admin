<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'phone',
        'business_address',
        'city',
        'state',
        'pincode',
        'gst_number',
        'approval_status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(VendorStatusHistory::class)
            ->with('reviewer')
            ->latest();
    }

    public function products(): HasMany
{
    return $this->hasMany(Product::class);
}
    
}
