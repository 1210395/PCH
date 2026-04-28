<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Designer;
use App\Models\ProductImage;
use App\Models\ProjectImage;
use App\Models\Service;

/**
 * Artisan command that renames existing images to a structured naming convention.
 *
 * Renames files across profiles/, products/, projects/, and services/ from random
 * upload names (e.g., abc123.jpg) to predictable identifiers (e.g., product_42_1.jpg).
 * Updates corresponding database records to reflect new paths. Supports --dry-run.
 * This command is a one-time data migration; running it on already-migrated data is safe.
 */
class MigrateImageNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-names {--dry-run : Show what would be renamed without actually renaming} {--force : Skip the interactive confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing images from random names to structured naming (product_123_1.jpg)';

    /**
     * Execute the console command.
     *
     * Prompts for confirmation when not in dry-run mode, then migrates all four
     * image categories in sequence. Returns Command::SUCCESS.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files or database records will be modified');
        } else {
            $this->warn('⚠️  WARNING: This will rename files and update database records!');
            if (!$this->option('force') && !$this->confirm('Do you want to continue?')) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Starting image name migration to structured format...');
        $this->newLine();

        $totalRenamed = 0;

        // 1. Migrate profile images
        $this->info('Migrating profile images...');
        $result = $this->migrateProfileImages($dryRun);
        $totalRenamed += $result;

        // 2. Migrate product images
        $this->info('Migrating product images...');
        $result = $this->migrateProductImages($dryRun);
        $totalRenamed += $result;

        // 3. Migrate project images
        $this->info('Migrating project images...');
        $result = $this->migrateProjectImages($dryRun);
        $totalRenamed += $result;

        // 4. Migrate service images
        $this->info('Migrating service images...');
        $result = $this->migrateServiceImages($dryRun);
        $totalRenamed += $result;

        $this->newLine();

        if ($totalRenamed > 0) {
            if ($dryRun) {
                $this->info("DRY RUN: Would rename {$totalRenamed} image(s)");
            } else {
                $this->info("✅ Successfully renamed {$totalRenamed} image(s)");
                \Log::debug("Image name migration completed: {$totalRenamed} images renamed");
            }
        } else {
            $this->info('No images needed migration (all already using structured names).');
        }

        return Command::SUCCESS;
    }

    /**
     * Migrate designer profile images to the profile_{id}.{ext} naming convention.
     *
     * Skips images already matching the structured pattern and files that no longer
     * exist on disk. Updates the designer's avatar column on success.
     *
     * @param  bool  $dryRun
     * @return int  Number of images renamed (or that would be renamed)
     */
    private function migrateProfileImages($dryRun)
    {
        $renamed = 0;

        try {
            // Get all designers with profile pictures
            $designers = Designer::whereNotNull('avatar')
                ->where('avatar', '!=', '')
                ->get();

            foreach ($designers as $designer) {
                $oldPath = $designer->avatar;

                // Skip if already using structured naming
                if (preg_match('/^profiles\/profile_\d+\.\w+$/', $oldPath)) {
                    continue;
                }

                // Check if file exists
                if (!Storage::disk('public')->exists($oldPath)) {
                    $this->warn("  ⚠️  File not found: {$oldPath} (Designer ID: {$designer->id})");
                    continue;
                }

                // Generate new structured name
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = "profile_{$designer->id}.{$extension}";
                $newPath = "profiles/{$newFilename}";

                if ($dryRun) {
                    $this->line("  Would rename: {$oldPath} → {$newPath}");
                } else {
                    // Rename the file
                    if ($this->renameImage($oldPath, $newPath)) {
                        // Update database
                        $designer->update(['avatar' => $newPath]);
                        $this->line("  ✓ Renamed: {$oldPath} → {$newPath}");
                    } else {
                        $this->error("  ✗ Failed to rename: {$oldPath}");
                        continue;
                    }
                }

                $renamed++;
            }

        } catch (\Exception $e) {
            $this->error("Error migrating profile images: {$e->getMessage()}");
        }

        return $renamed;
    }

    /**
     * Migrate product images to the product_{productId}_{imageNumber}.{ext} naming convention.
     *
     * Groups images by product_id and assigns sequential image numbers based on
     * display_order. Updates the image_path column in product_images on success.
     *
     * @param  bool  $dryRun
     * @return int  Number of images renamed (or that would be renamed)
     */
    private function migrateProductImages($dryRun)
    {
        $renamed = 0;

        try {
            // Get all products with images, ordered by product_id and display_order
            $productImages = ProductImage::orderBy('product_id')
                ->orderBy('display_order')
                ->get();

            // Group by product_id
            $groupedImages = $productImages->groupBy('product_id');

            foreach ($groupedImages as $productId => $images) {
                $imageNumber = 1;

                foreach ($images as $productImage) {
                    $oldPath = $productImage->image_path;

                    // Skip if already using structured naming
                    if (preg_match('/^products\/product_\d+_\d+\.\w+$/', $oldPath)) {
                        $imageNumber++;
                        continue;
                    }

                    // Check if file exists
                    if (!Storage::disk('public')->exists($oldPath)) {
                        $this->warn("  ⚠️  File not found: {$oldPath} (Product ID: {$productId})");
                        $imageNumber++;
                        continue;
                    }

                    // Generate new structured name
                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = "product_{$productId}_{$imageNumber}.{$extension}";
                    $newPath = "products/{$newFilename}";

                    if ($dryRun) {
                        $this->line("  Would rename: {$oldPath} → {$newPath} (is_primary: {$productImage->is_primary})");
                    } else {
                        // Rename the file
                        if ($this->renameImage($oldPath, $newPath)) {
                            // Update database
                            $productImage->update(['image_path' => $newPath]);
                            $this->line("  ✓ Renamed: {$oldPath} → {$newPath}");
                        } else {
                            $this->error("  ✗ Failed to rename: {$oldPath}");
                            $imageNumber++;
                            continue;
                        }
                    }

                    $renamed++;
                    $imageNumber++;
                }
            }

        } catch (\Exception $e) {
            $this->error("Error migrating product images: {$e->getMessage()}");
        }

        return $renamed;
    }

