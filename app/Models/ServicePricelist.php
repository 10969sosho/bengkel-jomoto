<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServicePricelist extends Model
{
    protected $fillable = [
        'service_name',
        'category',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'free_atpm' => 'Gratis ATPM',
            'free_jomoto' => 'Gratis Jomoto',
            'home_service' => 'Home Service',
            default => 'Reguler',
        };
    }
}
