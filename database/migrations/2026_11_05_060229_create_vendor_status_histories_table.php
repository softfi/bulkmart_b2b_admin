
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_profile_id')
                ->constrained('vendor_profiles')
                ->cascadeOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['vendor_profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_status_histories');
    }
};
