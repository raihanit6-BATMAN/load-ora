<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('file_type', 20)->default('csv');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->enum('status', ['uploaded', 'mapped', 'validating', 'completed', 'failed', 'rolled_back'])->default('uploaded')->index();
            $table->json('column_mapping')->nullable();
            $table->json('summary')->nullable();
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
