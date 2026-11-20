<div class="space-y-6">
    <!-- Header with 3 Compact KPI Mini-Cards and Action -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div class="shrink-0">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Dispatch Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Record, monitor, and reconcile material dispatches with weighbridge receipts.</p>
        </div>

        <!-- 3 Compact KPI Mini-Cards in the Header Gap -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1 max-w-2xl xl:mx-6">
            <!-- Mini-Card 1: Total Dispatches -->
            <div class="bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3 transition hover:border-slate-300">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Dispatches</span>
                    <span class="text-base font-bold text-slate-900 font-heading leading-tight">{{ number_format($totalDispatchesCount) }}</span>
                </div>
            </div>

            <!-- Mini-Card 2: Received Dispatches -->
            <div class="bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3 transition hover:border-emerald-200">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Received</span>
                    <span class="text-base font-bold text-emerald-600 font-heading leading-tight">{{ number_format($receivedDispatchesCount) }}</span>
                </div>
            </div>

            <!-- Mini-Card 3: Pending / Partial Dispatches -->
            <div class="bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3 transition hover:border-amber-200">
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pending / Partial</span>
                    <span class="text-base font-bold text-amber-600 font-heading leading-tight">{{ number_format($pendingDispatchesCount) }}</span>
                </div>
            </div>
        </div>

        <div class="shrink-0 flex items-center">
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm shadow-indigo-600/30 transition">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create New Dispatch
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Box -->
            <div class="lg:col-span-2 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search receipt #, vehicle plate, goods, location..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending Receipt</option>
                    <option value="partial">Partially Received</option>
                    <option value="received">Fully Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Goods Filter -->
            <div>
                <select wire:model.live="goodsFilter"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Materials</option>
                    @foreach($goodsList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Clear Filters -->
            <div>
                <button type="button" wire:click="$set('search', ''); $set('statusFilter', ''); $set('goodsFilter', ''); $set('dateFrom', ''); $set('dateTo', '');"
                        class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1.5"></i>
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-3.5 whitespace-nowrap">L-DATE</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">VEHICLE NO</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">DRIVER NAME</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">GOODS</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">LOCATION</th>
                        <th class="py-3 px-3.5 text-right whitespace-nowrap">L-QTY (MT)</th>
                        <th class="py-3 px-3.5 text-center whitespace-nowrap">RECEIPT #</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">R-DATE</th>
                        <th class="py-3 px-3.5 text-right whitespace-nowrap">R-QTY (MT)</th>
                        <th class="py-3 px-3.5 text-right whitespace-nowrap">DEDUCTION (MT)</th>
                        <th class="py-3 px-3.5 text-center whitespace-nowrap">STATUS</th>
                        <th class="py-3 px-3.5 text-right whitespace-nowrap">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($dispatches as $d)
                        <tr class="hover:bg-slate-50/90 transition group">
                            <!-- 1. L-DATE -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <a href="{{ route('dispatches.show', $d->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center">
                                    {{ $d->loading_date?->format('d/m/Y') ?? '-' }}
                                </a>
                                <span class="sr-only">{{ $d->dispatch_number }}</span>
                            </td>

                            <!-- 2. VEHICLE NO -->
                            <td class="py-3 px-3.5 whitespace-nowrap font-medium text-slate-900">
                                <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[11px] font-semibold text-slate-800 border border-slate-200/60">
                                    {{ $d->vehicle->vehicle_number ?? '-' }}
                                </span>
                            </td>

                            <!-- 3. DRIVER NAME -->
                            <td class="py-3 px-3.5 whitespace-nowrap text-slate-700">
                                {{ $d->driver_name ?: ($d->vehicle->driver_name ?: '-') }}
                            </td>

                            <!-- 4. GOODS -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="font-medium text-slate-800">
                                    {{ $d->goods->name ?? '-' }}
                                </span>
                            </td>

                            <!-- 5. LOCATION -->
                            <td class="py-3 px-3.5 whitespace-nowrap text-slate-600">
                                {{ $d->destination_location ?: ($d->loading_point ?: '-') }}
                            </td>

                            <!-- 6. L-QTY (MT) -->
                            <td class="py-3 px-3.5 text-right font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                {{ number_format($d->loading_quantity, 3) }}
                            </td>

                            <!-- 7. RECEIPT # -->
                            <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                @if($d->receipts->count() > 0)
                                    <div class="inline-flex flex-wrap items-center justify-center gap-1">
                                        @foreach($d->receipts as $r)
                                            <a href="{{ route('receipts.index', ['dispatch_id' => $d->id]) }}"
                                               class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 border border-indigo-200/80 text-[11px] font-bold font-mono text-indigo-700 hover:bg-indigo-100 transition"
                                               title="Date: {{ $r->receiving_date?->format('d/m/Y') }} • Qty: {{ number_format($r->received_quantity, 3) }} MT">
                                                {{ $r->receipt_number }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 font-medium">-</span>
                                @endif
                            </td>

                            <!-- 8. R-DATE -->
                            <td class="py-3 px-3.5 text-slate-600 whitespace-nowrap">
                                @if($d->receipts->count() > 0)
                                    {{ $d->receipts->pluck('receiving_date')->map(fn($dt) => $dt?->format('d/m/Y'))->filter()->implode(', ') ?: '-' }}
                                @else
                                    <span class="text-slate-400 font-medium">-</span>
                                @endif
                            </td>

                            <!-- 9. R-QTY (MT) -->
                            <td class="py-3 px-3.5 text-right tabular-nums whitespace-nowrap {{ $d->total_received_quantity > 0 ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                                {{ $d->total_received_quantity > 0 ? number_format($d->total_received_quantity, 3) : '-' }}
                            </td>

                            <!-- 10. DEDUCTION (MT) -->
                            <td class="py-3 px-3.5 text-right tabular-nums whitespace-nowrap {{ $d->total_deduction_quantity > 0 ? 'text-rose-600 font-bold' : ($d->status === 'pending' ? 'text-slate-400' : 'text-slate-500 font-medium') }}">
                                {{ $d->status === 'pending' ? '-' : number_format($d->total_deduction_quantity, 3) }}
                            </td>

                            <!-- 11. STATUS -->
                            <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                @if($d->status === 'received')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Received
                                    </span>
                                @elseif($d->status === 'partial')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Partial
                                    </span>
                                @elseif($d->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- 12. ACTION -->
                            <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    <a href="{{ route('dispatches.show', $d->id) }}"
                                       title="View Details & Reconciliation"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    @if($d->status !== 'received')
                                        <a href="{{ route('receipts.index', ['dispatch_id' => $d->id, 'create' => 'true']) }}"
                                           title="Add Weighbridge Receipt"
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                            <i data-lucide="file-plus" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    <button type="button" wire:click="openEditModal({{ $d->id }})"
                                            title="Edit Dispatch"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    @if(auth()->user()->isAdmin())
                                        <button type="button" wire:click="delete({{ $d->id }})"
                                                wire:confirm="Are you sure you want to delete dispatch {{ $d->dispatch_number }}?"
                                                title="Delete Record"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-medium text-slate-600">No dispatch records found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Try adjusting your filters or click "+ Create New Dispatch"</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $dispatches->links() }}
        </div>
    </div>

    <!-- Create Dispatch Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 font-heading">New Material Dispatch</h3>
                        <p class="text-xs text-slate-500">Record an outgoing transport trip and payload.</p>
                    </div>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="store" class="mt-4 space-y-4">
                    <!-- Loading Date & Goods Material -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Loading Date</label>
                            <input type="date" wire:model="loading_date" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                            @error('loading_date') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Goods Material</label>
                            <select wire:model="goods_id" required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600 font-medium">
                                <option value="">Select material...</option>
                                @foreach($goodsList as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->unit }})</option>
                                @endforeach
                            </select>
                            @error('goods_id') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Vehicle & Driver Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Transport Vehicle <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-xl shadow-sm border border-slate-300 focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600 overflow-hidden bg-white">
                                <!-- Left side (Small Dropdown): Vehicle prefix selector -->
                                <select wire:model.live="vehicle_prefix"
                                        class="w-24 px-2.5 py-2 text-xs font-bold font-mono text-slate-800 bg-slate-50 border-0 border-r border-slate-200 focus:ring-0 focus:outline-none cursor-pointer">
                                    <option value="DMT">DMT</option>
                                    <option value="BMT">BMT</option>
                                    <option value="BGT">BGT</option>
                                    <option value="CMT">CMT</option>
                                    <option value="KMT">KMT</option>
                                    <option value="JMT">JMT</option>
                                    <option value="SMT">SMT</option>
                                    <option value="Other">Other</option>
                                </select>
                                <!-- Right side (Manual Text Input): Truck number -->
                                <input type="text"
                                       wire:model.live.debounce.300ms="vehicle_number_input"
                                       placeholder="{{ $vehicle_prefix === 'Other' ? 'e.g. 11-1234' : '11-1234' }}"
                                       autocomplete="off"
                                       required
                                       class="flex-1 min-w-0 px-3 py-2 text-xs font-mono font-semibold text-slate-900 border-0 focus:ring-0 focus:outline-none uppercase placeholder:normal-case placeholder:font-sans placeholder:font-normal placeholder:text-slate-400">
                            </div>
                            @error('vehicle_number_input') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                            @error('vehicle_id') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Driver Name</label>
                            <input type="text" wire:model="driver_name" placeholder="Driver name..."
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                            @error('driver_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Loading Quantity & Destination -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Loaded Quantity (MT)</label>
                            <input type="number" step="0.001" wire:model="loading_quantity" placeholder="0.000" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold font-mono focus:ring-2 focus:ring-indigo-600">
                            @error('loading_quantity') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Destination Location</label>
                            <input type="text" wire:model="destination_location" placeholder="e.g. P-Block"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                            @error('destination_location') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Remarks / Notes</label>
                        <input type="text" wire:model="note" placeholder="e.g. outdoor, truck changed..."
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition">
                            Save Dispatch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Dispatch Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 font-heading">Edit Material Dispatch</h3>
                        <p class="text-xs text-slate-500">Update dispatch payload and assignment.</p>
                    </div>
                    <button type="button" wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="update" class="mt-4 space-y-4">
                    <!-- Loading Date & Goods Material -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Loading Date</label>
                            <input type="date" wire:model="loading_date" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Goods Material</label>
                            <select wire:model="goods_id" required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600 font-medium">
                                @foreach($goodsList as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Vehicle & Driver Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Transport Vehicle <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-xl shadow-sm border border-slate-300 focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600 overflow-hidden bg-white">
                                <!-- Left side (Small Dropdown): Vehicle prefix selector -->
                                <select wire:model.live="vehicle_prefix"
                                        class="w-24 px-2.5 py-2 text-xs font-bold font-mono text-slate-800 bg-slate-50 border-0 border-r border-slate-200 focus:ring-0 focus:outline-none cursor-pointer">
                                    <option value="DMT">DMT</option>
                                    <option value="BMT">BMT</option>
                                    <option value="BGT">BGT</option>
                                    <option value="CMT">CMT</option>
                                    <option value="KMT">KMT</option>
                                    <option value="JMT">JMT</option>
                                    <option value="SMT">SMT</option>
                                    <option value="Other">Other</option>
                                </select>
                                <!-- Right side (Manual Text Input): Truck number -->
                                <input type="text"
                                       wire:model.live.debounce.300ms="vehicle_number_input"
                                       placeholder="{{ $vehicle_prefix === 'Other' ? 'e.g. 11-1234' : '11-1234' }}"
                                       autocomplete="off"
                                       required
                                       class="flex-1 min-w-0 px-3 py-2 text-xs font-mono font-semibold text-slate-900 border-0 focus:ring-0 focus:outline-none uppercase placeholder:normal-case placeholder:font-sans placeholder:font-normal placeholder:text-slate-400">
                            </div>
                            @error('vehicle_number_input') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                            @error('vehicle_id') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Driver Name</label>
                            <input type="text" wire:model="driver_name" placeholder="Driver name..."
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                            @error('driver_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Loading Quantity & Destination -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Loaded Quantity (MT)</label>
                            <input type="number" step="0.001" wire:model="loading_quantity" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold font-mono focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Destination Location</label>
                            <input type="text" wire:model="destination_location"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Remarks / Notes</label>
                        <input type="text" wire:model="note"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showEditModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
