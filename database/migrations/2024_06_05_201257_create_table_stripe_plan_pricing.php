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
        Schema::create('stripe_plan_pricings', function (Blueprint $table) {
            $table->id();
            $table->float('amount');
            $table->string('billing_period');
            $table->foreignId('stripe_plan_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('stripe_plan_pricings', function (Blueprint $table) {
            //
        });
    }
};
