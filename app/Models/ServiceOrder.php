<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceOrder extends Model
{
    protected $fillable = [
        'wo_number',
        'customer_id',
        'vehicle_id',
        'sa_id',
        'technician_id',
        'customer_complaint',
        'sa_diagnosis',
        'service_type',
        'location_type',
        'status',
        'estimated_completion',
        'actual_completion',
        'offsite_address',
        'location_notes',
        'visit_fee',
        'odometer',
        'coupon_milestone',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion' => 'datetime',
            'actual_completion' => 'datetime',
            'visit_fee' => 'integer',
            'odometer' => 'integer',
            'coupon_milestone' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function sa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sa_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Antrian Baru',
            'waiting_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'working' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'invoiced' => 'Sudah Diterbitkan Invoice',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-700',
            'waiting_approval' => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-sky-100 text-sky-700',
            'working' => 'bg-indigo-100 text-indigo-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'invoiced' => 'bg-purple-100 text-purple-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function progressPercent(): int
    {
        return match ($this->status) {
            'draft' => 10,
            'waiting_approval' => 35,
            'approved' => 50,
            'working' => 70,
            'completed' => 90,
            'invoiced' => 100,
            default => 0,
        };
    }

    public function serviceTypeLabel(): string
    {
        return match ($this->service_type) {
            'free_atpm' => 'Gratis Berkala ATPM',
            'free_jomoto' => 'Gratis Berkala Jomoto',
            'home_service' => 'Home Service',
            default => 'Reguler',
        };
    }

    /** @return array{service_type: string, milestone: int}|null */
    public static function couponForOdometer(Vehicle $vehicle, int $odometer, ?int $ignoreOrderId = null): ?array
    {
        $milestones = [1_000 => 'free_atpm', 4_000 => 'free_atpm', 7_000 => 'free_atpm'];
        for ($milestone = 10_000; $milestone <= $odometer; $milestone += 10_000) {
            $milestones[$milestone] = 'free_jomoto';
        }

        foreach ($milestones as $milestone => $serviceType) {
            if ($odometer < $milestone || $odometer > $milestone + 500) {
                continue;
            }

            $alreadyClaimed = $vehicle->serviceOrders()
                ->where('coupon_milestone', $milestone)
                ->where('status', '!=', 'cancelled')
                ->when($ignoreOrderId !== null, fn ($query) => $query->where('id', '!=', $ignoreOrderId))
                ->exists();

            if (! $alreadyClaimed) {
                return ['service_type' => $serviceType, 'milestone' => $milestone];
            }
        }

        return null;
    }

    public function elapsedSeconds(): int
    {
        return (int) $this->workLogs()->sum('duration_seconds')
            + (int) $this->workLogs()
                ->whereNull('stopped_at')
                ->get()
                ->sum(fn (WorkLog $log) => $log->started_at->diffInSeconds(now()));
    }

    public function reminderMessage(): string
    {
        $name = explode(' ', $this->customer?->name ?? 'Bapak/Ibu')[0];
        $vehicle = $this->vehicle?->fullName() ?? 'motor Anda';
        $plate = $this->vehicle?->plate_number ?? '';
        $km = $this->vehicle?->last_odometer ?? 0;

        return "Halo {$name}! 👋\n\n"
            ."Jomoto Center mengingatkan jadwal servis berkala untuk {$vehicle} ({$plate}) "
            ."yang tercatat odometer terakhir {$km} KM.\n\n"
            .'Jadikan motor tetap prima — booking servis sekarang ya! '
            ."Gratis pengecekan awal di bengkel kami.\n\n"
            .'Salam, Jomoto Center';
    }

    public function waLink(): string
    {
        $number = $this->customer?->whatsappNumber();

        if (! $number) {
            return '#';
        }

        return 'https://web.whatsapp.com/send?phone='.$number.'&text='.urlencode($this->reminderMessage());
    }

    public static function generateWoNumber(): string
    {
        $prefix = 'WO-'.now()->format('ym').'-';
        $last = static::where('wo_number', 'like', $prefix.'%')
            ->orderByDesc('wo_number')
            ->value('wo_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
