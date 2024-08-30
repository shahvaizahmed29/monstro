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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('service'); // e.g., Stripe, GHL
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('integration_id');
            $table->json('additional_settings')->nullable(); // Any other data specific to the integration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            //
        });
    }
};
