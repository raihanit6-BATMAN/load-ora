<?php

namespace App\Livewire\Clients;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public ?int $editingClientId = null;

    public string $name = '';
    public string $code = '';
    public string $company_name = '';
    public string $contact_person = '';
    public string $phone = '';
    public string $email = '';
    public string $delivery_location = '';
    public string $status = 'active';
    public string $notes = '';

    public function openCreateModal(): void
    {
        Gate::authorize('create', Client::class);

        $this->resetValidation();
        $this->reset(['editingClientId', 'name', 'code', 'company_name', 'contact_person', 'phone', 'email', 'delivery_location', 'notes']);
        $this->status = 'active';

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $client = Client::findOrFail($id);
        Gate::authorize('update', $client);

        $this->editingClientId = $client->id;
        $this->name = $client->name;
        $this->code = (string) $client->code;
        $this->company_name = (string) $client->company_name;
        $this->contact_person = (string) $client->contact_person;
        $this->phone = (string) $client->phone;
        $this->email = (string) $client->email;
        $this->delivery_location = (string) $client->delivery_location;
        $this->status = $client->status;
        $this->notes = (string) $client->notes;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:clients,code,' . ($this->editingClientId ?? 'NULL') . ',id',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'delivery_location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:500',
        ];

        $this->validate($rules);

        $data = [
            'name' => trim($this->name),
            'code' => $this->code ? strtoupper(trim($this->code)) : null,
            'company_name' => $this->company_name ?: null,
            'contact_person' => $this->contact_person ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'delivery_location' => $this->delivery_location ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingClientId) {
            $client = Client::findOrFail($this->editingClientId);
            Gate::authorize('update', $client);
            $old = $client->toArray();
            $client->update($data);

            ActivityLog::record('client.updated', 'client', "Updated client {$client->name}", $client, $old, $client->toArray());
            $this->dispatch('toast', message: 'Client updated successfully!', type: 'success');
        } else {
            Gate::authorize('create', Client::class);
            $client = Client::create($data);

            ActivityLog::record('client.created', 'client', "Added new client {$client->name}", $client, newValues: $client->toArray());
            $this->dispatch('toast', message: 'Client party registered!', type: 'success');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $client = Client::findOrFail($id);
        Gate::authorize('delete', $client);

        $old = $client->toArray();
        $client->delete();

        ActivityLog::record('client.deleted', 'client', "Deleted client {$client->name}", $client, oldValues: $old);
        $this->dispatch('toast', message: 'Client record removed.', type: 'info');
    }

    public function render()
    {
        $query = Client::withCount('dispatches')
            ->withSum('dispatches as total_tonnage', 'loading_quantity')
            ->orderBy('name');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('delivery_location', 'like', "%{$s}%");
            });
        }

        return view('livewire.clients.client-index', [
            'clients' => $query->paginate(15),
        ])->layout('layouts.app', ['title' => 'Client Management']);
    }
}
