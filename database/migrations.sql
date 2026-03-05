-- ============================================================================
-- Palestine Creative Hub - Database Migrations
-- Run this SQL against the `technopark_portal` database
-- Generated: 2026-02-20
-- ============================================================================


-- ────────────────────────────────────────────────────────────────────────────
-- Migration 1: Add certifications column to designers table
-- ────────────────────────────────────────────────────────────────────────────

ALTER TABLE `designers`
    ADD COLUMN `certifications` JSON NULL AFTER `years_of_experience`;


-- ────────────────────────────────────────────────────────────────────────────
-- Migration 2: Add performance indexes
-- ────────────────────────────────────────────────────────────────────────────

-- Products (MyISAM) - heavily filtered by designer + approval status
-- Note: category uses prefix(191) because varchar(255) + utf8mb4 = 1020 bytes > MyISAM 1000-byte key limit
CREATE INDEX `idx_products_designer_approval` ON `products` (`designer_id`, `approval_status`);
CREATE INDEX `idx_products_approval_status` ON `products` (`approval_status`);
CREATE INDEX `idx_products_category` ON `products` (`category`(191));

-- Marketplace Posts (MyISAM) - filtered by designer + approval + type
-- Note: category uses prefix(191) for same MyISAM key length reason
CREATE INDEX `idx_marketplace_designer_approval` ON `marketplace_posts` (`designer_id`, `approval_status`);
CREATE INDEX `idx_marketplace_approval_status` ON `marketplace_posts` (`approval_status`);
CREATE INDEX `idx_marketplace_type` ON `marketplace_posts` (`type`);
CREATE INDEX `idx_marketplace_category` ON `marketplace_posts` (`category`(191));

-- Projects - filtered by designer + approval
CREATE INDEX `idx_projects_designer_approval` ON `projects` (`designer_id`, `approval_status`);
CREATE INDEX `idx_projects_approval_status` ON `projects` (`approval_status`);

-- Designers - frequently filtered on status columns
CREATE INDEX `idx_designers_is_active` ON `designers` (`is_active`);
CREATE INDEX `idx_designers_sector` ON `designers` (`sector`);

-- Academic Trainings - filtered by account + approval + dates
CREATE INDEX `idx_trainings_account_approval` ON `academic_trainings` (`academic_account_id`, `approval_status`);
CREATE INDEX `idx_trainings_start_date` ON `academic_trainings` (`start_date`);

-- Academic Workshops - filtered by account + approval + date
CREATE INDEX `idx_workshops_account_approval` ON `academic_workshops` (`academic_account_id`, `approval_status`);
CREATE INDEX `idx_workshops_date` ON `academic_workshops` (`workshop_date`);

-- Academic Announcements - filtered by account + approval + dates
CREATE INDEX `idx_announcements_account_approval` ON `academic_announcements` (`academic_account_id`, `approval_status`);
CREATE INDEX `idx_announcements_publish_date` ON `academic_announcements` (`publish_date`);


-- ────────────────────────────────────────────────────────────────────────────
-- Migration 3: Cleanup legacy tables and columns
-- ────────────────────────────────────────────────────────────────────────────

-- Disable FK checks so tables can be dropped in any order
SET FOREIGN_KEY_CHECKS = 0;

-- ── Old CMS content tables ──
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `pages_categories`;
DROP TABLE IF EXISTS `pages_replies`;
DROP TABLE IF EXISTS `pages_statuses`;
DROP TABLE IF EXISTS `files`;
DROP TABLE IF EXISTS `files_requests`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `menus_locations`;
DROP TABLE IF EXISTS `html_pieces`;
DROP TABLE IF EXISTS `html_templates`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `tags_rel`;
DROP TABLE IF EXISTS `glossary`;
DROP TABLE IF EXISTS `faqs`;

-- ── Old CMS user/auth tables ──
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `users_groups`;
DROP TABLE IF EXISTS `users_tokens`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `login_log`;
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `blacklist`;

-- ── Old CMS feature tables ──
DROP TABLE IF EXISTS `conferences`;
DROP TABLE IF EXISTS `conference_days`;
DROP TABLE IF EXISTS `conference_speaker`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `event_speaker`;
DROP TABLE IF EXISTS `speakers`;
DROP TABLE IF EXISTS `polls`;
DROP TABLE IF EXISTS `poll_votes`;
DROP TABLE IF EXISTS `forms`;
DROP TABLE IF EXISTS `form_entries`;
DROP TABLE IF EXISTS `form_entries_fields`;
DROP TABLE IF EXISTS `form_fields`;
DROP TABLE IF EXISTS `form_field_options`;
DROP TABLE IF EXISTS `form_field_types`;
DROP TABLE IF EXISTS `contact_complaints`;
DROP TABLE IF EXISTS `social_media`;

-- ── Old CMS config/metadata tables ──
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `settings_plugins`;
DROP TABLE IF EXISTS `languages`;
DROP TABLE IF EXISTS `categories_types`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `comments_types`;
DROP TABLE IF EXISTS `crop_sizes`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `authors_relations`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `attachment_old`;

-- ── Empty legacy pivot tables (modern code uses FK on child tables) ──
DROP TABLE IF EXISTS `designer_products`;
DROP TABLE IF EXISTS `designer_projects`;
DROP TABLE IF EXISTS `designer_services`;

SET FOREIGN_KEY_CHECKS = 1;

-- Remove unused column from designers
ALTER TABLE `designers` DROP COLUMN `hero_image`;

-- Drop legacy stored procedure
DROP PROCEDURE IF EXISTS `safe_add_column`;
