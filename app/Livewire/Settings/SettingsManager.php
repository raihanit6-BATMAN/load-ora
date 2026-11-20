<?php

namespace App\Livewire\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use Livewire\Component;

class SettingsManager extends Component
{
    public string $appName = 'LOADORA';
    public string $companyName = '';
    public string $quantityUnit = 'MT';
    public string $dateFormat = 'd/m/Y';
    public string $theme = 'light';
    public string $dispatchPrefix = 'DSP';
    public int $alertPendingHours = 48;

    public function mount(): void
    {
        $this->appName = Setting::get('app.name', 'LOADORA');
        $this->companyName = Setting::get('app.company_name', 'Loadora Logistics Management');
        $this->quantityUnit = Setting::get('app.quantity_unit', 'MT');
        $this->dateFormat = Setting::get('app.date_format', 'd/m/Y');
        $this->theme = Setting::get('app.theme', 'light');
        $this->dispatchPrefix = Setting::get('app.dispatch_prefix', 'DSP');
        $this->alertPendingHours = (int) Setting::get('app.alert_pending_hours', 48);
    }

    public function save(): void
    {
        $this->validate([
            'appName' => 'required|string|max:50',
            'companyName' => 'required|string|max:255',
            'quantityUnit' => 'required|string|max:20',
            'dateFormat' => 'required|string|max:20',
            'dispatchPrefix' => 'required|string|max:10',
            'alertPendingHours' => 'required|integer|min:1|max:720',
        ]);

        Setting::set('app.name', $this->appName);
        Setting::set('app.company_name', $this->companyName);
        Setting::set('app.quantity_unit', $this->quantityUnit, 'dispatch');
        Setting::set('app.date_format', $this->dateFormat);
        Setting::set('app.theme', $this->theme);
        Setting::set('app.dispatch_prefix', $this->dispatchPrefix, 'dispatch');
        Setting::set('app.alert_pending_hours', $this->alertPendingHours, 'dispatch', 'integer');

        ActivityLog::record(
            action: 'settings.updated',
            module: 'settings',
            description: "Updated system settings."
        );

        $this->dispatch('toast', message: 'System settings saved successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.settings-manager')
            ->layout('layouts.app', ['title' => 'System Settings']);
    }
}
