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
        Schema::create('achievement_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_id');
            $table->unsignedBigInteger('achievement_id');
            $table->integer('count');
            $table->string('metadata')->nullable(); // New column for additional metadata
            $table->foreign('action_id')->references('id')->on('actions');
            $table->foreign('achievement_id')->references('id')->on('achievements');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_actions');
    }
};
