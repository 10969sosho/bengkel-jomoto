@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin / Supervisor')
@section('page_icon', 'shield-check')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Admin / Supervisor</h1>
    <p class="text-sm text-slate-500">Ringkasan operasional, master data, dan performa teknisi.</p>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @php
        $cards = [
            ['Total WO', $stats['orders'], 'text-slate-900', 'bg-slate-100', 'clipboard-list'],
            ['WO Aktif', $stats['active'], 'text-indigo-600', 'bg-indigo-50', 'activity'],
            ['Pendapatan', 'Rp '.number_format($stats['revenue']), 'text-lime-600', 'bg-lime-50', 'wallet-cards'],
            ['Belum Dibayar', 'Rp '.number_format($stats['unpaid']), 'text-red-500', 'bg-red-50', 'circle-dollar-sign'],
            ['Teknisi', $stats['technicians'], 'text-sky-600', 'bg-sky-50', 'hard-hat'],
            ['Customer', $stats['customers'], 'text-amber-600', 'bg-amber-50', 'users'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $color, $bg, $icon])
        <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <span class="grid h-9 w-9 place-items-center rounded-xl {{ $bg }} {{ $color }}">
                <i data-lucide="{{ $icon }}" class="h-[18px] w-[18px]"></i>
            </span>
            <div class="mt-3 truncate text-lg font-extrabold {{ $color }}">{{ $value }}</div>
            <div class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                        <i data-lucide="list-ordered" class="h-4 w-4"></i>
                    </span>
                    Work Order Terbaru
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/80 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-2.5">WO</th>
                            <th class="px-4 py-2.5">Customer</th>
                            <th class="px-4 py-2.5">Teknisi</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-4 py-2.5 font-mono text-sm font-bold text-slate-900">{{ $order->wo_number }}</td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    {{ $order->customer?->name }}
                                    <span class="block text-xs text-slate-400">{{ $order->vehicle?->plate_number }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $order->technician?->name ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded-full px-2 py-1 text-[11px] font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-600">
                                    @if($order->invoice)
                                        <a href="{{ route('invoices.print', $order->invoice) }}" class="font-medium text-purple-600 hover:underline">
                                            {{ number_format($order->invoice->grand_total) }}
                                        </a>
                                    @else — @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div id="pricelist" class="scroll-mt-28 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4 font-bold text-slate-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                        <i data-lucide="tags" class="h-4 w-4"></i>
                    </span>
                    Pricelist Servis
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pricelists as $p)
                                <tr class="{{ $p->is_active ? '' : 'opacity-40' }}">
                                    <td class="px-4 py-2.5">
                                        {{ $p->service_name }}
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ $p->categoryLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-700">Rp {{ number_format($p->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="scroll-mt-28 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4 font-bold text-slate-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                        <i data-lucide="package-open" class="h-4 w-4"></i>
                    </span>
                    Katalog Sub-Material
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subMaterials as $s)
                                <tr class="{{ $s->is_active ? '' : 'opacity-40' }}">
                                    <td class="px-4 py-2.5">
                                        {{ $s->name }}
                                        <span class="block text-xs text-slate-400">{{ $s->description }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-700">Rp {{ number_format($s->default_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div id="performa" class="scroll-mt-28 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 font-bold text-slate-900">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                    <i data-lucide="chart-no-axes-column-increasing" class="h-4 w-4"></i>
                </span>
                Performa Teknisi
            </h2>
            <div class="space-y-3">
                @forelse($technicians as $tech)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-3 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-900 text-xs font-black text-lime-300">
                                {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $tech->name), 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-800">{{ $tech->name }}</div>
                                <div class="truncate text-xs text-slate-400">{{ $tech->email }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-slate-100">
                            {{ $tech->technician_orders_count }} WO
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada teknisi terdaftar.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-lime-200 bg-lime-50 p-5">
            <h2 class="flex items-center gap-2 font-bold text-lime-900">
                <i data-lucide="database" class="h-4 w-4"></i> Master Data
            </h2>
            <p class="mt-1.5 text-sm text-lime-800">
                Pricelist servis, katalog sub-material, dan data teknisi dikelola oleh Admin.
                Modul lengkap (CRUD + laporan) masuk Milestone berikutnya.
            </p>
        </div>
    </div>
</div>
@endsection
