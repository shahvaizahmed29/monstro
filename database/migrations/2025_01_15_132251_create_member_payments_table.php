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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('members')->onDelete('cascade'); // Member who pays
            $table->foreignId('beneficiary_id')->constrained('members')->onDelete('cascade'); // Member for whom payment is made
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade'); // Associated program
            $table->foreignId('member_plan_id')->nullable()->constrained('member_plans')->onDelete('cascade'); // Associated plan
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_payments');
    }
};
