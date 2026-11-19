<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_price_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_listing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('minimum_quantity', 12, 3);
            $table->decimal('maximum_quantity', 12, 3)->nullable();

            $table->decimal('unit_price', 12, 2);

            $table->timestamps();

            $table->unique(
                ['customer_listing_id', 'minimum_quantity'],
                'customer_tier_min_qty_unique'
            );

            $table->index(
                ['customer_listing_id', 'minimum_quantity', 'maximum_quantity'],
                'customer_tier_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_price_tiers');
    }
};
