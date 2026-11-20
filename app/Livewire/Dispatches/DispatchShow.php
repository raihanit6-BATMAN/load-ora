<?php

namespace App\Livewire\Dispatches;

use App\Models\ActivityLog;
use App\Models\Dispatch;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class DispatchShow extends Component
{
    public int $dispatchId;
    public bool $showReceiptModal = false;

    // Receipt form inputs
    public string $receipt_number = '';
    public string $receiving_date = '';
    public string $received_quantity = '';
    public string $unloading_location = '';
    public string $remarks = '';

    public function mount(int $id): void
    {
        $this->dispatchId = $id;
    }

    public function openAddReceiptModal(): void
    {
        $this->resetValidation();
        $this->reset(['received_quantity', 'remarks']);
        $this->receipt_number = Receipt::peekNextReceiptNumber();
        $this->receiving_date = now()->format('Y-m-d');
        
        $dispatch = Dispatch::findOrFail($this->dispatchId);
        $this->unloading_location = $dispatch->destination_location ?? 'P-Block';
        $this->received_quantity = (string) $dispatch->pending_quantity;

        $this->showReceiptModal = true;
    }

    public function saveReceipt(): void
    {
        $dispatch = Dispatch::findOrFail($this->dispatchId);
        Gate::authorize('create', Receipt::class);

        $this->validate([
            'receipt_number' => 'nullable|string|max:100',
            'receiving_date' => 'required|date',
            'received_quantity' => 'required|numeric|min:0.001',
            'unloading_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        $rQty = round((float) $this->received_quantity, 3);
        $lQty = (float) $dispatch->loading_quantity;
        
        // Deduction is loaded minus received for this receipt drop
        $deduction = max(0.000, round($lQty - $rQty, 3));

        $receipt = Receipt::create([
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

        ActivityLog::record(
            action: 'receipt.created',
            module: 'receipt',
            description: "Recorded receipt #{$receipt->receipt_number} ({$receipt->received_quantity} MT) on dispatch {$dispatch->dispatch_number}",
            record: $receipt,
            newValues: $receipt->toArray()
        );

        $this->showReceiptModal = false;
        $this->dispatch('toast', message: 'Weighbridge receipt recorded successfully!', type: 'success');
    }

    public function render()
    {
        $dispatch = Dispatch::with(['vehicle', 'goods', 'client', 'receipts.creator', 'creator', 'updater'])
            ->findOrFail($this->dispatchId);

        return view('livewire.dispatches.dispatch-show', [
            'dispatch' => $dispatch,
        ])->layout('layouts.app', ['title' => 'Dispatch #' . $dispatch->dispatch_number]);
    }
}
