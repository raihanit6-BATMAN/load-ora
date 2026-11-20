<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                    Admin Only
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">Staff & User Management</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Manage private internal operator accounts, reset credentials, and monitor staff activity.</p>
        </div>
        <div>
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                Create Operator Account
            </button>
        </div>
    </div>

    <!-- User Accounts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($users as $u)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:border-indigo-300 transition">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 font-extrabold flex items-center justify-center font-heading text-sm uppercase">
                                {{ substr($u->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $u->name }}</h3>
                                <span class="text-xs text-slate-400 font-mono">{{ $u->email }}</span>
                            </div>
                        </div>

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->isAdmin() ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                            {{ $u->role }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 my-5 text-xs">
                        <div>
                            <span class="text-slate-400 font-medium block">Account Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase mt-1 {{ $u->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $u->status }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Phone Contact</span>
                            <span class="text-slate-900 font-mono mt-1 block">{{ $u->phone ?? 'Not set' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Total Dispatches Created</span>
                            <span class="text-slate-900 font-bold mt-1 block">{{ $u->dispatches_count }} dispatches</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Receipts Recorded</span>
                            <span class="text-slate-900 font-bold mt-1 block">{{ $u->receipts_count }} receipts</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">
                        Last Active: {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
                    </span>

                    <div class="inline-flex items-center space-x-2">
                        <button type="button" wire:click="openEditModal({{ $u->id }})"
                                class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold transition">
                            Edit / Password
                        </button>

                        @if($u->id !== auth()->id())
                            <button type="button" wire:click="toggleStatus({{ $u->id }})"
                                    class="px-3 py-1.5 rounded-lg font-semibold transition {{ $u->status === 'active' ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                {{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- User Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 font-heading">
                        {{ $editingUserId ? 'Edit Operator Account' : 'Create Operator Account' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" wire:model="name" required placeholder="e.g. John Doe"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        @error('name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" wire:model="email" required placeholder="staff@loadora.test"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        @error('email') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            {{ $editingUserId ? 'Reset Password (leave blank to keep current)' : 'Password' }}
                        </label>
                        <input type="password" wire:model="password" placeholder="••••••••••••"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                        @error('password') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">System Role</label>
                            <select wire:model="role" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                <option value="staff">Staff (Operational)</option>
                                <option value="admin">Admin (Full Control)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Phone</label>
                        <input type="text" wire:model="phone" placeholder="+880..."
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                            Save Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
