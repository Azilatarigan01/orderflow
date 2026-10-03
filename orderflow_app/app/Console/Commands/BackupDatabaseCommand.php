<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orderflow:backup-db {--keep=7 : Jumlah hari backup disimpan sebelum dibersihkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan pencadangan (backup) database OrderFlow secara otomatis ke direktori storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  OrderFlow Enterprise - Database Backup Utility');
        $this->info('====================================================');

        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $connection = config('database.default');
        $timestamp = date('Y-m-d_His');

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if (! File::exists($dbPath)) {
                $this->error("File database SQLite tidak ditemukan di: {$dbPath}");
                return Command::FAILURE;
            }

            $targetFile = "{$backupDir}/orderflow_backup_{$timestamp}.sqlite";
            File::copy($dbPath, $targetFile);
            $this->info("✓ Cadangan SQLite berhasil dibuat: {$targetFile}");
            $this->line("  Ukuran berkas: " . number_format(File::size($targetFile) / 1024, 2) . " KB");
        } elseif ($connection === 'mysql') {
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $targetFile = "{$backupDir}/orderflow_backup_{$timestamp}.sql";

            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($targetFile)
            );

            $this->comment("Mengeksekusi mysqldump untuk database: {$database}...");

            $process = Process::fromShellCommandline($command);
            $process->run();

            if (! $process->isSuccessful() && (! File::exists($targetFile) || File::size($targetFile) === 0)) {
                $this->warn("mysqldump CLI tidak langsung tersedia di path. Menjalankan fallback schema dump Laravel...");
                
                // Fallback dump logic
                $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
                $dbProp = "Tables_in_" . $database;
                $sqlContent = "-- OrderFlow Backup: {$timestamp}\n-- Database: {$database}\n\n";

                foreach ($tables as $table) {
                    $tableName = $table->$dbProp ?? array_values((array)$table)[0];
                    $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$tableName}`");
                    $sqlContent .= ($createTable[0]->{'Create Table'} ?? '') . ";\n\n";
                    
                    $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            return is_null($val) ? 'NULL' : "'" . addslashes((string)$val) . "'";
                        }, (array)$row);
                        $sqlContent .= "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n";
                    }
                    $sqlContent .= "\n";
                }

                File::put($targetFile, $sqlContent);
                $this->info("✓ Cadangan MySQL (PHP Export Fallback) berhasil dibuat: {$targetFile}");
            } else {
                $this->info("✓ Cadangan MySQL berhasil dibuat: {$targetFile}");
            }

            if (File::exists($targetFile)) {
                $this->line("  Ukuran berkas: " . number_format(File::size($targetFile) / 1024, 2) . " KB");
            }
        } else {
            $this->error("Koneksi database '{$connection}' belum didukung untuk backup otomatis.");
            return Command::FAILURE;
        }

        // Clean up old backups based on --keep argument
        $keepDays = (int) $this->option('keep');
        $this->comment("Membersihkan backup lebih tua dari {$keepDays} hari...");
        $files = File::files($backupDir);
        $deletedCount = 0;
        $now = time();

        foreach ($files as $file) {
            $fileAge = ($now - $file->getMTime()) / 86400;
            if ($fileAge > $keepDays) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        $this->info("✓ Pembersihan selesai: {$deletedCount} berkas lama dihapus.");
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
