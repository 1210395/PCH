<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Auth\ImageUploadController;

/**
 * Artisan command that removes stale temporary upload sessions older than 12 hours.
 *
 * Delegates to ImageUploadController::cleanupOrphanedUploads() which scans the
 * temporary upload directory and removes session folders that have expired.
 * Intended to be run on a scheduled basis (e.g., nightly via the scheduler).
 */
class CleanupOrphanedUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup orphaned temporary upload files older than 24 hours';

    /**
     * Execute the console command.
     *
     * Calls the static cleanup helper and reports how many orphaned upload
     * sessions were removed. Returns Command::SUCCESS in all cases.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting cleanup of orphaned temporary uploads...');

        // cleanupOrphanedUploads() is an instance method that returns
        // ['deleted' => int, 'errors' => string[]]. Static call would error
        // (and return value was being read as int, which it never was).
        // (bugs.md H-38)
        $result = app(ImageUploadController::class)->cleanupOrphanedUploads();
        $deleted = $result['deleted'] ?? 0;
        $errors = $result['errors'] ?? [];

        if ($deleted > 0) {
            $this->info("Successfully cleaned up {$deleted} orphaned upload file(s).");
        } else {
            $this->info('No orphaned uploads found to clean up.');
        }

        if (!empty($errors)) {
            $this->warn(count($errors) . ' file(s) could not be deleted:');
            foreach (array_slice($errors, 0, 10) as $err) {
                $this->warn(' - ' . $err);
            }
        }

        return Command::SUCCESS;
    }
}
