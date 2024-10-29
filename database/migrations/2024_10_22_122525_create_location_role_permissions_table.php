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
        Schema::create('location_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_role_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('create')->default(false); // Permission to create
            $table->boolean('view')->default(false);   // Permission to view
            $table->boolean('update')->default(false); // Permission to update
            $table->boolean('delete')->default(false); // Permission to delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_role_permissions');
    }
};
