<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_number', 50)->unique()->index();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('goods_id')->constrained('goods')->restrictOnDelete();
            $table->date('loading_date')->index();
            $table->decimal('loading_quantity', 10, 3);
            $table->string('destination_location')->nullable();
            $table->string('loading_point')->nullable();
            $table->enum('status', ['pending', 'partial', 'received', 'cancelled'])->default('pending')->index();
            $table->decimal('total_received_quantity', 10, 3)->default(0.000);
            $table->decimal('total_deduction_quantity', 10, 3)->default(0.000);
            $table->decimal('pending_quantity', 10, 3)->default(0.000);
            $table->string('note', 500)->nullable();
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'loading_date']);
            $table->index(['goods_id', 'loading_date']);
            $table->index(['client_id', 'loading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
