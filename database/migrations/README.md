# database/migrations

Database migrations for the Palestine Creative Hub. Migrations are run with `php artisan migrate` and follow the standard Laravel timestamp-prefix naming convention.

There are **31 migration files** covering the full modern schema. Legacy CMS tables (used by the `/Control/` panel) are not managed by migrations.

---

## Migration History

### Baseline (Laravel defaults)
| File | Description |
|---|---|
| `0001_01_01_000000_create_users_table.php` | Creates `users`, `password_reset_tokens`, and `sessions` tables (Laravel default). |
| `0001_01_01_000001_create_cache_table.php` | Creates `cache` and `cache_locks` tables for database-backed cache driver. |
| `0001_01_01_000002_create_jobs_table.php` | Creates `jobs` and `failed_jobs` tables for queue workers. |

### November 2025 — Initial Schema
| File | Tables Created |
|---|---|
| `2025_11_12_093144_create_personal_access_tokens_table.php` | `personal_access_tokens` — Laravel Sanctum API tokens |
| `2025_11_14_123649_create_categories_table.php` | `categories` — Hierarchical content categories |
| `2025_11_14_123654_create_skills_table.php` | `skills` — Designer skill tags |
| `2025_11_14_123656_create_projects_table.php` | `projects` — Portfolio project entries with approval workflow |
| `2025_11_14_123659_create_project_images_table.php` | `project_images` — Multiple images per project |
| `2025_11_14_123702_create_project_tags_table.php` | `project_tags` — Project-tag pivot table |
| `2025_11_14_123704_create_user_skills_table.php` | `user_skills` — Designer-skill pivot (later renamed `designer_skills`) |
| `2025_11_14_123706_create_follows_table.php` | `follows` — Designer follow relationships |
| `2025_11_14_123709_create_likes_table.php` | `likes` — Generic like pivot for products and marketplace posts |
| `2025_11_14_123712_create_comments_table.php` | `comments` — Generic comment table (later superseded by model-specific tables) |
| `2025_11_14_123715_create_views_table.php` | `views` — Generic view tracking |
| `2025_11_14_123717_add_profile_fields_to_users_table.php` | Adds bio, avatar, sector, city, and social link fields to designers |
| `2025_11_14_162610_simplify_projects_table_to_match_figma.php` | Alters `projects` to match the final Figma design spec |
| `2025_11_16_073224_create_marketplace_posts_table.php` | `marketplace_posts` — Community post board with approval workflow |
| `2025_11_16_073224_create_products_table.php` | `products` — Product catalogue with approval workflow |
| `2025_11_16_073225_create_fab_labs_table.php` | `fab_labs` — Fabrication laboratory directory |

### February 2026 — Enhancements
| File | Description |
|---|---|
| `2026_02_19_000000_add_certifications_to_designers_table.php` | Adds `certifications` JSON column to `designers` for storing uploaded certification PDFs. |
| `2026_02_20_184909_add_performance_indexes.php` | Adds composite and single-column indexes on high-traffic query paths (approval_status, designer_id, created_at). |
| `2026_02_20_210911_cleanup_legacy_tables_and_columns.php` | Drops or alters legacy columns that were replaced during the November schema revision. |
| `2026_02_27_000000_add_vendor_sector_to_dropdown_options.php` | Adds `vendor_sector` option to the `dropdown_options` table. |

### March 2026 — Internationalisation and Features
| File | Description |
|---|---|
| `2026_03_04_000000_populate_dropdown_arabic_translations.php` | Data migration: fills `name_ar` translations for existing dropdown options. |
| `2026_03_05_000000_add_missing_performance_indexes.php` | Additional indexes identified during load testing. |
| `2026_03_09_235135_seed_marketplace_categories_dropdown.php` | Data migration: seeds initial marketplace category dropdown options. |
| `2026_03_10_000000_add_fulltext_and_composite_indexes.php` | Adds MySQL FULLTEXT indexes on title/description columns for `MATCH … AGAINST` search queries. |
| `2026_03_15_000001_create_rating_criteria_table.php` | `rating_criteria` — Admin-configurable rating dimensions. |
| `2026_03_15_000002_create_rating_criteria_responses_table.php` | `rating_criteria_responses` — Individual criterion scores within a profile rating. |
| `2026_03_17_000001_add_unique_index_to_profile_ratings_table.php` | Adds a unique composite index on `(rater_id, designer_id)` to prevent duplicate ratings. |
| `2026_03_17_000002_add_performance_indexes_to_profile_ratings_table.php` | Additional indexes on `profile_ratings` for dashboard analytics queries. |

---

## Naming Conventions

- `create_{table}_table` — creates a new table.
- `add_{column}_to_{table}_table` — adds one or more columns to an existing table.
- `cleanup_*` — drops or alters legacy columns/tables.
- `populate_*` / `seed_*` — data migrations that insert or update rows.
- `add_*_indexes` / `add_*_index_to_*` — adds database indexes for performance.

---

## Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Roll back the last batch
php artisan migrate:rollback

# Show migration status
php artisan migrate:status

# Fresh install (drops all tables and re-runs)
php artisan migrate:fresh
```

> **Note:** Data migrations (`populate_*`, `seed_*`) contain `DB::table()` insert/update statements in their `up()` methods. Rolling them back removes those rows.
