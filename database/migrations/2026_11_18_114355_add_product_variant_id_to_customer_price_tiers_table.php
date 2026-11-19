
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_price_tiers', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('customer_listing_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->index(
                ['customer_listing_id', 'product_variant_id', 'minimum_quantity'],
                'customer_tier_variant_lookup_idx'
            );
        });

        Schema::table('customer_price_tiers', function (Blueprint $table) {
            $table->dropUnique('customer_tier_min_qty_unique');

            $table->unique(
                [
                    'customer_listing_id',
                    'product_variant_id',
                    'minimum_quantity',
                ],
                'customer_tier_variant_min_qty_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('customer_price_tiers', function (Blueprint $table) {
            $table->dropUnique('customer_tier_variant_min_qty_unique');
            $table->dropIndex('customer_tier_variant_lookup_idx');
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');

            $table->unique(
                ['customer_listing_id', 'minimum_quantity'],
                'customer_tier_min_qty_unique'
            );
        });
    }
};
