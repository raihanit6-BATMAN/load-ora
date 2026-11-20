<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ReportBuilder extends Component
{
    public string $reportType = 'dispatch_summary'; // dispatch_summary, vehicle_wise, goods_wise, deductions, pending
    public string $datePreset = 'this_month'; // today, this_week, this_month, last_month, this_year, custom
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $vehicleId = null;
    public ?int $goodsId = null;
    public ?int $clientId = null;

    public function mount(): void
    {
        $this->applyPreset();
    }

    public function updatedDatePreset(): void
    {
        $this->applyPreset();
    }

    protected function applyPreset(): void
    {
        switch ($this->datePreset) {
            case 'today':
                $this->dateFrom = now()->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case 'this_week':
                $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->dateTo = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->dateFrom = now()->startOfYear()->format('Y-m-d');
                $this->dateTo = now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        $query = Dispatch::with(['vehicle', 'goods', 'client', 'receipts']);

        if (!empty($this->dateFrom)) {
            $query->whereDate('loading_date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('loading_date', '<=', $this->dateTo);
        }

        if ($this->vehicleId) {
            $query->where('vehicle_id', $this->vehicleId);
        }

        if ($this->goodsId) {
            $query->where('goods_id', $this->goodsId);
        }

        if ($this->clientId) {
            $query->where('client_id', $this->clientId);
        }

        if ($this->reportType === 'deductions') {
            $query->where('total_deduction_quantity', '>', 0);
        } elseif ($this->reportType === 'pending') {
            $query->whereIn('status', ['pending', 'partial']);
        }

        $records = $query->orderBy('loading_date', 'asc')->get();

        // Calculate report aggregates
        $totalLoaded = $records->sum('loading_quantity');
        $totalReceived = $records->sum('total_received_quantity');
        $totalDeduction = $records->sum('total_deduction_quantity');
        $totalPending = $records->sum('pending_quantity');

        return view('livewire.reports.report-builder', [
            'records' => $records,
            'totalLoaded' => $totalLoaded,
            'totalReceived' => $totalReceived,
            'totalDeduction' => $totalDeduction,
            'totalPending' => $totalPending,
            'vehicles' => Vehicle::orderBy('vehicle_number')->get(),
            'goodsList' => Goods::where('status', 'active')->orderBy('name')->get(),
            'clients' => Client::where('status', 'active')->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Reports & Analytics']);
    }
}
