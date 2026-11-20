<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_code',
        'file_name',
        'file_path',
        'file_type',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'skipped_rows',
        'status',
        'column_mapping',
        'summary',
        'imported_by',
    ];

    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'summary' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'batch_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class, 'import_batch_id');
    }
}
