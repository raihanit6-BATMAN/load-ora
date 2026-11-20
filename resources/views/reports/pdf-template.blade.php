<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LOADORA Operational Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .brand-sub {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-meta {
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
        .summary-boxes {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-cell {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: center;
        }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .summary-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">LOADORA</div>
                <div class="brand-sub">Logistics & Dispatch Management System</div>
            </td>
            <td class="report-meta">
                <div><strong>Period:</strong> {{ $filterInfo['from'] }} to {{ $filterInfo['to'] }}</div>
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Total Trips:</strong> {{ $records->count() }}</div>
            </td>
        </tr>
    </table>

    <!-- Aggregates -->
    <table class="summary-boxes">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Loaded</div>
                <div class="summary-val">{{ number_format($totalLoaded, 3) }} MT</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Received</div>
                <div class="summary-val" style="color: #059669;">{{ number_format($totalReceived, 3) }} MT</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Transit Deduction</div>
                <div class="summary-val" style="color: #e11d48;">{{ number_format($totalDeduction, 3) }} MT</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">In-Transit / Pending</div>
                <div class="summary-val" style="color: #d97706;">{{ number_format($totalPending, 3) }} MT</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th>Dispatch #</th>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Goods Material</th>
                <th class="text-right">Loaded (MT)</th>
                <th>Receipt #</th>
                <th>Recv Date</th>
                <th class="text-right">Received (MT)</th>
                <th class="text-right">Deduction (MT)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $d)
                @php $firstReceipt = $d->receipts->first(); @endphp
                <tr>
                    <td class="font-mono font-bold">{{ $d->dispatch_number }}</td>
                    <td>{{ $d->loading_date?->format('d/m/Y') }}</td>
                    <td class="font-mono">{{ $d->vehicle->vehicle_number ?? '--' }}</td>
                    <td>{{ $d->goods->name ?? '--' }}</td>
                    <td class="text-right font-bold">{{ number_format($d->loading_quantity, 3) }}</td>
                    <td class="font-mono">{{ $firstReceipt ? '#' . $firstReceipt->receipt_number : '--' }}</td>
                    <td>{{ $firstReceipt ? $firstReceipt->receiving_date?->format('d/m/Y') : '--' }}</td>
                    <td class="text-right font-bold" style="color: #059669;">
                        {{ $d->total_received_quantity > 0 ? number_format($d->total_received_quantity, 3) : '--' }}
                    </td>
                    <td class="text-right font-bold" style="color: {{ $d->total_deduction_quantity > 0 ? '#e11d48' : '#64748b' }};">
                        {{ number_format($d->total_deduction_quantity, 3) }}
                    </td>
                    <td class="text-center">
                        {{ strtoupper($d->status) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="4">TOTALS ({{ $records->count() }} Trips)</td>
                <td class="text-right">{{ number_format($totalLoaded, 3) }} MT</td>
                <td colspan="2"></td>
                <td class="text-right">{{ number_format($totalReceived, 3) }} MT</td>
                <td class="text-right">{{ number_format($totalDeduction, 3) }} MT</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Confidential &bull; LOADORA Internal Logistics Software &bull; Page 1 of 1
    </div>
</body>
</html>
