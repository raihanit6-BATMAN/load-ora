<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Weighbridge Receipts</h1>
            <p class="text-xs text-slate-500 mt-1">Registry of all receiving challans, weight confirmation, and transit deductions.</p>
        </div>
        <div>
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Add Receipt
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search receipt #, vehicle plate, location..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
            </div>

            @if($selectedDispatchId)
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        Filtering by Dispatch #{{ $selectedDispatchId }}
                        <button type="button" wire:click="$set('selectedDispatchId', null)" class="ml-2 text-indigo-500 hover:text-indigo-800">
                            &times;
                        </button>
                    </span>
                </div>
            @endif

            <div>
                <button type="button" wire:click="$set('search', ''); $set('selectedDispatchId', null); $set('dateFrom', ''); $set('dateTo', '');"
                        class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1.5"></i>
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Receipt #</th>
                        <th class="py-3.5 px-4">Receiving Date</th>
                        <th class="py-3.5 px-4">Linked Dispatch</th>
                        <th class="py-3.5 px-4">Vehicle Plate</th>
                        <th class="py-3.5 px-4 text-right">Received (MT)</th>
                        <th class="py-3.5 px-4 text-right">Deduction (MT)</th>
                        <th class="py-3.5 px-4">Unloading Site</th>
                        <th class="py-3.5 px-4">Remarks</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($receipts as $r)
                        <tr class="hover:bg-slate-50/90 transition group">
                            <!-- Receipt # -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ str_starts_with($r->receipt_number, '#') ? $r->receipt_number : '#' . $r->receipt_number }}
                            </td>

                            <!-- Date -->
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $r->receiving_date?->format('d/m/Y') }}
                            </td>

                            <!-- Linked Dispatch -->
                            <td class="py-3 px-4 font-mono">
                                @if($r->dispatch)
                                    <a href="{{ route('dispatches.show', $r->dispatch_id) }}" class="text-indigo-600 font-semibold hover:underline">
                                        {{ $r->dispatch->dispatch_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">--</span>
                                @endif
                            </td>

                            <!-- Vehicle -->
                            <td class="py-3 px-4 font-mono font-medium text-slate-900 whitespace-nowrap">
                                {{ $r->dispatch->vehicle->vehicle_number ?? '--' }}
                            </td>

                            <!-- Received Qty -->
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 tabular-nums">
                                {{ number_format($r->received_quantity, 3) }}
                            </td>

                            <!-- Deduction Qty -->
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $r->deduction_quantity > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($r->deduction_quantity, 3) }}
                            </td>

                            <!-- Location -->
                            <td class="py-3 px-4">
                                {{ $r->unloading_location ?? 'P-Block' }}
                            </td>

                            <!-- Remarks -->
                            <td class="py-3 px-4 text-slate-500">
                                {{ $r->remarks ?? '--' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if(auth()->user()->isAdmin())
                                    <button type="button" wire:click="delete({{ $r->id }})"
                                            wire:confirm="Are you sure you want to delete receipt #{{ $r->receipt_number }}? The dispatch will recalculate automatically."
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Delete Receipt">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="file-check-2" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-medium text-slate-600">No weighbridge receipts found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Click "+ Add Receipt" to record a delivery confirmation</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $receipts->links() }}
        </div>
    </div>

    <!-- Create Receipt Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 font-heading">Record Weighbridge Receipt</h3>
                        <p class="text-xs text-slate-500">Record delivery confirmation for a dispatch trip.</p>
                    </div>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="store" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Target Dispatch</label>
                        <select wire:model.live="dispatch_id" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-600 font-mono">
                            <option value="">Select outstanding dispatch...</option>
                            @foreach($pendingDispatches as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->vehicle->vehicle_number ?? 'Truck' }} ({{ number_format($p->pending_quantity, 3) }} MT Pending)
                                </option>
                            @endforeach
                        </select>
                        @error('dispatch_id') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

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
                        <input type="text" wire:model="remarks" placeholder="Optional receiving comments"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-600">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showCreateModal', false)"
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
