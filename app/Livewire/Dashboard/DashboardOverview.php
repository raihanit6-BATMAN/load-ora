<?php

namespace App\Livewire\Dashboard;

use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\Receipt;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardOverview extends Component
{
    public string $dateFilter = 'all'; // all, this_month, last_30_days, this_year

    public function render()
    {
        $query = Dispatch::query();

        if ($this->dateFilter === 'this_month') {
            $query->whereBetween('loading_date', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($this->dateFilter === 'last_30_days') {
            $query->where('loading_date', '>=', now()->subDays(30));
        } elseif ($this->dateFilter === 'this_year') {
            $query->whereYear('loading_date', now()->year);
        }

        // Aggregate KPIs
        $totalDispatches = (clone $query)->count();
        $totalLoaded = (float) ((clone $query)->sum('loading_quantity') ?? 0.0);
        $totalReceived = (float) ((clone $query)->sum('total_received_quantity') ?? 0.0);
        $totalDeduction = (float) ((clone $query)->sum('total_deduction_quantity') ?? 0.0);
        $totalPending = (float) ((clone $query)->sum('pending_quantity') ?? 0.0);
        $totalVehicles = Vehicle::count();

        // Recent 8 dispatches with relations
        $recentDispatches = (clone $query)
            ->with(['vehicle', 'goods', 'client', 'receipts'])
            ->orderBy('loading_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        // Pending dispatches needing attention
        $pendingDispatches = Dispatch::with(['vehicle', 'goods'])
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('loading_date', 'asc')
            ->limit(5)
            ->get();

        // Goods Distribution Data
        $goodsDistribution = DB::table('dispatches')
            ->join('goods', 'dispatches.goods_id', '=', 'goods.id')
            ->select('goods.name', DB::raw('SUM(dispatches.loading_quantity) as total_loaded'))
            ->groupBy('goods.name')
            ->get();

        // Monthly Trend Data (Last 6 months)
        $driver = DB::getDriverName();
        $monthKeyExpr = $driver === 'sqlite' ? "strftime('%Y-%m', loading_date)" : "DATE_FORMAT(loading_date, '%Y-%m')";
        $monthLabelExpr = $driver === 'sqlite' ? "strftime('%m/%Y', loading_date)" : "DATE_FORMAT(loading_date, '%b %Y')";

        $monthlyTrends = DB::table('dispatches')
            ->select(
                DB::raw("{$monthKeyExpr} as month_key"),
                DB::raw("{$monthLabelExpr} as month_label"),
                DB::raw('SUM(loading_quantity) as total_loaded'),
                DB::raw('SUM(total_received_quantity) as total_received')
            )
            ->whereNotNull('loading_date')
            ->groupBy('month_key', 'month_label')
            ->orderBy('month_key', 'asc')
            ->limit(6)
            ->get();

        // Daily Incoming Log (Daily Received Summary from MySQL)
        $dailyQuery = DB::table('receipts')
            ->whereNull('deleted_at')
            ->whereNotNull('receiving_date');

        if ($this->dateFilter === 'this_month') {
            $dailyQuery->whereBetween('receiving_date', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($this->dateFilter === 'last_30_days') {
            $dailyQuery->where('receiving_date', '>=', now()->subDays(30));
        } elseif ($this->dateFilter === 'this_year') {
            $dailyQuery->whereYear('receiving_date', now()->year);
        }

        $dailyIncomingLog = $dailyQuery
            ->select(
                'receiving_date',
                DB::raw('COUNT(DISTINCT dispatch_id) as truck_count'),
                DB::raw('SUM(received_quantity) as total_received_quantity')
            )
            ->groupBy('receiving_date')
            ->orderBy('receiving_date', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.dashboard-overview', [
            'dateFilter' => $this->dateFilter,
            'totalDispatches' => $totalDispatches,
            'totalLoaded' => $totalLoaded,
            'totalReceived' => $totalReceived,
            'totalDeduction' => $totalDeduction,
            'totalPending' => $totalPending,
            'totalVehicles' => $totalVehicles,
            'recentDispatches' => $recentDispatches,
            'pendingDispatches' => $pendingDispatches,
            'goodsDistribution' => $goodsDistribution,
            'monthlyTrends' => $monthlyTrends,
            'dailyIncomingLog' => $dailyIncomingLog,
        ])->layout('layouts.app', ['title' => 'Executive Dashboard']);
    }
}
