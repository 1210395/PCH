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
 * Artisan command that removes permanently stored images not referenced in the database.
 *
 * Scans the profiles/, products/, projects/, and services/ directories on the public disk
 * and deletes any file whose path does not appear in the corresponding database table.
 * Supports a --dry-run flag to preview deletions without making changes.
 */
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
     *
     * Iterates through all four image categories, tallies deleted file counts
     * and total freed bytes, then prints a summary. Returns Command::SUCCESS.
     *
     * @return int
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

        // 5–N. Cleanup additional folders that the original audit missed.
        // Each entry: [folder, callable returning array of in-use paths, label].
        // (bugs.md M-57)
        $extraFolders = [
            ['covers',                 fn() => Designer::whereNotNull('cover_image')->where('cover_image', '!=', '')->pluck('cover_image')->all(),                                                          'cover'],
            ['marketplace',            fn() => \App\Models\MarketplacePost::whereNotNull('image')->where('image', '!=', '')->pluck('image')->all(),                                                          'marketplace'],
            ['fablabs',                fn() => \App\Models\FabLab::whereNotNull('image')->where('image', '!=', '')->pluck('image')->all(),                                                                   'fablab'],
            ['fablabs/covers',         fn() => \App\Models\FabLab::whereNotNull('cover_image')->where('cover_image', '!=', '')->pluck('cover_image')->all(),                                                'fablab cover'],
            ['trainings',              fn() => \App\Models\Training::whereNotNull('image')->where('image', '!=', '')->pluck('image')->all(),                                                                'training'],
            ['trainings/instructors',  fn() => \App\Models\Training::whereNotNull('instructor_image')->where('instructor_image', '!=', '')->pluck('instructor_image')->all(),                              'training instructor'],
        ];
        foreach ($extraFolders as [$folder, $refsFn, $label]) {
            $this->info("Checking {$label} images...");
            $result = $this->cleanupGenericFolder($folder, $refsFn, $dryRun);
            $totalDeleted += $result['count'];
            $totalSize += $result['size'];
        }

        $this->newLine();

        if ($totalDeleted > 0) {
            $sizeMB = round($totalSize / 1024 / 1024, 2);
            if ($dryRun) {
                $this->info("DRY RUN: Would delete {$totalDeleted} orphaned image(s) totaling {$sizeMB} MB");
            } else {
                $this->info("Successfully deleted {$totalDeleted} orphaned image(s) totaling {$sizeMB} MB");
                // Use info (not debug) so cleanup events are visible in production
                // log files where LOG_LEVEL is typically set to info or warning.
                // (bugs.md M-52)
                \Log::info("Cleaned up {$totalDeleted} orphaned images ({$sizeMB} MB)");
            }
        } else {
            $this->info('No orphaned images found.');
        }

        return Command::SUCCESS;
    }

    /**
     * Cleanup orphaned profile images from the profiles/ storage directory.
     *
     * @param  bool  $dryRun  When true, only reports files without deleting them
     * @return array{count: int, size: int}
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
     * Cleanup orphaned product images from the products/ storage directory.
     *
     * @param  bool  $dryRun  When true, only reports files without deleting them
     * @return array{count: int, size: int}
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
     * Cleanup orphaned project images from the projects/ storage directory.
     *
     * @param  bool  $dryRun  When true, only reports files without deleting them
     * @return array{count: int, size: int}
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
     * Cleanup orphaned service images from the services/ storage directory.
     *
     * @param  bool  $dryRun  When true, only reports files without deleting them
     * @return array{count: int, size: int}
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

    /**
     * Generic per-folder cleanup. Used for the folders the original
     * implementation missed (covers, marketplace, fablabs, trainings).
     *
     * @param  string    $folder         Storage folder relative to disk('public').
     * @param  callable  $referencedFn   Returns an array of paths still referenced in the DB.
     * @param  bool      $dryRun
     * @return array{count: int, size: int}
     */
    private function cleanupGenericFolder(string $folder, callable $referencedFn, bool $dryRun): array
    {
        $deleted = 0;
        $size = 0;

        try {
            if (!Storage::disk('public')->exists($folder)) {
                return ['count' => 0, 'size' => 0];
            }

            // Use files() (non-recursive) to avoid descending into nested
            // folders that have their own dedicated cleanup pass — e.g.,
            // fablabs/ has fablabs/covers/ as a sibling cleanup.
            $files = Storage::disk('public')->files($folder);
            $referenced = array_flip((array) $referencedFn());

            foreach ($files as $file) {
                if (isset($referenced[$file])) {
                    continue;
                }

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
            $this->error("Error cleaning {$folder} images: {$e->getMessage()}");
        }

        return ['count' => $deleted, 'size' => $size];
    }
}
