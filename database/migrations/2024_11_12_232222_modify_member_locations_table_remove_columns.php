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
        Schema::table('member_locations', function (Blueprint $table) {
            $table->dropColumn(['go_high_level_location_id', 'go_high_level_contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_locations', function (Blueprint $table) {
            $table->string('go_high_level_location_id')->nullable();
            $table->string('go_high_level_contact_id')->nullable();
        });
    }
};
