<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_purchases', function (Blueprint $table) {
            // Existing purchases are treated as accepted so they remain usable.
            $table->string('vendor_response_status')
                ->default('accepted')
                ->after('status');

            $table->text('vendor_response_reason')
                ->nullable()
                ->after('vendor_response_status');

            $table->timestamp('vendor_responded_at')
                ->nullable()
                ->after('vendor_response_reason');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_response_status',
                'vendor_response_reason',
                'vendor_responded_at',
            ]);
        });
    }
};