@extends('layouts.app')

@section('title', 'Laporan Pendapatan')
@section('page_title', 'Laporan & Analytics')
@section('page_icon', 'chart-no-axes-combined')

@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Laporan Pendapatan &amp; Performa</h2>
        <p class="text-sm text-slate-500">Omzet invoice lunas, kanal pembayaran, dan produktivitas teknisi.</p>
    </div>
    <form method="GET" class="flex gap-2">
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm">
            <option value="all" @selected($period === 'all')>Semua Periode</option>
            <option value="month" @selected($period === 'month')>Bulan Ini</option>
            <option value="today" @selected($period === 'today')>Hari Ini</option>
        </select>
    </form>
</div>

<div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Omzet Total', $revenue['total'], 'wallet-cards', 'text-lime-700', 'bg-lime-50'],
        ['Jasa Servis', $revenue['services'], 'wrench', 'text-sky-700', 'bg-sky-50'],
        ['Sparepart (HET)', $revenue['spareparts'], 'package', 'text-indigo-700', 'bg-indigo-50'],
        ['Sub-Material Custom', $revenue['submaterials'], 'settings-2', 'text-amber-700', 'bg-amber-50'],
    ] as [$label, $amount, $icon, $color, $background])
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <span class="grid h-10 w-10 place-items-center rounded-2xl {{ $background }} {{ $color }}"><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></span>
            <div class="mt-3 text-xl font-extrabold text-slate-900">Rp {{ number_format($amount) }}</div>
            <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="grid gap-5 lg:grid-cols-2">
    <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-bold text-slate-900">Distribusi Metode Pembayaran</h3>
        @php($paymentMax = max(1, $payments->max('total')))
        <div class="space-y-4">
            @foreach($payments as $payment)
                <div>
                    <div class="mb-1.5 flex justify-between text-sm"><span class="font-semibold text-slate-700">{{ $payment['method'] }}</span><span class="font-bold text-slate-900">Rp {{ number_format($payment['total']) }}</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-lime-400 transition-all" style="width: {{ (int) round($payment['total'] / $paymentMax * 100) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-bold text-slate-900">Performa Teknisi</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"><tr><th class="pb-3">Teknisi</th><th class="pb-3 text-right">Unit Selesai</th><th class="pb-3 text-right">Rata-rata Durasi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($technicians as $technician)
                        <tr><td class="py-3 font-semibold text-slate-800">{{ $technician['name'] }}</td><td class="py-3 text-right font-bold text-lime-700">{{ $technician['units'] }} unit</td><td class="py-3 text-right text-slate-600">{{ $technician['average_minutes'] }} menit</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-slate-400">Belum ada teknisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
