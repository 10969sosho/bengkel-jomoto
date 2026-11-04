@extends('layouts.app')

@section('title', 'Teknisi Dashboard')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Dashboard Teknisi</h1>
    <p class="text-sm text-slate-500">Antrian tugas, lembar pemeriksaan, timer pengerjaan, dan penyelesaian.</p>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Tugas</div>
        <div class="mt-1 text-2xl font-bold text-slate-900">{{ $orders->count() }}</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Sedang Dikerjakan</div>
        <div class="mt-1 text-2xl font-bold text-indigo-600">{{ $orders->where('status', 'working')->count() }}</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Menunggu Inspeksi</div>
        <div class="mt-1 text-2xl font-bold text-amber-600">{{ $orders->where('status', 'approved')->count() }}</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Selesai</div>
        <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $orders->where('status', 'completed')->count() }}</div>
    </div>
</div>

<div class="space-y-4">
    @forelse($orders as $order)
        @php
            $closed = (int) $order->workLogs()->whereNotNull('stopped_at')->sum('duration_seconds');
            $open = $order->workLogs()->whereNull('stopped_at')->latest('id')->first();
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
            {{ $order->status === 'working' ? 'ring-2 ring-indigo-100' : '' }}">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-indigo-700">{{ $order->wo_number }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        @if($order->status === 'working')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-bold text-white">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span> TIMER BERJALAN
                            </span>
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ $order->customer?->name }} · <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }})
                    </div>
                    <p class="mt-1 max-w-xl text-sm text-slate-500">“{{ $order->customer_complaint }}”</p>
                </div>

                <div class="text-right">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Waktu</div>
                    <div class="font-mono text-2xl font-bold text-slate-900"
                         data-timer data-closed="{{ $closed }}"
                         data-open-start="{{ $open?->started_at?->getTimestampMs() }}">
                        {{ gmdate('H:i:s', $closed + ($open ? $open->started_at->diffInSeconds(now()) : 0)) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <a href="{{ route('teknisi.inspect', $order) }}"
                   class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    📋 {{ $order->inspection ? 'Lihat / Ubah Pemeriksaan' : 'Isi Lembar Pemeriksaan' }}
                </a>

                @if($order->status === 'approved' || $order->status === 'waiting_approval' || $order->status === 'draft')
                    <form method="POST" action="{{ route('teknisi.timer', $order) }}">
                        @csrf
                        <input type="hidden" name="action" value="start">
                        <button class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            ▶ Mulai Pengerjaan
                        </button>
                    </form>
                @elseif($order->status === 'working')
                    <form method="POST" action="{{ route('teknisi.timer', $order) }}">
                        @csrf
                        <input type="hidden" name="action" value="pause">
                        <button class="w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">
                            ⏸ Jeda Timer
                        </button>
                    </form>
                @else
                    <div class="grid place-items-center rounded-xl bg-slate-50 px-4 py-2.5 text-center text-xs font-semibold text-slate-400">
                        Pengerjaan selesai
                    </div>
                @endif

                @if($order->status === 'working')
                    <button onclick="document.getElementById('complete-{{ $order->id }}').classList.toggle('hidden')"
                            class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                        ✓ Submit Penyelesaian
                    </button>
                @else
                    <div class="grid place-items-center rounded-xl bg-slate-50 px-4 py-2.5 text-center text-xs text-slate-300">
                        —
                    </div>
                @endif
            </div>

            @if($order->status === 'working')
                <form id="complete-{{ $order->id }}" method="POST" action="{{ route('teknisi.complete', $order) }}"
                      class="mt-4 hidden rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-emerald-800">Odometer terbaru (KM)</label>
                            <input type="number" name="odometer" min="0" value="{{ $order->vehicle?->last_odometer }}"
                                   class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-emerald-800">Catatan pekerjaan</label>
                            <input type="text" name="notes" placeholder="Mis: ganti roller, bersihkan CVT..."
                                   class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                    <button class="mt-3 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Konfirmasi Selesai & Stop Timer
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
            Belum ada tugas yang di-assign ke Anda.
        </div>
    @endforelse
</div>

<script>
    document.querySelectorAll('[data-timer]').forEach(el => {
        const closed = Number(el.dataset.closed || 0);
        const openStart = el.dataset.openStart ? Number(el.dataset.openStart) : null;

        setInterval(() => {
            let seconds = closed;
            if (openStart) {
                seconds += Math.max(0, Math.floor((Date.now() - openStart) / 1000));
            }
            const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            el.textContent = `${h}:${m}:${s}`;
        }, 1000);
    });
</script>
@endsection
