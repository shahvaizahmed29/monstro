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
        Schema::table('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('purchase_id');
            $table->string('purchase_type');
            $table->string('amount');
            $table->enum('type', ['Credit', 'Debit']);
            $table->string('note');
            $table->enum('status', ['Completed', 'Pending', 'Failed']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            Schema::dropIfExists('transactions');
        });
    }
};
