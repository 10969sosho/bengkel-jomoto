@extends('layouts.app')

@section('title', 'Kelola '.$serviceOrder->wo_number)
@section('page_title', 'Kelola '.$serviceOrder->wo_number)
@section('page_icon', 'clipboard-pen-line')

@section('content')
<a href="{{ route('sa.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
    <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke daftar WO
</a>

<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="font-mono text-xl font-extrabold text-slate-900">{{ $serviceOrder->wo_number }}</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $serviceOrder->serviceTypeLabel() }} · {{ $serviceOrder->location_type === 'onsite' ? 'Di Bengkel' : 'Panggilan' }} ·
            Dibuat {{ $serviceOrder->created_at->format('d M Y H:i') }}
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if($serviceOrder->inspection)
            <a href="{{ route('sa.print.checkin', $serviceOrder) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 active:scale-[.98]">
                <i data-lucide="printer" class="h-4 w-4"></i> Cetak Check-in
            </a>
        @endif
        <a href="{{ route('sa.print.estimate', $serviceOrder) }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 active:scale-[.98]">
            <i data-lucide="printer" class="h-4 w-4"></i> Cetak Estimasi
        </a>
        @if($serviceOrder->spk_number)
            <a href="{{ route('sa.print.spk', $serviceOrder) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 active:scale-[.98]">
                <i data-lucide="printer" class="h-4 w-4"></i> Cetak SPK
            </a>
        @endif
        <span class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $serviceOrder->statusColor() }}">{{ $serviceOrder->statusLabel() }}</span>
    </div>
</div>

