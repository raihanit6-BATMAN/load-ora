<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('dispatches')->cascadeOnDelete();
            $table->string('receipt_number', 100)->index();
            $table->date('receiving_date')->index();
            $table->decimal('received_quantity', 10, 3);
            $table->decimal('deduction_quantity', 10, 3)->default(0.000);
            $table->string('unloading_location')->nullable();
            $table->enum('receipt_status', ['received', 'shortage', 'disputed'])->default('received');
            $table->string('remarks', 500)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dispatch_id', 'receiving_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
