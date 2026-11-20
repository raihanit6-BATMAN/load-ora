<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                    Admin Only
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Google Sheets Migration Center</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Manual CSV/XLSX migration pipeline with staging, schema validation, and transactional commit.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="button" wire:click="importHistoricalExport"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-sm transition">
                <i data-lucide="database" class="w-4 h-4 mr-2 text-indigo-400"></i>
                Stage Historical Sheet (217 Records)
            </button>
        </div>
    </div>

    <!-- Upload Card & Batch Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 font-heading mb-1">Upload Spreadsheet</h3>
                <p class="text-xs text-slate-500 mb-4">Upload exported CSV or XLSX file from Google Sheets.</p>

                <form wire:submit.prevent="upload" class="space-y-4">
                    <div class="border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-xl p-6 text-center transition cursor-pointer relative bg-slate-50/50">
                        <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-indigo-500 mb-2"></i>
                        <input type="file" wire:model="spreadsheetFile" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <span class="text-xs font-semibold text-slate-700 block">Click to browse or drop file here</span>
                        <span class="text-[11px] text-slate-400">CSV, XLSX up to 20MB</span>
                    </div>

                    @if($spreadsheetFile)
                        <div class="p-3 rounded-xl bg-indigo-50 text-xs text-indigo-800 flex items-center justify-between">
                            <span class="truncate font-medium">{{ $spreadsheetFile->getClientOriginalName() }}</span>
                            <span class="font-mono text-[11px]">{{ round($spreadsheetFile->getSize() / 1024, 1) }} KB</span>
                        </div>
                    @endif

                    @error('spreadsheetFile') <span class="text-rose-600 text-xs block">{{ $message }}</span> @enderror

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition flex items-center justify-center">
                        <span wire:loading.remove>Parse & Stage Rows</span>
                        <span wire:loading>Analyzing Columns...</span>
                    </button>
                </form>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Safe staging: Uploaded files are not saved to live tables until confirmed.
            </div>
        </div>

        <!-- Current Batch Breakdown (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            @if($currentBatch)
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <span class="text-xs font-bold text-slate-400 font-mono">{{ $currentBatch->batch_code }}</span>
                            <h3 class="text-lg font-bold text-slate-900 font-heading mt-0.5">
                                {{ $currentBatch->file_name }}
                            </h3>
                        </div>
                        <div>
                            @if($currentBatch->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 mr-1.5"></i>
                                    Committed to MySQL
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Staged &bull; Ready for Review
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-4 gap-4 my-5 text-center">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Total Rows</span>
                            <span class="text-xl font-extrabold text-slate-900 font-heading tabular-nums">{{ $currentBatch->total_rows }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                            <span class="text-[11px] uppercase tracking-wider text-emerald-600 font-semibold block">Clean / Valid</span>
                            <span class="text-xl font-extrabold text-emerald-700 font-heading tabular-nums">
                                {{ $currentBatch->summary['valid'] ?? 0 }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
                            <span class="text-[11px] uppercase tracking-wider text-amber-600 font-semibold block">Warnings</span>
                            <span class="text-xl font-extrabold text-amber-700 font-heading tabular-nums">
                                {{ $currentBatch->summary['warning'] ?? 0 }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                            <span class="text-[11px] uppercase tracking-wider text-rose-600 font-semibold block">Blocked / Error</span>
                            <span class="text-xl font-extrabold text-rose-700 font-heading tabular-nums">
                                {{ $currentBatch->failed_rows }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">
                        Review the preview rows below. Rows with warnings (e.g. pending receipt) will be safely inserted. Error rows will be skipped.
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Imported by: <strong>{{ $currentBatch->user->name ?? 'Admin' }}</strong> &bull; {{ $currentBatch->created_at->format('d/m/Y H:i') }}
                    </span>
                    @if($currentBatch->status !== 'completed')
                        <button type="button" wire:click="commitCurrentBatch"
                                wire:confirm="Commit this batch into live dispatches and receipts tables?"
                                class="inline-flex items-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/30 transition">
                            <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                            Commit Valid Records to Database
                        </button>
                    @endif
                </div>
            @else
                <div class="py-16 text-center text-slate-400">
                    <i data-lucide="file-spreadsheet" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                    <p class="text-sm font-medium text-slate-600">No import batch selected</p>
                    <p class="text-xs text-slate-400 mt-1">Upload a spreadsheet file or click "Stage Historical Sheet" to begin migration.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Staged Rows Table -->
    @if($currentBatch)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 font-heading">Staged Records Inspection</h3>
                    <p class="text-xs text-slate-500">Preview of sanitized columns before database commit.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <select wire:model.live="rowStatusFilter" class="px-3 py-1.5 text-xs rounded-xl border border-slate-300">
                        <option value="">All Validation Statuses</option>
                        <option value="valid">Clean / Valid</option>
                        <option value="warning">Warnings</option>
                        <option value="error">Errors</option>
                        <option value="imported">Committed</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4">Row #</th>
                            <th class="py-3 px-4">Loading Date</th>
                            <th class="py-3 px-4">Vehicle</th>
                            <th class="py-3 px-4">Goods Material</th>
                            <th class="py-3 px-4 text-right">Loaded (MT)</th>
                            <th class="py-3 px-4 text-right">Received (MT)</th>
                            <th class="py-3 px-4 text-right">Deduction (MT)</th>
                            <th class="py-3 px-4">Receipt #</th>
                            <th class="py-3 px-4 text-center">Validation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($stagedRows as $row)
                            <tr class="hover:bg-slate-50/80 transition {{ $row->validation_status === 'error' ? 'bg-rose-50/30' : '' }}">
                                <td class="py-2.5 px-4 font-mono text-slate-400">
                                    {{ $row->row_index }}
                                </td>
                                <td class="py-2.5 px-4 font-mono">
                                    {{ $row->parsed_data['loading_date'] ?? '--' }}
                                </td>
                                <td class="py-2.5 px-4 font-mono font-medium text-slate-900">
                                    {{ $row->parsed_data['vehicle_number'] ?? ($row->raw_data['vehicle No'] ?? '--') }}
                                </td>
                                <td class="py-2.5 px-4 font-medium">
                                    {{ $row->parsed_data['goods_name'] ?? '--' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900 tabular-nums">
                                    {{ isset($row->parsed_data['loading_quantity']) ? number_format($row->parsed_data['loading_quantity'], 3) : '--' }}
                                </td>
                                <td class="py-2.5 px-4 text-right tabular-nums text-emerald-700 font-semibold">
                                    {{ isset($row->parsed_data['received_quantity']) && $row->parsed_data['received_quantity'] !== null ? number_format($row->parsed_data['received_quantity'], 3) : '--' }}
                                </td>
                                <td class="py-2.5 px-4 text-right tabular-nums {{ ($row->parsed_data['deduction_quantity'] ?? 0) > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">
                                    {{ isset($row->parsed_data['deduction_quantity']) ? number_format($row->parsed_data['deduction_quantity'], 3) : '0.000' }}
                                </td>
                                <td class="py-2.5 px-4 font-mono">
                                    {{ $row->parsed_data['receipt_number'] ? '#' . $row->parsed_data['receipt_number'] : '--' }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if($row->validation_status === 'valid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Valid
                                        </span>
                                    @elseif($row->validation_status === 'warning')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200" title="Missing receipt or outstanding delivery">
                                            Warning
                                        </span>
                                    @elseif($row->validation_status === 'imported')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Committed
                                        </span>
                                    @elseif($row->validation_status === 'error')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200" title="{{ implode(', ', $row->validation_errors ?? []) }}">
                                            Error
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                            {{ $row->validation_status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                    No rows found matching current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $stagedRows->links() }}
            </div>
        </div>
    @endif
</div>
