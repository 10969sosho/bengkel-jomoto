@extends('layouts.app')

@section('title', 'Daftar Kendaraan')
@section('page_title', 'Daftar Kendaraan Saya')
@section('page_icon', 'bike')

@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Daftar Kendaraan</h1>
        <p class="text-sm text-slate-500">Motor yang terdaftar di akun Anda beserta odometer terakhir.</p>
    </div>
    <a href="{{ route('customer.booking.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-lime-300 px-4 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-lime-400 active:scale-[.98]">
        <i data-lucide="calendar-plus" class="h-4 w-4"></i> Booking Servis
    </a>
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="lg:col-span-2">
        <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
            <i data-lucide="bike" class="h-4 w-4"></i> Motor Saya
        </h2>
        <div class="grid gap-3 sm:grid-cols-2">
            @forelse($vehicles as $vehicle)
                <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black tracking-wider text-lime-300">{{ $vehicle->plate_number }}</span>
                        <span class="rounded-full bg-{{ $vehicle->fuel_type === 'electric' ? 'lime' : 'sky' }}-50 px-2.5 py-1 text-[11px] font-bold text-{{ $vehicle->fuel_type === 'electric' ? 'lime-700' : 'sky-700' }}">
                            {{ $vehicle->fuelLabel() }}
                        </span>
                    </div>
                    <div class="mt-2.5 font-bold text-slate-900">{{ $vehicle->fullName() }}</div>
                    <div class="mt-1 text-xs text-slate-500">
                        Tahun {{ $vehicle->year }} @if($vehicle->engine_cc)· {{ $vehicle->engine_cc }} CC @endif· {{ number_format($vehicle->last_odometer) }} KM
                    </div>
                    @if($vehicle->engine_number || $vehicle->chassis_number)
                        <div class="mt-2 space-y-0.5 text-[11px] text-slate-400">
                            @if($vehicle->engine_number)<div>No. Mesin: {{ $vehicle->engine_number }}</div>@endif
                            @if($vehicle->chassis_number)<div>No. Rangka: {{ $vehicle->chassis_number }}</div>@endif
                        </div>
                    @endif
                    <a href="{{ route('customer.booking.create', ['vehicle_id' => $vehicle->id]) }}"
                       class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-100 active:scale-[.98]">
                        <i data-lucide="calendar-plus" class="h-3.5 w-3.5"></i> Booking Servis Motor Ini
                    </a>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-400 sm:col-span-2">
                    Belum ada motor terdaftar. Daftarkan kendaraan lewat formulir di samping.
                </div>
            @endforelse
        </div>
    </section>

    <section>
        <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
            <i data-lucide="file-plus-2" class="h-4 w-4"></i> Kendaraan Baru
        </h2>
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('customer.vehicles.store') }}" class="grid gap-3">
                @csrf
                <input name="brand" required placeholder="Merk" value="{{ old('brand') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <input name="model" required placeholder="Model" value="{{ old('model') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <input name="plate_number" required placeholder="Plat nomor" value="{{ old('plate_number') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <input name="engine_number" placeholder="Nomor mesin" value="{{ old('engine_number') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <input name="chassis_number" placeholder="Nomor rangka" value="{{ old('chassis_number') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <input name="engine_cc" placeholder="Kapasitas Mesin (CC), cth: 150" value="{{ old('engine_cc') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <select name="fuel_type" required class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="gasoline" @selected(old('fuel_type') === 'gasoline')>Bensin</option>
                    <option value="electric" @selected(old('fuel_type') === 'electric')>Listrik</option>
                </select>
                <input type="number" min="0" name="last_odometer" required placeholder="KM awal" value="{{ old('last_odometer') }}"
                       class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-lime-300 transition hover:bg-slate-700 active:scale-[.98]">
                    Simpan Kendaraan
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
