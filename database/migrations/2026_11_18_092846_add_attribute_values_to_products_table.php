<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'attribute_values')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('attribute_values')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'attribute_values')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('attribute_values');
            });
        }
    }
};
