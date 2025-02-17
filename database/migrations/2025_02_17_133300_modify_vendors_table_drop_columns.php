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
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['is_new', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_new')->default(true);
            $table->unsignedBigInteger('plan_id')->nullable();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }
};
