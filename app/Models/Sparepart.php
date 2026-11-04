<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'sku', 'name', 'brand', 'model', 'price', 'stock', 'ecommerce_product_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock' => 'integer', 'is_active' => 'boolean'];
    }
}
