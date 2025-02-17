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
        Schema::create('wallet_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->string('description');
            $table->string('category');
            $table->decimal('amount')->default(0.00);
            $table->decimal('event_id')->default(0.00);
            $table->decimal('balance')->default(0.00);
            $table->decimal('recharge_threshold')->default(0.00);
            $table->date('activity_date')->defaul(now());
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('wallet_id')->references('id')->on('wallet')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_usage');
    }
};
