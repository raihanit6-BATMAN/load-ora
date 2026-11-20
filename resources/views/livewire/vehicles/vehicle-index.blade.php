<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Fleet & Vehicle Management</h1>
            <p class="text-xs text-slate-500 mt-1">Registry of transport trucks, assigned drivers, and total tonnage performance.</p>
        </div>
        @if(auth()->user()->isAdmin())
            <div>
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Register Vehicle
                </button>
            </div>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search vehicle plate number, driver, notes..."
                   class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        </div>
        <div>
            <select wire:model.live="typeFilter" class="px-3 py-2 text-xs rounded-xl border border-slate-300">
                <option value="">All Vehicle Types</option>
                <option value="Dump Truck">Dump Truck</option>
                <option value="Heavy Trailer">Heavy Trailer</option>
                <option value="Covered Van">Covered Van</option>
            </select>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Vehicle Number</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Driver & Contact</th>
                        <th class="py-3.5 px-4 text-center">Total Trips</th>
                        <th class="py-3.5 px-4 text-right">Tonnage Hauled (MT)</th>
                        <th class="py-3.5 px-4 text-right">Deductions (MT)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        @if(auth()->user()->isAdmin())
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($vehicles as $v)
                        <tr class="hover:bg-slate-50/90 transition group">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 text-sm">
                                {{ $v->vehicle_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    {{ $v->vehicle_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($v->driver_name)
                                    <span class="font-medium text-slate-900 block">{{ $v->driver_name }}</span>
                                    <span class="text-slate-400 text-[11px]">{{ $v->driver_phone ?? 'No phone' }}</span>
                                @else
                                    <span class="text-slate-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                    {{ $v->dispatches_count }} trips
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">
                                {{ number_format($v->total_loaded_tonnage ?? 0, 3) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ ($v->total_deduction_tonnage ?? 0) > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($v->total_deduction_tonnage ?? 0, 3) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $v->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $v->status }}
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <button type="button" wire:click="openEditModal({{ $v->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" wire:click="delete({{ $v->id }})"
                                                wire:confirm="Remove vehicle {{ $v->vehicle_number }}?"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-medium text-slate-600">No vehicles registered yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $vehicles->links() }}
        </div>
    </div>

    <!-- Vehicle Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 font-heading">
                        {{ $editingVehicleId ? 'Edit Vehicle' : 'Register Vehicle' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Vehicle License Plate</label>
                        <input type="text" wire:model="vehicle_number" placeholder="e.g. DMD 81-0493" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-600">
                        @error('vehicle_number') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Vehicle Type</label>
                            <select wire:model="vehicle_type" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                <option value="Dump Truck">Dump Truck</option>
                                <option value="Heavy Trailer">Heavy Trailer</option>
                                <option value="Covered Van">Covered Van</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Capacity (MT)</label>
                            <input type="number" step="0.1" wire:model="capacity_mt" placeholder="e.g. 55.0"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Driver Name</label>
                            <input type="text" wire:model="driver_name" placeholder="Full name"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Driver Mobile</label>
                            <input type="text" wire:model="driver_phone" placeholder="+880..."
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Ownership</label>
                            <select wire:model="owner_type" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                <option value="hired">Hired Truck</option>
                                <option value="company_owned">Company Owned</option>
                                <option value="contractor">Contractor</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                <option value="active">Active</option>
                                <option value="in_transit">In Transit</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                        <input type="text" wire:model="notes" placeholder="Optional notes"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                            Save Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
