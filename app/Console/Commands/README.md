# Console Commands

This directory contains custom Artisan commands for Palestine Creative Hub.

---

## CleanupOrphanedImages

**File:** `CleanupOrphanedImages.php`
**Signature:** `images:cleanup-orphaned {--dry-run}`

**What it does:**
Scans the following directories on the `public` storage disk and deletes any file whose path is not referenced in the corresponding database table:

| Directory | Database check |
|---|---|
| `profiles/` | `designers.profile_picture` column |
| `products/` | `product_images.image_path` column |
| `projects/` | `project_images.image_path` column |
| `services/` | `services.image` column |

At the end it prints a summary of how many files were deleted and the total disk space freed (in MB).

**Options:**
- `--dry-run` — Prints what would be deleted without making any changes. Safe to run at any time.

**When to run:**
- After bulk import/migration operations that may have left orphaned files.
- Periodically via the Laravel scheduler (e.g. weekly) to reclaim disk space.
- Always run with `--dry-run` first to review the output before a live deletion.

**Example:**
```bash
php artisan images:cleanup-orphaned --dry-run
php artisan images:cleanup-orphaned
```

---

## CleanupOrphanedUploads

**File:** `CleanupOrphanedUploads.php`
**Signature:** `uploads:cleanup`

**What it does:**
Removes stale temporary upload session folders that are older than 12 hours. Delegates to `ImageUploadController::cleanupOrphanedUploads()`, which scans the temporary upload staging directory and removes any session folder whose creation time exceeds the 12-hour threshold.

This targets the progressive upload flow used during registration and profile editing — if a user abandons an upload mid-way, the temporary files accumulate. This command prevents unbounded disk usage.

**When to run:**
Schedule this to run nightly or every few hours via the Laravel task scheduler in `routes/console.php` or `App\Console\Kernel`:

```php
$schedule->command('uploads:cleanup')->hourly();
```

**Example:**
```bash
php artisan uploads:cleanup
```

---

## MigrateImageNames

**File:** `MigrateImageNames.php`
**Signature:** `images:migrate-names {--dry-run}`

**What it does:**
A one-time data migration command that renames existing images from random upload names (e.g. `f3a9b2c1.jpg`) to a predictable structured convention and updates the corresponding database records:

| Image type | New naming pattern |
|---|---|
| Profile avatar | `profiles/profile_{designer_id}.{ext}` |
| Product image | `products/product_{product_id}_{image_number}.{ext}` |
| Project image | `projects/project_{project_id}_{image_number}.{ext}` |
| Service image | `services/service_{service_id}.{ext}` |

Images already matching the structured pattern are skipped. Missing files are warned about but do not halt the process.

**Options:**
- `--dry-run` — Shows what would be renamed without touching files or the database. No confirmation prompt is shown in dry-run mode.

**When to run:**
This command is intended as a **one-time migration** run after the structured naming system was introduced. It is safe to run on already-migrated data (it will report "No images needed migration"). Always use `--dry-run` first to review the planned renames.

**Example:**
```bash
php artisan images:migrate-names --dry-run
php artisan images:migrate-names
```
