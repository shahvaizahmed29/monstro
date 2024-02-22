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
        Schema::create('redeem_points_logs', function (Blueprint $table) {
            $table->id();
            $table->double('previous_points');
            $table->double('redeem_points');
            $table->double('current_points');
            $table->date('date_claimed');
            $table->unsignedBigInteger('member_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_points_logs');
    }
};
