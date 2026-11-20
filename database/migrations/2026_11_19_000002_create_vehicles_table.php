<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 100)->unique()->index();
            $table->string('vehicle_type', 100)->default('Dump Truck');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->decimal('capacity_mt', 10, 3)->nullable();
            $table->enum('owner_type', ['company_owned', 'hired', 'contractor'])->default('hired');
            $table->enum('status', ['active', 'in_transit', 'maintenance', 'inactive'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
