<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Products (MyISAM) - heavily filtered by designer + approval status
        // category uses prefix(191) because varchar(255) + utf8mb4 = 1020 bytes > MyISAM 1000-byte key limit
        Schema::table('products', function (Blueprint $table) {
            $table->index(['designer_id', 'approval_status'], 'idx_products_designer_approval');
            $table->index('approval_status', 'idx_products_approval_status');
        });
        DB::statement('CREATE INDEX `idx_products_category` ON `products` (`category`(191))');

        // Marketplace Posts (MyISAM) - filtered by designer + approval + type
        Schema::table('marketplace_posts', function (Blueprint $table) {
            $table->index(['designer_id', 'approval_status'], 'idx_marketplace_designer_approval');
            $table->index('approval_status', 'idx_marketplace_approval_status');
            $table->index('type', 'idx_marketplace_type');
        });
        DB::statement('CREATE INDEX `idx_marketplace_category` ON `marketplace_posts` (`category`(191))');

        // Projects - filtered by designer + approval
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['designer_id', 'approval_status'], 'idx_projects_designer_approval');
            $table->index('approval_status', 'idx_projects_approval_status');
        });

        // Designers - frequently filtered on status columns
        Schema::table('designers', function (Blueprint $table) {
            $table->index('is_active', 'idx_designers_is_active');
            $table->index('sector', 'idx_designers_sector');
        });

        // Academic Trainings - filtered by account + approval + dates
        Schema::table('academic_trainings', function (Blueprint $table) {
            $table->index(['academic_account_id', 'approval_status'], 'idx_trainings_account_approval');
            $table->index('start_date', 'idx_trainings_start_date');
        });

        // Academic Workshops - filtered by account + approval + date
        Schema::table('academic_workshops', function (Blueprint $table) {
            $table->index(['academic_account_id', 'approval_status'], 'idx_workshops_account_approval');
            $table->index('workshop_date', 'idx_workshops_date');
        });

        // Academic Announcements - filtered by account + approval + dates
        Schema::table('academic_announcements', function (Blueprint $table) {
            $table->index(['academic_account_id', 'approval_status'], 'idx_announcements_account_approval');
            $table->index('publish_date', 'idx_announcements_publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_designer_approval');
            $table->dropIndex('idx_products_approval_status');
            $table->dropIndex('idx_products_category');
        });

        Schema::table('marketplace_posts', function (Blueprint $table) {
            $table->dropIndex('idx_marketplace_designer_approval');
            $table->dropIndex('idx_marketplace_approval_status');
            $table->dropIndex('idx_marketplace_type');
            $table->dropIndex('idx_marketplace_category');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_designer_approval');
            $table->dropIndex('idx_projects_approval_status');
        });

        Schema::table('designers', function (Blueprint $table) {
            $table->dropIndex('idx_designers_is_active');
            $table->dropIndex('idx_designers_sector');
        });

        Schema::table('academic_trainings', function (Blueprint $table) {
            $table->dropIndex('idx_trainings_account_approval');
            $table->dropIndex('idx_trainings_start_date');
        });

        Schema::table('academic_workshops', function (Blueprint $table) {
            $table->dropIndex('idx_workshops_account_approval');
            $table->dropIndex('idx_workshops_date');
        });

        Schema::table('academic_announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcements_account_approval');
            $table->dropIndex('idx_announcements_publish_date');
        });
    }
};
