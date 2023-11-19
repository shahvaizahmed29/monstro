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
        Schema::create('vendor_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_locations', function (Blueprint $table) {
            Schema::dropIfExists('vendor_locations');
        });
    }
};
