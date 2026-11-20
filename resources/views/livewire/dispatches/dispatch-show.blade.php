<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs text-slate-500">
            <a href="{{ route('dispatches.index') }}" class="hover:text-indigo-600 transition flex items-center">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i>
                All Dispatches
            </a>
            <span>/</span>
            <span class="text-slate-800 font-semibold font-mono">{{ $dispatch->dispatch_number }}</span>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button" wire:click="openAddReceiptModal"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                Add Weighbridge Receipt
            </button>
        </div>
    </div>

    <!-- Overview Card Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Dispatch Metadata (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dispatch Details</span>
                    <h2 class="text-xl font-extrabold text-slate-900 font-heading font-mono mt-0.5">
                        {{ $dispatch->dispatch_number }}
                    </h2>
                </div>
                <div>
                    @if($dispatch->status === 'received')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Completed &bull; Received
                        </span>
                    @elseif($dispatch->status === 'partial')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Partial Delivery
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                            Pending Receipt
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 text-xs">
                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Loading Date</span>
                    <span class="text-slate-900 font-bold text-sm mt-1 block">
                        {{ $dispatch->loading_date?->format('d/m/Y') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Vehicle Number</span>
                    <span class="text-slate-900 font-mono font-bold text-sm mt-1 block">
                        {{ $dispatch->vehicle->vehicle_number ?? '--' }}
                    </span>
                    <span class="text-slate-400 text-[11px]">{{ $dispatch->vehicle->vehicle_type ?? '' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Material Grade</span>
                    <span class="text-slate-900 font-bold text-sm mt-1 block">
                        {{ $dispatch->goods->name ?? '--' }}
                    </span>
                    <span class="text-slate-400 text-[11px]">Unit: {{ $dispatch->goods->unit ?? 'MT' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Client / Party</span>
                    <span class="text-slate-900 font-bold text-sm mt-1 block">
                        {{ $dispatch->client->name ?? 'Direct' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Delivery Location</span>
                    <span class="text-slate-900 font-bold text-sm mt-1 block">
                        {{ $dispatch->destination_location ?? 'P-Block' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 uppercase tracking-wider font-semibold block">Recorded By</span>
                    <span class="text-slate-900 font-medium text-sm mt-1 block">
                        {{ $dispatch->creator->name ?? 'System' }}
                    </span>
                </div>
            </div>

            @if($dispatch->note)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <span class="font-bold text-slate-700">Remarks / Operational Note:</span>
                    <span class="text-slate-600 ml-1">{{ $dispatch->note }}</span>
                </div>
            @endif
        </div>

        <!-- Right: Tonnage Reconciliation Summary (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 font-heading uppercase tracking-wider pb-3 border-b border-slate-100">
                    Tonnage Reconciliation
                </h3>

                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Loaded Weight:</span>
                        <span class="text-base font-extrabold text-slate-900 font-mono tabular-nums">
                            {{ number_format($dispatch->loading_quantity, 3) }} MT
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Total Received:</span>
                        <span class="text-base font-extrabold text-emerald-700 font-mono tabular-nums">
                            {{ number_format($dispatch->total_received_quantity, 3) }} MT
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Transit Deduction:</span>
                        <span class="text-base font-extrabold text-rose-600 font-mono tabular-nums">
                            {{ number_format($dispatch->total_deduction_quantity, 3) }} MT
                        </span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">Pending In-Transit:</span>
                        <span class="text-base font-extrabold text-amber-600 font-mono tabular-nums">
                            {{ number_format($dispatch->pending_quantity, 3) }} MT
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                Created: {{ $dispatch->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Receipts Registered Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 font-heading">Weighbridge Receipts</h3>
                <p class="text-xs text-slate-500">Unloading challans and receiving verification records.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                {{ $dispatch->receipts->count() }} Entry/Entries
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Receiving Date</th>
                        <th class="py-3 px-4">Unloading Site</th>
                        <th class="py-3 px-4 text-right">Received (MT)</th>
                        <th class="py-3 px-4 text-right">Deduction (MT)</th>
                        <th class="py-3 px-4">Remarks</th>
                        <th class="py-3 px-4">Entered By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($dispatch->receipts as $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                {{ str_starts_with($r->receipt_number, '#') ? $r->receipt_number : '#' . $r->receipt_number }}
                            </td>
                            <td class="py-3 px-4">{{ $r->receiving_date?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">{{ $r->unloading_location ?? 'P-Block' }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 tabular-nums">
                                {{ number_format($r->received_quantity, 3) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $r->deduction_quantity > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($r->deduction_quantity, 3) }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $r->remarks ?? '--' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $r->creator->name ?? 'Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                No receipts recorded yet for this dispatch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Receipt Modal -->
    @if($showReceiptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 font-heading">Add Receipt</h3>
                        <p class="text-xs text-slate-500">Record weighbridge receiving data.</p>
                    </div>
                    <button type="button" wire:click="$set('showReceiptModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveReceipt" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Receipt #</label>
                            <input type="text" wire:model="receipt_number" placeholder="e.g. #1" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-emerald-600">
                            @error('receipt_number') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Receiving Date</label>
                            <input type="date" wire:model="receiving_date" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-600">
                            @error('receiving_date') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Received Quantity (MT)</label>
                        <input type="number" step="0.001" wire:model="received_quantity" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold font-mono text-emerald-700 focus:ring-2 focus:ring-emerald-600">
                        @error('received_quantity') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Unloading Site</label>
                        <input type="text" wire:model="unloading_location" placeholder="e.g. P-Block"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Remarks</label>
                        <input type="text" wire:model="remarks" placeholder="Optional notes from weighbridge"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-600">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showReceiptModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                            Save Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
