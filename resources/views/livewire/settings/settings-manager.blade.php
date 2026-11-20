<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                    Admin Only
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-heading">System Settings</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Configure global logistics defaults, business parameters, and notification thresholds.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- General Identity -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 font-heading pb-2 border-b border-slate-100">
                Application Brand Identity
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Application Name</label>
                    <input type="text" wire:model="appName" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Company / Entity Name</label>
                    <input type="text" wire:model="companyName" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>
        </div>

        <!-- Logistics Defaults -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 font-heading pb-2 border-b border-slate-100">
                Dispatch & Measurement Parameters
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Default Unit of Weight</label>
                    <input type="text" wire:model="quantityUnit" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-600">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Standard: MT (Metric Ton)</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Dispatch Code Prefix</label>
                    <input type="text" wire:model="dispatchPrefix" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-600">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">e.g. DSP &rarr; DSP-2026-0001</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Overdue Receipt Alert (Hours)</label>
                    <input type="number" wire:model="alertPendingHours" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-600">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Flags pending trips on dashboard</span>
                </div>
            </div>
        </div>

        <!-- Localization & Display -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 font-heading pb-2 border-b border-slate-100">
                Localization & Presentation
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Display Date Format</label>
                    <select wire:model="dateFormat" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        <option value="d/m/Y">DD/MM/YYYY (e.g. 01/10/2026)</option>
                        <option value="Y-m-d">YYYY-MM-DD (e.g. 2026-10-01)</option>
                        <option value="m/d/Y">MM/DD/YYYY (e.g. 10/01/2026)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Visual Theme</label>
                    <select wire:model="theme" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        <option value="light">Modern Light Theme (Recommended)</option>
                        <option value="dark">Dark Theme (Optional)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                Save System Settings
            </button>
        </div>
    </form>
</div>
