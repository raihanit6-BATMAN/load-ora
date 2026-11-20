@php
    $headerAlerts = [];
    if (auth()->check()) {
        $headerAlerts = cache()->remember('user_alerts_' . auth()->id(), 10, function () {
            $list = [];
            // Pending dispatches needing receipts
            $pending = \App\Models\Dispatch::with('vehicle')
                ->whereIn('status', ['pending', 'partial'])
                ->orderBy('loading_date', 'desc')
                ->limit(4)
                ->get();

            foreach ($pending as $p) {
                $list[] = [
                    'id' => 'p-' . $p->id,
                    'title' => 'Pending Receipt',
                    'message' => ($p->vehicle->vehicle_number ?? 'Truck') . ' (' . number_format($p->pending_quantity, 2) . ' MT pending)',
                    'time' => $p->loading_date ? $p->loading_date->diffForHumans() : 'Recently',
                    'url' => route('receipts.index', ['dispatch_id' => $p->id]),
                    'type' => 'pending',
                ];
            }

            // Transit deduction variance alerts
            $deductions = \App\Models\Dispatch::with(['receipts', 'vehicle'])
                ->where('total_deduction_quantity', '>', 0.2)
                ->orderBy('id', 'desc')
                ->limit(2)
                ->get();

            foreach ($deductions as $d) {
                $rNum = $d->receipts->first()?->receipt_number;
                $label = $rNum ? "Receipt {$rNum}" : ($d->vehicle->vehicle_number ?? 'Truck');
                $list[] = [
                    'id' => 'd-' . $d->id,
                    'title' => 'Transit Variance',
                    'message' => "{$label} recorded " . number_format($d->total_deduction_quantity, 2) . ' MT deduction',
                    'time' => $d->updated_at ? $d->updated_at->diffForHumans() : 'Recently',
                    'url' => route('dispatches.show', $d->id),
                    'type' => 'warning',
                ];
            }

            return $list;
        });
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#111111]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'LOADORA') }} — Logistics & Dispatch Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Seamless Flush Concave Curves for Active Navigation Item merging into #F4F5F7 */
        .nav-item-active {
            position: relative;
            display: flex;
            align-items: center;
            background-color: #F4F5F7;
            color: #111111;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.75rem 1rem 0.75rem 1.25rem;
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
            margin-right: 0;
            z-index: 20;
            transition: all 0.15s ease;
        }
        .nav-item-active i {
            color: #111111 !important;
        }
        .nav-item-active::before {
            content: "";
            position: absolute;
            top: -24px;
            right: 0;
            width: 24px;
            height: 24px;
            background: radial-gradient(circle at 0 0, transparent 24px, #F4F5F7 24.5px);
            pointer-events: none;
            z-index: 20;
        }
        .nav-item-active::after {
            content: "";
            position: absolute;
            bottom: -24px;
            right: 0;
            width: 24px;
            height: 24px;
            background: radial-gradient(circle at 0 100%, transparent 24px, #F4F5F7 24.5px);
            pointer-events: none;
            z-index: 20;
        }

        .nav-item-inactive {
            display: flex;
            align-items: center;
            color: #94A3B8;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.75rem 1rem 0.75rem 1.25rem;
            border-radius: 9999px;
            margin-right: 1rem;
            transition: all 0.15s ease;
        }
        .nav-item-inactive:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.06);
        }
        .nav-item-inactive i {
            color: #94A3B8;
        }
        .nav-item-inactive:hover i {
            color: #FFFFFF;
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-[#111111] text-slate-900" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen bg-[#111111] flex flex-col lg:flex-row antialiased">
        
        <!-- Mobile Sidebar Drawer Overlay -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" style="display: none;">
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-black/70 backdrop-blur-sm"></div>

            <div class="fixed inset-0 flex z-50">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex-1 flex flex-col max-w-xs w-full bg-[#111111] text-slate-300 border-r border-white/10 shadow-2xl">
                    
                    <!-- Close button -->
                    <div class="absolute top-0 right-0 -mr-12 pt-4">
                        <button type="button" @click="sidebarOpen = false"
                                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full text-white focus:outline-none">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <!-- Mobile Brand Logo -->
                    <div class="h-20 flex items-center px-6 border-b border-white/10">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-teal-400 p-0.5 shadow-sm">
                                <div class="w-full h-full bg-[#111111] rounded-[10px] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m21.12 6.4-6.05-4.06a2 2 0 0 0-2.17-.05L2.95 8.41a2 2 0 0 0-.95 1.7v5.78a2 2 0 0 0 .95 1.7l9.95 6.12a2 2 0 0 0 2.17-.05l6.05-4.06A2 2 0 0 0 22 17.89V8.1a2 2 0 0 0-.88-1.7Z"/>
                                        <path d="m10 22 12-7.89"/>
                                        <path d="m2 9.89 10 6.11"/>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-lg font-extrabold tracking-wide text-white font-heading">LOADORA</span>
                        </a>
                    </div>

                    <!-- Mobile Nav -->
                    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 mr-3"></i> Dashboard
                        </a>
                        <a href="{{ route('dispatches.index') }}" class="{{ request()->routeIs('dispatches.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="truck" class="w-4 h-4 mr-3"></i> Dispatches
                        </a>
                        <a href="{{ route('receipts.index') }}" class="{{ request()->routeIs('receipts.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="file-check-2" class="w-4 h-4 mr-3"></i> Receipts
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="shield-check" class="w-4 h-4 mr-3"></i> Vehicles
                        </a>
                        <a href="{{ route('goods.index') }}" class="{{ request()->routeIs('goods.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="package" class="w-4 h-4 mr-3"></i> Goods & Materials
                        </a>
                        <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="users" class="w-4 h-4 mr-3"></i> Clients / Parties
                        </a>
                        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-3"></i> Reports & Export
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('import.index') }}" class="{{ request()->routeIs('import.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                                <i data-lucide="file-up" class="w-4 h-4 mr-3"></i> Import Center
                            </a>
                            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                                <i data-lucide="user-cog" class="w-4 h-4 mr-3"></i> Staff
                            </a>
                            <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                                <i data-lucide="activity" class="w-4 h-4 mr-3"></i> Activity Logs
                            </a>
                            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'bg-[#F4F5F7] text-[#111111] font-semibold rounded-xl flex items-center px-4 py-3 text-sm' : 'text-slate-400 hover:text-white rounded-xl flex items-center px-4 py-3 text-sm' }}">
                                <i data-lucide="settings" class="w-4 h-4 mr-3"></i> Settings
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Curved Black Sidebar (#111111) -->
        <aside class="hidden lg:flex lg:flex-col lg:w-60 xl:w-64 bg-[#111111] text-slate-300 shrink-0 z-30 relative select-none">
            <!-- Brand Logo -->
            <div class="h-20 flex items-center px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-teal-400 p-0.5 shadow-sm group-hover:scale-105 transition">
                        <div class="w-full h-full bg-[#111111] rounded-[10px] flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.12 6.4-6.05-4.06a2 2 0 0 0-2.17-.05L2.95 8.41a2 2 0 0 0-.95 1.7v5.78a2 2 0 0 0 .95 1.7l9.95 6.12a2 2 0 0 0 2.17-.05l6.05-4.06A2 2 0 0 0 22 17.89V8.1a2 2 0 0 0-.88-1.7Z"/>
                                <path d="m10 22 12-7.89"/>
                                <path d="m2 9.89 10 6.11"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold tracking-wide text-white font-heading">LOADORA</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links with Concave Corner Curves on Active Item -->
            <div class="flex-1 flex flex-col justify-between overflow-visible py-2">
                <nav class="space-y-1 pl-4 pr-0 overflow-visible relative pt-6">
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-3"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('dispatches.index') }}"
                       class="{{ request()->routeIs('dispatches.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="truck" class="w-4 h-4 mr-3"></i>
                        <span>Dispatches</span>
                    </a>

                    <a href="{{ route('receipts.index') }}"
                       class="{{ request()->routeIs('receipts.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="file-check-2" class="w-4 h-4 mr-3"></i>
                        <span>Receipts</span>
                    </a>

                    <a href="{{ route('vehicles.index') }}"
                       class="{{ request()->routeIs('vehicles.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="shield-check" class="w-4 h-4 mr-3"></i>
                        <span>Vehicles</span>
                    </a>

                    <a href="{{ route('goods.index') }}"
                       class="{{ request()->routeIs('goods.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="package" class="w-4 h-4 mr-3"></i>
                        <span>Goods</span>
                    </a>

                    <a href="{{ route('clients.index') }}"
                       class="{{ request()->routeIs('clients.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="users" class="w-4 h-4 mr-3"></i>
                        <span>Clients</span>
                    </a>

                    <a href="{{ route('reports.index') }}"
                       class="{{ request()->routeIs('reports.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 mr-3"></i>
                        <span>Reports</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <div class="pt-3 pb-1 pr-4">
                            <div class="h-px bg-white/10 w-full"></div>
                        </div>

                        <a href="{{ route('import.index') }}"
                           class="{{ request()->routeIs('import.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i data-lucide="file-up" class="w-4 h-4 mr-3"></i>
                            <span>Import Center</span>
                        </a>

                        <a href="{{ route('users.index') }}"
                           class="{{ request()->routeIs('users.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i data-lucide="user-cog" class="w-4 h-4 mr-3"></i>
                            <span>Staff</span>
                        </a>

                        <a href="{{ route('activity.index') }}"
                           class="{{ request()->routeIs('activity.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i data-lucide="activity" class="w-4 h-4 mr-3"></i>
                            <span>Activity Logs</span>
                        </a>

                        <a href="{{ route('settings.index') }}"
                           class="{{ request()->routeIs('settings.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i data-lucide="settings" class="w-4 h-4 mr-3"></i>
                            <span>Settings</span>
                        </a>
                    @endif
                </nav>

                <!-- User Footer Pill in Matte Black -->
                <div class="p-4 border-t border-white/10">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center space-x-2.5 truncate">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold text-xs uppercase shrink-0">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div class="truncate">
                                <span class="block text-white font-medium truncate">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">{{ auth()->user()->role }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Sign Out" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-white/5 transition">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Floating Softly-Rounded Light Gray Main Container (#F4F5F7) -->
        <div class="flex-1 bg-[#F4F5F7] rounded-3xl my-2 mr-2 lg:my-2.5 lg:mr-2.5 overflow-hidden flex flex-col min-h-[calc(100vh-1.25rem)] shadow-2xl relative">
            
            <!-- Top Header Bar inside the Light Container -->
            <header class="px-6 lg:px-8 pt-5 pb-3 flex items-center justify-between gap-4">
                <!-- Mobile menu toggle button -->
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2.5 rounded-full bg-white shadow-sm text-slate-700 hover:bg-slate-50 transition">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Wide Pill-Shaped White Search Bar -->
                <div class="flex-1 max-w-xl">
                    <div class="flex items-center bg-white rounded-full px-5 py-2.5 shadow-sm border border-slate-200/50 focus-within:border-slate-300 focus-within:ring-2 focus-within:ring-slate-900/5 transition">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 mr-3 shrink-0"></i>
                        <input type="text"
                               placeholder="Search dispatches, vehicles, goods, receipts..."
                               class="bg-transparent border-0 focus:outline-none focus:ring-0 text-sm text-slate-800 placeholder:text-slate-400 w-full outline-none">
                    </div>
                </div>

                <!-- Right Header Actions (Orange +, Circular Notification, User Avatar) -->
                <div class="flex items-center space-x-2.5 sm:space-x-3 shrink-0">
                    <!-- Orange + Quick-Add Dispatch Button -->
                    <a href="{{ route('dispatches.index', ['create' => 'true']) }}"
                       title="Create New Dispatch"
                       class="w-9 h-9 rounded-full bg-orange-500 hover:bg-orange-600 text-white shadow-sm flex items-center justify-center transition transform hover:scale-105 shrink-0 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </a>

                    <!-- Interactive Notification Bell & Dropdown -->
                    <div class="relative"
                         x-data="{
                             open: false,
                             alerts: {{ json_encode($headerAlerts) }},
                             readIds: JSON.parse(localStorage.getItem('loadora_read_alerts') || '[]'),
                             get unreadCount() {
                                 return this.alerts.filter(a => !this.readIds.includes(a.id)).length;
                             },
                             markAllAsRead() {
                                 this.readIds = this.alerts.map(a => a.id);
                                 localStorage.setItem('loadora_read_alerts', JSON.stringify(this.readIds));
                             },
                             markAsRead(id) {
                                 if (!this.readIds.includes(id)) {
                                     this.readIds.push(id);
                                     localStorage.setItem('loadora_read_alerts', JSON.stringify(this.readIds));
                                 }
                             }
                         }"
                         @keydown.escape.window="open = false">
                        <!-- Bell Trigger Button -->
                        <button type="button"
                                @click="open = !open"
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow-sm border border-slate-200/50 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:shadow transition relative focus:outline-none focus:ring-2 focus:ring-slate-400"
                                :class="{ 'ring-2 ring-slate-900 text-slate-900': open }">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <!-- Unread Indicator Dot/Badge -->
                            <span x-show="unreadCount > 0"
                                  class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">
                                <span x-text="unreadCount"></span>
                            </span>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute right-0 mt-3 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-100 py-3 z-50 text-xs overflow-hidden"
                             style="display: none;">
                            
                            <!-- Dropdown Header -->
                            <div class="px-4 pb-2.5 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-slate-900 text-sm font-heading">Notifications</span>
                                    <template x-if="unreadCount > 0">
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600" x-text="unreadCount + ' new'"></span>
                                    </template>
                                </div>
                                <button type="button"
                                        @click="markAllAsRead()"
                                        x-show="unreadCount > 0"
                                        class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                    Mark all as read
                                </button>
                            </div>

                            <!-- Alert List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <template x-for="item in alerts" :key="item.id">
                                    <a :href="item.url"
                                       @click="markAsRead(item.id)"
                                       class="flex items-start p-3.5 hover:bg-slate-50/80 transition group"
                                       :class="{ 'bg-slate-50/40': !readIds.includes(item.id) }">
                                        <!-- Alert Icon -->
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mr-3 mt-0.5"
                                             :class="item.type === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600'">
                                            <template x-if="item.type === 'pending'">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                            </template>
                                            <template x-if="item.type !== 'pending'">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Alert Text -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="font-bold text-slate-900 text-xs truncate" x-text="item.title"></p>
                                                <span class="text-[10px] text-slate-400 shrink-0 ml-2" x-text="item.time"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-0.5 leading-snug line-clamp-2" x-text="item.message"></p>
                                        </div>

                                        <!-- Unread Dot -->
                                        <span x-show="!readIds.includes(item.id)" class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0 ml-2 mt-2"></span>
                                    </a>
                                </template>

                                <!-- Empty State -->
                                <template x-if="alerts.length === 0">
                                    <div class="py-8 px-4 text-center">
                                        <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-slate-800 text-xs">All caught up!</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">No pending receipts or transit alerts right now.</p>
                                    </div>
                                </template>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="pt-2 px-4 border-t border-slate-100 flex items-center justify-between">
                                <a href="{{ route('receipts.index') }}" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 transition">
                                    Receipts Queue &rarr;
                                </a>
                                <a href="{{ route('dispatches.index') }}" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 transition">
                                    All Dispatches &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Circular User Avatar & Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center focus:outline-none">
                            <div class="w-10 h-10 rounded-full bg-slate-900 text-white font-bold text-xs uppercase flex items-center justify-center shadow-sm border-2 border-white ring-1 ring-slate-200 hover:scale-105 transition">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 text-xs"
                             style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase mt-1 bg-indigo-50 text-indigo-700">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                            <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2 text-slate-700 hover:bg-slate-50">
                                <i data-lucide="settings" class="w-3.5 h-3.5 mr-2 text-slate-400"></i> Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-rose-600 hover:bg-rose-50">
                                    <i data-lucide="log-out" class="w-3.5 h-3.5 mr-2 text-rose-500"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Body -->
            <main class="flex-1 px-6 lg:px-8 py-4 overflow-y-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js Global Toast Notification Container -->
    <div x-data="{
            toasts: [],
            add(msg, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, msg, type });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id) }, 4000);
            }
         }"
         @toast.window="add($event.detail.message, $event.detail.type)"
         class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto flex items-center px-4 py-3 rounded-xl shadow-lg border text-sm font-medium transition transform duration-200"
                 :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-800': t.type === 'success',
                    'bg-rose-50 border-rose-200 text-rose-800': t.type === 'error',
                    'bg-indigo-50 border-indigo-200 text-indigo-800': t.type === 'info'
                 }">
                <span x-text="t.msg"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>