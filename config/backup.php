<?php

return [

    /*
    | Path to the mysqldump binary. Leave empty to use the one on PATH.
    | Laragon example: C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe
    */
    'mysqldump_path' => env('MYSQLDUMP_PATH'),

    /* Number of completed backups to keep (older ones are deleted). */
    'keep' => (int) env('BACKUP_KEEP', 14),

    /* Cron expression for the scheduled nightly backup. */
    'schedule' => env('BACKUP_SCHEDULE', '0 2 * * *'),
];
