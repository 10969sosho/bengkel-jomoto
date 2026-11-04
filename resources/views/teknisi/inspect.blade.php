@extends('layouts.app')

@section('title', 'Lembar Pemeriksaan')
@section('page_title', 'Lembar Pemeriksaan')
@section('page_icon', 'clipboard-check')

@section('content')
<a href="{{ route('teknisi.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
    <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke tugas saya
</a>

<div class="mx-auto mt-4 max-w-3xl">
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="min-w-0">
                <h1 class="text-lg font-bold text-slate-900">Lembar Pemeriksaan Sepeda Motor</h1>
                <p class="mt-0.5 text-sm text-slate-500">
                    <span class="font-mono font-bold text-slate-700">{{ $order->wo_number }}</span> ·
                    <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }})
                </p>
            </div>
            <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm">
            <div class="flex items-center gap-2 font-semibold text-slate-700">
                <i data-lucide="message-square-quote" class="h-4 w-4 text-slate-400"></i> Keluhan Customer
            </div>
            <p class="mt-1.5 text-slate-600">{{ $order->customer_complaint }}</p>
            <div class="mt-2.5 flex flex-wrap gap-3 text-xs font-medium text-slate-400">
                <span class="rounded-lg bg-white px-2 py-1">Odo: {{ number_format($order->vehicle?->last_odometer ?? 0) }} KM</span>
                <span class="rounded-lg bg-white px-2 py-1">{{ $order->vehicle?->fuelLabel() }}</span>
                <span class="rounded-lg bg-white px-2 py-1">Tahun {{ $order->vehicle?->year }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('teknisi.inspect.save', $order) }}" class="mt-5">
            @csrf

            <div class="mb-3 flex items-center gap-2">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                    <i data-lucide="list-checks" class="h-4 w-4"></i>
                </span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Checklist Pemeriksaan</h2>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($checklistItems as $key => $label)
                    @php($current = $order->inspection && is_array($order->inspection->checklist)
                        ? (collect($order->inspection->checklist)->firstWhere('key', $key)['status'] ?? null)
                        : null)
                    <div class="flex items-center justify-between gap-2 rounded-2xl border border-slate-200/80 bg-white px-3.5 py-3 transition hover:border-slate-300 hover:bg-slate-50">
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        <select name="status[{{ $key }}]"
                                class="cursor-pointer rounded-lg border-slate-200 px-2 py-1.5 text-xs font-bold outline-none transition focus:ring-2 focus:ring-lime-200
                                {{ ($current ?? 'ok') === 'ok' ? 'bg-lime-50 text-lime-700' : (($current ?? 'ok') === 'attention' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                            <option value="ok" @selected(($current ?? 'ok') === 'ok')>OK</option>
                            <option value="attention" @selected($current === 'attention')>Perlu Perhatian</option>
                            <option value="broken" @selected($current === 'broken')>Rusak</option>
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Temuan Teknisi</label>
                    <textarea name="findings" rows="3"
                              class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200"
                              placeholder="Contoh: kampas rem depan tersisa 2 mm, roller CVT aus...">{{ old('findings', $order->inspection?->findings) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rekomendasi Perbaikan</label>
                    <textarea name="recommendations" rows="3"
                              class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200"
                              placeholder="Contoh: ganti kampas rem, bersihkan CVT, tambah gemuk...">{{ old('recommendations', $order->inspection?->recommendations) }}</textarea>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('teknisi.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Batal</a>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700 active:scale-[.98]">
                    Simpan Lembar Pemeriksaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
