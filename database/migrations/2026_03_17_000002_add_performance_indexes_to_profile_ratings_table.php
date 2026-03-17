<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_ratings', function (Blueprint $table) {
            // For date-range filtering in admin analytics
            $table->index('created_at', 'profile_ratings_created_at_index');
            // For approved ratings ordered by date (public portfolio endpoint)
            $table->index(['status', 'designer_id', 'created_at'], 'profile_ratings_status_designer_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('profile_ratings', function (Blueprint $table) {
            $table->dropIndex('profile_ratings_created_at_index');
            $table->dropIndex('profile_ratings_status_designer_date_index');
        });
    }
};
