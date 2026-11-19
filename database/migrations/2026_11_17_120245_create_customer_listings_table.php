<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();

            $table->string('display_name', 150)->nullable();
            $table->text('customer_description')->nullable();
            $table->string('image_path')->nullable();

            $table->decimal('minimum_order_quantity', 12, 3)->default(1);

            $table->boolean('is_active')->default(false);

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_listings');
    }
};
