<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_purchases', function (Blueprint $table) {
            $table->id();

            $table->string('purchase_number')->unique();

            $table->foreignId('vendor_profile_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 14, 2);

            $table->string('status')->default('draft');
            // draft, ordered, partially_received, received, cancelled

            $table->string('payment_status')->default('unpaid');
            // unpaid, partially_paid, paid

            $table->date('purchase_date');
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['vendor_profile_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_purchases');
    }
};
