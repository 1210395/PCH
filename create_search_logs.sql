CREATE TABLE `search_logs` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `query`         VARCHAR(200) NOT NULL,
    `results_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `ip_address`    VARCHAR(45) NULL,
    `designer_id`   BIGINT UNSIGNED NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `search_logs_query_index` (`query`),
    INDEX `search_logs_created_at_index` (`created_at`),
    INDEX `search_logs_results_count_created_at_index` (`results_count`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
