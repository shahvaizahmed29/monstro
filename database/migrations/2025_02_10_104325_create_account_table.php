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
        Schema::create('account', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->text('type')->nullable();
            $table->text('provider')->nullable();
            $table->text('provider_account_id')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('access_token')->nullable();
            $table->text('expires_at')->nullable();
            $table->text('token_type')->nullable();
            $table->text('scope')->nullable();
            $table->text('id_token')->nullable();
            $table->text('session_state')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->primary(['provider', 'provider_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account');
    }
};
