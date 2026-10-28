<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    public const TAX_RATE = 0.11;

    protected $fillable = [
        'service_order_id',
        'invoice_number',
        'total_services',
        'total_spareparts',
        'total_submaterials',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_method',
        'payment_status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_services' => 'integer',
            'total_spareparts' => 'integer',
            'total_submaterials' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'grand_total' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public static function createFromOrder(ServiceOrder $order, float $discount = 0): static
    {
        $totals = ['service' => 0, 'sparepart' => 0, 'sub_material' => 0];

        foreach ($order->items as $item) {
            if (! $item->is_free) {
                $totals[$item->item_type] += $item->subtotal;
            }
        }

        $subtotal = array_sum($totals);
        $tax = (int) round(($subtotal - $discount) * self::TAX_RATE);

        return static::create([
            'service_order_id' => $order->id,
            'invoice_number' => self::generateNumber(),
            'total_services' => $totals['service'],
            'total_spareparts' => $totals['sparepart'],
            'total_submaterials' => $totals['sub_material'],
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'grand_total' => $subtotal - $discount + $tax,
            'payment_status' => 'unpaid',
        ]);
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $last = static::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function markPaid(string $method): void
    {
        $this->update([
            'payment_method' => $method,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function paymentMethodLabel(): string
    {
        return strtoupper($this->payment_method ?? '-');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public static function terbilang(int $number): string
    {
        $text = self::convert($number);

        return $text === '' ? 'nol' : $text;
    }

    private static function convert(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $text = match (true) {
            $number < 12 => $satuan[$number],
            $number < 20 => self::convert($number - 10).' belas',
            $number < 100 => self::convert(intdiv($number, 10)).' puluh '.self::convert($number % 10),
            $number < 200 => 'seratus '.self::convert($number - 100),
            $number < 1000 => self::convert(intdiv($number, 100)).' ratus '.self::convert($number % 100),
            $number < 2000 => 'seribu '.self::convert($number - 1000),
            $number < 1_000_000 => self::convert(intdiv($number, 1000)).' ribu '.self::convert($number % 1000),
            $number < 1_000_000_000 => self::convert(intdiv($number, 1_000_000)).' juta '.self::convert($number % 1_000_000),
            default => self::convert(intdiv($number, 1_000_000_000)).' miliar '.self::convert($number % 1_000_000_000),
        };

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
