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
        Schema::table('member_contracts', function (Blueprint $table) {
            $table->jsonb('variables')->default('{}');
            $table->text('signature')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_contracts', function (Blueprint $table) {
            $table->dropColumn(['variables', 'signature']);
        });
    }
};
