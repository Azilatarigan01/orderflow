<?php

// Jembatan Serverless Laravel untuk Vercel (ketika Root Directory diset ke orderflow_app)
$tmpDir = sys_get_temp_dir();

$requiredDirs = [
    $tmpDir . '/storage/framework/views',
    $tmpDir . '/storage/framework/cache/data',
    $tmpDir . '/storage/framework/sessions',
    $tmpDir . '/storage/logs',
    $tmpDir . '/storage/app/public',
];

foreach ($requiredDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = $tmpDir . '/database.sqlite';

if (! file_exists($targetDb)) {
    if (file_exists($sourceDb)) {
        @copy($sourceDb, $targetDb);
    } else {
        @touch($targetDb);
    }
}

putenv("VIEW_COMPILED_PATH={$tmpDir}/storage/framework/views");
putenv("SESSION_DRIVER=cookie");
putenv("CACHE_STORE=array");
putenv("LOG_CHANNEL=stderr");
putenv("DB_CONNECTION=sqlite");
putenv("DB_DATABASE={$targetDb}");
putenv("APP_ENV=production");
putenv("APP_DEBUG=false");

require __DIR__ . '/../public/index.php';
