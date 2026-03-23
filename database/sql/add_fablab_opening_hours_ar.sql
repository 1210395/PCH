-- ============================================================
-- Add Arabic opening hours column to fab_labs table
-- ============================================================

ALTER TABLE `fab_labs` ADD COLUMN `opening_hours_ar` TEXT NULL AFTER `opening_hours`;

-- ============================================================
-- Optional: Copy existing opening hours as a starting point
-- (admin should then update with proper Arabic text)
-- ============================================================
-- UPDATE `fab_labs` SET `opening_hours_ar` = `opening_hours` WHERE `opening_hours` IS NOT NULL;
