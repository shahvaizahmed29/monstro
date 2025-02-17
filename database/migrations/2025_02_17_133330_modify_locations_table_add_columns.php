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
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->enum('status', ['Pending', 'Active', 'Payment Failed', 'Paused', 'Trial', 'Archived'])->default('Active');

            $table->foreign('subscription_plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan_id', 'status']);
        });
    }
};
