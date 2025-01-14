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
        Schema::create('vendor_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('amount');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('referral_id');
            $table->timestamp('created_at');
            $table->timestamp('accepted_at')->nullable();

            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_referrals');
    }
};
