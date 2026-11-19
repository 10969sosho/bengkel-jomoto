<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    public const DOCUMENTS_CHECKLIST = [
        'stnk' => 'STNK',
        'starter_key' => 'Kunci Starter Sepeda Motor',
        'service_book' => 'Catatan Perawatan',
        'basic_tools' => 'Kunci Dasar (Obeng, Kunci Ring)',
    ];

    public const EXTERIOR_SECTIONS = [
        'Bodi Motor' => [
            'cover_body' => 'Cover Body',
            'jok' => 'Jok',
            'rangka' => 'Rangka',
            'sticker' => 'Sticker',
        ],
        'Mesin' => [
            'karburator' => 'Karburator',
            'injector' => 'Injector',
            'ecu' => 'ECU',
            'overhaul' => 'Overhaul',
            'pompa_oli' => 'Pompa Oli',
            'transmisi' => 'Transmisi CVT/MT',
        ],
        'Penggerak' => [
            'rantai' => 'Rantai',
            'gigi' => 'Gigi',
            'bearing_roda' => 'Bearing Roda',
            'velg' => 'Velg',
            'ban' => 'Ban',
            'kampas_rem' => 'Kampas Rem',
            'as_roda' => 'As Roda',
            'kaliper' => 'Kaliper',
            'per_rem' => 'Per Rem',
            'master_rem' => 'Master Rem/Silinder',
        ],
        'Kelistrikan' => [
            'aki' => 'Aki',
            'lampu' => 'Lampu',
            'sein' => 'Sein',
            'kabel' => 'Kabel',
            'stater' => 'Stater',
            'busi' => 'Busi',
            'indikator' => 'Indikator',
        ],
        'Lain-lain' => [
            'service_gratis' => 'Service Gratis',
            'perawatan_berkala' => 'Perawatan Berkala',
            'ganti_oli' => 'Ganti Oli',
        ],
    ];

    protected $fillable = [
        'service_order_id',
        'technician_id',
        'inspection_number',
        'inspection_date',
        'manager_name',
        'sa_name',
        'odometer_photo',
        'fuel_photo',
        'fuel_level',
        'documents_checklist',
        'exterior_checklist',
        'exterior_photos',
        'checklist',
        'findings',
        'recommendations',
        'mechanic_confirmed_at',
        'mechanic_notes',
        'before_photos',
        'after_photos',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'mechanic_confirmed_at' => 'datetime',
            'documents_checklist' => 'array',
            'exterior_checklist' => 'array',
            'exterior_photos' => 'array',
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

    public static function generateInspectionNumber(): string
    {
        $prefix = 'CI/'.now()->format('Y/m').'/';
        $last = static::where('inspection_number', 'like', $prefix.'%')
            ->orderByDesc('inspection_number')
            ->value('inspection_number');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
