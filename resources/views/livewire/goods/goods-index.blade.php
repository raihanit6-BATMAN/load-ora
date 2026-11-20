<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Goods & Material Categories</h1>
            <p class="text-xs text-slate-500 mt-1">Manage aggregates, stone sizes, units, and cumulative material movements.</p>
        </div>
        @if(auth()->user()->isAdmin())
            <div>
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Material Grade
                </button>
            </div>
        @endif
    </div>

    <!-- Goods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($goodsList as $g)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:border-indigo-300 transition group">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-50 text-indigo-700">
                            {{ $g->code ?? 'NO-CODE' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $g->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $g->status }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 font-heading mt-3">
                        {{ $g->name }}
                    </h3>

                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                        {{ $g->description ?? 'No specific technical description entered.' }}
                    </p>

                    <!-- Metrics -->
                    <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 font-medium block">Total Dispatched</span>
                            <span class="text-slate-900 font-bold font-mono text-sm mt-0.5 block">
                                {{ number_format($g->total_loaded_tonnage ?? 0, 3) }} {{ $g->unit }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Total Received</span>
                            <span class="text-emerald-700 font-bold font-mono text-sm mt-0.5 block">
                                {{ number_format($g->total_received_tonnage ?? 0, 3) }} {{ $g->unit }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">{{ $g->dispatches_count }} dispatches</span>

                    @if(auth()->user()->isAdmin())
                        <div class="inline-flex items-center space-x-1">
                            <button type="button" wire:click="openEditModal({{ $g->id }})"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            @if($g->dispatches_count == 0)
                                <button type="button" wire:click="delete({{ $g->id }})"
                                        wire:confirm="Delete this material category?"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white p-12 text-center rounded-2xl border border-slate-200">
                <p class="text-sm font-medium text-slate-600">No goods or aggregate materials configured.</p>
            </div>
        @endforelse
    </div>

    <!-- Goods Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 font-heading">
                        {{ $editingGoodsId ? 'Edit Material Grade' : 'Add Material Grade' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Material Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. STONE 10-20mm" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-600">
                        @error('name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Code</label>
                            <input type="text" wire:model="code" placeholder="e.g. STN-1020"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Unit</label>
                            <input type="text" wire:model="unit" placeholder="MT" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Sieve sizing, density or usage notes..."
                                  class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                        <select wire:model="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                            Save Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
