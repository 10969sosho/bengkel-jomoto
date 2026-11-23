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
        <span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-bold text-lime-800">Tahap 1 · Pemeriksaan Awal SA</span>
    </div>
</div>

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <div class="font-bold">Mohon periksa data input:</div>
        <ul class="mt-1 list-inside list-disc">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('sa.checkin.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($booking)
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
    @endif

    {{-- CARD 1: DATA PELANGGAN & PENANGGUNG JAWAB --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                <span class="grid h-6 w-6 place-items-center rounded-lg bg-amber-400 text-black">
                    <i data-lucide="user" class="h-3.5 w-3.5"></i>
                </span>
                Bagian 1 · Data Pelanggan &amp; Petugas Bengkel
            </div>
            <span class="text-xs text-slate-400">* Terhubung otomatis ke lembar estimasi</span>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Pilih Customer --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Pilih Akun Pelanggan <span class="text-red-500">*</span></label>
                @if($booking)
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ $booking->customer_id }}">
                    <div class="rounded-2xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-800">
                        {{ $booking->customer?->name }} ({{ $booking->customer?->phone ?: 'No HP -' }})
                    </div>
                @else
                    <select name="customer_id" id="customer_select" required
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone ?: $c->whatsapp }}" data-address="{{ $c->address }}" @selected(old('customer_id') == $c->id)>
                                {{ $c->name }} ({{ $c->phone ?: $c->email }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Nama Pelanggan --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Lengkap Pelanggan *</label>
                <input type="text" name="customer_name" id="customer_name" required
                       value="{{ old('customer_name', $booking?->customer?->name) }}"
                       placeholder="Nama pelanggan..."
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Nomor Kontak --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nomor Kontak (HP / WA) *</label>
                <input type="text" name="customer_phone" id="customer_phone" required
                       value="{{ old('customer_phone', $booking?->customer?->phone ?: $booking?->customer?->whatsapp) }}"
                       placeholder="0812xxxxxxx"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Alamat Pelanggan --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat Pelanggan</label>
                <input type="text" name="customer_address" id="customer_address"
                       value="{{ old('customer_address', $booking?->customer?->address ?: $booking?->offsite_address) }}"
                       placeholder="Alamat domisili pelanggan..."
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Nama Service Advisor --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Service Advisor (SA) *</label>
                <input type="text" name="sa_name" required
                       value="{{ old('sa_name', auth()->user()?->name ?? 'Sales Adviser') }}"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Nama Mekanik --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Mekanik / Teknisi *</label>
                <select name="technician_id" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
                    <option value="">— Pilih Mekanik —</option>
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" @selected(old('technician_id', $booking?->technician_id) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nama Manager --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Manager Bengkel *</label>
                <input type="text" name="manager_name" required
                       value="{{ old('manager_name', 'ARTI DEWI') }}"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>
        </div>
    </div>

    {{-- CARD 2: DATA KENDARAAN --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                <span class="grid h-6 w-6 place-items-center rounded-lg bg-sky-400 text-black">
                    <i data-lucide="bike" class="h-3.5 w-3.5"></i>
                </span>
                Bagian 2 · Data Kendaraan &amp; Informasi Kunjungan
            </div>
            <span class="text-xs text-slate-400">* Terhubung ke lembar estimasi &amp; SPK</span>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Pilih Kendaraan Terdaftar --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Pilih Kendaraan Terdaftar <span class="text-red-500">*</span></label>
                @if($booking)
                    <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ $booking->vehicle_id }}">
                    <div class="rounded-2xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-800">
                        {{ $booking->vehicle?->plate_number }} — {{ $booking->vehicle?->fullName() }}
                    </div>
                @else
                    <select name="vehicle_id" id="vehicle_select" required
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
                        <option value="">— Pilih Kendaraan —</option>
                        @foreach($customers as $c)
                            @foreach($c->vehicles as $v)
                                <option value="{{ $v->id }}"
                                        data-customer="{{ $c->id }}"
                                        data-brand="{{ $v->brand }}"
                                        data-model="{{ $v->model }}"
                                        data-plate="{{ $v->plate_number }}"
                                        data-year="{{ $v->year }}"
                                        data-serial="{{ $v->serial_number ?: $v->engine_number }}"
                                        data-chassis="{{ $v->chassis_number }}"
                                        data-transmission="{{ $v->transmission ?: 'Otomatis / CVT' }}"
                                        data-engine="{{ $v->engine_type ?: ($v->fuel_type === 'electric' ? 'Listrik BLDC' : 'Bensin 4-Tak') }}"
                                        data-odo="{{ $v->last_odometer }}"
                                        @selected(old('vehicle_id') == $v->id)>
                                    {{ $v->plate_number }} — {{ $v->fullName() }} ({{ $c->name }})
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Merk --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Merk *</label>
                <input type="text" name="vehicle_brand" id="vehicle_brand" required
                       value="{{ old('vehicle_brand', $booking?->vehicle?->brand) }}"
                       placeholder="Honda, Yamaha, Jomoto, dll"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Tipe / Model --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Tipe / Model *</label>
                <input type="text" name="vehicle_model" id="vehicle_model" required
                       value="{{ old('vehicle_model', $booking?->vehicle?->model) }}"
                       placeholder="Vario 160, NMAX, EV One, dll"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Nomor Polisi --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nomor Polisi *</label>
                <input type="text" name="plate_number" id="plate_number" required
                       value="{{ old('plate_number', $booking?->vehicle?->plate_number) }}"
                       placeholder="L 1234 ABC"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm uppercase outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- No Seri Kendaraan --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">No. Seri Kendaraan / No. Mesin</label>
                <input type="text" name="vehicle_serial_number" id="vehicle_serial_number"
                       value="{{ old('vehicle_serial_number', $booking?->vehicle?->serial_number ?: $booking?->vehicle?->engine_number) }}"
                       placeholder="Nomor seri mesin..."
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Nomor Rangka --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nomor Rangka</label>
                <input type="text" name="vehicle_chassis_number" id="vehicle_chassis_number"
                       value="{{ old('vehicle_chassis_number', $booking?->vehicle?->chassis_number) }}"
                       placeholder="Nomor rangka chassis..."
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Tahun Rakitan --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Tahun Rakitan *</label>
                <input type="number" name="vehicle_year" id="vehicle_year" required min="1950" max="{{ now()->year + 1 }}"
                       value="{{ old('vehicle_year', $booking?->vehicle?->year ?? now()->year) }}"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Transmisi --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Transmisi *</label>
                <select name="vehicle_transmission" id="vehicle_transmission" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
                    <option value="Otomatis / CVT" @selected(old('vehicle_transmission', $booking?->vehicle?->transmission) === 'Otomatis / CVT')>Otomatis / CVT (Matic)</option>
                    <option value="Manual / Kopling" @selected(old('vehicle_transmission', $booking?->vehicle?->transmission) === 'Manual / Kopling')>Manual / Kopling</option>
                    <option value="Semi-Otomatis (Bebek)" @selected(old('vehicle_transmission', $booking?->vehicle?->transmission) === 'Semi-Otomatis (Bebek)')>Semi-Otomatis (Bebek)</option>
                    <option value="Direct Drive (Listrik)" @selected(old('vehicle_transmission', $booking?->vehicle?->transmission) === 'Direct Drive (Listrik)')>Direct Drive (Motor Listrik)</option>
                </select>
            </div>

            {{-- Jenis Mesin & CC --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Jenis Mesin *</label>
                <input type="text" name="vehicle_engine_type" id="vehicle_engine_type" required
                       value="{{ old('vehicle_engine_type', $booking?->vehicle?->engine_type ?: ($booking?->vehicle?->fuel_type === 'electric' ? 'Listrik BLDC / PMSM' : 'Bensin 4-Tak')) }}"
                       placeholder="Contoh: Bensin 4-Tak SOHC / Listrik BLDC"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Kapasitas Mesin (CC) --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kapasitas Mesin (CC)</label>
                <input type="text" name="vehicle_engine_cc" id="vehicle_engine_cc"
                       value="{{ old('vehicle_engine_cc', $booking?->vehicle?->engine_cc) }}"
                       placeholder="Contoh: 125, 150, 250"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Tanggal Kunjungan --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Tanggal Kunjungan *</label>
                <input type="date" name="visit_date" required
                       value="{{ old('visit_date', now()->toDateString()) }}"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Odometer Masuk --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Odometer Masuk (KM) *</label>
                <input type="number" name="odometer" id="odometer_input" min="0" required
                       value="{{ old('odometer', $booking?->odometer ?? $booking?->vehicle?->last_odometer ?? 0) }}"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm outline-none transition focus:border-lime-400 focus:bg-white">
            </div>

            {{-- Level Bahan Bakar --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Level Bahan Bakar / Baterai *</label>
                <select name="fuel_level" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-semibold outline-none transition focus:border-lime-400 focus:bg-white">
                    <option value="E" @selected(old('fuel_level') === 'E')>E (Empty / Kritis)</option>
                    <option value="1/4" @selected(old('fuel_level') === '1/4')>1/4 Tangki / 25%</option>
                    <option value="1/2" @selected(old('fuel_level', '1/2') === '1/2')>1/2 Tangki / 50%</option>
                    <option value="3/4" @selected(old('fuel_level') === '3/4')>3/4 Tangki / 75%</option>
                    <option value="F" @selected(old('fuel_level') === 'F')>F (Full / 100%)</option>
                </select>
            </div>

            {{-- Tipe Lokasi Servis --}}
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Lokasi Pelayanan *</label>
                <select name="location_type" id="location_type" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
                    <option value="onsite" @selected(old('location_type', $booking?->location_type ?? 'onsite') === 'onsite')>Servis di Bengkel (In-Shop)</option>
                    <option value="offsite" @selected(old('location_type', $booking?->location_type) === 'offsite')>Home Service (Panggilan)</option>
                </select>
            </div>
        </div>

        {{-- Alamat Home Service --}}
        <div id="offsite_box" class="mt-4 {{ old('location_type', $booking?->location_type) === 'offsite' ? '' : 'hidden' }}">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat Lengkap Home Service (Panggilan)</label>
            <input type="text" name="offsite_address" value="{{ old('offsite_address', $booking?->offsite_address) }}"
                   placeholder="Alamat lokasi motor untuk home service..."
                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
        </div>

        {{-- Upload Foto Odo & BBM --}}
        <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-3.5">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">
                    <i data-lucide="camera" class="inline h-3.5 w-3.5 text-indigo-600"></i> Foto Odometer Masuk
                </label>
                <div class="flex items-center gap-2">
                    <input type="file" name="odometer_photo" id="odo-file" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                    <input type="file" name="odometer_photo" id="odo-camera" accept="image/*" capture="environment" class="hidden" onchange="document.getElementById('odo-file').files = this.files;">
                    <button type="button" onclick="document.getElementById('odo-camera').click()"
                            class="inline-flex shrink-0 items-center gap-1 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:bg-slate-100">
                        <i data-lucide="camera" class="h-3.5 w-3.5 text-indigo-600"></i> Kamera
                    </button>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-3.5">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">
                    <i data-lucide="fuel" class="inline h-3.5 w-3.5 text-amber-600"></i> Foto Indikator Bahan Bakar / Baterai
                </label>
                <div class="flex items-center gap-2">
                    <input type="file" name="fuel_photo" id="fuel-file" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                    <input type="file" name="fuel_photo" id="fuel-camera" accept="image/*" capture="environment" class="hidden" onchange="document.getElementById('fuel-file').files = this.files;">
                    <button type="button" onclick="document.getElementById('fuel-camera').click()"
                            class="inline-flex shrink-0 items-center gap-1 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:bg-slate-100">
                        <i data-lucide="camera" class="h-3.5 w-3.5 text-indigo-600"></i> Kamera
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3: KELUHAN & DIAGNOSA SA --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-500">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-400 text-white">
                <i data-lucide="message-square" class="h-3.5 w-3.5"></i>
            </span>
            Bagian 3 · Keluhan Pelanggan &amp; Analisa Awal SA
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kategori Keluhan *</label>
                <select name="complaint_category" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-lime-400 focus:bg-white">
                    <option value="Servis Berkala &amp; Ganti Oli">Servis Berkala &amp; Ganti Oli</option>
                    <option value="Mesin &amp; Transmisi">Mesin &amp; Transmisi (CVT/Mesin Kasar/Mogok)</option>
                    <option value="Pengereman &amp; Kaki-kaki">Pengereman &amp; Kaki-kaki (Rem Blong/Bunyi/Ban)</option>
                    <option value="Kelistrikan &amp; Baterai">Kelistrikan &amp; Baterai (Mati Total/Lampu/Starter)</option>
                    <option value="Bodi &amp; Eksterior">Bodi &amp; Eksterior (Kecelakaan/Cat/Patah)</option>
                    <option value="Klaim Garansi / Kupon Gratis">Klaim Garansi / Kupon Gratis</option>
                    <option value="Lainnya">Lainnya / General Inspection</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Deskripsi Keluhan Pelanggan *</label>
                <textarea name="customer_complaint" rows="2" required
                          placeholder="Jelaskan detail kendala atau permintaan servis pelanggan..."
                          class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none transition focus:border-lime-400 focus:bg-white">{{ old('customer_complaint', $booking?->customer_complaint) }}</textarea>
            </div>
            <div class="sm:col-span-3">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Catatan / Analisa Awal Sales Adviser (SA)</label>
                <textarea name="sa_diagnosis" rows="2"
                          placeholder="Analisa awal SA sebelum ditinjau teknisi..."
                          class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm outline-none transition focus:border-lime-400 focus:bg-white">{{ old('sa_diagnosis', $booking?->sa_diagnosis) }}</textarea>
            </div>
        </div>
    </div>

    {{-- CARD 4: CHECKLIST DOKUMEN & PERLENGKAPAN --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wider text-slate-500">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-emerald-400 text-black">
                <i data-lucide="folder-check" class="h-3.5 w-3.5"></i>
            </span>
            Bagian 4 · Checklist Dokumen &amp; Perlengkapan Kendaraan
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

    {{-- CARD 5: CHECKLIST EKSTERIOR & UPLOAD FOTO EKSTERIOR --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                <span class="grid h-6 w-6 place-items-center rounded-lg bg-lime-300 text-black">
                    <i data-lucide="list-checks" class="h-3.5 w-3.5"></i>
                </span>
                Bagian 5 · Checklist Eksterior &amp; Foto Kondisi Fisik Kendaraan
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-emerald-800">
                    <b class="rounded bg-emerald-600 px-1 text-white">G</b> Baik (Good)
                </span>
                <span class="inline-flex items-center gap-1 rounded-lg bg-rose-100 px-2.5 py-1 text-rose-800">
                    <b class="rounded bg-rose-600 px-1 text-white">R</b> Rusak / Lecet / Perlu Perbaikan
                </span>
            </div>
        </div>

        {{-- Upload Foto Eksterior --}}
        <div class="mt-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-700">
                        <i data-lucide="images" class="inline h-4 w-4 mr-1 text-indigo-600"></i> Masukkan Foto Kondisi Eksterior / Kerusakan Kendaraan
                    </label>
                    <p class="text-xs text-slate-500">Pilih dari galeri atau ambil langsung dengan kamera (bodi depan, belakang, samping, baret/penyok).</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="file" name="exterior_photos[]" id="ext-photos-file" multiple accept="image/*"
                           class="text-xs text-slate-500 file:mr-2 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-lime-300">
                    <input type="file" name="exterior_photos[]" id="ext-photos-cam" accept="image/*" capture="environment" class="hidden" onchange="document.getElementById('ext-photos-file').files = this.files;">
                    <button type="button" onclick="document.getElementById('ext-photos-cam').click()"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-100">
                        <i data-lucide="camera" class="h-3.5 w-3.5 text-indigo-600"></i> Kamera
                    </button>
                </div>
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
            <i data-lucide="check" class="h-4 w-4"></i> Simpan Pemeriksaan Awal SA &amp; Teruskan ke Mekanik
        </button>
    </div>
</form>

<script>
    const customerSelect = document.getElementById('customer_select');
    const vehicleSelect = document.getElementById('vehicle_select');
    const locationTypeSelect = document.getElementById('location_type');
    const offsiteBox = document.getElementById('offsite_box');

    locationTypeSelect?.addEventListener('change', (e) => {
        if (e.target.value === 'offsite') {
            offsiteBox.classList.remove('hidden');
        } else {
            offsiteBox.classList.add('hidden');
        }
    });

    customerSelect?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected) {
            document.getElementById('customer_name').value = selected.dataset.name || '';
            document.getElementById('customer_phone').value = selected.dataset.phone || '';
            document.getElementById('customer_address').value = selected.dataset.address || '';

            // Filter vehicles
            const custId = selected.value;
            if (vehicleSelect) {
                let firstMatch = null;
                Array.from(vehicleSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    const match = opt.dataset.customer === custId;
                    opt.style.display = match ? '' : 'none';
                    if (match && !firstMatch) firstMatch = opt;
                });
                if (firstMatch) {
                    vehicleSelect.value = firstMatch.value;
                    vehicleSelect.dispatchEvent(new Event('change'));
                }
            }
        }
    });

    vehicleSelect?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('vehicle_brand').value = opt.dataset.brand || '';
            document.getElementById('vehicle_model').value = opt.dataset.model || '';
            document.getElementById('plate_number').value = opt.dataset.plate || '';
            document.getElementById('vehicle_year').value = opt.dataset.year || new Date().getFullYear();
            document.getElementById('vehicle_serial_number').value = opt.dataset.serial || '';
            document.getElementById('vehicle_chassis_number').value = opt.dataset.chassis || '';
            if (opt.dataset.transmission) document.getElementById('vehicle_transmission').value = opt.dataset.transmission;
            if (opt.dataset.engine) document.getElementById('vehicle_engine_type').value = opt.dataset.engine;
            if (opt.dataset.odo) document.getElementById('odometer_input').value = opt.dataset.odo;
        }
    });
</script>
@endsection
