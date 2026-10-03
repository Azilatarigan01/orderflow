<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\GoodsReceipt;

class CleanupOrphanedStorageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:cleanup-orphans {--dry-run : Only show files that would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned uploaded files from storage that are not referenced in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info("Scanning storage for orphaned files...");
        if ($isDryRun) {
            $this->warn("[DRY RUN MODE] No files will be permanently deleted.");
        }

        // Collect all legitimate database references
        $dbPaths = collect();

        $dbPaths = $dbPaths->merge(Attachment::pluck('file_path')->filter());
        $dbPaths = $dbPaths->merge(PurchaseRequest::whereNotNull('attachment_path')->pluck('attachment_path')->filter());
        $dbPaths = $dbPaths->merge(Quotation::whereNotNull('file_path')->pluck('file_path')->filter());
        $dbPaths = $dbPaths->merge(GoodsReceipt::whereNotNull('delivery_note_doc')->pluck('delivery_note_doc')->filter());
        $dbPaths = $dbPaths->merge(GoodsReceipt::whereNotNull('bast_document_path')->pluck('bast_document_path')->filter());

        // Normalize paths (slashes, trim)
        $knownPaths = $dbPaths->map(fn($p) => str_replace('\\', '/', trim($p)))->flip();

        $directoriesToScan = [
            'attachments',
            'quotations',
            'delivery_notes',
            'basts',
            'receipts',
        ];

        $disk = Storage::disk('public');
        $orphanedFiles = [];
        $totalBytesFreed = 0;

        foreach ($directoriesToScan as $dir) {
            if (!$disk->exists($dir)) {
                continue;
            }

            $files = $disk->allFiles($dir);
            foreach ($files as $file) {
                $normalizedFile = str_replace('\\', '/', $file);
                // Exclude system files like .gitignore or .empty
                if (str_starts_with(basename($normalizedFile), '.')) {
                    continue;
                }

                if (!$knownPaths->has($normalizedFile)) {
                    $size = $disk->exists($normalizedFile) ? $disk->size($normalizedFile) : 0;
                    $orphanedFiles[] = [
                        'file' => $normalizedFile,
                        'size' => $size,
                    ];
                    $totalBytesFreed += $size;
                }
            }
        }

        if (empty($orphanedFiles)) {
            $this->info("No orphaned files found. Storage is clean and fully synchronized with database.");
            return Command::SUCCESS;
        }

        $this->table(
            ['Orphaned File Path', 'Size (KB)'],
            collect($orphanedFiles)->map(fn($item) => [
                $item['file'],
                number_format($item['size'] / 1024, 2) . ' KB',
            ])
        );

        $formattedFreed = number_format($totalBytesFreed / (1024 * 1024), 2) . ' MB';

        if ($isDryRun) {
            $this->info("Found " . count($orphanedFiles) . " orphaned file(s). Estimated space to free: {$formattedFreed}.");
            return Command::SUCCESS;
        }

        $deletedCount = 0;
        foreach ($orphanedFiles as $item) {
            if ($disk->delete($item['file'])) {
                $deletedCount++;
            }
        }

        $this->info("Successfully deleted {$deletedCount} orphaned file(s), reclaiming {$formattedFreed} of disk space.");
        return Command::SUCCESS;
    }
}
