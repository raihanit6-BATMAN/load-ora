<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SpreadsheetImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ImportGoogleSheet extends Command
{
    protected $signature = 'loadora:import-sheet {file=sheet_export.csv}';
    protected $description = 'Import and migrate historical Google Sheet data into LOADORA MySQL database';

    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('Admin user not found. Please run seeders first.');
            return 1;
        }

        Auth::login($admin);

        $this->info("Parsing and staging file: {$filePath}...");
        $service = new SpreadsheetImportService();
        $batch = $service->stageFile($filePath, basename($filePath), pathinfo($filePath, PATHINFO_EXTENSION));

        $this->line("Batch Code: {$batch->batch_code}");
        $this->line("Total Staged Rows: {$batch->total_rows}");
        $this->line("Valid Rows: {$batch->summary['valid']}");
        $this->line("Warning Rows: {$batch->summary['warning']}");
        $this->line("Error / Blocked Rows: {$batch->failed_rows}");

        $this->info("Committing batch to MySQL...");
        $result = $service->commitBatch($batch);

        $this->info("Migration Complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Dispatches Created', $result['dispatches']],
                ['Receipts Created', $result['receipts']],
                ['Rows Skipped', $result['skipped']],
            ]
        );

        return 0;
    }
}
