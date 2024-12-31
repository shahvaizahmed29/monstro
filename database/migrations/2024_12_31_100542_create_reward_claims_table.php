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
        Schema::create('reward_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reward_id');
            $table->unsignedBigInteger('member_id');
            $table->double('previous_points')->nullable(); // Merged from member_reward_claims
            $table->dateTime('date_claimed')->nullable(); // Merged from member_reward_claims
            $table->tinyInteger('status')->default(0); // Merged from member_reward_claims
            $table->timestamps();
            $table->softDeletes(); // Merged from member_reward_claims
            $table->foreign('reward_id')->references('id')->on('rewards');
            $table->foreign('member_id')->references('id')->on('members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_claims');
    }
};
