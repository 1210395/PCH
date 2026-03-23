<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send rating reminders for conversations accepted 24+ hours ago (runs hourly)
Schedule::command('conversations:send-rating-reminders')->hourly();
