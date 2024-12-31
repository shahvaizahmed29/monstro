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
        Schema::create('member_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id');
            $table->unsignedBigInteger('member_id');
            $table->string('status');
            $table->string('note')->nullable();
            $table->integer('progress')->default(0); // New column for tracking progress
            $table->dateTime('date_achieved')->nullable();
            $table->foreign('achievement_id')->references('id')->on('achievements');
            $table->foreign('member_id')->references('id')->on('members');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_achievements');
    }
};
