<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$daily = config('backup.schedules.daily', []);
$weekly = config('backup.schedules.weekly', []);
$monthly = config('backup.schedules.monthly', []);

if ($daily['enabled'] ?? false) {
    Schedule::command('backup:database --type=daily')
        ->dailyAt((string) ($daily['time'] ?? '02:00'))
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/backup-daily.log'));
}

if ($weekly['enabled'] ?? false) {
    Schedule::command('backup:database --type=weekly')
        ->weeklyOn((int) ($weekly['day'] ?? 0), (string) ($weekly['time'] ?? '03:00'))
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/backup-weekly.log'));
}

if ($monthly['enabled'] ?? false) {
    Schedule::command('backup:database --type=monthly')
        ->monthlyOn((int) ($monthly['day'] ?? 1), (string) ($monthly['time'] ?? '04:00'))
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/backup-monthly.log'));
}
