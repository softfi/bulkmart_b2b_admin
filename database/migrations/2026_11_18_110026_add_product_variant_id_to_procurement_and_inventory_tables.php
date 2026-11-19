<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_purchases', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->index(
                ['product_id', 'product_variant_id'],
                'vendor_purchases_product_variant_index'
            );
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->index(
                ['product_id', 'product_variant_id'],
                'goods_receipt_items_product_variant_index'
            );
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->index(
                ['product_id', 'product_variant_id'],
                'inventory_batches_product_variant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex('inventory_batches_product_variant_index');
            $table->dropColumn('product_variant_id');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex('goods_receipt_items_product_variant_index');
            $table->dropColumn('product_variant_id');
        });

        Schema::table('vendor_purchases', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex('vendor_purchases_product_variant_index');
            $table->dropColumn('product_variant_id');
        });
    }
};
