<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'driver_name',
        'driver_phone',
        'capacity_mt',
        'owner_type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity_mt' => 'decimal:3',
        ];
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
