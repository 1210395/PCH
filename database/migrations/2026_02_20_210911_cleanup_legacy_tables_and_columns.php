<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legacy CMS tables to drop.
     * These were only used by the old Control panel (now deleted).
     */
    private array $legacyTables = [
        // ── Old CMS content tables ──
        'pages',
        'pages_categories',
        'pages_replies',
        'pages_statuses',
        'files',
        'files_requests',
        'menus',
        'menus_locations',
        'html_pieces',
        'html_templates',
        'tags',
        'tags_rel',
        'glossary',
        'faqs',

        // ── Old CMS user/auth tables ──
        'users',              // Legacy CMS admins (control guard - deleted)
        'users_groups',
        'users_tokens',
        'customers',
        'password_resets',    // Replaced by password_reset_tokens (Laravel 11+)
        'login_log',
        'logs',
        'blacklist',

        // ── Old CMS feature tables ──
        'conferences',
        'conference_days',
        'conference_speaker',
        'events',
        'event_speaker',
        'speakers',
        'polls',
        'poll_votes',
        'forms',
        'form_entries',
        'form_entries_fields',
        'form_fields',
        'form_field_options',
        'form_field_types',
        'contact_complaints',
        'social_media',

        // ── Old CMS config/metadata tables ──
        'settings',           // Replaced by admin_settings + site_settings
        'settings_plugins',   // Plugins system removed
        'languages',
        'categories_types',
        'comments',
        'comments_types',
        'crop_sizes',
        'authors',
        'authors_relations',
        'cities',             // Not queried - code uses designer.city column directly
        'attachment_old',

        // ── Empty legacy pivot tables (modern code uses FK on child tables) ──
        'designer_products',  // Modern: products.designer_id
        'designer_projects',  // Modern: projects.designer_id
        'designer_services',  // Modern: services.designer_id
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Phase 1: Drop legacy tables ──
        // Disable FK checks so we can drop in any order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->legacyTables as $table) {
            Schema::dropIfExists($table);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Phase 2: Remove unused columns from designers table ──
        Schema::table('designers', function (Blueprint $table) {
            // hero_image: exists in DB but never referenced in model or views
            if (Schema::hasColumn('designers', 'hero_image')) {
                $table->dropColumn('hero_image');
            }
        });

        // ── Phase 3: Drop legacy stored procedures ──
        DB::statement('DROP PROCEDURE IF EXISTS safe_add_column');
    }

    /**
     * Reverse the migrations.
     *
     * Note: This only recreates the designers column. Legacy tables are NOT
     * recreated — restore from a database backup if needed.
     */
    public function down(): void
    {
        Schema::table('designers', function (Blueprint $table) {
            if (!Schema::hasColumn('designers', 'hero_image')) {
                $table->string('hero_image', 255)->nullable()->after('avatar');
            }
        });
    }
};