    /**
     * Migrate project images to the project_{projectId}_{imageNumber}.{ext} naming convention.
     *
     * Groups images by project_id and assigns sequential image numbers based on
     * display_order. Updates the image_path column in project_images on success.
     *
     * @param  bool  $dryRun
     * @return int  Number of images renamed (or that would be renamed)
     */
    private function migrateProjectImages($dryRun)
    {
        $renamed = 0;

        try {
            // Get all projects with images, ordered by project_id and display_order
            $projectImages = ProjectImage::orderBy('project_id')
                ->orderBy('display_order')
                ->get();

            // Group by project_id
            $groupedImages = $projectImages->groupBy('project_id');

            foreach ($groupedImages as $projectId => $images) {
                $imageNumber = 1;

                foreach ($images as $projectImage) {
                    $oldPath = $projectImage->image_path;

                    // Skip if already using structured naming
                    if (preg_match('/^projects\/project_\d+_\d+\.\w+$/', $oldPath)) {
                        $imageNumber++;
                        continue;
                    }

                    // Check if file exists
                    if (!Storage::disk('public')->exists($oldPath)) {
                        $this->warn("  ⚠️  File not found: {$oldPath} (Project ID: {$projectId})");
                        $imageNumber++;
                        continue;
                    }

                    // Generate new structured name
                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = "project_{$projectId}_{$imageNumber}.{$extension}";
                    $newPath = "projects/{$newFilename}";

                    if ($dryRun) {
                        $this->line("  Would rename: {$oldPath} → {$newPath} (is_primary: {$projectImage->is_primary})");
                    } else {
                        // Rename the file
                        if ($this->renameImage($oldPath, $newPath)) {
                            // Update database
                            $projectImage->update(['image_path' => $newPath]);
                            $this->line("  ✓ Renamed: {$oldPath} → {$newPath}");
                        } else {
                            $this->error("  ✗ Failed to rename: {$oldPath}");
                            $imageNumber++;
                            continue;
                        }
                    }

                    $renamed++;
                    $imageNumber++;
                }
            }

        } catch (\Exception $e) {
            $this->error("Error migrating project images: {$e->getMessage()}");
        }

        return $renamed;
    }

    /**
     * Migrate service images to the service_{id}.{ext} naming convention.
     *
     * Updates the image column on the services table for each renamed file.
     *
     * @param  bool  $dryRun
     * @return int  Number of images renamed (or that would be renamed)
     */
    private function migrateServiceImages($dryRun)
    {
        $renamed = 0;

        try {
            // Get all services with images
            $services = Service::whereNotNull('image')
                ->where('image', '!=', '')
                ->get();

            foreach ($services as $service) {
                $oldPath = $service->image;

                // Skip if already using structured naming
                if (preg_match('/^services\/service_\d+\.\w+$/', $oldPath)) {
                    continue;
                }

                // Check if file exists
                if (!Storage::disk('public')->exists($oldPath)) {
                    $this->warn("  ⚠️  File not found: {$oldPath} (Service ID: {$service->id})");
                    continue;
                }

                // Generate new structured name
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = "service_{$service->id}.{$extension}";
                $newPath = "services/{$newFilename}";

                if ($dryRun) {
                    $this->line("  Would rename: {$oldPath} → {$newPath}");
                } else {
                    // Rename the file
                    if ($this->renameImage($oldPath, $newPath)) {
                        // Update database
                        $service->update(['image' => $newPath]);
                        $this->line("  ✓ Renamed: {$oldPath} → {$newPath}");
                    } else {
                        $this->error("  ✗ Failed to rename: {$oldPath}");
                        continue;
                    }
                }

                $renamed++;
            }

        } catch (\Exception $e) {
            $this->error("Error migrating service images: {$e->getMessage()}");
        }

        return $renamed;
    }

    /**
     * Atomically rename an image file on the public storage disk.
     *
     * Returns false (without throwing) if the target path already exists or if
     * the Storage move operation fails, logging the error for later review.
     *
     * @param  string  $oldPath  Current storage-relative path
     * @param  string  $newPath  Desired storage-relative path
     * @return bool
     */
    private function renameImage($oldPath, $newPath)
    {
        try {
            // Check if new path already exists
            if (Storage::disk('public')->exists($newPath)) {
                $this->warn("  ⚠️  Target already exists: {$newPath}, skipping...");
                return false;
            }

            // Move (rename) the file
            Storage::disk('public')->move($oldPath, $newPath);
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to rename image', [
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
