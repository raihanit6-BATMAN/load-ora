<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Business Parties & Sites</h1>
            <p class="text-xs text-slate-500 mt-1">Manage contracting parties, project delivery sites, and commercial partnerships.</p>
        </div>
        @if(auth()->user()->isAdmin())
            <div>
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Business Client
                </button>
            </div>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search party name, company, site location..."
                   class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Party Name</th>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Contact Person</th>
                        <th class="py-3.5 px-4">Delivery Site</th>
                        <th class="py-3.5 px-4 text-center">Dispatches</th>
                        <th class="py-3.5 px-4 text-right">Total Tonnage (MT)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        @if(auth()->user()->isAdmin())
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($clients as $c)
                        <tr class="hover:bg-slate-50/90 transition group">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $c->name }}
                                @if($c->company_name)
                                    <span class="block text-[11px] font-normal text-slate-400">{{ $c->company_name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-600">
                                {{ $c->code ?? '--' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-900 block font-medium">{{ $c->contact_person ?? '--' }}</span>
                                <span class="text-slate-400 text-[11px]">{{ $c->phone ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">
                                {{ $c->delivery_location ?? '--' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $c->dispatches_count }} trips
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">
                                {{ number_format($c->total_tonnage ?? 0, 3) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <button type="button" wire:click="openEditModal({{ $c->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        @if($c->dispatches_count == 0)
                                            <button type="button" wire:click="delete({{ $c->id }})"
                                                    wire:confirm="Remove client {{ $c->name }}?"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-medium text-slate-600">No client parties registered</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- Client Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 font-heading">
                        {{ $editingClientId ? 'Edit Client Party' : 'Add Client Party' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Party Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. P-Block Project" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-600">
                        @error('name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Code</label>
                            <input type="text" wire:model="code" placeholder="e.g. P-BLK"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Delivery Site</label>
                            <input type="text" wire:model="delivery_location" placeholder="e.g. P-Block Site"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Person</label>
                            <input type="text" wire:model="contact_person" placeholder="Manager name"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone</label>
                            <input type="text" wire:model="phone" placeholder="+880..."
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
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
                            Save Party
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
