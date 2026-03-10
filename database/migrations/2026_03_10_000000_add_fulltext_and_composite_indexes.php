<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add full-text search indexes and missing composite indexes for performance.
     */
    public function up(): void
    {
        // Full-text indexes for search queries (works on both MyISAM and InnoDB)
        if (!$this->hasIndex('products', 'ft_products_search')) {
            DB::statement('CREATE FULLTEXT INDEX `ft_products_search` ON `products` (`title`, `description`)');
        }

        if (!$this->hasIndex('projects', 'ft_projects_search')) {
            DB::statement('CREATE FULLTEXT INDEX `ft_projects_search` ON `projects` (`title`, `description`)');
        }

        if (!$this->hasIndex('designers', 'ft_designers_search')) {
            DB::statement('CREATE FULLTEXT INDEX `ft_designers_search` ON `designers` (`name`, `bio`, `sector`, `sub_sector`, `city`)');
        }

        if (!$this->hasIndex('marketplace_posts', 'ft_marketplace_search')) {
            DB::statement('CREATE FULLTEXT INDEX `ft_marketplace_search` ON `marketplace_posts` (`title`, `description`)');
        }

        // Missing composite indexes for common filter patterns
        if (Schema::hasTable('designers')) {
            Schema::table('designers', function ($table) {
                if (!$this->hasIndex('designers', 'idx_designers_admin_active')) {
                    $table->index(['is_admin', 'is_active'], 'idx_designers_admin_active');
                }
                if (!$this->hasIndex('designers', 'idx_designers_city')) {
                    $table->index('city', 'idx_designers_city');
                }
            });
        }

        // Services - filtered by designer + approval
        if (Schema::hasTable('services')) {
            Schema::table('services', function ($table) {
                if (!$this->hasIndex('services', 'idx_services_designer_approval')) {
                    $table->index(['designer_id', 'approval_status'], 'idx_services_designer_approval');
                }
                if (!$this->hasIndex('services', 'idx_services_approval_status')) {
                    $table->index('approval_status', 'idx_services_approval_status');
                }
            });
        }

        // Likes - queried by designer + likeable for toggle checks
        if (Schema::hasTable('likes')) {
            Schema::table('likes', function ($table) {
                if (!$this->hasIndex('likes', 'idx_likes_designer_likeable')) {
                    $table->index(['designer_id', 'likeable_type', 'likeable_id'], 'idx_likes_designer_likeable');
                }
            });
        }

        // Notifications - queried by designer_id + read status
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function ($table) {
                if (!$this->hasIndex('notifications', 'idx_notifications_designer_read')) {
                    $table->index(['designer_id', 'read'], 'idx_notifications_designer_read');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->hasIndex('products', 'ft_products_search')) {
            DB::statement('DROP INDEX `ft_products_search` ON `products`');
        }
        if ($this->hasIndex('projects', 'ft_projects_search')) {
            DB::statement('DROP INDEX `ft_projects_search` ON `projects`');
        }
        if ($this->hasIndex('designers', 'ft_designers_search')) {
            DB::statement('DROP INDEX `ft_designers_search` ON `designers`');
        }
        if ($this->hasIndex('marketplace_posts', 'ft_marketplace_search')) {
            DB::statement('DROP INDEX `ft_marketplace_search` ON `marketplace_posts`');
        }

        Schema::table('designers', function ($table) {
            if ($this->hasIndex('designers', 'idx_designers_admin_active')) {
                $table->dropIndex('idx_designers_admin_active');
            }
            if ($this->hasIndex('designers', 'idx_designers_city')) {
                $table->dropIndex('idx_designers_city');
            }
        });

        if (Schema::hasTable('services')) {
            Schema::table('services', function ($table) {
                if ($this->hasIndex('services', 'idx_services_designer_approval')) {
                    $table->dropIndex('idx_services_designer_approval');
                }
                if ($this->hasIndex('services', 'idx_services_approval_status')) {
                    $table->dropIndex('idx_services_approval_status');
                }
            });
        }

        if (Schema::hasTable('likes')) {
            Schema::table('likes', function ($table) {
                if ($this->hasIndex('likes', 'idx_likes_designer_likeable')) {
                    $table->dropIndex('idx_likes_designer_likeable');
                }
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function ($table) {
                if ($this->hasIndex('notifications', 'idx_notifications_designer_read')) {
                    $table->dropIndex('idx_notifications_designer_read');
                }
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn($idx) => $idx['name'] === $index);
    }
};
