<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'engine_number',
        'chassis_number',
        'fuel_type',
        'last_odometer',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_odometer' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function fuelLabel(): string
    {
        return $this->fuel_type === 'electric' ? 'Listrik' : 'Bensin';
    }

    public function fullName(): string
    {
        return "{$this->brand} {$this->model}";
    }
}
