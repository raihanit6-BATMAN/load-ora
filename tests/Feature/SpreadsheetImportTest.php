<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\ImportBatch;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\SpreadsheetImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpreadsheetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_spreadsheet_import_service_processes_csv_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Create temporary test CSV file
        $tempCsv = tempnam(sys_get_temp_dir(), 'loadora_test_') . '.csv';
        $file = fopen($tempCsv, 'w');

        // Header matching Google Sheet format
        fputcsv($file, [
            'SL', 'L-Date', 'Vehicle No', 'Goods', 'L-Qty', 'R-Date', 'Slip No', 'R-Qty', 'Deduction'
        ]);

        // Row 1: Complete dispatch & receipt
        fputcsv($file, [
            '1', '15/10/2026', 'WB-23-4567', 'STONE 10-20mm', '35.500', '16/10/2026', 'SLP-101', '35.300', '0.200'
        ]);

        // Row 2: Missing L-Date (inferred from R-Date)
        fputcsv($file, [
            '2', '', 'JH-01-8899', 'STONE 5-10mm', '28.400', '17/10/2026', 'SLP-102', '28.400', '0.000'
        ]);

        // Row 3: Missing vehicle plate (invalid row)
        fputcsv($file, [
            '3', '18/10/2026', '', 'STONE 10-20mm', '30.000', '', '', '', ''
        ]);

        fclose($file);

        $service = new SpreadsheetImportService();
        $batch = $service->stageFile($tempCsv, 'test_export.csv', 'csv');

        $this->assertInstanceOf(ImportBatch::class, $batch);
        $this->assertEquals('mapped', $batch->status);
        $this->assertEquals(3, $batch->total_rows);
        $this->assertEquals(1, $batch->failed_rows); // Row 3 has missing vehicle
        $this->assertEquals(1, $batch->summary['valid']); // Row 1
        $this->assertEquals(1, $batch->summary['warning']); // Row 2 (inferred date)

        // Commit batch
        $results = $service->commitBatch($batch);
        $batch->refresh();

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(2, $results['dispatches']);
        $this->assertEquals(2, $results['receipts']);

        // Verify dispatches created in database
        $this->assertDatabaseHas('dispatches', [
            'loading_quantity' => 35.500,
            'total_received_quantity' => 35.300,
            'total_deduction_quantity' => 0.200,
            'status' => 'received',
        ]);

        // Verify vehicle created
        $this->assertDatabaseHas('vehicles', [
            'vehicle_number' => 'WB-23-4567',
        ]);

        // Verify goods created
        $this->assertDatabaseHas('goods', [
            'name' => 'STONE 10-20mm',
        ]);

        // Clean up temp file
        @unlink($tempCsv);
    }
}
