@extends('layouts.app')

@section('title', 'Pemeriksaan Awal (Check-in) Kendaraan')
@section('page_title', 'Pemeriksaan Awal (Check-in)')
@section('page_icon', 'clipboard-check')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ $booking ? route('sa.bookings.show', $booking) : route('sa.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali
    </a>
    <div class="flex items-center gap-2">
        <span class="rounded-xl border border-slate-200 bg-white px-3 py-1 font-mono text-xs font-bold text-slate-700 shadow-sm">
            No: {{ $nextInspectionNumber }}
        </span>
        <span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-bold text-lime-800">Tahap 1 · Check-in</span>
    </div>
</div>

<form method="POST" action="{{ route('sa.checkin.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($booking)
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
    @endif

    {{-- Card 1: Data Pelanggan & Kendaraan --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-amber-400 text-black">
                <i data-lucide="user" class="h-3.5 w-3.5"></i>
            </span>
            Bagian 1 · Data Pelanggan &amp; Kendaraan (Check-in Standard)
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Nama Pengelola / Bengkel</label>
                <input type="text" name="manager_name" value="{{ old('manager_name', 'ARTI DEWI') }}" required
                       class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Pelanggan <span class="text-red-500">*</span></label>
                @if($booking)
                    <input type="hidden" name="customer_id" value="{{ $booking->customer_id }}">
                    <div class="rounded-2xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-800">
                        {{ $booking->customer?->name }} ({{ $booking->customer?->phone ?: 'No HP -' }})
                    </div>
                @else
                    <select name="customer_id" id="customer_select" required
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->phone ?: $c->email }})</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Kendaraan <span class="text-red-500">*</span></label>
                @if($booking)
                    <input type="hidden" name="vehicle_id" value="{{ $booking->vehicle_id }}">
                    <div class="rounded-2xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-800">
                        {{ $booking->vehicle?->fullName() }} · <span class="font-mono text-lime-800">{{ $booking->vehicle?->plate_number }}</span>
                    </div>
                @else
                    <select name="vehicle_id" id="vehicle_select" required
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih Kendaraan —</option>
                        @foreach($customers as $c)
                            @foreach($c->vehicles as $v)
                                <option value="{{ $v->id }}" data-customer="{{ $c->id }}" @selected(old('vehicle_id') == $v->id)>
                                    {{ $v->plate_number }} — {{ $v->fullName() }} ({{ $c->name }})
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Teknisi / Mekanik Pelaksana <span class="text-red-500">*</span></label>
                <select name="technician_id" required
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                    <option value="">— Pilih Teknisi —</option>
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" @selected(old('technician_id', $booking?->technician_id) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Odometer Masuk (KM) <span class="text-red-500">*</span></label>
                <input type="number" name="odometer" min="0" required
                       value="{{ old('odometer', $booking?->odometer ?? $booking?->vehicle?->last_odometer ?? 0) }}"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Level Bahan Bakar <span class="text-red-500">*</span></label>
                <select name="fuel_level" required
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-semibold outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                    <option value="E" @selected(old('fuel_level') === 'E')>E (Empty)</option>
                    <option value="1/4" @selected(old('fuel_level') === '1/4')>1/4 Tangki</option>
                    <option value="1/2" @selected(old('fuel_level', '1/2') === '1/2')>1/2 Tangki</option>
                    <option value="3/4" @selected(old('fuel_level') === '3/4')>3/4 Tangki</option>
                    <option value="F" @selected(old('fuel_level') === 'F')>F (Full)</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Tipe Lokasi Servis</label>
                <select name="location_type" id="location_type" required
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                    <option value="onsite" @selected(old('location_type', $booking?->location_type ?? 'onsite') === 'onsite')>Servis di Bengkel</option>
                    <option value="offsite" @selected(old('location_type', $booking?->location_type) === 'offsite')>Home Service (Panggilan)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Alamat Home Service (jika offsite)</label>
                <input type="text" name="offsite_address" value="{{ old('offsite_address', $booking?->offsite_address) }}"
                       placeholder="Alamat lengkap lokasi motor..."
                       class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
            </div>
        </div>

        {{-- Upload Foto Odo & BBM --}}
        <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-3.5">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">
                    <i data-lucide="camera" class="inline h-3.5 w-3.5"></i> Foto Odometer Masuk
                </label>
                <input type="file" name="odometer_photo" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-2 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-3.5">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">
                    <i data-lucide="fuel" class="inline h-3.5 w-3.5"></i> Foto Indikator Bahan Bakar
                </label>
                <input type="file" name="fuel_photo" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-2 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
            </div>
        </div>
    </div>

    {{-- Card 2: Keluhan & Analisis Awal SA --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-sky-400 text-black">
                <i data-lucide="message-square" class="h-3.5 w-3.5"></i>
            </span>
            Bagian 2 · Keluhan Pelanggan &amp; Catatan Awal SA
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Deskripsi Keluhan Pelanggan <span class="text-red-500">*</span></label>
                <textarea name="customer_complaint" rows="3" required
                          placeholder="Keluhan yang dirasakan pelanggan saat berkendara..."
                          class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">{{ old('customer_complaint', $booking?->customer_complaint) }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Catatan / Analisa Awal Sales Adviser</label>
                <textarea name="sa_diagnosis" rows="3"
                          placeholder="Analisa awal SA sebelum ditinjau oleh mekanik..."
                          class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">{{ old('sa_diagnosis', $booking?->sa_diagnosis) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Card 3: Dokumen & Perlengkapan --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-400 text-white">
                <i data-lucide="folder-check" class="h-3.5 w-3.5"></i>
            </span>
            Bagian 3 · Checklist Dokumen &amp; Perlengkapan Kendaraan
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($documentsChecklist as $docKey => $docLabel)
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3.5">
                    <div class="text-xs font-bold text-slate-700">{{ $docLabel }}</div>
                    <div class="mt-2.5 flex items-center gap-4 text-xs font-semibold">
                        <label class="inline-flex cursor-pointer items-center gap-1.5 text-emerald-700">
                            <input type="radio" name="documents[{{ $docKey }}]" value="ada" checked class="text-emerald-600 focus:ring-emerald-400">
                            Ada
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-1.5 text-slate-500">
                            <input type="radio" name="documents[{{ $docKey }}]" value="tidak" class="text-slate-400 focus:ring-slate-300">
                            Tidak Ada
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Card 4: Kondisi Fisik / Eksterior Kendaraan (BENGKEL - checkin.pdf) --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <span class="grid h-6 w-6 place-items-center rounded-lg bg-lime-300 text-black">
                    <i data-lucide="list-checks" class="h-3.5 w-3.5"></i>
                </span>
                Bagian 4 · Kondisi Fisik &amp; Eksterior Kendaraan
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-emerald-800">
                    <b class="rounded bg-emerald-600 px-1 text-white">G</b> Baik (Good)
                </span>
                <span class="inline-flex items-center gap-1 rounded-lg bg-rose-100 px-2.5 py-1 text-rose-800">
                    <b class="rounded bg-rose-600 px-1 text-white">R</b> Rusak / Perlu Perbaikan
                </span>
            </div>
        </div>

        <div class="mt-5 space-y-5">
            @foreach($exteriorSections as $sectionName => $items)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                    <h3 class="mb-3 text-xs font-extrabold uppercase tracking-wider text-slate-700">{{ $sectionName }}</h3>
                    <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @foreach($items as $itemKey => $itemLabel)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/70 bg-white px-3 py-2 text-xs">
                                <span class="font-medium text-slate-700">{{ $itemLabel }}</span>
                                <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                                    <label class="cursor-pointer rounded-md px-2 py-0.5 font-bold transition has-[:checked]:bg-emerald-500 has-[:checked]:text-white">
                                        <input type="radio" name="exterior[{{ $itemKey }}]" value="G" checked class="hidden">
                                        G
                                    </label>
                                    <label class="cursor-pointer rounded-md px-2 py-0.5 font-bold transition has-[:checked]:bg-rose-500 has-[:checked]:text-white">
                                        <input type="radio" name="exterior[{{ $itemKey }}]" value="R" class="hidden">
                                        R
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Submit action bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <a href="{{ $booking ? route('sa.bookings.show', $booking) : route('sa.index') }}"
           class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100">
            Batal
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-bold text-lime-300 shadow-md transition hover:bg-slate-800 active:scale-[.98]">
            <i data-lucide="check" class="h-4 w-4"></i> Simpan Pemeriksaan Awal &amp; Teruskan ke Mekanik
        </button>
    </div>
</form>
@endsection
