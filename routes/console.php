<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

// Schedule::command('otps:clean')->dailyAt('02:00')
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/otp-clean.log'));

Schedule::command('notifications:send-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::command('queue-work-command')->withoutOverlapping()->everyMinute();
Schedule::job(new \App\Jobs\PruneTelescopeEntriesJob)->dailyAt('05:00');
