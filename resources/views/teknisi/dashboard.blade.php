@extends('layouts.app')

@section('title', 'Dashboard Teknisi')
@section('page_title', 'Dashboard Teknisi')
@section('page_icon', 'wrench')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Dashboard Teknisi</h1>
    <p class="text-sm text-slate-500">Antrian tugas, lembar pemeriksaan, timer pengerjaan, dan penyelesaian.</p>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $stats = [
            ['Total Tugas', $orders->count(), 'clipboard-list', 'text-slate-700', 'bg-slate-100'],
            ['Sedang Dikerjakan', $orders->where('status', 'working')->count(), 'timer', 'text-indigo-600', 'bg-indigo-50'],
            ['Menunggu Inspeksi', $orders->where('status', 'approved')->count(), 'clipboard-check', 'text-amber-600', 'bg-amber-50'],
            ['Selesai', $orders->where('status', 'completed')->count(), 'circle-check-big', 'text-emerald-600', 'bg-emerald-50'],
        ];
    @endphp
    @foreach($stats as [$label, $value, $icon, $color, $bg])
        <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $bg }} {{ $color }}">
                    <i data-lucide="{{ $icon }}" class="h-[18px] w-[18px]"></i>
                </span>
                <div class="min-w-0">
                    <div class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</div>
                    <div class="mt-0.5 text-2xl font-extrabold leading-none text-slate-900">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="space-y-4">
    @forelse($orders as $order)
        @php
            $closed = (int) $order->workLogs()->whereNotNull('stopped_at')->sum('duration_seconds');
            $open = $order->workLogs()->whereNull('stopped_at')->latest('id')->first();
        @endphp
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-300 hover:shadow-md
            {{ $order->status === 'working' ? 'ring-2 ring-lime-300/60' : '' }}">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-sm font-extrabold text-slate-900">{{ $order->wo_number }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        @if($order->status === 'working')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-2.5 py-1 text-xs font-bold text-lime-300">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-lime-400"></span> TIMER BERJALAN
                            </span>
                        @endif
                    </div>
                    <div class="mt-1.5 text-sm text-slate-600">
                        {{ $order->customer?->name }} · <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }})
                    </div>
                    <p class="mt-1 max-w-xl text-sm text-slate-500">“{{ $order->customer_complaint }}”</p>
                </div>

                <div class="rounded-2xl bg-slate-900 px-4 py-3 text-right">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Total Waktu</div>
                    <div class="font-mono text-2xl font-bold text-lime-300"
                         data-timer data-closed="{{ $closed }}"
                         data-open-start="{{ $open?->started_at?->getTimestampMs() }}">
                        {{ gmdate('H:i:s', $closed + ($open ? $open->started_at->diffInSeconds(now()) : 0)) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <a href="{{ route('teknisi.inspect', $order) }}"
                   class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[.98]">
                    <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                    {{ $order->inspection ? 'Lihat / Ubah Pemeriksaan' : 'Isi Lembar Pemeriksaan' }}
                </a>

                @if($order->status === 'approved' || $order->status === 'waiting_approval' || $order->status === 'draft')
                    <form method="POST" action="{{ route('teknisi.timer', $order) }}">
                        @csrf
                        <input type="hidden" name="action" value="start">
                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-lime-300 px-4 py-2.5 text-sm font-bold text-black transition hover:bg-lime-400 active:scale-[.98]">
                            <i data-lucide="play" class="h-4 w-4"></i> Mulai Pengerjaan
                        </button>
                    </form>
                @elseif($order->status === 'working')
                    <form method="POST" action="{{ route('teknisi.timer', $order) }}">
                        @csrf
                        <input type="hidden" name="action" value="pause">
                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-amber-950 transition hover:bg-amber-500 active:scale-[.98]">
                            <i data-lucide="pause" class="h-4 w-4"></i> Jeda Timer
                        </button>
                    </form>
                @else
                    <div class="grid place-items-center rounded-xl bg-slate-100 px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wide text-slate-400">
                        Pengerjaan selesai
                    </div>
                @endif

                @if($order->status === 'working')
                    <button onclick="document.getElementById('complete-{{ $order->id }}').classList.toggle('hidden')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700 active:scale-[.98]">
                        <i data-lucide="check-check" class="h-4 w-4"></i> Submit Penyelesaian
                    </button>
                @else
                    <div class="grid place-items-center rounded-xl bg-slate-50 px-4 py-2.5 text-center text-xs text-slate-300">
                        —
                    </div>
                @endif
            </div>

            @if($order->status === 'working')
                <form id="complete-{{ $order->id }}" method="POST" action="{{ route('teknisi.complete', $order) }}"
                      class="mt-4 hidden rounded-2xl border border-lime-200 bg-lime-50/60 p-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-lime-900">Odometer terbaru (KM)</label>
                            <input type="number" name="odometer" min="0" value="{{ $order->vehicle?->last_odometer }}"
                                   class="w-full rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:ring-2 focus:ring-lime-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-lime-900">Catatan pekerjaan</label>
                            <input type="text" name="notes" placeholder="Mis: ganti roller, bersihkan CVT..."
                                   class="w-full rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:ring-2 focus:ring-lime-200">
                        </div>
                    </div>
                    <button class="mt-3 rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-black transition hover:bg-lime-400 active:scale-[.98]">
                        Konfirmasi Selesai &amp; Stop Timer
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
            <i data-lucide="inbox" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
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
