<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique()->nullable();
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('delivery_location')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
