<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Reports & Analytics Center</h1>
            <p class="text-xs text-slate-500 mt-1">Generate filtered business reports, track deductions, and export to CSV or PDF.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.export.csv', ['report_type' => $reportType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'vehicle_id' => $vehicleId, 'goods_id' => $goodsId]) }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-sm transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2 text-emerald-600"></i>
                Export CSV / Excel
            </a>

            <a href="{{ route('reports.export.pdf', ['report_type' => $reportType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'vehicle_id' => $vehicleId, 'goods_id' => $goodsId]) }}"
               target="_blank"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm shadow-indigo-600/30 transition">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                Generate PDF
            </a>
        </div>
    </div>

    <!-- Filter & Configuration Panel -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Report Type -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Report Category</label>
                <select wire:model.live="reportType" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600 font-medium">
                    <option value="dispatch_summary">Full Dispatch Summary</option>
                    <option value="deductions">Transit Deduction & Losses</option>
                    <option value="pending">Pending Receipts</option>
                </select>
            </div>

            <!-- Date Preset -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Date Preset</label>
                <select wire:model.live="datePreset" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_week">This Week</option>
                    <option value="today">Today</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Vehicle Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Vehicle</label>
                <select wire:model.live="vehicleId" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->vehicle_number }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Material Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Goods Material</label>
                <select wire:model.live="goodsId" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    <option value="">All Materials</option>
                    @foreach($goodsList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Custom Date Inputs -->
            <div class="flex items-center space-x-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-300">
                </div>
                <div class="flex-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To</label>
                    <input type="date" wire:model.live="dateTo" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-300">
                </div>
            </div>
        </div>
    </div>

    <!-- Live Aggregates Preview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Loaded Tonnage</span>
            <span class="text-xl font-extrabold text-slate-900 font-heading tabular-nums mt-1 block">
                {{ number_format($totalLoaded, 3) }} MT
            </span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Received Tonnage</span>
            <span class="text-xl font-extrabold text-emerald-700 font-heading tabular-nums mt-1 block">
                {{ number_format($totalReceived, 3) }} MT
            </span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Transit Deductions</span>
            <span class="text-xl font-extrabold text-rose-600 font-heading tabular-nums mt-1 block">
                {{ number_format($totalDeduction, 3) }} MT
            </span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Pending In-Transit</span>
            <span class="text-xl font-extrabold text-amber-600 font-heading tabular-nums mt-1 block">
                {{ number_format($totalPending, 3) }} MT
            </span>
        </div>
    </div>

    <!-- Report Table Preview -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 font-heading">Report Preview ({{ $records->count() }} Records)</h3>
            <span class="text-xs text-slate-400">All calculations rendered live from MySQL</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Vehicle</th>
                        <th class="py-3 px-4">Goods</th>
                        <th class="py-3 px-4 text-right">Loaded (MT)</th>
                        <th class="py-3 px-4 text-right">Received (MT)</th>
                        <th class="py-3 px-4 text-right">Deduction (MT)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($records as $d)
                        @php $firstR = $d->receipts->first(); @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-900">
                                {{ $firstR ? (str_starts_with($firstR->receipt_number, '#') ? $firstR->receipt_number : '#' . $firstR->receipt_number) : '--' }}
                            </td>
                            <td class="py-2.5 px-4">{{ $d->loading_date?->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-4 font-mono font-semibold text-slate-800">
                                {{ $d->vehicle->vehicle_number ?? '--' }}
                            </td>
                            <td class="py-2.5 px-4">{{ $d->goods->name ?? '--' }}</td>
                            <td class="py-2.5 px-4 text-right font-bold text-slate-900 tabular-nums">
                                {{ number_format($d->loading_quantity, 3) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-emerald-700 tabular-nums">
                                {{ $d->total_received_quantity > 0 ? number_format($d->total_received_quantity, 3) : '--' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold tabular-nums {{ $d->total_deduction_quantity > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($d->total_deduction_quantity, 3) }}
                            </td>
                            <td class="py-2.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $d->status === 'received' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $d->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                No records found for the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
