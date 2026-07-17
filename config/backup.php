<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    |
    | Disk used to store database backups. Defaults to the same Cloudflare R2
    | disk used for documents, under a separate folder prefix.
    |
    */

    'disk' => env('BACKUPS_DISK', env('DOCUMENTS_DISK', 'r2')),

    /*
    |--------------------------------------------------------------------------
    | Backup Path Prefix
    |--------------------------------------------------------------------------
    */

    'path_prefix' => 'backups/database',

    /*
    |--------------------------------------------------------------------------
    | Temporary Local Directory
    |--------------------------------------------------------------------------
    */

    'temp_directory' => storage_path('app/private/backups-temp'),

    /*
    |--------------------------------------------------------------------------
    | Download URL Lifetime
    |--------------------------------------------------------------------------
    */

    'download_url_minutes' => 60,

    /*
    |--------------------------------------------------------------------------
    | Automatic Schedules
    |--------------------------------------------------------------------------
    |
    | Times are interpreted in the application timezone.
    | Weekly runs on Sunday (0). Monthly runs on the 1st day of the month.
    |
    */

    'schedules' => [
        'daily' => [
            'enabled' => env('BACKUP_DAILY_ENABLED', true),
            'time' => env('BACKUP_DAILY_TIME', '02:00'),
        ],
        'weekly' => [
            'enabled' => env('BACKUP_WEEKLY_ENABLED', true),
            'day' => (int) env('BACKUP_WEEKLY_DAY', 0),
            'time' => env('BACKUP_WEEKLY_TIME', '03:00'),
        ],
        'monthly' => [
            'enabled' => env('BACKUP_MONTHLY_ENABLED', true),
            'day' => (int) env('BACKUP_MONTHLY_DAY', 1),
            'time' => env('BACKUP_MONTHLY_TIME', '04:00'),
        ],
    ],

];
