<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ExportController;
use App\Livewire\Activity\ActivityLogViewer;
use App\Livewire\Clients\ClientIndex;
use App\Livewire\Dashboard\DashboardOverview;
use App\Livewire\Dispatches\DispatchIndex;
use App\Livewire\Dispatches\DispatchShow;
use App\Livewire\Goods\GoodsIndex;
use App\Livewire\ImportCenter\ImportManager;
use App\Livewire\Receipts\ReceiptIndex;
use App\Livewire\Reports\ReportBuilder;
use App\Livewire\Settings\SettingsManager;
use App\Livewire\Users\UserManager;
use App\Livewire\Vehicles\VehicleIndex;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes (Admin & Staff)
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', DashboardOverview::class)->name('dashboard');

    // Dispatches
    Route::get('/dispatches', DispatchIndex::class)->name('dispatches.index');
    Route::get('/dispatches/{id}', DispatchShow::class)->name('dispatches.show');

    // Receipts
    Route::get('/receipts', ReceiptIndex::class)->name('receipts.index');

    // Vehicles
    Route::get('/vehicles', VehicleIndex::class)->name('vehicles.index');

    // Goods & Materials
    Route::get('/goods', GoodsIndex::class)->name('goods.index');

    // Clients / Parties
    Route::get('/clients', ClientIndex::class)->name('clients.index');

    // Reports & Analytics
    Route::get('/reports', ReportBuilder::class)->name('reports.index');
    Route::get('/reports/export/csv', [ExportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ExportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Admin Exclusive Routes
    Route::middleware('admin')->group(function () {
        Route::get('/import', ImportManager::class)->name('import.index');
        Route::get('/users', UserManager::class)->name('users.index');
        Route::get('/activity', ActivityLogViewer::class)->name('activity.index');
        Route::get('/settings', SettingsManager::class)->name('settings.index');
    });
});
