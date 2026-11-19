<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_item_id')
                ->unique()
                ->constrained('goods_receipt_items')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignId('vendor_profile_id')
                ->constrained('vendor_profiles')
                ->restrictOnDelete();

            $table->foreignId('vendor_purchase_id')
                ->constrained('vendor_purchases')
                ->restrictOnDelete();

            $table->decimal('quantity_received', 12, 3);
            $table->decimal('quantity_remaining', 12, 3);
            $table->decimal('unit_cost', 12, 2);

            $table->dateTime('received_at');
            $table->timestamps();

            $table->index(['product_id', 'received_at']);
            $table->index(['vendor_purchase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};