<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class ServiceOrder extends Model
{
    protected $fillable = [
        'wo_number',
        'customer_id',
        'vehicle_id',
        'sa_id',
        'technician_id',
        'customer_complaint',
        'requested_services',
        'stnk_photo',
        'sa_diagnosis',
        'service_type',
        'location_type',
        'status',
        'estimated_completion',
        'actual_completion',
        'estimate_approved_at',
        'final_check_at',
        'final_check_notes',
        'result_confirmed_at',
        'result_confirmation_notes',
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
            'estimate_approved_at' => 'datetime',
            'final_check_at' => 'datetime',
            'result_confirmed_at' => 'datetime',
            'requested_services' => 'array',
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

    /** Tahapan alur servis dari booking hingga kwitansi sesuai alur operasional bengkel. */
    public const FLOW_STAGES = [
        'Booking Servis',
        'Pemeriksaan Awal SA',
        'Pemeriksaan Mekanik',
        'Laporan Pemeriksaan',
        'Estimasi Servis',
        'Persetujuan Estimasi',
        'Pengerjaan Servis',
        'Pemeriksaan Akhir SA',
        'Konfirmasi Customer',
        'Invoice',
        'Pembayaran',
        'Kwitansi',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Antrian Baru',
            'waiting_approval' => 'Menunggu Persetujuan Estimasi',
            'approved' => 'Estimasi Disetujui',
            'working' => 'Sedang Dikerjakan',
            'completed' => 'Pengerjaan Selesai',
            'waiting_confirmation' => 'Menunggu Konfirmasi Customer',
            'confirmed' => 'Hasil Disetujui Customer',
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
            'waiting_confirmation' => 'bg-orange-100 text-orange-700',
            'confirmed' => 'bg-cyan-100 text-cyan-700',
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
            'completed' => 85,
            'waiting_confirmation' => 90,
            'confirmed' => 96,
            'invoiced' => 100,
            default => 0,
        };
    }

    /**
     * Status yang menandakan tahap pengerjaan mekanik sudah dimulai maupun selesai.
     *
     * @var array<int, string>
     */
    public const WORKED_STATUSES = ['working', 'completed', 'waiting_confirmation', 'confirmed', 'invoiced'];

    /** Status setelah customer menyetujui estimasi sehingga teknisi boleh mengerjakan. */
    public const APPROVED_STATUSES = ['approved', 'working', 'completed', 'waiting_confirmation', 'confirmed', 'invoiced'];

    /**
     * Posisi WO pada alur servis 12 tahap.
     *
     * @return array<int, array{no: int, label: string, done: bool}>
     */
    public function flowStages(): array
    {
        $inspectionExists = $this->inspection !== null;
        $invoice = $this->invoice;

        $done = [
            1 => true,
            2 => $this->sa_id !== null,
            3 => $inspectionExists,
            4 => $inspectionExists && $this->sa_id !== null,
            5 => $this->items->isNotEmpty(),
            6 => $this->estimate_approved_at !== null || in_array($this->status, self::APPROVED_STATUSES, true),
            7 => $this->workLogs->isNotEmpty() || in_array($this->status, self::WORKED_STATUSES, true),
            8 => $this->final_check_at !== null,
            9 => $this->result_confirmed_at !== null,
            10 => $invoice !== null,
            11 => $invoice?->isPaid() === true,
            12 => $invoice?->isPaid() === true,
        ];

        return collect(self::FLOW_STAGES)
            ->map(fn (string $label, int $index): array => [
                'no' => $index + 1,
                'label' => $label,
                'done' => (bool) ($done[$index + 1] ?? false),
            ])
            ->values()
            ->all();
    }

    public function currentFlowStage(): int
    {
        $stage = collect($this->flowStages())->first(fn (array $stage): bool => ! $stage['done']);

        return $stage['no'] ?? count(self::FLOW_STAGES);
    }

    /**
     * Nama layanan/servis yang dipilih customer saat booking.
     *
     * @param  array<int, string>|null  $pricelistNames  Peta id => nama layanan hasil preload untuk menghindari query berulang.
     * @return array<int, string>
     */
    public function requestedServiceLabels(?array $pricelistNames = null): array
    {
        $ids = collect($this->requested_services ?? [])->map(fn ($id): int => (int) $id)->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $names = $pricelistNames ?? ServicePricelist::whereIn('id', $ids)->pluck('service_name', 'id')->all();

        return $ids->map(fn (int $id): string => $names[$id] ?? "Layanan #{$id}")->all();
    }

    /** URL foto STNK kendaraan yang diunggah customer saat booking. */
    public function stnkUrl(): ?string
    {
        return $this->stnk_photo ? Storage::disk('public')->url($this->stnk_photo) : null;
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
