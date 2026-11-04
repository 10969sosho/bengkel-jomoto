<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    protected $fillable = [
        'service_order_id',
        'technician_id',
        'checklist',
        'findings',
        'recommendations',
        'before_photos',
        'after_photos',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'before_photos' => 'array',
            'after_photos' => 'array',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
