<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE `rating_criteria_responses` (
                `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `profile_rating_id`   BIGINT UNSIGNED NOT NULL,
                `rating_criteria_id`  BIGINT UNSIGNED NOT NULL,
                `created_at`          TIMESTAMP NULL DEFAULT NULL,
                `updated_at`          TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_rating_criteria` (`profile_rating_id`, `rating_criteria_id`),
                INDEX `rcr_profile_rating_id_index` (`profile_rating_id`),
                INDEX `rcr_rating_criteria_id_index` (`rating_criteria_id`),
                CONSTRAINT `fk_rcr_profile_rating`
                    FOREIGN KEY (`profile_rating_id`)
                    REFERENCES `profile_ratings` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_rcr_rating_criteria`
                    FOREIGN KEY (`rating_criteria_id`)
                    REFERENCES `rating_criteria` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS `rating_criteria_responses`");
    }
};
