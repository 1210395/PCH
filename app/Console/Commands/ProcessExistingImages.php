<?php

namespace App\Console\Commands;

use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProcessExistingImages extends Command
{
    protected $signature = 'images:process
                            {--folder= : Process a specific folder (e.g. products, profiles)}
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Batch process existing images: center-crop, resize, and convert to WebP';

    private array $folderRatioMap = [
        'profiles'              => ImageService::SQUARE,
        'covers'                => ImageService::BANNER,
        'products'              => ImageService::CARD,
        'projects'              => ImageService::CARD,
        'services'              => ImageService::CARD,
        'marketplace'           => ImageService::CARD,
        'fablabs'               => ImageService::CARD,
        'fablabs/covers'        => ImageService::BANNER,
        'trainings'             => ImageService::CARD,
        'trainings/instructors' => ImageService::SQUARE,
        'academic-trainings'    => ImageService::CARD,
        'academic-workshops'    => ImageService::CARD,
        'academic-announcements'=> ImageService::CARD,
        'academic-accounts'     => ImageService::SQUARE,
    ];

    public function handle(): int
    {
        $specificFolder = $this->option('folder');
        $dryRun = $this->option('dry-run');

        $folders = $specificFolder
            ? [$specificFolder => $this->folderRatioMap[$specificFolder] ?? ImageService::CARD]
            : $this->folderRatioMap;

        $totalProcessed = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach ($folders as $folder => $ratio) {
            if (!Storage::disk('public')->exists($folder)) {
                $this->line("  Skipping {$folder} (not found)");
                continue;
            }

            $files = Storage::disk('public')->files($folder);
            $imageFiles = array_filter($files, function ($f) {
                return preg_match('/\.(jpe?g|png|gif|bmp|tiff?)$/i', $f);
            });

            if (empty($imageFiles)) {
                $this->line("  {$folder}: no images to process");
                continue;
            }

            $this->info("Processing {$folder} (" . count($imageFiles) . " images, ratio: {$ratio})");
            $bar = $this->output->createProgressBar(count($imageFiles));

            foreach ($imageFiles as $file) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would process: {$file} → " . preg_replace('/\.[^.]+$/', '.webp', $file));
                    $totalProcessed++;
                    $bar->advance();
                    continue;
                }

                $result = ImageService::processExisting($file, $ratio);

                if ($result) {
                    $totalProcessed++;
                } else {
                    $totalFailed++;
                    $this->warn("  Failed: {$file}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("Done! Processed: {$totalProcessed}, Failed: {$totalFailed}, Skipped: {$totalSkipped}");

        if ($dryRun) {
            $this->warn('This was a dry run. No files were modified.');
        }

        // Update database paths from old extensions to .webp
        if (!$dryRun && $totalProcessed > 0) {
            $this->info('Updating database paths to .webp...');
            $this->updateDatabasePaths();
        }

        return Command::SUCCESS;
    }

    private function updateDatabasePaths(): void
    {
        $models = [
            [\App\Models\Designer::class, ['avatar', 'cover_image']],
            [\App\Models\Product::class, ['image']],
            [\App\Models\Project::class, ['image']],
            [\App\Models\Service::class, ['image']],
            [\App\Models\MarketplacePost::class, ['image']],
            [\App\Models\FabLab::class, ['image', 'cover_image']],
            [\App\Models\Training::class, ['image', 'cover_image', 'instructor_image']],
        ];

        foreach ($models as [$modelClass, $columns]) {
            if (!class_exists($modelClass)) continue;

            foreach ($columns as $col) {
                $updated = $modelClass::whereNotNull($col)
                    ->where($col, '!=', '')
                    ->where($col, 'NOT LIKE', '%.webp')
                    ->update([
                        // Replace ONLY the trailing extension. The previous
                        // SUBSTRING_INDEX(col, '.', 1) sliced at the FIRST
                        // dot, which corrupts paths like
                        // products/v1.2/img.jpg → products/v1.webp.
                        // LOCATE('.', REVERSE(col)) finds the last-dot
                        // position counted from the end. (bugs.md M-53)
                        $col => \DB::raw("CONCAT(SUBSTRING(`{$col}`, 1, LENGTH(`{$col}`) - LOCATE('.', REVERSE(`{$col}`))), '.webp')")
                    ]);

                if ($updated > 0) {
                    $this->line("  Updated {$updated} {$modelClass}::{$col} paths");
                }
            }
        }

        // Update ProductImage and ProjectImage tables
        $imageModels = [
            [\App\Models\ProductImage::class, 'image_path'],
            [\App\Models\ProjectImage::class, 'image_path'],
        ];

        foreach ($imageModels as [$modelClass, $col]) {
            if (!class_exists($modelClass)) continue;

            $updated = $modelClass::whereNotNull($col)
                ->where($col, '!=', '')
                ->where($col, 'NOT LIKE', '%.webp')
                ->update([
                    $col => \DB::raw("CONCAT(SUBSTRING_INDEX(`{$col}`, '.', 1), '.webp')")
                ]);

            if ($updated > 0) {
                $this->line("  Updated {$updated} {$modelClass}::{$col} paths");
            }
        }

        $this->info('Database paths updated.');
    }
}
