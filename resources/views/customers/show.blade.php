@extends('layouts.app')

@section('title', $customer->name)
@section('page_title', 'Detail Customer')
@section('page_icon', 'user-round')

@section('content')
<a href="{{ route('sa.customers.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-slate-800">← Kembali ke pelanggan</a>

<section class="mb-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $customer->email }}</p>
        </div>
        <div class="text-sm text-slate-600">Telepon: <b>{{ $customer->phone ?: '—' }}</b><br>WhatsApp: <b>{{ $customer->whatsapp ?: '—' }}</b></div>
    </div>
</section>

<section class="mb-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between"><h2 class="font-bold text-slate-900">Kendaraan ({{ $customer->vehicles->count() }})</h2></div>
    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($customer->vehicles as $vehicle)
            <form method="POST" action="{{ route('sa.vehicles.update', $vehicle) }}" class="rounded-2xl border border-slate-200 p-4">
                @csrf
                @method('PUT')
                <div class="mb-3 flex items-center justify-between"><b class="text-slate-800">{{ $vehicle->plate_number }}</b><span class="text-xs text-slate-400">{{ $vehicle->fuelLabel() }}</span></div>
                <div class="grid gap-2 sm:grid-cols-2">
                    <input name="brand" value="{{ $vehicle->brand }}" required placeholder="Merk" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <input name="model" value="{{ $vehicle->model }}" required placeholder="Model" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <input name="plate_number" value="{{ $vehicle->plate_number }}" required placeholder="Plat nomor" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <input type="number" name="year" value="{{ $vehicle->year }}" min="1950" max="{{ now()->year + 1 }}" required placeholder="Tahun" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <input name="engine_number" value="{{ $vehicle->engine_number }}" placeholder="No. mesin" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <input name="chassis_number" value="{{ $vehicle->chassis_number }}" placeholder="No. rangka" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <select name="fuel_type" required class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm"><option value="gasoline" @selected($vehicle->fuel_type === 'gasoline')>Bensin</option><option value="electric" @selected($vehicle->fuel_type === 'electric')>Listrik</option></select>
                    <input type="number" name="last_odometer" value="{{ $vehicle->last_odometer }}" min="0" required placeholder="Odometer" class="rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                </div>
                <div class="mt-3 text-right"><button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">Simpan Perubahan</button></div>
            </form>
        @empty
            <p class="text-sm text-slate-400">Belum ada kendaraan.</p>
        @endforelse
    </div>

    <details class="mt-5 rounded-2xl border border-lime-200 bg-lime-50 p-4">
        <summary class="cursor-pointer font-bold text-slate-800">+ Tambah Motor</summary>
        <form method="POST" action="{{ route('sa.vehicles.store', $customer) }}" class="mt-4 grid gap-2 sm:grid-cols-2">
            @csrf
            <input name="brand" required placeholder="Merk" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <input name="model" required placeholder="Model" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <input name="plate_number" required placeholder="Plat nomor" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <input type="number" name="year" min="1950" max="{{ now()->year + 1 }}" required placeholder="Tahun" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <input name="engine_number" placeholder="No. mesin" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <input name="chassis_number" placeholder="No. rangka" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <select name="fuel_type" required class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm"><option value="gasoline">Bensin</option><option value="electric">Listrik</option></select>
            <input type="number" name="last_odometer" min="0" required placeholder="Odometer" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-sm">
            <button class="rounded-lg bg-lime-300 px-3 py-2 text-sm font-bold text-black sm:col-span-2">Simpan Motor</button>
        </form>
    </details>
</section>

<section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Riwayat Servis</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"><tr><th class="px-5 py-3">Tanggal / WO</th><th class="px-5 py-3">Kendaraan</th><th class="px-5 py-3">Keluhan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Invoice</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr>
                        <td class="px-5 py-3.5"><a class="font-mono font-bold text-slate-800 hover:underline" href="{{ route('sa.show', $order) }}">{{ $order->wo_number }}</a><div class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y') }}</div></td>
                        <td class="px-5 py-3.5">{{ $order->vehicle?->fullName() }}<div class="text-xs text-slate-400">{{ $order->vehicle?->plate_number }}</div></td>
                        <td class="max-w-sm px-5 py-3.5 text-slate-600">{{ $order->customer_complaint }}</td>
                        <td class="px-5 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="px-5 py-3.5">{{ $order->invoice?->invoice_number ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat servis.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
