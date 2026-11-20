<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dispatch_id',
        'sequence_number',
        'receipt_number',
        'receiving_date',
        'received_quantity',
        'deduction_quantity',
        'unloading_location',
        'receipt_status',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'receiving_date' => 'date',
            'received_quantity' => 'decimal:3',
            'deduction_quantity' => 'decimal:3',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Preview the next sequential receipt number (e.g. #213).
     */
    public static function peekNextReceiptNumber(): string
    {
        $maxSeq = (int) (static::max('sequence_number') ?: 0);
        return '#' . ($maxSeq + 1);
    }

    protected static function booted(): void
    {
        static::creating(function (Receipt $receipt) {
            // Auto-incrementing receipt sequence using DB lock to guarantee sequential #1, #2, #3 without duplicates
            if (empty($receipt->sequence_number)) {
                $maxSeq = (int) (static::lockForUpdate()->max('sequence_number') ?: 0);
                $nextSeq = $maxSeq + 1;
                $receipt->sequence_number = $nextSeq;
                if (empty($receipt->receipt_number) || !str_starts_with($receipt->receipt_number, '#')) {
                    $receipt->receipt_number = "#{$nextSeq}";
                }
            } elseif (empty($receipt->receipt_number)) {
                $receipt->receipt_number = "#{$receipt->sequence_number}";
            }
        });

        static::saved(function (Receipt $receipt) {
            $receipt->dispatch?->recalculateTotals();
        });

        static::deleted(function (Receipt $receipt) {
            $receipt->dispatch?->recalculateTotals();
        });
    }
}
