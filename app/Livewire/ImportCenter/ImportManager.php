<?php

namespace App\Livewire\ImportCenter;

use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Services\SpreadsheetImportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ImportManager extends Component
{
    use WithFileUploads, WithPagination;

    public $spreadsheetFile;
    public ?int $selectedBatchId = null;
    public string $rowStatusFilter = '';

    public function mount(): void
    {
        Gate::authorize('upload', ImportBatch::class);
        $latest = ImportBatch::latest()->first();
        if ($latest) {
            $this->selectedBatchId = $latest->id;
        }
    }

    public function upload(): void
    {
        Gate::authorize('upload', ImportBatch::class);

        $this->validate([
            'spreadsheetFile' => 'required|file|mimes:csv,txt,xlsx|max:20480', // 20MB
        ]);

        $path = $this->spreadsheetFile->store('imports', 'local');
        $fullPath = storage_path('app/' . $path);

        $service = new SpreadsheetImportService();
        $batch = $service->stageFile(
            $fullPath,
            $this->spreadsheetFile->getClientOriginalName(),
            $this->spreadsheetFile->getClientOriginalExtension()
        );

        $this->selectedBatchId = $batch->id;
        $this->reset('spreadsheetFile');
        $this->dispatch('toast', message: "Spreadsheet parsed! {$batch->total_rows} rows staged for validation.", type: 'success');
    }

    /**
     * One-click migration of the server's existing historical Google Sheet CSV export.
     */
    public function importHistoricalExport(): void
    {
        Gate::authorize('upload', ImportBatch::class);

        $csvPath = base_path('sheet_export.csv');
        if (!file_exists($csvPath)) {
            $this->dispatch('toast', message: 'Historical export file (sheet_export.csv) not found.', type: 'error');
            return;
        }

        $service = new SpreadsheetImportService();
        $batch = $service->stageFile(
            $csvPath,
            'Google_Sheet_Historical_Export.csv',
            'csv'
        );

        $this->selectedBatchId = $batch->id;
        $this->dispatch('toast', message: "Historical data staged! {$batch->total_rows} records ready for preview and commit.", type: 'success');
    }

    public function commitCurrentBatch(): void
    {
        Gate::authorize('commit', ImportBatch::class);

        if (!$this->selectedBatchId) {
            return;
        }

        $batch = ImportBatch::findOrFail($this->selectedBatchId);
        if ($batch->status === 'completed') {
            $this->dispatch('toast', message: 'This batch has already been committed to the database.', type: 'info');
            return;
        }

        $service = new SpreadsheetImportService();
        $result = $service->commitBatch($batch);

        $this->dispatch('toast', message: "Successfully committed {$result['dispatches']} dispatches and {$result['receipts']} receipts to MySQL!", type: 'success');
    }

    public function selectBatch(int $id): void
    {
        $this->selectedBatchId = $id;
        $this->resetPage();
    }

    public function render()
    {
        $batches = ImportBatch::with('user')->latest()->get();
        $currentBatch = $this->selectedBatchId ? ImportBatch::find($this->selectedBatchId) : null;

        $rowsQuery = ImportRow::query();
        if ($currentBatch) {
            $rowsQuery->where('batch_id', $currentBatch->id);
            if (!empty($this->rowStatusFilter)) {
                $rowsQuery->where('validation_status', $this->rowStatusFilter);
            }
        }

        return view('livewire.import-center.import-manager', [
            'batches' => $batches,
            'currentBatch' => $currentBatch,
            'stagedRows' => $currentBatch ? $rowsQuery->paginate(20) : collect(),
        ])->layout('layouts.app', ['title' => 'Google Sheets Import Center']);
    }
}
