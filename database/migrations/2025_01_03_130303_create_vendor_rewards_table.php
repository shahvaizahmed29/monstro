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
        Schema::create('vendor_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id')->nullable();
            $table->string('name');
            $table->string('description');
            $table->string('icon');
            $table->json('data');
            $table->integer('total_claimed');
            $table->foreign('achievement_id')->references('id')->on('vendor_achievements');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_rewards');
    }
};
