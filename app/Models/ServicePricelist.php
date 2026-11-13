<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServicePricelist extends Model
{
    /** 8 kategori layanan bengkel (P/S/G) — tidak termasuk baris paket kupon lama. */
    public const SERVICE_CATEGORIES = [
        'mesin' => 'Mesin',
        'kelistrikan' => 'Kelistrikan',
        'pengereman' => 'Sistem Pengereman',
        'roda' => 'Roda',
        'suspensi' => 'Suspensi',
        'pendingin' => 'Sistem Pendingin',
        'kemudi' => 'Sistem Kemudi',
        'sprocket' => 'Sprocket & Rantai',
    ];

    protected $fillable = [
        'service_name',
        'category',
        'price',
        'price_p',
        'price_s',
        'price_g',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'price_p' => 'integer',
            'price_s' => 'integer',
            'price_g' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeServiceCategories(Builder $query): Builder
    {
        return $query->whereIn('category', array_keys(self::SERVICE_CATEGORIES));
    }

    public function isServiceCategory(): bool
    {
        return isset(self::SERVICE_CATEGORIES[$this->category]);
    }

    /** @return array<string, int> Harga per aksi P/S/G (aksi tanpa harga tidak disertakan). */
    public function actionPrices(): array
    {
        return array_filter([
            'P' => (int) $this->price_p,
            'S' => (int) $this->price_s,
            'G' => (int) $this->price_g,
        ], fn (int $price): bool => $price > 0);
    }

    public function categoryLabel(): string
    {
        return self::SERVICE_CATEGORIES[$this->category] ?? match ($this->category) {
            'free_atpm' => 'Gratis ATPM',
            'free_jomoto' => 'Gratis Jomoto',
            'home_service' => 'Home Service',
            default => 'Reguler',
        };
    }
}
