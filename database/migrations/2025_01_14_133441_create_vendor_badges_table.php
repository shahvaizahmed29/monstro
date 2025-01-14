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
        Schema::create('vendor_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_progress_id');
            $table->unsignedBigInteger('badge_id');
            $table->unsignedInteger('progress');
            $table->boolean('completed');
            $table->timestamp('created_at');
            $table->timestamp('claimed_at')->nullable();
            $table->foreign('vendor_progress_id')->references('id')->on('vendor_progress')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_badges');
    }
};
