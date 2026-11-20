<?php

namespace App\Livewire\Dispatches;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class DispatchIndex extends Component
{
    use WithPagination;

    // Search and filters
    public string $search = '';
    public string $statusFilter = '';
    public string $goodsFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Modal state
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingDispatchId = null;

    // Form inputs
    public string $dispatch_number = '';
    public ?int $client_id = null;
    public ?int $vehicle_id = null;
    public string $vehicle_prefix = 'DMT';
    public string $vehicle_number_input = '';
    public string $driver_name = '';
    public ?int $goods_id = null;
    public string $loading_date = '';
    public string $loading_quantity = '';
    public string $destination_location = '';
    public string $note = '';

    // Legacy support
    public bool $showNewVehicleField = false;
    public string $new_vehicle_number = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (request()->query('create') === 'true') {
            $this->openCreateModal();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['editingDispatchId', 'client_id', 'vehicle_id', 'vehicle_number_input', 'driver_name', 'goods_id', 'loading_quantity', 'destination_location', 'note', 'showNewVehicleField', 'new_vehicle_number']);
        $this->vehicle_prefix = 'DMT';
        
        // Auto-generate next sequential dispatch number: DSP-YYYY-XXXX
        $year = now()->format('Y');
        $latest = Dispatch::whereYear('created_at', $year)->latest('id')->first();
        $nextSeq = $latest ? ((int) substr($latest->dispatch_number, -4)) + 1 : 1;
        $this->dispatch_number = sprintf('DSP-%s-%04d', $year, $nextSeq);
        
        $this->loading_date = now()->format('Y-m-d');
        
        // Default to first client and goods if available
        $this->client_id = Client::first()?->id;
        $this->goods_id = Goods::first()?->id;

