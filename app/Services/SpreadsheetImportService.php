<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\Receipt;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpreadsheetImportService
{
    /**
     * Parse raw uploaded file and create an ImportBatch with staged ImportRows.
     */
    public function stageFile(string $filePath, string $originalName, string $fileType): ImportBatch
    {
        $batchCode = 'IMP-' . date('Ymd-His') . '-' . Str::random(4);

        $batch = ImportBatch::create([
            'batch_code' => $batchCode,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'status' => 'uploaded',
            'imported_by' => Auth::id(),
        ]);

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read header
            $headers = fgetcsv($handle);
            if (!$headers) {
                $batch->update(['status' => 'failed']);
                fclose($handle);
                return $batch;
            }

            // Normalize header strings
            $headers = array_map(function ($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $headers);

            $rowIndex = 2;
            while (($data = fgetcsv($handle)) !== false) {
                // Skip completely empty rows
                if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                    continue;
                }

                $rawRow = [];
                foreach ($headers as $idx => $headerName) {
                    if (!empty($headerName)) {
                        $rawRow[$headerName] = isset($data[$idx]) ? trim($data[$idx]) : '';
                    }
                }

                $validated = $this->validateAndParseRow($rawRow, $rowIndex);

                ImportRow::create([
                    'batch_id' => $batch->id,
                    'row_index' => $rowIndex,
                    'raw_data' => $rawRow,
                    'parsed_data' => $validated['parsed'],
                    'validation_status' => $validated['status'],
                    'validation_errors' => $validated['errors'],
                ]);

                $rowIndex++;
            }
            fclose($handle);
        }

        $total = $batch->rows()->count();
        $failed = $batch->rows()->where('validation_status', 'error')->count();
        $valid = $batch->rows()->where('validation_status', 'valid')->count();
        $warning = $batch->rows()->where('validation_status', 'warning')->count();

        $batch->update([
            'total_rows' => $total,
            'failed_rows' => $failed,
            'status' => 'mapped',
            'summary' => [
                'total' => $total,
                'valid' => $valid,
                'warning' => $warning,
                'failed' => $failed,
            ],
        ]);

        return $batch;
    }

    /**
     * Validate and clean a single spreadsheet row.
     */
    protected function validateAndParseRow(array $raw, int $rowIndex): array
    {
        $errors = [];
        $status = 'valid';

        // Extract raw fields using fuzzy key lookup
        $lDateRaw = $this->getVal($raw, ['L-Date', 'Loading Date', 'Date', 'L Date', 'Loading']);
        $vehRaw = $this->getVal($raw, ['vehicle No', 'Vehicle Number', 'Vehicle', 'Truck', 'Vehicle No', 'Vehicle_No']);
        $driverRaw = $this->getVal($raw, ['Driver Name', 'Driver', 'Driver_Name']);
        $goodsRaw = $this->getVal($raw, ['Goods', 'Material', 'Item', 'Product']);
        $lQtyRaw = $this->getVal($raw, ['L-Quantity', 'Loading Qty', 'Loaded Qty', 'Loaded', 'L-Qty', 'L Qty', 'Quantity']);
        $receiptNoRaw = $this->getVal($raw, ['Receipt Number', 'Receipt No', 'Receipt', 'Challan', 'Slip No', 'Slip']);
        $rDateRaw = $this->getVal($raw, ['R-Date', 'Receiving Date', 'Recv Date', 'R Date', 'Received Date']);
        $rQtyRaw = $this->getVal($raw, ['R-Quantity', 'Receiving Qty', 'Received Qty', 'Received', 'R-Qty', 'R Qty']);
        $locationRaw = $this->getVal($raw, ['Location', 'Site', 'Destination', 'Yard']);
        $noteRaw = $this->getVal($raw, ['Note', 'Remarks', 'Comment', 'Notes']);
        $deductionRaw = $this->getVal($raw, ['DEDUCTION', 'Deduction', 'Deduction ', 'Ded']);

        // Clean & Normalize Vehicle
        $cleanVehicle = strtoupper(preg_replace('/\s+/', ' ', trim($vehRaw)));
        if (empty($cleanVehicle)) {
            $errors[] = "Row {$rowIndex}: Missing vehicle license plate.";
            $status = 'error';
        }

        // Clean & Normalize Goods
        $cleanGoods = preg_replace('/\s+/', ' ', trim($goodsRaw));
        if (empty($cleanGoods)) {
            $cleanGoods = 'STONE 10-20mm';
        }

        // Parse Receiving Date first (if present)
        $cleanRDate = null;
        if (!empty($rDateRaw)) {
            try {
                $cleanRDate = Carbon::createFromFormat('d/m/Y', trim($rDateRaw))->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $cleanRDate = Carbon::parse(trim($rDateRaw))->format('Y-m-d');
                } catch (\Exception $e2) {
                    // Ignore non-fatal parse errors
                }
            }
        }

        // Clean & Normalize Loading Date (DD/MM/YYYY)
        $cleanLDate = null;
        if (!empty($lDateRaw)) {
            try {
                $cleanLDate = Carbon::createFromFormat('d/m/Y', trim($lDateRaw))->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $cleanLDate = Carbon::parse(trim($lDateRaw))->format('Y-m-d');
                } catch (\Exception $e2) {
                    $errors[] = "Row {$rowIndex}: Invalid loading date '{$lDateRaw}'.";
                    $status = 'error';
                }
            }
        } elseif (!empty($cleanRDate)) {
            // Intelligent fallback: if L-Date is empty, infer from R-Date
            $cleanLDate = $cleanRDate;
            if ($status !== 'error') {
                $status = 'warning';
            }
        } else {
            $errors[] = "Row {$rowIndex}: Missing loading and receiving date.";
            $status = 'error';
        }

        // Clean & Normalize Loaded Quantity
        $cleanLQty = null;
        if ($lQtyRaw !== '' && is_numeric($lQtyRaw)) {
            $cleanLQty = round((float) $lQtyRaw, 3);
            if ($cleanLQty <= 0) {
                $errors[] = "Row {$rowIndex}: Loading quantity must be positive.";
                $status = 'error';
            }
        } elseif ($rQtyRaw !== '' && is_numeric($rQtyRaw)) {
            // If loaded quantity is blank but received is present, infer loaded from received
            $cleanLQty = round((float) $rQtyRaw, 3);
            if ($status !== 'error') {
                $status = 'warning';
            }
        } else {
            $errors[] = "Row {$rowIndex}: Missing or invalid loading quantity.";
            $status = 'error';
        }

        $cleanRQty = ($rQtyRaw !== '' && is_numeric($rQtyRaw)) ? round((float) $rQtyRaw, 3) : null;
        $cleanReceiptNo = trim($receiptNoRaw);
        $cleanLocation = !empty($locationRaw) ? trim($locationRaw) : 'P-Block';

        // Check for deduction calculation
        $cleanDeduction = 0.000;
        if ($cleanLQty !== null && $cleanRQty !== null) {
            $cleanDeduction = max(0.000, round($cleanLQty - $cleanRQty, 3));
        }

        if ($status !== 'error' && empty($cleanReceiptNo)) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'errors' => $errors,
            'parsed' => [
                'loading_date' => $cleanLDate,
                'vehicle_number' => $cleanVehicle,
                'driver_name' => !empty($driverRaw) ? trim($driverRaw) : null,
                'goods_name' => $cleanGoods,
                'loading_quantity' => $cleanLQty,
                'destination_location' => $cleanLocation,
                'receipt_number' => $cleanReceiptNo,
                'receiving_date' => $cleanRDate ?: $cleanLDate,
                'received_quantity' => $cleanRQty,
                'deduction_quantity' => $cleanDeduction,
                'note' => trim($noteRaw),
            ],
        ];
    }

    /**
     * Atomically commit staged rows into the dispatches and receipts tables.
     */
    public function commitBatch(ImportBatch $batch): array
    {
        $defaultClient = Client::firstOrCreate(
            ['name' => 'P-Block Project'],
            ['code' => 'P-BLK', 'delivery_location' => 'P-Block Site', 'status' => 'active']
        );

        $insertedDispatches = 0;
        $insertedReceipts = 0;
        $skippedRows = 0;

        DB::transaction(function () use ($batch, $defaultClient, &$insertedDispatches, &$insertedReceipts, &$skippedRows) {
            $year = now()->format('Y');
            $latest = Dispatch::whereYear('created_at', $year)->latest('id')->first();
            $nextSeq = $latest ? ((int) substr($latest->dispatch_number, -4)) + 1 : 1;

            $rows = $batch->rows()->whereIn('validation_status', ['valid', 'warning'])->get();

            foreach ($rows as $row) {
                $p = $row->parsed_data;
                if (!$p || empty($p['vehicle_number']) || empty($p['loading_quantity']) || empty($p['loading_date'])) {
                    $skippedRows++;
                    $row->update(['validation_status' => 'skipped']);
                    continue;
                }

                // 1. Get or create vehicle
                $vehicle = Vehicle::firstOrCreate(
                    ['vehicle_number' => $p['vehicle_number']],
                    ['vehicle_type' => 'Dump Truck', 'status' => 'active']
                );

                // 2. Get or create goods
                $goods = Goods::firstOrCreate(
                    ['name' => $p['goods_name']],
                    ['unit' => 'MT', 'status' => 'active']
                );

                // 3. Generate sequential dispatch number
                $dispatchNumber = sprintf('DSP-%s-%04d', $year, $nextSeq++);

                // 4. Create dispatch record
                $dispatch = Dispatch::create([
                    'dispatch_number' => $dispatchNumber,
                    'client_id' => $defaultClient->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_name' => $p['driver_name'] ?? $vehicle->driver_name ?? null,
                    'goods_id' => $goods->id,
                    'loading_date' => $p['loading_date'],
                    'loading_quantity' => $p['loading_quantity'],
                    'destination_location' => $p['destination_location'] ?? 'P-Block',
                    'status' => 'pending',
                    'pending_quantity' => $p['loading_quantity'],
                    'total_received_quantity' => 0.000,
                    'total_deduction_quantity' => 0.000,
                    'note' => $p['note'] ?? null,
                    'import_batch_id' => $batch->id,
                    'created_by' => $batch->imported_by,
                ]);

                $insertedDispatches++;

                // 5. If receipt number and received quantity exist, attach receipt
                if (!empty($p['receipt_number']) && isset($p['received_quantity']) && $p['received_quantity'] > 0) {
                    Receipt::create([
                        'dispatch_id' => $dispatch->id,
                        'receipt_number' => $p['receipt_number'],
                        'receiving_date' => $p['receiving_date'] ?? $p['loading_date'],
                        'received_quantity' => $p['received_quantity'],
                        'deduction_quantity' => $p['deduction_quantity'] ?? 0.000,
                        'unloading_location' => $p['destination_location'] ?? 'P-Block',
                        'receipt_status' => ($p['deduction_quantity'] ?? 0) > 0 ? 'shortage' : 'received',
                        'remarks' => $p['note'] ?? 'Imported from spreadsheet',
                        'created_by' => $batch->imported_by,
                    ]);

                    $insertedReceipts++;
                }

                $row->update([
                    'validation_status' => 'imported',
                    'dispatch_id' => $dispatch->id,
                ]);
            }

            $batch->update([
                'status' => 'completed',
                'successful_rows' => $insertedDispatches,
                'skipped_rows' => $skippedRows,
            ]);

            ActivityLog::record(
                action: 'import.completed',
                module: 'import',
                description: "Import batch {$batch->batch_code} committed: {$insertedDispatches} dispatches, {$insertedReceipts} receipts.",
                record: $batch
            );
        });

        return [
            'dispatches' => $insertedDispatches,
            'receipts' => $insertedReceipts,
            'skipped' => $skippedRows,
        ];
    }

    protected function getVal(array $data, array $candidates): string
    {
        foreach ($candidates as $cand) {
            foreach ($data as $key => $val) {
                if (strcasecmp(trim($key), trim($cand)) === 0) {
                    return (string) $val;
                }
            }
        }
        return '';
    }
}
