<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderItem extends Model
{
    protected $fillable = [
        'service_order_id',
        'item_type',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'is_free',
        'free_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'is_free' => 'boolean',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function typeLabel(): string
    {
        return match ($this->item_type) {
            'service' => 'Jasa',
            'sparepart' => 'Sparepart',
            default => 'Sub-Material',
        };
    }
}
