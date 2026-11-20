<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                    Admin Only
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Security & Activity Audit Trail</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Immutable historical record of every login, dispatch mutation, weighbridge receipt, and export.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search action, description, IP..."
                   class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        </div>

        <div>
            <select wire:model.live="moduleFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                <option value="">All Modules</option>
                <option value="auth">Authentication</option>
                <option value="dispatch">Dispatch Management</option>
                <option value="receipt">Receipt Management</option>
                <option value="vehicle">Fleet Management</option>
                <option value="goods">Materials</option>
                <option value="import">Import Center</option>
                <option value="reports">Reports & Exports</option>
            </select>
        </div>

        <div>
            <select wire:model.live="userFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                <option value="">All Personnel</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Timestamp</th>
                        <th class="py-3.5 px-4">Operator</th>
                        <th class="py-3.5 px-4">Module</th>
                        <th class="py-3.5 px-4">Action</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-2.5 px-4 font-medium text-slate-900 whitespace-nowrap">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium text-[11px] text-slate-700">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 font-mono font-semibold text-indigo-700">
                                {{ $log->action }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-800">
                                {{ $log->description }}
                            </td>
                            <td class="py-2.5 px-4 font-mono text-slate-400 text-[11px]">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No activity log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $logs->links() }}
        </div>
    </div>
</div>
