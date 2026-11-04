@extends('layouts.app')

@section('title', 'Lembar Pemeriksaan')

@section('content')
<a href="{{ route('teknisi.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Kembali ke tugas saya</a>

<div class="mx-auto mt-3 max-w-3xl">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900">Lembar Pemeriksaan Sepeda Motor</h1>
                <p class="text-sm text-slate-500">
                    {{ $order->wo_number }} · <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }})
                </p>
            </div>
            <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
        </div>

        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">
            <div class="font-semibold text-slate-700">Keluhan Customer:</div>
            <p class="mt-1 text-slate-600">{{ $order->customer_complaint }}</p>
            <div class="mt-2 text-xs text-slate-400">
                Odometer: {{ number_format($order->vehicle?->last_odometer ?? 0) }} KM ·
                {{ $order->vehicle?->fuelLabel() }} · Tahun {{ $order->vehicle?->year }}
            </div>
        </div>

        <form method="POST" action="{{ route('teknisi.inspect.save', $order) }}" class="mt-5">
            @csrf

            <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Checklist Pemeriksaan</h2>

            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($checklistItems as $key => $label)
                    @php($current = $order->inspection && is_array($order->inspection->checklist)
                        ? (collect($order->inspection->checklist)->firstWhere('key', $key)['status'] ?? null)
                        : null)
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5">
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        <select name="status[{{ $key }}]"
                                class="rounded-lg border-slate-200 px-2 py-1.5 text-xs font-semibold focus:border-indigo-500 focus:ring-indigo-500
                                {{ ($current ?? 'ok') === 'ok' ? 'bg-emerald-50 text-emerald-700' : (($current ?? 'ok') === 'attention' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
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
                              class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                              placeholder="Contoh: kampas rem depan tersisa 2 mm, roller CVT aus...">{{ old('findings', $order->inspection?->findings) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rekomendasi Perbaikan</label>
                    <textarea name="recommendations" rows="3"
                              class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                              placeholder="Contoh: ganti kampas rem, bersihkan CVT, tambah gemuk...">{{ old('recommendations', $order->inspection?->recommendations) }}</textarea>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('teknisi.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Simpan Lembar Pemeriksaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
