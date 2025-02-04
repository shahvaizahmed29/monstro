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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade'); // Primary member
            $table->foreignId('related_member_id')->constrained('members')->onDelete('cascade'); // Family member
            $table->enum('relationship', ['parent', 'spouse', 'child', 'sibling', 'other'])->nullable(); // Relationship type
            $table->boolean('is_payer')->default(false); // Indicates if this member pays for the related member
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
