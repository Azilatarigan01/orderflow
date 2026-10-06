<?php

// Aktifkan error reporting untuk mendiagnosis jika ada kendala serverless
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$tmpDir = sys_get_temp_dir();
$storageDir = $tmpDir . '/storage';

// Pastikan semua direktori writable yang dibutuhkan Laravel ada di /tmp
$requiredDirs = [
    $storageDir . '/framework/views',
    $storageDir . '/framework/cache/data',
    $storageDir . '/framework/sessions',
    $storageDir . '/logs',
    $storageDir . '/app/public',
];

foreach ($requiredDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Inisialisasi Database SQLite di /tmp jika belum ada
$appBase = file_exists(__DIR__ . '/../orderflow_app') ? realpath(__DIR__ . '/../orderflow_app') : realpath(__DIR__ . '/..');
$sourceDb = $appBase . '/database/demo_seed.sqlite';
if (! file_exists($sourceDb)) {
    $sourceDb = $appBase . '/database/database.sqlite';
}
$targetDb = $tmpDir . '/database.sqlite';

if (! file_exists($targetDb) || filesize($targetDb) === 0) {
    if (file_exists($sourceDb)) {
        @copy($sourceDb, $targetDb);
    } else {
        @touch($targetDb);
    }
}

// Inisialisasi Environment Variables esensial
$appKey = getenv('APP_KEY') ?: 'base64:5T2ud78dMgLUV8EIhLArb9wcMGBDigGTpKTk+lefN00=';

$serverlessEnv = [
    'APP_KEY' => $appKey,
    'APP_ENV' => getenv('APP_ENV') ?: 'production',
    'APP_DEBUG' => getenv('APP_DEBUG') ?: 'false',
    'APP_NAME' => 'OrderFlow Enterprise',
    'APP_STORAGE_PATH' => $storageDir,
    'VIEW_COMPILED_PATH' => $storageDir . '/framework/views',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $targetDb,
];

foreach ($serverlessEnv as $key => $val) {
    putenv("{$key}={$val}");
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
}

try {
    if (! defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }

    require $appBase . '/vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require $appBase . '/bootstrap/app.php';
    $app->useStoragePath($storageDir);

    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>OrderFlow Enterprise - Diagnostic</title>';
    echo '<style>body{font-family:ui-sans-serif,system-ui,sans-serif;padding:32px;background:#090d16;color:#e2e8f0;line-height:1.6;}';
    echo 'h2{color:#38bdf8;} pre{background:#1e293b;padding:16px;border-radius:8px;overflow:auto;color:#fca5a5;border:1px solid #334155;}</style></head><body>';
    echo '<h2>OrderFlow Enterprise - System Diagnostic</h2>';
    echo '<p><strong>Pesan:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' (Baris ' . $e->getLine() . ')</p>';
    echo '<h3>Detail Stack Trace:</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
}
