<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'row_index',
        'raw_data',
        'parsed_data',
        'validation_status',
        'validation_errors',
        'dispatch_id',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'parsed_data' => 'array',
            'validation_errors' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }
}
