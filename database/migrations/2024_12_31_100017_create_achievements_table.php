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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('name');
            $table->string('badge');
            $table->unsignedBigInteger('location_id');
            $table->bigInteger('points');
            $table->foreign('program_id')->references('id')->on('programs');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
