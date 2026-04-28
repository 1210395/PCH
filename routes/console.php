<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// IMPORTANT — for any of the schedules below to actually run, the cPanel
// account needs a cron entry calling Laravel's scheduler every minute:
//
//     * * * * * cd /home/<user>/PalestineCreativeHub && php artisan schedule:run >/dev/null 2>&1

// Send rating reminders for conversations accepted 24+ hours ago.
Schedule::command('conversations:send-rating-reminders')
    ->hourly()
    ->withoutOverlapping(15)
    ->onFailure(function () {
        Log::error('schedule: conversations:send-rating-reminders failed');
    });

// Daily cleanup of orphaned temp upload sessions (>24h).
Schedule::command('uploads:cleanup')
    ->dailyAt('03:00')
    ->withoutOverlapping(30)
    ->onFailure(function () {
        Log::error('schedule: uploads:cleanup failed');
    });

// Weekly cleanup of orphaned image files no longer referenced in any record.
Schedule::command('images:cleanup-orphaned --no-interaction')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->withoutOverlapping(60)
    ->onFailure(function () {
        Log::error('schedule: images:cleanup-orphaned failed');
    });

// Weekly counter rebuild — repairs any drift in followers_count /
// following_count / likes_count / comments_count / projects_count caused
// by partial failures or legacy races. (bugs.md M-9)
Schedule::command('pch:recompute-counters')
    ->weekly()
    ->mondays()
    ->at('05:00')
    ->withoutOverlapping(30)
    ->onFailure(function () {
        Log::error('schedule: pch:recompute-counters failed');
    });
