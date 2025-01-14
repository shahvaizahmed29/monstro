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
        Schema::create('vendor_claimed_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_progress_id');
            $table->unsignedBigInteger('reward_id');
            $table->timestamp('claimed_at');
            $table->foreign('vendor_progress_id')->references('id')->on('vendor_progress')->cascadeOnDelete();
            $table->foreign('reward_id')->references('id')->on('vendor_rewards')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_claimed_rewards');
    }
};
