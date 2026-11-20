<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goods extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'goods';

    protected $fillable = [
        'name',
        'code',
        'unit',
        'description',
        'status',
    ];

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
