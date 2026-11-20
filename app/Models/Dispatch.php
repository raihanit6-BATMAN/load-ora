<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dispatch_number',
        'client_id',
        'vehicle_id',
        'driver_name',
        'goods_id',
        'loading_date',
        'loading_quantity',
        'destination_location',
        'loading_point',
        'status',
        'total_received_quantity',
        'total_deduction_quantity',
        'pending_quantity',
        'note',
        'import_batch_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'loading_date' => 'date',
            'loading_quantity' => 'decimal:3',
            'total_received_quantity' => 'decimal:3',
            'total_deduction_quantity' => 'decimal:3',
            'pending_quantity' => 'decimal:3',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class)->orderBy('receiving_date', 'asc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    /**
     * Recalculate cached totals and delivery status based on associated receipts.
     */
    public function recalculateTotals(): void
    {
        $totalReceived = (float) $this->receipts()->sum('received_quantity');
        $totalDeductions = (float) $this->receipts()->sum('deduction_quantity');
        $loadingQty = (float) $this->loading_quantity;

        $this->total_received_quantity = $totalReceived;
        $this->total_deduction_quantity = $totalDeductions;

        if ($this->receipts()->count() === 0) {
            $this->status = 'pending';
            $this->pending_quantity = $loadingQty;
        } else {
            $deliveredSum = $totalReceived + $totalDeductions;
            if ($deliveredSum >= $loadingQty || $totalReceived >= $loadingQty) {
                $this->status = 'received';
                $this->pending_quantity = 0.000;
            } else {
                $this->status = 'partial';
                $this->pending_quantity = max(0.000, round($loadingQty - $deliveredSum, 3));
            }
        }

        $this->save();
    }
}
