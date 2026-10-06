<?php

// Jembatan Serverless Laravel untuk Vercel
$tmpDir = sys_get_temp_dir();

// Pastikan direktori sementara untuk view, cache, dan session tersedia di /tmp
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

// Inisialisasi Database SQLite di /tmp jika belum ada
$sourceDb = __DIR__ . '/../orderflow_app/database/database.sqlite';
$targetDb = $tmpDir . '/database.sqlite';

if (! file_exists($targetDb)) {
    if (file_exists($sourceDb)) {
        @copy($sourceDb, $targetDb);
    } else {
        @touch($targetDb);
    }
}

// Set environment variable sementara untuk filesystem serverless Vercel
putenv("VIEW_COMPILED_PATH={$tmpDir}/storage/framework/views");
putenv("SESSION_DRIVER=cookie");
putenv("CACHE_STORE=array");
putenv("LOG_CHANNEL=stderr");
putenv("DB_CONNECTION=sqlite");
putenv("DB_DATABASE={$targetDb}");
putenv("APP_ENV=production");
putenv("APP_DEBUG=false");

// Jalankan aplikasi Laravel melalui public/index.php
require __DIR__ . '/../orderflow_app/public/index.php';
