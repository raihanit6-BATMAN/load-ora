<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_index');
            $table->json('raw_data');
            $table->json('parsed_data')->nullable();
            $table->enum('validation_status', ['pending', 'valid', 'warning', 'error', 'imported', 'skipped'])->default('pending')->index();
            $table->json('validation_errors')->nullable();
            $table->foreignId('dispatch_id')->nullable()->constrained('dispatches')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
