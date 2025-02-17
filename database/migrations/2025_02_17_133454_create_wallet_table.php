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
        Schema::create('wallet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->decimal('balance')->default(0.00);
            $table->decimal('credit')->default(0.00);
            $table->decimal('recharge_amount')->default(0.00);
            $table->decimal('recharge_threshold')->default(0.00);
            $table->date('last_changed')->defaul(now());
            $table->date('removed_at')->defaul(now());
            $table->date('removed_by')->defaul(now());
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet');
    }
};
