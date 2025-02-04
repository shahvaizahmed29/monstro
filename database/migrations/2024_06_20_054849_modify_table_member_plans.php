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
        Schema::table('member_plans', function (Blueprint $table) {
            $table->boolean('family')->default(false);
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('family_member_limit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_plans', function (Blueprint $table) {
            $table->dropColumn(['family', 'program_id', 'family_member_limit']);
        });
    }
};
