<?php

namespace App\Livewire\Vehicles;

use App\Models\ActivityLog;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editingVehicleId = null;

    // Form inputs
    public string $vehicle_number = '';
    public string $vehicle_type = 'Dump Truck';
    public string $driver_name = '';
    public string $driver_phone = '';
    public string $capacity_mt = '';
    public string $owner_type = 'hired';
    public string $status = 'active';
    public string $notes = '';

    protected $queryString = ['search' => ['except' => '']];

    public function openCreateModal(): void
    {
        Gate::authorize('create', Vehicle::class);

        $this->resetValidation();
        $this->reset(['editingVehicleId', 'vehicle_number', 'driver_name', 'driver_phone', 'capacity_mt', 'notes']);
        $this->vehicle_type = 'Dump Truck';
        $this->owner_type = 'hired';
        $this->status = 'active';

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        Gate::authorize('update', $vehicle);

        $this->editingVehicleId = $vehicle->id;
        $this->vehicle_number = $vehicle->vehicle_number;
        $this->vehicle_type = $vehicle->vehicle_type;
        $this->driver_name = (string) $vehicle->driver_name;
        $this->driver_phone = (string) $vehicle->driver_phone;
        $this->capacity_mt = (string) ($vehicle->capacity_mt ?? '');
        $this->owner_type = $vehicle->owner_type;
        $this->status = $vehicle->status;
        $this->notes = (string) $vehicle->notes;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'vehicle_number' => 'required|string|max:100|unique:vehicles,vehicle_number,' . ($this->editingVehicleId ?? 'NULL') . ',id',
            'vehicle_type' => 'required|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'capacity_mt' => 'nullable|numeric|min:0.1',
            'owner_type' => 'required|in:company_owned,hired,contractor',
            'status' => 'required|in:active,in_transit,maintenance,inactive',
            'notes' => 'nullable|string|max:500',
        ];

        $this->validate($rules);

        $data = [
            'vehicle_number' => strtoupper(trim($this->vehicle_number)),
            'vehicle_type' => $this->vehicle_type,
            'driver_name' => $this->driver_name ?: null,
            'driver_phone' => $this->driver_phone ?: null,
            'capacity_mt' => $this->capacity_mt ? round((float) $this->capacity_mt, 3) : null,
            'owner_type' => $this->owner_type,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingVehicleId) {
            $vehicle = Vehicle::findOrFail($this->editingVehicleId);
            Gate::authorize('update', $vehicle);
            $old = $vehicle->toArray();
            $vehicle->update($data);

            ActivityLog::record('vehicle.updated', 'vehicle', "Updated vehicle {$vehicle->vehicle_number}", $vehicle, $old, $vehicle->toArray());
            $this->dispatch('toast', message: 'Vehicle updated successfully!', type: 'success');
        } else {
            Gate::authorize('create', Vehicle::class);
            $vehicle = Vehicle::create($data);

            ActivityLog::record('vehicle.created', 'vehicle', "Added new vehicle {$vehicle->vehicle_number}", $vehicle, newValues: $vehicle->toArray());
            $this->dispatch('toast', message: 'Vehicle registered successfully!', type: 'success');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        Gate::authorize('delete', $vehicle);

        $old = $vehicle->toArray();
        $vehicle->delete();

        ActivityLog::record('vehicle.deleted', 'vehicle', "Deleted vehicle {$vehicle->vehicle_number}", $vehicle, oldValues: $old);
        $this->dispatch('toast', message: 'Vehicle removed.', type: 'info');
    }

    public function render()
    {
        $query = Vehicle::withCount('dispatches')
            ->withSum('dispatches as total_loaded_tonnage', 'loading_quantity')
            ->withSum('dispatches as total_deduction_tonnage', 'total_deduction_quantity')
            ->orderBy('vehicle_number');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('vehicle_number', 'like', "%{$s}%")
                  ->orWhere('driver_name', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if (!empty($this->typeFilter)) {
            $query->where('vehicle_type', $this->typeFilter);
        }

        return view('livewire.vehicles.vehicle-index', [
            'vehicles' => $query->paginate(15),
        ])->layout('layouts.app', ['title' => 'Fleet Management']);
    }
}
