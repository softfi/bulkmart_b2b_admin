<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_batch_id')
                ->constrained('inventory_batches')
                ->restrictOnDelete();

            $table->foreignId('adjusted_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('adjustment_type', 20); // increase or decrease
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_before', 12, 3);
            $table->decimal('quantity_after', 12, 3);
            $table->text('reason');

            $table->timestamps();

            $table->index(
                ['inventory_batch_id', 'created_at'],
                'inventory_adjustment_batch_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
