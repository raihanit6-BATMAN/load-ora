<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('driver_name', 150)->nullable()->after('vehicle_id');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('sequence_number')->nullable()->index()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('driver_name');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('sequence_number');
        });
    }
};
