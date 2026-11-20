<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Dispatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request);
        $records = $query->orderBy('loading_date', 'asc')->get();

        $filename = 'LOADORA_Dispatch_Report_' . date('Ymd_His') . '.csv';

        ActivityLog::record(
            action: 'report.exported_csv',
            module: 'reports',
            description: "Exported CSV report with {$records->count()} rows."
        );

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, [
                'Dispatch Number',
                'Loading Date',
                'Vehicle Plate',
                'Goods Material',
                'Loading Quantity (MT)',
                'Destination Site',
                'Receipt Number',
                'Receiving Date',
                'Received Quantity (MT)',
                'Deduction (MT)',
                'Pending (MT)',
                'Status',
                'Remarks',
            ]);

            foreach ($records as $d) {
                $latestReceipt = $d->receipts->first();
                fputcsv($handle, [
                    $d->dispatch_number,
                    $d->loading_date?->format('d/m/Y'),
                    $d->vehicle->vehicle_number ?? '',
                    $d->goods->name ?? '',
                    number_format($d->loading_quantity, 3, '.', ''),
                    $d->destination_location ?? 'P-Block',
                    $latestReceipt?->receipt_number ?? '',
                    $latestReceipt?->receiving_date?->format('d/m/Y') ?? '',
                    number_format($d->total_received_quantity, 3, '.', ''),
                    number_format($d->total_deduction_quantity, 3, '.', ''),
                    number_format($d->pending_quantity, 3, '.', ''),
                    strtoupper($d->status),
                    $d->note ?? '',
                ]);
            }

            // Summary Totals Row
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                '',
                number_format($records->sum('loading_quantity'), 3, '.', ''),
                '',
                '',
                '',
                number_format($records->sum('total_received_quantity'), 3, '.', ''),
                number_format($records->sum('total_deduction_quantity'), 3, '.', ''),
                number_format($records->sum('pending_quantity'), 3, '.', ''),
                '',
                '',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $records = $query->orderBy('loading_date', 'asc')->get();

        ActivityLog::record(
            action: 'report.exported_pdf',
            module: 'reports',
            description: "Generated PDF report with {$records->count()} rows."
        );

        $pdf = Pdf::loadView('reports.pdf-template', [
            'records' => $records,
            'totalLoaded' => $records->sum('loading_quantity'),
            'totalReceived' => $records->sum('total_received_quantity'),
            'totalDeduction' => $records->sum('total_deduction_quantity'),
            'totalPending' => $records->sum('pending_quantity'),
            'generatedAt' => now()->format('d/m/Y H:i'),
            'filterInfo' => [
                'from' => $request->query('date_from', 'Start'),
                'to' => $request->query('date_to', 'End'),
            ],
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('LOADORA_Report_' . date('Ymd_His') . '.pdf');
    }

    protected function buildFilteredQuery(Request $request)
    {
        $query = Dispatch::with(['vehicle', 'goods', 'client', 'receipts']);

        if ($request->query('date_from')) {
            $query->whereDate('loading_date', '>=', $request->query('date_from'));
        }

        if ($request->query('date_to')) {
            $query->whereDate('loading_date', '<=', $request->query('date_to'));
        }

        if ($request->query('vehicle_id')) {
            $query->where('vehicle_id', $request->query('vehicle_id'));
        }

        if ($request->query('goods_id')) {
            $query->where('goods_id', $request->query('goods_id'));
        }

        if ($request->query('report_type') === 'deductions') {
            $query->where('total_deduction_quantity', '>', 0);
        } elseif ($request->query('report_type') === 'pending') {
            $query->whereIn('status', ['pending', 'partial']);
        }

        return $query;
    }
}
