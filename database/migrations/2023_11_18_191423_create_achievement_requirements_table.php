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
        Schema::create('achievement_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_id');
            $table->double('count')->default(0);
            $table->unsignedBigInteger('achievement_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_requirements');
    }
};
