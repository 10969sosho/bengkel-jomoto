<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubMaterialCatalog extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
