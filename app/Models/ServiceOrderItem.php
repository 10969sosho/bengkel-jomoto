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
        'discount',
        'subtotal',
        'is_free',
        'free_reason',
        'category',
        'service_action',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'discount' => 'decimal:2',
            'subtotal' => 'integer',
            'is_free' => 'boolean',
        ];
    }

    public function categoryLabel(): string
    {
        if (! empty($this->category)) {
            return $this->category;
        }
        if ($this->item_type === 'sparepart') {
            return 'Sparepart';
        }
        if ($this->item_type === 'sub_material') {
            return 'Sub-Material';
        }
        $pricelist = \App\Models\ServicePricelist::where('service_name', $this->item_name)->first();
        return $pricelist ? $pricelist->categoryLabel() : 'Mesin';
    }

    public function actionLabel(): string
    {
        if (! empty($this->service_action)) {
            return strtoupper($this->service_action);
        }
        if ($this->item_type === 'service') {
            return 'G';
        }
        return '-';
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
