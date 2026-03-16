<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS `rating_criteria`");
    }
};