        $this->showCreateModal = true;
    }

    public function getFormattedVehicleNumber(): string
    {
        $num = trim($this->vehicle_number_input);
        if (empty($num)) {
            if ($this->vehicle_id) {
                return (string) (Vehicle::find($this->vehicle_id)?->vehicle_number ?? '');
            }
            if (!empty($this->new_vehicle_number)) {
                return strtoupper(trim($this->new_vehicle_number));
            }
            return '';
        }

        $prefixes = ['DMT', 'BMT', 'BGT', 'CMT', 'KMT', 'JMT', 'SMT'];
        $pattern = '/^(' . implode('|', $prefixes) . ')[\s\-]*(.+)$/i';
        if (preg_match($pattern, $num, $matches)) {
            return strtoupper($matches[1] . ' ' . trim($matches[2]));
        }

        if ($this->vehicle_prefix === 'Other' || empty($this->vehicle_prefix)) {
            return strtoupper($num);
        }

        return strtoupper(trim($this->vehicle_prefix . ' ' . $num));
    }

    public function updatedVehicleId($value): void
    {
        if ($value) {
            $veh = Vehicle::find($value);
            if ($veh) {
                $prefixes = ['DMT', 'BMT', 'BGT', 'CMT', 'KMT', 'JMT', 'SMT'];
                $pattern = '/^(' . implode('|', $prefixes) . ')[\s\-]*(.+)$/i';
                if (preg_match($pattern, $veh->vehicle_number, $matches)) {
                    $this->vehicle_prefix = strtoupper($matches[1]);
                    $this->vehicle_number_input = trim($matches[2]);
                } else {
                    $this->vehicle_prefix = 'Other';
                    $this->vehicle_number_input = $veh->vehicle_number;
                }
                if (empty($this->driver_name) && !empty($veh->driver_name)) {
                    $this->driver_name = $veh->driver_name;
                }
            }
        }
    }

    public function updatedVehicleNumberInput(): void
    {
        $this->syncDriverFromVehicleNumber();
    }

    public function updatedVehiclePrefix(): void
    {
        $this->syncDriverFromVehicleNumber();
    }

    protected function syncDriverFromVehicleNumber(): void
    {
        if (empty($this->driver_name)) {
            $fullNumber = $this->getFormattedVehicleNumber();
            if (!empty($fullNumber)) {
                $veh = Vehicle::where('vehicle_number', $fullNumber)->first();
                if ($veh && !empty($veh->driver_name)) {
                    $this->driver_name = $veh->driver_name;
                }
            }
        }
    }

    public function store(): void
    {
        Gate::authorize('create', Dispatch::class);

        $rules = [
            'dispatch_number' => 'required|string|max:50|unique:dispatches,dispatch_number',
            'client_id' => 'nullable|exists:clients,id',
            'driver_name' => 'nullable|string|max:150',
            'goods_id' => 'required|exists:goods,id',
            'loading_date' => 'required|date',
            'loading_quantity' => 'required|numeric|min:0.001|max:9999.999',
            'destination_location' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ];

        if (empty($this->vehicle_number_input) && empty($this->vehicle_id) && empty($this->new_vehicle_number)) {
            $rules['vehicle_number_input'] = 'required|string|min:1';
            $rules['vehicle_id'] = 'required';
        }

        $this->validate($rules, [], [
            'vehicle_number_input' => 'vehicle number',
        ]);

        $fullNumber = $this->getFormattedVehicleNumber();

        // Automatically find or register the truck in the vehicles table
        $vehicle = Vehicle::firstOrCreate(
            ['vehicle_number' => $fullNumber],
            [
                'driver_name' => trim($this->driver_name) ?: null,
                'vehicle_type' => 'Dump Truck',
                'status' => 'active',
            ]
        );

        $this->vehicle_id = $vehicle->id;
        $loadingQty = round((float) $this->loading_quantity, 3);

        $dispatch = Dispatch::create([
            'dispatch_number' => strtoupper(trim($this->dispatch_number)),
            'client_id' => $this->client_id,
            'vehicle_id' => $vehicle->id,
            'driver_name' => trim($this->driver_name) ?: null,
            'goods_id' => $this->goods_id,
            'loading_date' => $this->loading_date,
            'loading_quantity' => $loadingQty,
            'destination_location' => $this->destination_location,
            'status' => 'pending',
            'pending_quantity' => $loadingQty,
            'total_received_quantity' => 0.000,
            'total_deduction_quantity' => 0.000,
            'note' => $this->note,
            'created_by' => Auth::id(),
        ]);

        ActivityLog::record(
            action: 'dispatch.created',
            module: 'dispatch',
            description: "Created dispatch {$dispatch->dispatch_number} for {$dispatch->loading_quantity} MT",
            record: $dispatch,
            newValues: $dispatch->toArray()
        );

        $this->showCreateModal = false;
        $this->dispatch('toast', message: 'Dispatch created successfully!', type: 'success');
    }

    public function openEditModal(int $id): void
    {
        $dispatch = Dispatch::findOrFail($id);
        Gate::authorize('update', $dispatch);

        $this->editingDispatchId = $dispatch->id;
        $this->dispatch_number = $dispatch->dispatch_number;
        $this->client_id = $dispatch->client_id;
        $this->vehicle_id = $dispatch->vehicle_id;
        $this->driver_name = (string) ($dispatch->driver_name ?? '');
        $this->goods_id = $dispatch->goods_id;
        $this->loading_date = $dispatch->loading_date->format('Y-m-d');
        $this->loading_quantity = (string) $dispatch->loading_quantity;
        $this->destination_location = (string) $dispatch->destination_location;
        $this->note = (string) $dispatch->note;
        $this->showNewVehicleField = false;

        // Parse prefix and number from existing vehicle
        $vehNumber = $dispatch->vehicle->vehicle_number ?? '';
        $prefixes = ['DMT', 'BMT', 'BGT', 'CMT', 'KMT', 'JMT', 'SMT'];
        $pattern = '/^(' . implode('|', $prefixes) . ')[\s\-]*(.+)$/i';
        if (preg_match($pattern, $vehNumber, $matches)) {
            $this->vehicle_prefix = strtoupper($matches[1]);
            $this->vehicle_number_input = trim($matches[2]);
        } else {
            $this->vehicle_prefix = 'Other';
            $this->vehicle_number_input = $vehNumber;
        }

        $this->showEditModal = true;
    }

    public function update(): void
    {
        $dispatch = Dispatch::findOrFail($this->editingDispatchId);
        Gate::authorize('update', $dispatch);

        $rules = [
            'dispatch_number' => "required|string|max:50|unique:dispatches,dispatch_number,{$dispatch->id}",
            'client_id' => 'nullable|exists:clients,id',
            'driver_name' => 'nullable|string|max:150',
            'goods_id' => 'required|exists:goods,id',
            'loading_date' => 'required|date',
            'loading_quantity' => 'required|numeric|min:0.001',
            'destination_location' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ];

        if (empty($this->vehicle_number_input) && empty($this->vehicle_id)) {
            $rules['vehicle_number_input'] = 'required|string|min:1';
            $rules['vehicle_id'] = 'required';
        }

        $this->validate($rules, [], [
            'vehicle_number_input' => 'vehicle number',
        ]);

        $fullNumber = $this->getFormattedVehicleNumber();

        // Automatically find or register the truck in the vehicles table
        $vehicle = Vehicle::firstOrCreate(
            ['vehicle_number' => $fullNumber],
            [
                'driver_name' => trim($this->driver_name) ?: null,
                'vehicle_type' => 'Dump Truck',
                'status' => 'active',
            ]
        );

        $this->vehicle_id = $vehicle->id;
        $oldValues = $dispatch->toArray();

        $dispatch->update([
            'dispatch_number' => strtoupper(trim($this->dispatch_number)),
            'client_id' => $this->client_id,
            'vehicle_id' => $vehicle->id,
            'driver_name' => trim($this->driver_name) ?: null,
            'goods_id' => $this->goods_id,
            'loading_date' => $this->loading_date,
            'loading_quantity' => round((float) $this->loading_quantity, 3),
            'destination_location' => $this->destination_location,
            'note' => $this->note,
            'updated_by' => Auth::id(),
        ]);

        $dispatch->recalculateTotals();

        ActivityLog::record(
            action: 'dispatch.updated',
            module: 'dispatch',
            description: "Updated dispatch {$dispatch->dispatch_number}",
            record: $dispatch,
            oldValues: $oldValues,
            newValues: $dispatch->toArray()
        );

        $this->showEditModal = false;
        $this->dispatch('toast', message: 'Dispatch updated successfully!', type: 'success');
    }

    public function delete(int $id): void
    {
        $dispatch = Dispatch::findOrFail($id);
        Gate::authorize('delete', $dispatch);

        $oldValues = $dispatch->toArray();
        $dispatch->delete();

        ActivityLog::record(
            action: 'dispatch.deleted',
            module: 'dispatch',
            description: "Deleted dispatch {$dispatch->dispatch_number}",
            record: $dispatch,
            oldValues: $oldValues
        );

        $this->dispatch('toast', message: 'Dispatch record soft-deleted.', type: 'info');
    }

    public function render()
    {
        $query = Dispatch::with(['vehicle', 'goods', 'client', 'receipts'])
            ->latest('loading_date')
            ->latest('id');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('dispatch_number', 'like', "%{$s}%")
                  ->orWhere('note', 'like', "%{$s}%")
                  ->orWhereHas('vehicle', fn($vq) => $vq->where('vehicle_number', 'like', "%{$s}%"))
                  ->orWhereHas('receipts', fn($rq) => $rq->where('receipt_number', 'like', "%{$s}%"));
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->goodsFilter)) {
            $query->where('goods_id', $this->goodsFilter);
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('loading_date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('loading_date', '<=', $this->dateTo);
        }

        return view('livewire.dispatches.dispatch-index', [
            'dispatches' => $query->paginate(15),
            'vehicles' => Vehicle::orderBy('vehicle_number')->get(),
            'goodsList' => Goods::where('status', 'active')->orderBy('name')->get(),
            'clients' => Client::where('status', 'active')->orderBy('name')->get(),
            'totalDispatchesCount' => Dispatch::count(),
            'receivedDispatchesCount' => Dispatch::where('status', 'received')->count(),
            'pendingDispatchesCount' => Dispatch::whereIn('status', ['pending', 'partial'])->count(),
        ])->layout('layouts.app', ['title' => 'Dispatch Management']);
    }
}
