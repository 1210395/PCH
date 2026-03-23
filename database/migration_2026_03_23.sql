-- =====================================================
-- Migration: Add missing fields to academic_workshops
-- Date: 2026-03-23
-- Run this on your production database
-- =====================================================

ALTER TABLE `academic_workshops`
    ADD COLUMN IF NOT EXISTS `objectives` TEXT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `is_online` TINYINT(1) NOT NULL DEFAULT 0 AFTER `location`,
    ADD COLUMN IF NOT EXISTS `instructor` VARCHAR(255) NULL AFTER `is_online`,
    ADD COLUMN IF NOT EXISTS `is_free` TINYINT(1) NOT NULL DEFAULT 1 AFTER `price`,
    ADD COLUMN IF NOT EXISTS `registration_link` VARCHAR(500) NULL AFTER `has_certificate`;
