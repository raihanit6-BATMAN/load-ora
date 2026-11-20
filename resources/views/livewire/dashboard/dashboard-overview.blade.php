<div class="space-y-6">
    <!-- Row 1: 4 Minimal White KPI Cards (Mobile: 2-column compact grid, Desktop: 4 columns) -->
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-5">
        <!-- KPI 1: Total Loaded -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[11px] lg:text-xs font-medium text-slate-400">Total Loaded</span>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-slate-900 font-heading tabular-nums tracking-tight">
                    {{ number_format($totalLoaded, 2) }} <span class="text-[10px] lg:text-xs font-semibold text-slate-400 font-sans">MT</span>
                </div>
                <div class="text-[10px] sm:text-xs text-emerald-600 font-medium flex items-center mt-2 lg:mt-3">
                    <i data-lucide="trending-up" class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-1 inline shrink-0"></i>
                    <span class="truncate">100% scheduled</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Received -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[11px] lg:text-xs font-medium text-slate-400">Total Received</span>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-slate-900 font-heading tabular-nums tracking-tight">
                    {{ number_format($totalReceived, 2) }} <span class="text-[10px] lg:text-xs font-semibold text-slate-400 font-sans">MT</span>
                </div>
                @php
                    $recvPct = $totalLoaded > 0 ? round(($totalReceived / $totalLoaded) * 100, 1) : 0;
                @endphp
                <div class="w-full bg-slate-100 h-1 lg:h-1.5 rounded-full overflow-hidden mt-2 lg:mt-3">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ min($recvPct, 100) }}%"></div>
                </div>
                <span class="text-[10px] lg:text-xs text-slate-400 mt-1 lg:mt-1.5 block font-medium">{{ $recvPct }}% completed</span>
            </div>
        </div>

        <!-- KPI 3: In-Transit / Pending -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[11px] lg:text-xs font-medium text-slate-400">In-Transit / Pending</span>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-slate-900 font-heading tabular-nums tracking-tight">
                    {{ number_format($totalPending, 2) }} <span class="text-[10px] lg:text-xs font-semibold text-slate-400 font-sans">MT</span>
                </div>
                <div class="text-[10px] sm:text-xs text-slate-500 font-medium flex items-center mt-2 lg:mt-3">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-400 mr-1.5 inline-block shrink-0"></span>
                    <span class="truncate">{{ $pendingDispatches->count() }} active transit</span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Total Deductions -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[11px] lg:text-xs font-medium text-slate-400">Total Deductions</span>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-slate-900 font-heading tabular-nums tracking-tight">
                    {{ number_format($totalDeduction, 2) }} <span class="text-[10px] lg:text-xs font-semibold text-slate-400 font-sans">MT</span>
                </div>
                @php
                    $dedPct = $totalLoaded > 0 ? round(($totalDeduction / $totalLoaded) * 100, 2) : 0;
                @endphp
                <div class="w-full bg-slate-100 h-1 lg:h-1.5 rounded-full overflow-hidden mt-2 lg:mt-3">
                    <div class="bg-rose-500 h-full rounded-full transition-all duration-500" style="width: {{ min($dedPct * 10, 100) }}%"></div>
                </div>
                <span class="text-[10px] lg:text-xs text-slate-400 mt-1 lg:mt-1.5 block font-medium">{{ $dedPct }}% transit variance</span>
            </div>
        </div>
    </div>

    <!-- Row 2: 2-Column Split (Material Split & Daily Incoming Log) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Left: Material Distribution (Donut Chart with Soft Lavender Shades) -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div class="flex items-center justify-between pb-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 font-heading">Material Distribution</h2>
                <span class="text-[11px] sm:text-xs font-semibold text-slate-400">By Grade</span>
            </div>

            <div class="flex items-center justify-between gap-3 sm:gap-4 my-auto py-2">
                <!-- Donut Chart Canvas (Compact on mobile, full size on desktop) -->
                <div class="w-28 h-28 sm:w-36 sm:h-36 lg:w-44 lg:h-44 relative flex items-center justify-center shrink-0">
                    <canvas id="goodsDonutChart"></canvas>
                </div>

                <!-- Clean Vertical Legend -->
                <div class="flex-1 space-y-2 sm:space-y-3.5 pl-1 sm:pl-2 min-w-0">
                    @php
                        $donutColors = ['#7C83D2', '#9FA4E0', '#BFC3EB', '#D4D7F5'];
                    @endphp
                    @foreach($goodsDistribution->take(4) as $idx => $g)
                        <div>
                            <span class="text-[11px] sm:text-xs text-slate-400 block font-medium truncate" title="{{ $g->name }}">
                                {{ $g->name }}
                            </span>
                            <div class="flex items-center space-x-1.5 sm:space-x-2 mt-0.5">
                                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full shrink-0" style="background-color: {{ $donutColors[$idx % count($donutColors)] }};"></span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 tabular-nums">
                                    {{ number_format($g->total_loaded, 1) }} MT
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] sm:text-xs text-slate-400">
                <span>Total Materials: {{ $goodsDistribution->count() }}</span>
                <a href="{{ route('goods.index') }}" class="font-semibold text-slate-700 hover:text-slate-900 transition">
                    View Catalog &rarr;
                </a>
            </div>
        </div>

        <!-- Right: Daily Incoming Log / Shipment Tracking Table -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 font-heading">Daily Incoming Log / Outstanding Receipts</h2>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Recent weighbridge daily received summaries</p>
                    </div>
                    <a href="{{ route('receipts.index') }}" class="text-[11px] sm:text-xs font-semibold text-slate-600 hover:text-slate-900 transition shrink-0">
                        View All &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto mt-2 -mx-3.5 sm:mx-0 px-3.5 sm:px-0">
                    <table class="w-full text-left text-xs min-w-[420px] sm:min-w-0">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider text-[10px] sm:text-[11px] font-semibold border-b border-slate-100">
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Date</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Received Qty</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Truck Count</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($dailyIncomingLog->take(5) as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-2 sm:px-3 font-medium text-slate-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->receiving_date)->format('Y-m-d') }}
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                        {{ number_format($log->total_received_quantity, 2) }} MT
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-slate-600 font-medium tabular-nums whitespace-nowrap">
                                        {{ $log->truck_count }} Trucks
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-right whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            Received
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                        No incoming receipts recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] sm:text-xs text-slate-400">
                <span>Weighbridge Verified</span>
                <span class="font-medium text-slate-500">Auto-synced</span>
            </div>
        </div>
    </div>

    <!-- Row 3: 2-Column Split (Recent Dispatches & Monthly Trend Wave Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Left: Recent Dispatches (Active Orders Table) -->
        <div class="lg:col-span-8 bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 font-heading">Recent Dispatches</h2>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Active trips, destinations and transit status</p>
                    </div>
                    <a href="{{ route('dispatches.index') }}" class="text-[11px] sm:text-xs font-semibold text-slate-600 hover:text-slate-900 transition shrink-0">
                        View All &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto mt-2 -mx-3.5 sm:mx-0 px-3.5 sm:px-0">
                    <table class="w-full text-left text-xs min-w-[540px] sm:min-w-0">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider text-[10px] sm:text-[11px] font-semibold border-b border-slate-100">
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Receipt #</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Vehicle / Client</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Location</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3 text-center">Status</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3">Date</th>
                                <th class="py-2.5 sm:py-3 px-2 sm:px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($recentDispatches->take(6) as $d)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-2 sm:px-3 font-mono font-medium whitespace-nowrap">
                                        @if($d->receipts->count() > 0)
                                            <div class="inline-flex flex-wrap items-center gap-1">
                                                @foreach($d->receipts as $r)
                                                    <a href="{{ route('receipts.index', ['dispatch_id' => $d->id]) }}"
                                                       class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 border border-indigo-200/80 text-[11px] font-bold font-mono text-indigo-700 hover:bg-indigo-100 transition"
                                                       title="Date: {{ $r->receiving_date?->format('d/m/Y') }} • Qty: {{ number_format($r->received_quantity, 3) }} MT">
                                                        {{ str_starts_with($r->receipt_number, '#') ? $r->receipt_number : '#' . $r->receipt_number }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 font-medium">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-900">{{ $d->vehicle->vehicle_number ?? 'Truck' }}</div>
                                        <div class="text-[10px] sm:text-[11px] text-slate-400 truncate max-w-[140px]">{{ $d->client->name ?? 'Party' }}</div>
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-slate-600 truncate max-w-[120px] whitespace-nowrap">
                                        {{ $d->location ?? 'Site' }}
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-center whitespace-nowrap">
                                        @if($d->status === 'received')
                                            <span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                Received
                                            </span>
                                        @elseif($d->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-sky-100 text-sky-700">
                                                In Transit
                                            </span>
                                        @elseif($d->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-slate-100 text-slate-600">
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-rose-100 text-rose-700">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-slate-500 whitespace-nowrap">
                                        {{ $d->loading_date?->format('Y-m-d') }}
                                    </td>
                                    <td class="py-3 px-2 sm:px-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center space-x-1 sm:space-x-1.5 text-slate-400">
                                            <a href="{{ route('dispatches.show', $d->id) }}" title="View Details" class="p-1 hover:text-slate-900 transition">
                                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                            </a>
                                            <a href="{{ route('receipts.index', ['dispatch_id' => $d->id]) }}" title="Add Receipt" class="p-1 hover:text-slate-900 transition">
                                                <i data-lucide="file-check-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        No dispatch records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] sm:text-xs text-slate-400">
                <span>Showing latest {{ min($recentDispatches->count(), 6) }} entries</span>
                <a href="{{ route('dispatches.index') }}" class="font-semibold text-slate-700 hover:text-slate-900 transition">
                    Dispatch Ledger &rarr;
                </a>
            </div>
        </div>

        <!-- Right: Monthly Trend (Sage Green Wave / Area Chart) -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 font-heading">Monthly Trend</h2>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Tonnage wave turnaround</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                        Live MT
                    </span>
                </div>

                <div class="h-36 sm:h-44 lg:h-52 relative w-full mt-3">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] sm:text-xs text-slate-400">
                <span class="flex items-center"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#5B8E7D] mr-1.5 inline-block"></span> Loaded</span>
                <span class="flex items-center"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#7FA89B] mr-1.5 inline-block"></span> Received</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('livewire:navigated', initCharts);

function initCharts() {
    // 1. Sage-Green Layered Area/Wave Chart for Monthly Trend
    const monthlyCtx = document.getElementById('monthlyTrendChart');
    if (monthlyCtx) {
        const trendData = @json($monthlyTrends);
        const labels = trendData.length ? trendData.map(d => d.month_label) : ['Oct 2026', 'Nov 2026'];
        const loaded = trendData.length ? trendData.map(d => parseFloat(d.total_loaded)) : [150, 220];
        const received = trendData.length ? trendData.map(d => parseFloat(d.total_received)) : [140, 210];

        if (window.trendChartInstance) {
            window.trendChartInstance.destroy();
        }

        const ctx = monthlyCtx.getContext('2d');
        const gradientLoaded = ctx.createLinearGradient(0, 0, 0, 200);
        gradientLoaded.addColorStop(0, 'rgba(91, 142, 125, 0.45)');
        gradientLoaded.addColorStop(1, 'rgba(91, 142, 125, 0.01)');

        const gradientReceived = ctx.createLinearGradient(0, 0, 0, 200);
        gradientReceived.addColorStop(0, 'rgba(127, 168, 155, 0.35)');
        gradientReceived.addColorStop(1, 'rgba(127, 168, 155, 0.01)');

        window.trendChartInstance = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Loaded (MT)',
                        data: loaded,
                        borderColor: '#5B8E7D',
                        borderWidth: 2.5,
                        backgroundColor: gradientLoaded,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#5B8E7D'
                    },
                    {
                        label: 'Received (MT)',
                        data: received,
                        borderColor: '#7FA89B',
                        borderWidth: 2,
                        backgroundColor: gradientReceived,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#7FA89B'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y + ' MT';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F8FAFC' },
                        ticks: { font: { size: 10 }, color: '#94A3B8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#94A3B8' }
                    }
                }
            }
        });
    }

    // 2. Thick Donut Chart for Material Distribution in Soft Lavender/Indigo Palette
    const donutCtx = document.getElementById('goodsDonutChart');
    if (donutCtx) {
        const goodsData = @json($goodsDistribution);
        const labels = goodsData.length ? goodsData.map(d => d.name) : ['Stone 10-20mm', 'Stone 5-10mm', 'Sand', 'Gravel'];
        const values = goodsData.length ? goodsData.map(d => parseFloat(d.total_loaded)) : [40, 30, 20, 10];

        if (window.donutChartInstance) {
            window.donutChartInstance.destroy();
        }

        window.donutChartInstance = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#7C83D2', '#9FA4E0', '#BFC3EB', '#D4D7F5'],
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.parsed + ' MT';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
}
</script>
