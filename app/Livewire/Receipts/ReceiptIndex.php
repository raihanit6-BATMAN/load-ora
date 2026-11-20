<?php

namespace App\Livewire\Receipts;

use App\Models\ActivityLog;
use App\Models\Dispatch;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ReceiptIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $selectedDispatchId = null;

    // Modal state
    public bool $showCreateModal = false;
    public ?int $dispatch_id = null;
    public string $receipt_number = '';
    public string $receiving_date = '';
    public string $received_quantity = '';
    public string $unloading_location = '';
    public string $remarks = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedDispatchId' => ['except' => null],
    ];

    public function mount(): void
    {
        if (request()->query('dispatch_id')) {
            $this->selectedDispatchId = (int) request()->query('dispatch_id');
            $this->dispatch_id = $this->selectedDispatchId;
            if (request()->query('create') === 'true') {
                $this->openCreateModal();
            }
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['received_quantity', 'remarks']);
        $this->receipt_number = Receipt::peekNextReceiptNumber();
        $this->receiving_date = now()->format('Y-m-d');

        if ($this->dispatch_id) {
            $disp = Dispatch::find($this->dispatch_id);
            if ($disp) {
                $this->unloading_location = $disp->destination_location ?? 'P-Block';
                $this->received_quantity = (string) $disp->pending_quantity;
            }
        }

        $this->showCreateModal = true;
    }

    public function updatedDispatchId($value): void
    {
        if ($value) {
            $disp = Dispatch::find($value);
            if ($disp) {
                $this->unloading_location = $disp->destination_location ?? 'P-Block';
                $this->received_quantity = (string) $disp->pending_quantity;
            }
        }
    }

    public function store(): void
    {
        Gate::authorize('create', Receipt::class);

        $this->validate([
            'dispatch_id' => 'required|exists:dispatches,id',
            'receipt_number' => 'nullable|string|max:100',
            'receiving_date' => 'required|date',
            'received_quantity' => 'required|numeric|min:0.001',
            'unloading_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        $dispatch = Dispatch::findOrFail($this->dispatch_id);
        $rQty = round((float) $this->received_quantity, 3);
        $lQty = (float) $dispatch->loading_quantity;

        // Deduction is loaded minus received
        $deduction = max(0.000, round($lQty - $rQty, 3));

        $receipt = DB::transaction(function () use ($dispatch, $rQty, $deduction) {
            return Receipt::create([
                'dispatch_id' => $dispatch->id,
                'receipt_number' => trim($this->receipt_number) ?: null,
                'receiving_date' => $this->receiving_date,
                'received_quantity' => $rQty,
                'deduction_quantity' => $deduction,
                'unloading_location' => $this->unloading_location,
                'receipt_status' => $deduction > 0 ? 'shortage' : 'received',
                'remarks' => $this->remarks,
                'created_by' => Auth::id(),
            ]);
        });

        ActivityLog::record(
            action: 'receipt.created',
            module: 'receipt',
            description: "Recorded receipt {$receipt->receipt_number} ({$receipt->received_quantity} MT)",
            record: $receipt,
            newValues: $receipt->toArray()
        );

        $this->showCreateModal = false;
        $this->dispatch('toast', message: 'Receipt recorded and dispatch status reconciled!', type: 'success');
    }

    public function delete(int $id): void
    {
        $receipt = Receipt::findOrFail($id);
        Gate::authorize('delete', $receipt);

        $oldValues = $receipt->toArray();
        $receipt->delete();

        ActivityLog::record(
            action: 'receipt.deleted',
            module: 'receipt',
            description: "Deleted receipt #{$receipt->receipt_number}",
            record: $receipt,
            oldValues: $oldValues
        );

        $this->dispatch('toast', message: 'Receipt deleted. Dispatch totals recalculated.', type: 'info');
    }

    public function render()
    {
        $query = Receipt::with(['dispatch.vehicle', 'dispatch.goods', 'creator'])
            ->latest('receiving_date')
            ->latest('id');

        if (!empty($this->selectedDispatchId)) {
            $query->where('dispatch_id', $this->selectedDispatchId);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('receipt_number', 'like', "%{$s}%")
                  ->orWhere('remarks', 'like', "%{$s}%")
                  ->orWhere('unloading_location', 'like', "%{$s}%")
                  ->orWhereHas('dispatch', function ($dq) use ($s) {
                      $dq->where('dispatch_number', 'like', "%{$s}%")
                         ->orWhereHas('vehicle', fn($vq) => $vq->where('vehicle_number', 'like', "%{$s}%"));
                  });
            });
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('receiving_date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('receiving_date', '<=', $this->dateTo);
        }

        return view('livewire.receipts.receipt-index', [
            'receipts' => $query->paginate(15),
            'pendingDispatches' => Dispatch::with('vehicle')->whereIn('status', ['pending', 'partial'])->get(),
        ])->layout('layouts.app', ['title' => 'Weighbridge Receipts']);
    }
}
