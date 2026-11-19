<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_documents', function (Blueprint $table) {
             $table->id();

        $table->foreignId('vendor_profile_id')
            ->constrained('vendor_profiles')
            ->cascadeOnDelete();

        $table->string('document_type', 30);
        $table->string('file_path');
        $table->string('original_filename');
        $table->string('mime_type', 100);
        $table->unsignedBigInteger('file_size');

        $table->string('review_status', 20)->default('pending');
        $table->text('admin_note')->nullable();
        $table->foreignId('reviewed_by')->nullable()
            ->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();

        $table->timestamps();

        $table->index(['vendor_profile_id', 'review_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
