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
        Schema::create('vendor_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id')->nullable();
            $table->integer('progress')->default(0);
            $table->boolean('completed')->default(0);
            $table->boolean('claimed')->default(0);
            $table->foreign('achievement_id')->references('id')->on('vendor_achievements');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_progress');
    }
};
