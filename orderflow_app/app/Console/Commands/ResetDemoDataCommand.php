<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orderflow:reset-demo {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database demo OrderFlow ke kondisi awal tanpa data pribadi atau data rahasia perusahaan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  OrderFlow Enterprise - Demo Environment Reset');
        $this->info('====================================================');

        if (app()->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('PERINGATAN: Anda berada di lingkungan PRODUCTION! Apakah Anda yakin ingin mereset data demo?')) {
                $this->warn('Operasi dibatalkan.');
                return Command::FAILURE;
            }
        }

        $this->comment('1. Membersihkan cache aplikasi...');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        $this->info('✓ Cache berhasil dibersihkan.');

        $this->comment('2. Memastikan tautan storage publik (storage:link)...');
        try {
            Artisan::call('storage:link');
            $this->info('✓ Storage link aktif.');
        } catch (\Throwable $e) {
            $this->line('  Storage link sudah ada atau terkonfigurasi.');
        }

        $this->comment('3. Mereset migrasi dan menjalankan seeding data demo terkurasi...');
        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
        $this->info('✓ Database berhasil dimigrasi ulang dan di-seed dengan data demo murni.');

        $this->newLine();
        $this->info('----------------------------------------------------');
        $this->info('  DAFTAR AKUN DEMO RESMI SIAP DIGUNAKAN');
        $this->info('----------------------------------------------------');
        $this->table(
            ['Role', 'Email Akun Demo', 'Password Default'],
            [
                ['Requester', 'requester@orderflow.demo', 'password'],
                ['Manager', 'manager@orderflow.demo', 'password'],
                ['Procurement', 'procurement@orderflow.demo', 'password'],
                ['Finance', 'finance@orderflow.demo', 'password'],
                ['Admin', 'admin@orderflow.demo', 'password'],
                ['Warehouse', 'warehouse@orderflow.demo', 'password'],
            ]
        );

        $this->info('✓ Status: Data demo aman, tidak memuat data pribadi atau rahasia perusahaan asli.');
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
