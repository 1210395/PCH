<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The image column must be nullable — marketplace posts can be created
     * without an image. The original migration had it as NOT NULL which
     * causes silent INSERT failures when no image is provided.
     */
    public function up(): void
    {
        // Use raw SQL because MyISAM doesn't support some Blueprint operations
        DB::statement("ALTER TABLE `marketplace_posts` MODIFY `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `marketplace_posts` MODIFY `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};
