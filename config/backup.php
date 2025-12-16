<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mysqldump Binary Path
    |--------------------------------------------------------------------------
    |
    | Path to mysqldump binary. If empty, will auto-detect from common paths.
    | Can also be set via MYSQLDUMP_PATH environment variable.
    |
    */
    'mysqldump_path' => env('MYSQLDUMP_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | The following settings are used by Spatie's backup package to
    | configure where backups are stored and which files/databases
    | are included in the backup.
    |
    */

    'backup' => [

        /*
        |--------------------------------------------------------------------------
        | Sources to backup
        |--------------------------------------------------------------------------
        |
        | These directories and files will be included in the backup.
        |
        */

        'source' => [

            'files' => [

                /*
                |--------------------------------------------------------------------------
                | Directories and files to be backed up
                |--------------------------------------------------------------------------
                |
                | You can specify individual files, directories, or use wildcards
                | for more flexibility.
                |
                | NOTE: These are default values. When creating backups through the
                | application UI, these defaults are overridden with user selections.
                |
                */

                'include' => [
                    // Empty by default - populated dynamically via application UI
                ],

                'exclude' => [
                    base_path('storage/logs'),
                    base_path('storage/app/backups'),
                    base_path('bootstrap/cache'),
                    base_path('node_modules'),
                    base_path('vendor'),
                    base_path('.git'),
                ],

                'follow_links' => false,

                /*
                |--------------------------------------------------------------------------
                | Symlinks
                |--------------------------------------------------------------------------
                |
                | Following symlinks can be risky and could result in infinite loops.
                |
                */

                'relative_path' => null,
            ],

            'databases' => [
                // Empty by default - populated dynamically via application UI
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | The destination where the backups will be saved
        |--------------------------------------------------------------------------
        |
        | The destination disk and path where backups will be stored.
        |
        */

        'destination' => [

            'disks' => [
                'local',
            ],

            /*
            |--------------------------------------------------------------------------
            | The directory where the backups will be stored
            |--------------------------------------------------------------------------
            |
            | If you want to store the backup to a specific directory
            |
            */

            'directory_prefix' => 'backups',

        ],

        /*
        |--------------------------------------------------------------------------
        | Cleanup strategy
        |--------------------------------------------------------------------------
        |
        | Here you can specify which backups should be deleted.
        | The strategy `Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy`
        | will keep all backups and remove those older than 7 days.
        |
        */

        'cleanup' => [
            'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
            'deleteOldBackupsWhenBackupWasSuccessful' => true,
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 30,
            'keep_weekly_backups_for_weeks' => 5,
            'keep_monthly_backups_for_months' => 12,
            'keep_yearly_backups_for_years' => 2,
            'deleteOldBackupsWhenNoneExists' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Here you can specify which notifications should be sent when
    | a backup is created.
    |
    */

    'notifications' => [

        'notifications' => [
            // Map all backup events to silent notification classes
            \Spatie\Backup\Events\BackupHasSucceeded::class => \App\Notifications\BackupNotifications\BackupSuccessfulNotification::class,
            \Spatie\Backup\Events\BackupHasFailed::class => \App\Notifications\BackupNotifications\BackupFailedNotification::class,
        ],

        'notifiable' => \App\Notifications\BackupNotification::class,

        'channels' => [],

        'mail' => [
            'to' => 'admin@example.com',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Monitorable Backups
    |--------------------------------------------------------------------------
    |
    | Here you can specify the backups that you want to monitor for health.
    |
    */

    'monitorable' => [
        [
            'name' => env('APP_NAME', 'Laravel'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

];
