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
        Schema::create('member_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->double('previous_points')->nullable();
            $table->dateTime('date_claimed');
            $table->unsignedBigInteger('reward_id'); 
            $table->tinyInteger('status'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_reward_claims');
    }
};