<div class="mt-5">
    @include('partials.service-flow', ['order' => $serviceOrder])
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-3">
    {{-- Info ringkas --}}
    <div class="space-y-5 lg:col-span-1">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="user-round" class="h-4 w-4"></i> Customer &amp; Motor
            </h2>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">{{ $serviceOrder->customer?->name }}</div>
                <div class="text-slate-500">{{ $serviceOrder->customer?->email }}</div>
                <div class="mt-1 text-slate-500">WA: {{ $serviceOrder->customer?->whatsapp ?: $serviceOrder->customer?->phone ?: '—' }}</div>

                <div class="mt-4 rounded-2xl bg-slate-50 p-3.5">
                    <div class="font-semibold text-slate-800">{{ $serviceOrder->vehicle?->fullName() }}</div>
                    <div class="mt-1.5 grid grid-cols-2 gap-1.5 text-xs text-slate-500">
                        <span class="rounded-lg bg-white px-2 py-1">Plat: <b class="text-slate-700">{{ $serviceOrder->vehicle?->plate_number }}</b></span>
                        <span class="rounded-lg bg-white px-2 py-1">{{ $serviceOrder->vehicle?->fuelLabel() }}</span>
                        <span class="rounded-lg bg-white px-2 py-1">Tahun: {{ $serviceOrder->vehicle?->year }}</span>
                        <span class="rounded-lg bg-white px-2 py-1">Odo: {{ number_format($serviceOrder->vehicle?->last_odometer ?? 0) }} KM</span>
                    </div>
                </div>

                @if($serviceOrder->location_type === 'offsite')
                    <div class="mt-3 rounded-2xl border border-lime-200 bg-lime-50 p-3.5 text-sm">
                        <div class="font-bold text-lime-900">HOME SERVICE</div>
                        <div class="mt-1 text-lime-800">{{ $serviceOrder->offsite_address }}</div>
                        @if($serviceOrder->location_notes)<div class="mt-1 text-xs text-lime-700">Patokan: {{ $serviceOrder->location_notes }}</div>@endif
                        <div class="mt-1 text-xs font-semibold text-lime-700">Biaya kunjungan Rp {{ number_format($serviceOrder->visit_fee) }}</div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Keluhan</div>
                    <p class="mt-1 text-slate-700">{{ $serviceOrder->customer_complaint }}</p>
                </div>

                @if($serviceOrder->requestedServiceLabels($pricelistNames) !== [])
                    <div class="mt-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Layanan Diminta Customer</div>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($serviceOrder->requestedServiceLabels($pricelistNames) as $serviceLabel)
                                <span class="rounded-lg bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">{{ $serviceLabel }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($serviceOrder->stnkUrl())
                    <a href="{{ $serviceOrder->stnkUrl() }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100 active:scale-[.98]">
                        <i data-lucide="image" class="h-4 w-4"></i> Lihat Foto STNK
                    </a>
                @endif

                @if($serviceOrder->sa_diagnosis)
                    <div class="mt-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Diagnosis SA</div>
                        <p class="mt-1 text-slate-700">{{ $serviceOrder->sa_diagnosis }}</p>
                    </div>
                @endif

                <a href="{{ $serviceOrder->waLink() }}" target="_blank" rel="noopener"
                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600 active:scale-[.98]">
                    <i data-lucide="message-circle" class="h-4 w-4"></i> Kirim Reminder WhatsApp
                </a>
            </div>
        </div>

        {{-- Assign teknisi --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="hard-hat" class="h-4 w-4"></i> Assign Teknisi
            </h2>
            <form method="POST" action="{{ route('sa.assign', $serviceOrder) }}" class="space-y-3">
                @csrf
                <select name="technician_id" required
                        class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                    <option value="">— Pilih teknisi —</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" @selected($serviceOrder->technician_id === $tech->id)>{{ $tech->name }}</option>
                    @endforeach
                </select>
                <input type="datetime-local" name="estimated_completion"
                       value="{{ optional($serviceOrder->estimated_completion)->format('Y-m-d\TH:i') }}"
                       placeholder="Target selesai"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700 active:scale-[.98]">
                    Simpan Assignment
                </button>
            </form>
            <div class="mt-3 text-sm text-slate-500">
                Teknisi aktif: <b class="text-slate-800">{{ $serviceOrder->technician?->name ?? 'Belum ada' }}</b>
                @if($serviceOrder->estimated_completion)
                    <br>Target selesai: <b>{{ $serviceOrder->estimated_completion->format('d M Y H:i') }}</b>
                @endif
            </div>
        </div>

        {{-- Inspeksi --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                    <i data-lucide="clipboard-check" class="h-4 w-4"></i> Lembar Pemeriksaan
                </h2>
                @if($serviceOrder->inspection)
                    <a href="{{ route('sa.print.checkin', $serviceOrder) }}" target="_blank" class="font-mono text-xs font-bold text-slate-500 hover:text-slate-800">
                        {{ $serviceOrder->inspection->inspection_number }}
                    </a>
                @endif
            </div>

            @if($serviceOrder->inspection)
                <div class="mb-3">
                    @if($serviceOrder->inspection->mechanic_confirmed_at)
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">
                            <i data-lucide="check-circle" class="h-3.5 w-3.5"></i> Dikonfirmasi Mekanik {{ $serviceOrder->inspection->mechanic_confirmed_at->format('d M H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                            <i data-lucide="clock" class="h-3.5 w-3.5"></i> Menunggu Konfirmasi Mekanik
                        </span>
                    @endif
                </div>

                @if($serviceOrder->inspection->fuel_level)
                    <div class="mb-2 text-xs text-slate-500">
                        Bahan Bakar: <b>{{ $serviceOrder->inspection->fuel_level }}</b> · Odo Masuk: <b>{{ number_format($serviceOrder->odometer) }} KM</b>
                    </div>
                @endif

                @php($checklist = $serviceOrder->inspection->checklist ?? [])
                @if(!empty($checklist))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($checklist as $item)
                            @php($color = $item['status'] === 'ok' ? 'bg-lime-50 text-lime-700' : (in_array($item['status'], ['p', 's', 'attention']) ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'))
                            <span class="rounded-lg px-2 py-1 text-[11px] font-semibold {{ $color }}">{{ $item['label'] }}</span>
                        @endforeach
                    </div>
                @endif

                @if($serviceOrder->inspection->findings)
                    <p class="mt-3 text-sm text-slate-600"><b>Temuan:</b> {{ $serviceOrder->inspection->findings }}</p>
                @endif
                @if($serviceOrder->inspection->recommendations)
                    <p class="mt-1 text-sm text-slate-600"><b>Rekomendasi:</b> {{ $serviceOrder->inspection->recommendations }}</p>
                @endif
                @if($serviceOrder->inspection->mechanic_notes)
                    <p class="mt-1 text-sm text-slate-500"><b>Catatan Mekanik:</b> {{ $serviceOrder->inspection->mechanic_notes }}</p>
                @endif
            @else
                <p class="text-sm text-slate-400">Belum ada pemeriksaan awal.</p>
                <a href="{{ route('sa.checkin.create', ['booking_id' => $serviceOrder->id]) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline">
                    Isi Form Pemeriksaan Awal (Check-in) →
                </a>
            @endif
        </div>

        @if($serviceOrder->workLogs->isNotEmpty())
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="timer" class="h-4 w-4"></i> Riwayat Waktu Pengerjaan
            </h2>
            @foreach($serviceOrder->workLogs as $log)
                <div class="border-b border-slate-50 py-1.5 last:border-0">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">{{ $log->started_at->format('d M H:i') }}</span>
                        <span class="font-semibold {{ $log->isOpen() ? 'text-indigo-600' : 'text-slate-700' }}">
                            @if($log->isOpen()) ● Berjalan @else {{ gmdate('H:i:s', $log->duration_seconds) }} @endif
                        </span>
                    </div>
                    @if($log->pause_reason)
                        <div class="mt-1 rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-800">
                            Jeda: {{ $log->pause_reason }}
                        </div>
                    @endif
                </div>
            @endforeach
            <div class="mt-2 flex justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm font-bold">
                <span class="text-slate-500">Total</span>
                <span class="font-mono text-slate-900" id="total-elapsed">{{ gmdate('H:i:s', $serviceOrder->elapsedSeconds()) }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Kolom kanan: estimasi & invoice --}}
    <div class="space-y-5 lg:col-span-2">
        @if(in_array($serviceOrder->status, ['draft', 'inspected', 'waiting_approval']))
            {{-- Lembar Estimasi Biaya --}}
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="flex items-center gap-2 font-bold text-slate-900">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                                <i data-lucide="calculator" class="h-4 w-4"></i>
                            </span>
                            Lembar Estimasi Biaya
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">Jasa dari pricelist · Sparepart HET · Sub-Material custom (open item).</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="addRow('service')" class="rounded-xl bg-lime-50 px-3 py-2 text-xs font-bold text-lime-700 transition hover:bg-lime-100 active:scale-95">+ Jasa</button>
                        <button type="button" onclick="addRow('sub_material')" class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100 active:scale-95">+ Sub-Material</button>
                    </div>
                </div>

                @if(! $serviceOrder->inspection)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                        Estimasi baru bisa dibuat setelah teknisi mengisi lembar pemeriksaan.
                    </div>
                @else
                {{-- Data Terhubung dari Pemeriksaan Awal --}}
                <div class="mb-4 rounded-2xl border border-slate-200/90 bg-slate-50/80 p-4 text-xs">
                    <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="grid h-6 w-6 place-items-center rounded-lg bg-lime-300 text-slate-900 font-bold">
                                <i data-lucide="link-2" class="h-3.5 w-3.5"></i>
                            </span>
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-800">Data Terhubung dari Pemeriksaan Awal</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-md border border-slate-200 bg-white px-2 py-0.5 font-mono text-[11px] font-bold text-slate-600">
                                Ref: {{ $serviceOrder->inspection?->inspection_number ?? '—' }}
                            </span>
                            @if($serviceOrder->estimate_number)
                                <span class="rounded-md bg-lime-100 px-2 py-0.5 font-mono text-[11px] font-bold text-lime-800">
                                    No: {{ $serviceOrder->estimate_number }}
                                </span>
                            @endif
                            <a href="{{ route('sa.print.estimate', $serviceOrder) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-lime-300 transition hover:bg-slate-800">
                                <i data-lucide="printer" class="h-3 w-3"></i> Cetak Lembar Estimasi
                            </a>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Data Pelanggan</div>
                            <div class="font-bold text-slate-800">{{ $serviceOrder->customer?->name }}</div>
                            <div class="text-slate-500">{{ $serviceOrder->customer?->phone ?: ($serviceOrder->customer?->whatsapp ?: '—') }}</div>
                            <div class="truncate text-slate-500">{{ $serviceOrder->customer?->address ?: ($serviceOrder->offsite_address ?: '—') }}</div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Petugas Bengkel</div>
                            <div>SA: <b class="text-slate-800">{{ $serviceOrder->inspection?->sa_name ?: ($serviceOrder->sa?->name ?? 'Sales Adviser') }}</b></div>
                            <div>Mekanik: <b class="text-sky-700">{{ $serviceOrder->technician?->name ?? '—' }}</b></div>
                            <div>Manager: <b class="text-slate-800">{{ $serviceOrder->inspection?->manager_name ?? 'ARTI DEWI' }}</b></div>
                        </div>
                        <div class="space-y-1 sm:col-span-2 lg:col-span-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Data Kendaraan &amp; Keluhan</div>
                            <div><b class="font-mono text-slate-900">{{ $serviceOrder->vehicle?->plate_number }}</b> · {{ $serviceOrder->vehicle?->brand }} {{ $serviceOrder->vehicle?->model }}</div>
                            <div class="text-slate-500">Odometer: <b class="text-slate-800">{{ number_format($serviceOrder->odometer ?: ($serviceOrder->vehicle?->last_odometer ?? 0)) }} KM</b></div>
                            <div class="text-[11px] text-slate-600"><b>Keluhan:</b> <span class="rounded bg-amber-100 px-1 py-0.2 font-bold text-amber-900">{{ $serviceOrder->complaint_category ?: 'Servis' }}</span> {{ Str::limit($serviceOrder->customer_complaint, 50) }}</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('sa.estimate', $serviceOrder) }}" id="estimate-form">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Diagnosis SA</label>
                        <textarea name="sa_diagnosis" rows="2"
                                  class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200"
                                  placeholder="Hasil analisa SA setelah melihat inspeksi teknisi...">{{ old('sa_diagnosis', $serviceOrder->sa_diagnosis) }}</textarea>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="items-table">
                            <thead class="border-b border-slate-200 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="pb-2 pr-2 text-center w-8">No</th>
                                    <th class="pb-2 pr-2">Jenis</th>
                                    <th class="pb-2 pr-2">Nama Barang / Jasa</th>
                                    <th class="pb-2 pr-2 text-center w-16">Qty</th>
                                    <th class="pb-2 pr-2 text-right w-28">Harga Satuan</th>
                                    <th class="pb-2 pr-2 text-right w-24">Diskon</th>
                                    <th class="whitespace-nowrap pb-2 pr-2 text-center">Gratis</th>
                                    <th class="whitespace-nowrap pb-2 text-right w-28">Subtotal</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-2">
                            <div class="text-xs text-slate-400" id="empty-hint">Belum ada item — tambahkan minimal 1 jasa/sparepart/sub-material.</div>
                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-slate-600">Diskon Tambahan Global (Opsional)</label>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="number" name="estimate_discount" id="global-discount" min="0"
                                           value="{{ old('estimate_discount', $serviceOrder->estimate_discount > 0 ? (int)$serviceOrder->estimate_discount : 0) }}"
                                           oninput="recalc()"
                                           class="w-full rounded-xl border-slate-200 bg-slate-50 py-1.5 pl-9 pr-3 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                                </div>
                            </div>
                        </div>

                        {{-- Summary Kalkulasi: Total Biaya, Total Diskon, Total Setelah Diskon, PPN, Total Keseluruhan --}}
                        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs space-y-1.5">
                            <div class="flex justify-between text-slate-600">
                                <span>Total Biaya (Sebelum Diskon):</span>
                                <span id="sum-total-biaya" class="font-bold text-slate-900">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-red-600 font-medium">
                                <span>Total Diskon:</span>
                                <span id="sum-total-diskon">-Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-700 font-semibold border-t border-slate-200 pt-1">
                                <span>Total Setelah Diskon:</span>
                                <span id="sum-setelah-diskon" class="font-bold text-slate-900">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>PPN (11%):</span>
                                <span id="sum-ppn" class="font-medium text-slate-800">Rp 0</span>
                            </div>
                            <div class="mt-1 flex justify-between rounded-xl bg-slate-900 px-3.5 py-2.5 text-sm font-black text-white">
                                <span class="uppercase tracking-wide">Total Keseluruhan:</span>
                                <span id="sum-keseluruhan" class="font-mono text-lime-300">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Tanda Tangan (TTD) 3 Kolom --}}
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2.5 text-center">
                            Persetujuan Estimasi Biaya Pekerjaan (3 Kolom TTD)
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-center text-xs">
                            <div class="rounded-xl border border-dashed border-slate-300 p-2.5 bg-white">
                                <div class="font-bold text-slate-700">Pelanggan</div>
                                <div class="h-12 flex items-center justify-center text-[11px] text-slate-400">
                                    @if($serviceOrder->estimate_approved_at)
                                        <span class="font-semibold text-emerald-700">✓ Disetujui</span>
                                    @else
                                        (Persetujuan Estimasi)
                                    @endif
                                </div>
                                <div class="border-t border-slate-300 pt-1 font-bold text-slate-800 truncate">{{ $serviceOrder->customer?->name }}</div>
                            </div>
                            <div class="rounded-xl border border-dashed border-slate-300 p-2.5 bg-white">
                                <div class="font-bold text-slate-700">Service Advisor (SA)</div>
                                <div class="h-12 flex items-center justify-center text-[11px] text-slate-400">
                                    <span class="font-semibold text-indigo-700">✓ SA Estimator</span>
                                </div>
                                <div class="border-t border-slate-300 pt-1 font-bold text-slate-800 truncate">{{ $serviceOrder->inspection?->sa_name ?: ($serviceOrder->sa?->name ?? 'Sales Adviser') }}</div>
                            </div>
                            <div class="rounded-xl border border-dashed border-slate-300 p-2.5 bg-white">
                                <div class="font-bold text-slate-700">Mekanik</div>
                                <div class="h-12 flex items-center justify-center text-[11px] text-slate-400">
                                    @if($serviceOrder->inspection?->mechanic_confirmed_at)
                                        <span class="font-semibold text-sky-700">✓ Konfirmasi Hasil</span>
                                    @else
                                        (Persetujuan Pengerjaan)
                                    @endif
                                </div>
                                <div class="border-t border-slate-300 pt-1 font-bold text-slate-800 truncate">{{ $serviceOrder->technician?->name ?? 'Mekanik Bengkel' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <a href="{{ route('sa.print.estimate', $serviceOrder) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                            <i data-lucide="printer" class="h-4 w-4"></i> Cetak / Preview PDF
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-lime-400 active:scale-[.98]">
                            <i data-lucide="send" class="h-4 w-4"></i> Simpan Estimasi &amp; Minta Persetujuan
                        </button>
                    </div>
                </form>

                <div class="mt-3 rounded-2xl bg-slate-50 p-3.5 text-xs leading-relaxed text-slate-500">
                    <b class="text-slate-700">Data pricelist:</b><br>
                    @foreach($pricelists->groupBy('category') as $cat => $rows)
                        <span class="mr-2 font-semibold text-slate-600">{{ $rows->first()->categoryLabel() }}:</span>
                        {{ $rows->map(fn($p) => $p->service_name.' ('.($p->actionPrices() !== []
                            ? collect($p->actionPrices())->map(fn($price, $action) => $action.' Rp '.number_format($price))->join(' · ')
                            : 'Rp '.number_format($p->price)).')')->join(' · ') }}<br>
                    @endforeach
                    <b class="text-slate-600">Sub-material:</b>
                    {{ $subMaterials->map(fn($s) => $s->name.' (Rp '.number_format($s->default_price).')')->join(' · ') }}
                </div>
                @endif
            </div>
        @else
            {{-- Rincian item read-only --}}
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="mb-4 font-bold text-slate-900">Rincian Biaya</h2>
                @if($serviceOrder->items->isEmpty())
                    <p class="text-sm text-slate-400">Belum ada rincian biaya.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-200 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="pb-2">No</th>
                                    <th class="pb-2">Kategori</th>
                                    <th class="pb-2">Pekerjaan</th>
                                    <th class="pb-2 text-center">Jenis Layanan</th>
                                    <th class="pb-2 text-right">Harga</th>
                                    <th class="pb-2 text-center">Qty</th>
                                    <th class="pb-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($serviceOrder->items as $item)
                                    <tr>
                                        <td class="py-2.5">{{ $loop->iteration }}</td>
                                        <td class="py-2.5">{{ $item->categoryLabel() }}</td>
                                        <td class="py-2.5">
                                            {{ $item->item_name }}
                                            @if($item->is_free)
                                                <div class="text-xs text-emerald-600">GRATIS — {{ $item->free_reason }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-center">{{ $item->actionLabel() }}</td>
                                        <td class="py-2.5 text-right">Rp {{ number_format($item->unit_price) }}</td>
                                        <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                                        <td class="py-2.5 text-right font-semibold {{ $item->is_free ? 'text-emerald-600' : '' }}">
                                            {{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Aksi persetujuan / pemeriksaan akhir / invoice --}}
        @if($serviceOrder->status === 'waiting_approval')
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-amber-900">
                    <i data-lucide="hourglass" class="h-4 w-4"></i> Tahap 1 — Menunggu Persetujuan Estimasi
                </h3>
                <p class="mt-1 text-sm text-amber-700">
                    Customer menyetujui jenis pekerjaan, sparepart, dan estimasi biaya. Customer dapat menyetujui dari portalnya,
                    atau SA mencatat persetujuan yang disampaikan langsung.
                </p>
                <form method="POST" action="{{ route('sa.approve', $serviceOrder) }}" class="mt-3">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-2.5 text-sm font-bold text-amber-950 transition hover:bg-amber-500 active:scale-[.98]">
                        <i data-lucide="check-check" class="h-4 w-4"></i> Catat Persetujuan Estimasi Customer
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->status === 'approved')
            <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="flex items-center gap-2 font-bold text-sky-900">
                            <i data-lucide="file-badge" class="h-4 w-4"></i> Tahap 5 — Penerbitan Surat Perintah Kerja (SPK)
                        </h3>
                        <p class="mt-1 text-sm text-sky-800">
                            Estimasi telah disetujui customer{{ $serviceOrder->estimate_approved_at ? ' pada '.$serviceOrder->estimate_approved_at->format('d M Y H:i') : '' }}.
                            @if($serviceOrder->spk_number)
                                SPK No: <b class="font-mono text-sky-950">{{ $serviceOrder->spk_number }}</b> diterbitkan {{ $serviceOrder->spk_issued_at?->format('d M Y H:i') }}. Mekanik dapat memulai pengerjaan (start timer).
                            @else
                                Terbitkan SPK resmi agar teknisi / mekanik dapat memulai pengerjaan servis (start timer).
                            @endif
                        </p>
                    </div>
                    <div>
                        @if($serviceOrder->spk_number)
                            <a href="{{ route('sa.print.spk', $serviceOrder) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-sky-700 active:scale-[.98]">
                                <i data-lucide="printer" class="h-4 w-4"></i> Cetak SPK
                            </a>
                        @else
                            <form method="POST" action="{{ route('sa.spk.issue', $serviceOrder) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-sky-700 active:scale-[.98]">
                                    <i data-lucide="file-check" class="h-4 w-4"></i> Terbitkan SPK Sekarang
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($serviceOrder->status === 'completed')
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-emerald-900">
                    <i data-lucide="clipboard-check" class="h-4 w-4"></i> Tahap 8 — Pemeriksaan Akhir SA
                </h3>
                <p class="mt-1 text-sm text-emerald-700">Cek hasil kerja mekanik, lalu teruskan ke customer untuk konfirmasi hasil.</p>
                <form method="POST" action="{{ route('sa.finalCheck', $serviceOrder) }}" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="final_check_notes" rows="3" required maxlength="2000"
                              placeholder="Hasil pemeriksaan akhir: kondisi part terpasang, tes jalan, kebersihan kendaraan, dsb."
                              class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">{{ old('final_check_notes') }}</textarea>
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[.98]">
                        <i data-lucide="badge-check" class="h-4 w-4"></i> Simpan &amp; Minta Konfirmasi Customer
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->status === 'waiting_confirmation')
            <div class="rounded-3xl border border-teal-200 bg-teal-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-teal-900">
                    <i data-lucide="badge-check" class="h-4 w-4"></i> Tahap 9 — Menunggu Konfirmasi Hasil Customer
                </h3>
                @if($serviceOrder->final_check_at)
                    <p class="mt-1 text-sm text-teal-700">
                        Pemeriksaan akhir SA {{ $serviceOrder->final_check_at->format('d M Y H:i') }}: {{ $serviceOrder->final_check_notes }}
                    </p>
                @endif
                <p class="mt-1 text-sm text-teal-700">
                    Customer mengonfirmasi hasil pengerjaan sudah sesuai sebelum invoice diterbitkan — dari portal customer
                    atau dicatat SA berdasarkan konfirmasi langsung.
                </p>
                <form method="POST" action="{{ route('sa.confirm', $serviceOrder) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="text" name="result_confirmation_notes" maxlength="500"
                           placeholder="Catatan konfirmasi customer (opsional)"
                           class="w-full rounded-xl border-teal-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200">
                    <button class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 active:scale-[.98]">
                        <i data-lucide="check-check" class="h-4 w-4"></i> Catat Konfirmasi Customer
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->status === 'confirmed')
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-emerald-900">
                    <i data-lucide="receipt-text" class="h-4 w-4"></i> Tahap 10 — Siap Menerbitkan Invoice
                </h3>
                <p class="mt-1 text-sm text-emerald-700">
                    Hasil pengerjaan disetujui customer{{ $serviceOrder->result_confirmed_at ? ' pada '.$serviceOrder->result_confirmed_at->format('d M Y H:i') : '' }}.
                    Terbitkan invoice / kwitansi resmi untuk customer.
                </p>
                <form method="POST" action="{{ route('sa.invoice', $serviceOrder) }}" class="mt-3">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[.98]">
                        <i data-lucide="receipt" class="h-4 w-4"></i> Terbitkan Invoice / Kwitansi
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->invoice)
            @php($invoice = $serviceOrder->invoice)
            <div class="rounded-3xl border border-purple-200 bg-purple-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="flex items-center gap-2 font-bold text-purple-900">
                            <i data-lucide="receipt" class="h-4 w-4"></i> {{ $invoice->invoice_number }}
                        </h3>
                        <p class="mt-1 text-sm text-purple-700">
                            Grand total: <b>Rp {{ number_format($invoice->grand_total) }}</b> ·
                            Status:
                            <b class="rounded-full {{ $invoice->isPaid() ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }} px-2 py-0.5 text-[11px] uppercase">
                                {{ $invoice->isPaid() ? 'Lunas' : 'Belum Bayar' }}
                            </b>
                        </p>
                    </div>
                    @if($invoice->isPaid())
                        <a href="{{ route('invoices.print', $invoice) }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-purple-700 active:scale-[.98]">
                            <i data-lucide="printer" class="h-4 w-4"></i> Cetak Kwitansi
                        </a>
                    @else
                        <a href="{{ route('sa.payments.index', ['status' => 'unpaid', 'q' => $invoice->invoice_number]) }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-purple-700 active:scale-[.98]">
                            <i data-lucide="credit-card" class="h-4 w-4"></i> Proses di Kasir
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Data untuk lembar estimasi --}}
@if($serviceOrder->inspection && in_array($serviceOrder->status, ['draft', 'inspected', 'waiting_approval']))
<script>
<?php
    $pricelistData = $pricelists->map(function ($p) {
        return [
            'name' => $p->service_name,
            'price' => (int) $p->price,
            'category' => $p->category,
            'type' => $p->type ?? null,
            'actionPrices' => $p->actionPrices(),
        ];
    });
    $subMaterialsData = $subMaterials->map(function ($s) {
        return ['name' => $s->name, 'price' => (int) $s->default_price];
    });
    $existingItemsData = $serviceOrder->items->map(function ($i) {
        return [
            'item_type' => $i->item_type,
            'category' => $i->category,
            'item_name' => $i->item_name,
            'action' => $i->service_action,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
            'discount' => (int) ($i->discount ?? 0),
            'is_free' => $i->is_free,
            'free_reason' => $i->free_reason,
        ];
    })->values();
?>
    const pricelist = <?php echo json_encode($pricelistData); ?>;
    const subMaterials = <?php echo json_encode($subMaterialsData); ?>;
    const existingItems = <?php echo json_encode($existingItemsData->isNotEmpty() ? $existingItemsData : $suggestedItems); ?>;
    const visitFee = {{ (int) $serviceOrder->visit_fee }};

    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const body = document.getElementById('items-body');
    if (!body) throw new Error('no estimate form');

    function optionsFor(type) {
        const source = type === 'service' ? pricelist : (type === 'sub_material' ? subMaterials : []);
        return source.map(o => `<option value="${escapeHtml(o.name)}" data-price="${o.price}" data-category="${escapeHtml(o.category)}" data-action-prices="${escapeHtml(JSON.stringify(o.actionPrices || {}))}">${escapeHtml(o.name)} — ${rupiah(o.price)}</option>`).join('');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char]));
    }

    function addRow(type, item = {}) {
        const idx = body.children.length;
        const tr = document.createElement('tr');
        tr.dataset.type = type;

        const customName = type === 'sparepart';
        const presetPrice = item.unit_price ?? (type === 'service' ? (pricelist[0]?.price ?? 0) : (subMaterials[0]?.price ?? 0));

        tr.innerHTML = `
            <td class="py-2 pr-2 align-top text-center font-mono text-xs text-slate-400 row-no">${idx + 1}</td>
            <td class="py-2 pr-2 align-top">
                <span class="rounded-md px-2 py-0.5 text-xs font-semibold ${type === 'service' ? 'bg-indigo-50 text-indigo-700' : (type === 'sparepart' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700')}">
                    ${type === 'service' ? 'Jasa' : (type === 'sparepart' ? 'Sparepart' : 'Sub-Material')}
                </span>
                <input type="hidden" name="items[${idx}][item_type]" value="${type}">
            </td>
            <td class="py-2 pr-2 align-top">
                ${customName
                    ? `<input required name="items[${idx}][item_name]" value="${escapeHtml(item.item_name)}" placeholder="Nama sparepart (HET)"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">`
                    : type === 'service'
                    ? `<div class="space-y-2">
                            <select required name="items[${idx}][category]" onchange="updateServiceDetails(this)" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                                <option value="">— Pilih Kategori —</option>
                                ${[...new Set(pricelist.map(p => p.category))].map(cat => `<option value="${escapeHtml(cat)}">${escapeHtml(cat)}</option>`).join('')}
                            </select>
                            <select required name="items[${idx}][item_name]" onchange="onNameChange(this)" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                                <option value="">— Pilih Item Pekerjaan —</option>
                                ${pricelist.filter(p => !item.category || p.category === item.category).map(p => `<option value="${escapeHtml(p.name)}" data-price="${p.price}" data-category="${escapeHtml(p.category)}" data-action-prices="${escapeHtml(JSON.stringify(p.actionPrices || {}))}">${escapeHtml(p.name)} — ${rupiah(p.price)}</option>`).join('')}
                            </select>
                            <select required name="items[${idx}][service_action]" onchange="onActionChange(this)" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                                <option value="">— Pilih Jenis Layanan —</option>
                                ${pricelist.find(p => p.name === item.item_name)?.actionPrices ? Object.entries(pricelist.find(p => p.name === item.item_name).actionPrices).map(([act, price]) => `<option value="${act}">${act} — ${rupiah(price)}</option>`).join('') : ''}
                            </select>
                        </div>`
                    : `<select required name="items[${idx}][item_name]" onchange="onNameChange(this)"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                        ${optionsFor(type)}
                        ${type === 'service' && !pricelist.find(p => p.name === item.item_name) && item.item_name ? `<option selected value="${escapeHtml(item.item_name)}">${escapeHtml(item.item_name)}</option>` : ''}
                        ${type === 'sub_material' && item.item_name ? `<option selected value="${escapeHtml(item.item_name)}">${escapeHtml(item.item_name)}</option>` : ''}
                        ${type === 'sub_material' ? `<input type="hidden" name="items[${idx}][category]" value="Sub-Material"><input type="hidden" name="items[${idx}][service_action]" value="-">` : ''}
                       </select>`}
                <input type="hidden" name="items[${idx}][free_reason]" class="free-reason"
                        value="${escapeHtml(item.free_reason)}" placeholder="Alasan gratis">
            </td>
            <td class="py-2 pr-2 align-top">
                <input type="number" name="items[${idx}][quantity]" min="1" value="${item.quantity ?? 1}" oninput="recalc()"
                    class="w-16 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-center text-sm outline-none focus:ring-2 focus:ring-lime-200">
            </td>
            <td class="py-2 pr-2 align-top text-right">
                <input type="number" name="items[${idx}][unit_price]" min="0" value="${item.unit_price ?? presetPrice}" oninput="recalc()"
                    class="w-24 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-right text-sm outline-none focus:ring-2 focus:ring-lime-200">
            </td>
            <td class="py-2 pr-2 align-top text-right">
                <input type="number" name="items[${idx}][discount]" min="0" value="${item.discount ?? 0}" oninput="recalc()" placeholder="0"
                    class="w-20 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-right text-sm outline-none focus:ring-2 focus:ring-lime-200">
            </td>
            <td class="py-2 pr-2 align-top text-center">
                <input type="checkbox" name="items[${idx}][is_free]" value="1" ${item.is_free ? 'checked' : ''} onchange="recalc()"
                    class="h-4 w-4 rounded border-slate-300 text-lime-500 focus:ring-lime-400">
            </td>
            <td class="whitespace-nowrap py-2 align-top text-right font-bold text-slate-900 row-subtotal">Rp 0</td>
            <td class="py-2 align-top text-right">
                <button type="button" onclick="this.closest('tr').remove(); reindex(); recalc()"
                    class="text-xs font-bold text-red-400 transition hover:text-red-600">✕</button>
            </td>`;

        body.appendChild(tr);

        if (type === 'service' && item.item_name && !pricelist.find(p => p.name === item.item_name)) {
            const sel = tr.querySelector('select');
            const opt = document.createElement('option');
            opt.value = item.item_name;
            opt.textContent = item.item_name;
            opt.selected = true;
            sel.appendChild(opt);
        }

        if (item.is_free) {
            const reason = tr.querySelector('.free-reason');
            reason.type = 'text';
            if (!reason.value) reason.value = 'Tanpa biaya';
        }

        document.getElementById('empty-hint').style.display = 'none';
        recalc();
    }

    function onNameChange(sel) {
        const opt = sel.selectedOptions[0];
        const price = opt?.dataset.price;
        const actionPrices = opt?.dataset.actionPrices ? JSON.parse(opt.dataset.actionPrices) : {};
        
        if (price !== undefined) {
            const row = sel.closest('tr');
            const actionSelect = row.querySelector('[name$="[service_action]"]');
            
            let finalPrice = parseInt(price);
            
            if (actionSelect && actionSelect.value) {
                if (actionPrices[actionSelect.value] > 0) {
                    finalPrice = actionPrices[actionSelect.value];
                }
            }
            
            row.querySelector('[name$="[unit_price]"]').value = finalPrice;
            
            const serviceActionSelect = row.querySelector('[name$="[service_action]"]');
            if (serviceActionSelect) {
                serviceActionSelect.innerHTML = '';
                const actions = Object.keys(actionPrices);
                if (actions.length > 0) {
                    serviceActionSelect.innerHTML = '<option value="">— Pilih Jenis Layanan —</option>' + 
                        actions.map(act => {
                            const actPrice = actionPrices[act];
                            return `<option value="${act}" data-price="${actPrice}">${act} — ${rupiah(actPrice)}</option>`;
                        }).join('');
                    const firstOption = serviceActionSelect.querySelector('option:not([value=""])');
                    if (firstOption) {
                        firstOption.selected = true;
                        if (firstOption.dataset.price) {
                            row.querySelector('[name$="[unit_price]"]').value = parseInt(firstOption.dataset.price);
                        }
                    }
                } else {
                    serviceActionSelect.innerHTML = '<option value="">— Pilih Jenis Layanan —</option>';
                }
            }
        }
        recalc();
    }

    function onActionChange(actionSel) {
        const row = actionSel.closest('tr');
        const itemNameSel = row.querySelector('select[name$="[item_name]"]');
        const opt = itemNameSel.selectedOptions[0];
        const actionPrices = opt?.dataset.actionPrices ? JSON.parse(opt.dataset.actionPrices) : {};
        const basePrice = opt?.dataset.price ? parseInt(opt.dataset.price) : 0;
        
        let finalPrice = basePrice;
        
        if (actionSel.value && actionPrices[actionSel.value] > 0) {
            finalPrice = actionPrices[actionSel.value];
        }
        
        row.querySelector('[name$="[unit_price]"]').value = finalPrice;
        recalc();
    }

    function updateServiceDetails(categorySel) {
        const row = categorySel.closest('tr');
        const itemNameSel = row.querySelector('select[name$="[item_name]"]');
        const serviceActionSel = row.querySelector('select[name$="[service_action]"]');
        const unitPriceEl = row.querySelector('input[name$="[unit_price]"]');
        const pricelistItems = pricelist.filter(p => p.category === categorySel.value);
        
        itemNameSel.innerHTML = '';
        serviceActionSel.innerHTML = '';
        while (itemNameSel.firstChild) itemNameSel.removeChild(itemNameSel.firstChild);
        while (serviceActionSel.firstChild) serviceActionSel.removeChild(serviceActionSel.firstChild);
        
        if (pricelistItems.length > 0) {
            itemNameSel.innerHTML = pricelistItems.map(p => `<option value="${escapeHtml(p.name)}" data-price="${p.price}" data-category="${escapeHtml(p.category)}" data-action-prices="${escapeHtml(JSON.stringify(p.actionPrices))}">${escapeHtml(p.name)} — ${rupiah(p.price)}</option>`).join('');
            
            const actionPrices = pricelistItems[0].actionPrices || {};
            if (Object.keys(actionPrices).length > 0) {
                serviceActionSel.innerHTML = `
                    <option value=\"\">— Pilih Jenis Layanan —</option>
                    <option value=\"P\">Pemeriksaan — ${rupiah(actionPrices.P || actionPrices.p || 0)}</option>
                    <option value=\"S\">Penyetelan/Pembersihan — ${rupiah(actionPrices.S || actionPrices.s || 0)}</option>
                    <option value=\"G\">Penggantian/Perbaikan — ${rupiah(actionPrices.G || actionPrices.g || 0)}</option>
                `;
            } else {
                serviceActionSel.innerHTML = '<option value=\"\">— Tidak ada jenis layanan —</option>';
            }
        } else {
            itemNameSel.innerHTML = '<option value="">— Tidak ada item dalam kategori ini —</option>';
            serviceActionSel.innerHTML = '<option value="">— Tidak ada jenis layanan —</option>';
        }
        
        unitPriceEl.value = '';
        recalc();
    }

    function reindex() {
        [...body.children].forEach((tr, i) => {
            const noEl = tr.querySelector('.row-no');
            if (noEl) noEl.textContent = i + 1;
            tr.querySelector('[name$="[item_type]"]').name = `items[${i}][item_type]`;
            tr.querySelector('[name$="[item_name]"]').name = `items[${i}][item_name]`;
            tr.querySelector('[name$="[quantity]"]').name = `items[${i}][quantity]`;
            tr.querySelector('[name$="[unit_price]"]').name = `items[${i}][unit_price]`;
            tr.querySelector('[name$="[discount]"]').name = `items[${i}][discount]`;
            tr.querySelector('[name$="[is_free]"]').name = `items[${i}][is_free]`;
            tr.querySelector('.free-reason').name = `items[${i}][free_reason]`;
            const catEl = tr.querySelector('[name*="[category]"]'); if (catEl) catEl.name = 'items[' + i + '][category]';
            const actEl = tr.querySelector('[name*="[service_action]"]'); if (actEl) actEl.name = 'items[' + i + '][service_action]';
        });
        if (!body.children.length) document.getElementById('empty-hint').style.display = '';
    }

    function recalc() {
        let totalBiaya = visitFee;
        let totalItemDiscount = 0;

        [...body.children].forEach((tr, i) => {
            const noEl = tr.querySelector('.row-no');
            if (noEl) noEl.textContent = i + 1;

            const free = tr.querySelector('[name$="[is_free]"]')?.checked;
            const qty = Number(tr.querySelector('[name$="[quantity]"]')?.value || 0);
            const price = Number(tr.querySelector('[name$="[unit_price]"]')?.value || 0);
            const disc = Math.max(0, Number(tr.querySelector('[name$="[discount]"]')?.value || 0));

            const gross = qty * price;
            const subtotal = free ? 0 : Math.max(0, gross - disc);
            tr.querySelector('.row-subtotal').textContent = rupiah(subtotal);

            totalBiaya += free ? 0 : gross;
            totalItemDiscount += free ? 0 : disc;

            const reasonInput = tr.querySelector('.free-reason');
            if (reasonInput) {
                if (free) {
                    reasonInput.type = 'text';
                    if (!reasonInput.value) reasonInput.value = 'Tanpa biaya';
                } else {
                    reasonInput.value = '';
                }
            }
        });

        const globalDiscountInput = document.getElementById('global-discount');
        const globalDiscount = globalDiscountInput ? Math.max(0, Number(globalDiscountInput.value || 0)) : 0;
        const totalDiskon = totalItemDiscount + globalDiscount;

        const totalSetelahDiskon = Math.max(0, totalBiaya - totalDiskon);
        const ppn = Math.round(totalSetelahDiskon * 0.11);
        const totalKeseluruhan = totalSetelahDiskon + ppn;

        const elTotalBiaya = document.getElementById('sum-total-biaya');
        const elTotalDiskon = document.getElementById('sum-total-diskon');
        const elSetelahDiskon = document.getElementById('sum-setelah-diskon');
        const elPpn = document.getElementById('sum-ppn');
        const elKeseluruhan = document.getElementById('sum-keseluruhan');

        if (elTotalBiaya) elTotalBiaya.textContent = rupiah(totalBiaya);
        if (elTotalDiskon) elTotalDiskon.textContent = '- ' + rupiah(totalDiskon);
        if (elSetelahDiskon) elSetelahDiskon.textContent = rupiah(totalSetelahDiskon);
        if (elPpn) elPpn.textContent = rupiah(ppn);
        if (elKeseluruhan) elKeseluruhan.textContent = rupiah(totalKeseluruhan);
    }

    existingItems.forEach(item => addRow(item.item_type, item));

    document.getElementById('estimate-form').addEventListener('submit', e => {
        if (!body.children.length) {
            e.preventDefault();
            alert('Tambahkan minimal 1 item estimasi.');
        }
    });
</script>
@endif

@if($serviceOrder->elapsedSeconds() > 0)
<script>
    const elapsedBase = {{ $serviceOrder->elapsedSeconds() }};
    const elapsedStartedAt = Date.now();
    const tick = () => {
        const el = document.getElementById('total-elapsed');
        if (el) el.textContent = new Date((elapsedBase + Math.floor((Date.now() - elapsedStartedAt) / 1000)) * 1000).toISOString().substr(11, 8);
    };
    setInterval(tick, 1000);
</script>
@endif
@endsection
