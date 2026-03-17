<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any duplicate ratings before adding the unique constraint
        // Keep the oldest rating per (designer_id, rater_id) pair
        DB::statement('
            DELETE pr1 FROM profile_ratings pr1
            INNER JOIN profile_ratings pr2
            WHERE pr1.id > pr2.id
              AND pr1.designer_id = pr2.designer_id
              AND pr1.rater_id = pr2.rater_id
        ');

        Schema::table('profile_ratings', function (Blueprint $table) {
            $table->unique(['designer_id', 'rater_id'], 'profile_ratings_designer_rater_unique');
        });
    }

    public function down(): void
    {
        Schema::table('profile_ratings', function (Blueprint $table) {
            $table->dropUnique('profile_ratings_designer_rater_unique');
        });
    }
};
