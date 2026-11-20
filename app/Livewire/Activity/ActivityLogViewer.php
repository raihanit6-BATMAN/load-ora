<?php

namespace App\Livewire\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public string $search = '';
    public string $moduleFilter = '';
    public ?int $userFilter = null;

    public function render()
    {
        $query = ActivityLog::with('user')->latest('created_at');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        if (!empty($this->moduleFilter)) {
            $query->where('module', $this->moduleFilter);
        }

        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
        }

        return view('livewire.activity.activity-log-viewer', [
            'logs' => $query->paginate(25),
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'System Audit Logs']);
    }
}
