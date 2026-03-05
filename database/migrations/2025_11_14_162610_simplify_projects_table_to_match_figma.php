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
        Schema::table('projects', function (Blueprint $table) {
            // Remove columns not needed in Figma design
            $table->dropColumn(['slug', 'description', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Restore removed columns
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable();
        });
    }
};
