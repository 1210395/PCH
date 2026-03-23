-- ============================================================
-- Rating Criteria Table
-- Run this on your MySQL database before deploying the feature.
-- ============================================================

CREATE TABLE `rating_criteria` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `en_label`   VARCHAR(255) NOT NULL,
    `ar_label`   VARCHAR(255) NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `rating_criteria_is_active_sort_order_index` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed with default criteria (optional)
-- ============================================================

INSERT INTO `rating_criteria` (`en_label`, `ar_label`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('Did you get the product you wanted?',  'هل حصلت على المنتج الذي أردته؟',          1, 1, NOW(), NOW()),
('Did you achieve your goal?',           'هل حققت هدفك؟',                            1, 2, NOW(), NOW()),
('Would you work with this designer again?', 'هل ستعمل مع هذا المصمم مرة أخرى؟',    1, 3, NOW(), NOW());
