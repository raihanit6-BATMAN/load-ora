<?php

namespace App\Livewire\Users;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManager extends Component
{
    public bool $showModal = false;
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'staff';
    public string $status = 'active';
    public string $phone = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', User::class);

        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'email', 'password', 'phone']);
        $this->role = 'staff';
        $this->status = 'active';

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        Gate::authorize('update', $user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->status = $user->status;
        $this->phone = (string) $user->phone;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($this->editingUserId ?? 'NULL') . ',id',
            'role' => 'required|in:admin,staff',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:50',
        ];

        if (!$this->editingUserId || !empty($this->password)) {
            $rules['password'] = 'required|string|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'role' => $this->role,
            'status' => $this->status,
            'phone' => $this->phone ?: null,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            Gate::authorize('update', $user);
            $old = $user->toArray();
            $user->update($data);

            ActivityLog::record('user.updated', 'users', "Updated user account {$user->email}", $user, $old, $user->toArray());
            $this->dispatch('toast', message: 'User updated successfully!', type: 'success');
        } else {
            Gate::authorize('create', User::class);
            $user = User::create($data);

            ActivityLog::record('user.created', 'users', "Created new user account {$user->email}", $user, newValues: $user->toArray());
            $this->dispatch('toast', message: 'New user account created!', type: 'success');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $user = User::findOrFail($id);
        Gate::authorize('update', $user);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', message: 'You cannot deactivate your own account.', type: 'error');
            return;
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::record('user.status_toggled', 'users', "Changed status of {$user->email} to {$newStatus}", $user);
        $this->dispatch('toast', message: "User status changed to {$newStatus}.", type: 'info');
    }

    public function render()
    {
        $users = User::withCount(['dispatches', 'receipts'])->latest()->get();

        return view('livewire.users.user-manager', [
            'users' => $users,
        ])->layout('layouts.app', ['title' => 'Staff & User Administration']);
    }
}
