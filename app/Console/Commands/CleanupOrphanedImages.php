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

class CleanupOrphanedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup-orphaned {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup orphaned images from permanent storage that are no longer referenced in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        $this->info('Starting cleanup of orphaned permanent storage images...');
        $this->newLine();

        $totalDeleted = 0;
        $totalSize = 0;

        // 1. Cleanup orphaned profile images
        $this->info('Checking profile images...');
        $result = $this->cleanupProfileImages($dryRun);
        $totalDeleted += $result['count'];
        $totalSize += $result['size'];

        // 2. Cleanup orphaned product images
        $this->info('Checking product images...');
        $result = $this->cleanupProductImages($dryRun);
        $totalDeleted += $result['count'];
        $totalSize += $result['size'];

        // 3. Cleanup orphaned project images
        $this->info('Checking project images...');
        $result = $this->cleanupProjectImages($dryRun);
        $totalDeleted += $result['count'];
        $totalSize += $result['size'];

        // 4. Cleanup orphaned service images
        $this->info('Checking service images...');
        $result = $this->cleanupServiceImages($dryRun);
        $totalDeleted += $result['count'];
        $totalSize += $result['size'];

        $this->newLine();

        if ($totalDeleted > 0) {
            $sizeMB = round($totalSize / 1024 / 1024, 2);
            if ($dryRun) {
                $this->info("DRY RUN: Would delete {$totalDeleted} orphaned image(s) totaling {$sizeMB} MB");
            } else {
                $this->info("Successfully deleted {$totalDeleted} orphaned image(s) totaling {$sizeMB} MB");
                og::debug("Cleaned up {$totalDeleted} orphaned images ({$sizeMB} MB)");
            }
        } else {
            $this->info('No orphaned images found.');
        }

        return Command::SUCCESS;
    }

    /**
     * Cleanup orphaned profile images
     */
    private function cleanupProfileImages($dryRun)
    {
        $deleted = 0;
        $size = 0;

        try {
            // Get all profile images from storage
            $files = Storage::disk('public')->files('profiles');

            // Get all profile images referenced in database
            $referencedImages = Designer::whereNotNull('profile_picture')
                ->where('profile_picture', '!=', '')
                ->pluck('profile_picture')
                ->toArray();

            foreach ($files as $file) {
                // Skip if referenced in database
                if (in_array($file, $referencedImages)) {
                    continue;
                }

                // This is an orphaned file
                $fileSize = Storage::disk('public')->size($file);
                $size += $fileSize;

                if ($dryRun) {
                    $this->line("  Would delete: {$file}");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("  Deleted: {$file}");
                }
                $deleted++;
            }

        } catch (\Exception $e) {
            $this->error("Error cleaning profile images: {$e->getMessage()}");
        }

        return ['count' => $deleted, 'size' => $size];
    }

    /**
     * Cleanup orphaned product images
     */
    private function cleanupProductImages($dryRun)
    {
        $deleted = 0;
        $size = 0;

        try {
            // Get all product images from storage
            $files = Storage::disk('public')->files('products');

            // Get all product images referenced in database
            $referencedImages = ProductImage::pluck('image_path')->toArray();

            foreach ($files as $file) {
                // Skip if referenced in database
                if (in_array($file, $referencedImages)) {
                    continue;
                }

                // This is an orphaned file
                $fileSize = Storage::disk('public')->size($file);
                $size += $fileSize;

                if ($dryRun) {
                    $this->line("  Would delete: {$file}");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("  Deleted: {$file}");
                }
                $deleted++;
            }

        } catch (\Exception $e) {
            $this->error("Error cleaning product images: {$e->getMessage()}");
        }

        return ['count' => $deleted, 'size' => $size];
    }

    /**
     * Cleanup orphaned project images
     */
    private function cleanupProjectImages($dryRun)
    {
        $deleted = 0;
        $size = 0;

        try {
            // Get all project images from storage
            $files = Storage::disk('public')->files('projects');

            // Get all project images referenced in database
            $referencedImages = ProjectImage::pluck('image_path')->toArray();

            foreach ($files as $file) {
                // Skip if referenced in database
                if (in_array($file, $referencedImages)) {
                    continue;
                }

                // This is an orphaned file
                $fileSize = Storage::disk('public')->size($file);
                $size += $fileSize;

                if ($dryRun) {
                    $this->line("  Would delete: {$file}");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("  Deleted: {$file}");
                }
                $deleted++;
            }

        } catch (\Exception $e) {
            $this->error("Error cleaning project images: {$e->getMessage()}");
        }

        return ['count' => $deleted, 'size' => $size];
    }

    /**
     * Cleanup orphaned service images
     */
    private function cleanupServiceImages($dryRun)
    {
        $deleted = 0;
        $size = 0;

        try {
            // Get all service images from storage
            $files = Storage::disk('public')->files('services');

            // Get all service images referenced in database
            $referencedImages = Service::whereNotNull('image')
                ->where('image', '!=', '')
                ->pluck('image')
                ->toArray();

            foreach ($files as $file) {
                // Skip if referenced in database
                if (in_array($file, $referencedImages)) {
                    continue;
                }

                // This is an orphaned file
                $fileSize = Storage::disk('public')->size($file);
                $size += $fileSize;

                if ($dryRun) {
                    $this->line("  Would delete: {$file}");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("  Deleted: {$file}");
                }
                $deleted++;
            }

        } catch (\Exception $e) {
            $this->error("Error cleaning service images: {$e->getMessage()}");
        }

        return ['count' => $deleted, 'size' => $size];
    }
}
