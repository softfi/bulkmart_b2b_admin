<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendor_purchase_status_histories')) {
            return;
        }

        Schema::create('vendor_purchase_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_purchase_id')
                ->constrained('vendor_purchases')
                ->cascadeOnDelete();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->string('previous_payment_status')->nullable();
            $table->string('new_payment_status')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['vendor_purchase_id', 'created_at'],
                'vp_status_history_lookup_idx'
            );
        });
    }

public function down(): void
{
    Schema::dropIfExists('vendor_purchase_status_histories');
}
};
