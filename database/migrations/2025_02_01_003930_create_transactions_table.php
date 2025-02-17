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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->string('statement_description');
            $table->enum('payment_method', ['cash', 'stripe', 'zelle', 'bank payment', 'cheque', 'charge a card']);
            $table->enum('transaction_type', ['incoming', 'refund', 'pending']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['completed', 'pending', 'failed']);
            $table->timestamps();
            $table->softDeletes();
            
            $table->enum('model', ['member', 'vendor', 'staff']);
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('member_plan_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            $table->foreign('member_plan_id')->references('id')->on('member_plans')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
