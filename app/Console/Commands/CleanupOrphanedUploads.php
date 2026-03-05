<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Auth\ImageUploadController;

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
    protected $description = 'Cleanup orphaned temporary upload files older than 12 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of orphaned temporary uploads...');

        $count = ImageUploadController::cleanupOrphanedUploads();

        if ($count > 0) {
            $this->info("Successfully cleaned up {$count} orphaned upload session(s).");
        } else {
            $this->info('No orphaned uploads found to clean up.');
        }

        return Command::SUCCESS;
    }
}
