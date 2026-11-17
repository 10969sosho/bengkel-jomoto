@extends('layouts.app')

@section('title', 'Detail Booking '.$booking->wo_number)
@section('page_title', 'Detail Booking Service')
@section('page_icon', 'calendar-check')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('sa.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke Dashboard SA
    </a>
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Booking Masuk</span>
        <span class="font-mono text-xs font-semibold text-slate-400">Dibuat {{ $booking->created_at->format('d M Y H:i') }}</span>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Left 2 cols: Main details --}}
    <div class="space-y-6 lg:col-span-2">
        {{-- Card Keluhan & Layanan --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="clipboard-list" class="h-4 w-4 text-amber-500"></i> Keluhan &amp; Permintaan Layanan
            </div>

            <div class="mt-4">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Keluhan Pelanggan</div>
                <div class="mt-1 rounded-2xl bg-amber-50/70 p-4 text-sm font-medium text-amber-950">
                    “{{ $booking->customer_complaint ?: 'Tidak ada keluhan tertulis.' }}”
                </div>
            </div>

            <div class="mt-5">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Layanan yang Diminta</div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse($booking->requestedServiceLabels($pricelistNames) as $serviceLabel)
                        <span class="inline-flex items-center gap-1 rounded-xl bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                            <i data-lucide="wrench" class="h-3 w-3"></i> {{ $serviceLabel }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">Tidak ada layanan spesifik yang dipilih saat booking.</span>
                    @endforelse
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Tipe Layanan / Lokasi</div>
                    <div class="mt-1 font-bold text-slate-800">
                        @if($booking->location_type === 'offsite')
                            <span class="inline-flex items-center gap-1 text-emerald-700"><i data-lucide="home" class="h-4 w-4"></i> Home Service</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-sky-700"><i data-lucide="store" class="h-4 w-4"></i> Servis di Bengkel</span>
                        @endif
                    </div>
                    @if($booking->location_type === 'offsite')
                        <div class="mt-2 text-xs text-slate-600"><b>Alamat:</b> {{ $booking->offsite_address }}</div>
                        @if($booking->location_notes)<div class="mt-1 text-xs text-slate-500">Patokan: {{ $booking->location_notes }}</div>@endif
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Odometer Tercatat</div>
                    <div class="mt-1 font-mono text-lg font-extrabold text-slate-900">
                        {{ number_format($booking->odometer ?? $booking->vehicle?->last_odometer ?? 0) }} KM
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Kupon milestone: {{ $booking->coupon_milestone ? number_format($booking->coupon_milestone).' KM' : '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Card Foto STNK --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="image" class="h-4 w-4 text-sky-500"></i> Dokumen Pendukung (Foto STNK)
            </div>
            <div class="mt-4">
                @if($booking->stnkUrl())
                    <a href="{{ $booking->stnkUrl() }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <img src="{{ $booking->stnkUrl() }}" alt="Foto STNK" class="max-h-72 w-full object-contain p-2 transition group-hover:scale-[1.01]">
                        <div class="border-t border-slate-200 bg-white px-4 py-2.5 text-center text-xs font-bold text-slate-600 group-hover:text-sky-600">
                            <i data-lucide="external-link" class="inline h-3.5 w-3.5"></i> Klik untuk melihat foto ukuran penuh
                        </div>
                    </a>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                        Customer tidak mengunggah foto STNK saat booking.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right 1 col: Customer & Vehicle info + Action box --}}
    <div class="space-y-6 lg:col-span-1">
        {{-- Action card --}}
        <div class="rounded-3xl border-2 border-amber-300 bg-amber-50/70 p-5 shadow-sm">
            <h3 class="font-extrabold text-amber-950">Tindakan Sales Adviser</h3>
            <p class="mt-1 text-xs text-amber-800">
                Lanjutkan booking ini ke tahap Pemeriksaan Awal (Check-in) kendaraan mengacu pada form standar bengkel.
            </p>
            <div class="mt-4 space-y-2">
                <a href="{{ route('sa.checkin.create', ['booking_id' => $booking->id]) }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600 active:scale-[.98]">
                    <i data-lucide="clipboard-check" class="h-4 w-4"></i> Lanjutkan Pemeriksaan Awal
                </a>
                <a href="{{ route('sa.index') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    Kembali ke Antrian
                </a>
            </div>
        </div>

        {{-- Customer details --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="user" class="h-4 w-4"></i> Informasi Pelanggan
            </div>
            <div class="mt-3 text-sm">
                <div class="font-bold text-slate-900">{{ $booking->customer?->name }}</div>
                <div class="mt-0.5 text-xs text-slate-500">{{ $booking->customer?->email }}</div>
                <div class="mt-2 text-xs text-slate-600">
                    <b>No. Telepon / HP:</b> {{ $booking->customer?->phone ?: '—' }}
                </div>
                @if($booking->customer?->whatsappNumber())
                    <a href="https://wa.me/{{ $booking->customer->whatsappNumber() }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                        <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Hubungi WhatsApp Customer
                    </a>
                @endif
            </div>
        </div>

        {{-- Vehicle details --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="bike" class="h-4 w-4"></i> Detail Kendaraan
            </div>
            <div class="mt-3 space-y-2 text-sm">
                <div>
                    <div class="text-xs text-slate-400">Merk &amp; Tipe Model</div>
                    <div class="font-bold text-slate-900">{{ $booking->vehicle?->fullName() }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-400">Nomor Polisi</div>
                    <span class="inline-block rounded-lg bg-slate-900 px-2.5 py-1 font-mono text-xs font-bold text-lime-300">
                        {{ $booking->vehicle?->plate_number }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                    <div>
                        <span class="text-slate-400">Tahun:</span>
                        <b class="text-slate-700">{{ $booking->vehicle?->year }}</b>
                    </div>
                    <div>
                        <span class="text-slate-400">Bahan Bakar:</span>
                        <b class="text-slate-700">{{ $booking->vehicle?->fuelLabel() }}</b>
                    </div>
                    <div>
                        <span class="text-slate-400">No. Rangka:</span>
                        <b class="text-slate-700">{{ $booking->vehicle?->chassis_number ?: '—' }}</b>
                    </div>
                    <div>
                        <span class="text-slate-400">No. Mesin:</span>
                        <b class="text-slate-700">{{ $booking->vehicle?->engine_number ?: '—' }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
