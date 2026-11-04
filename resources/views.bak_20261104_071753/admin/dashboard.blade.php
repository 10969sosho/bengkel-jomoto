@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Admin / Supervisor</h1>
    <p class="text-sm text-slate-500">Ringkasan operasional, master data, dan performa teknisi.</p>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @php($cards = [
        ['Total WO', $stats['orders'], 'text-slate-900'],
        ['WO Aktif', $stats['active'], 'text-indigo-600'],
        ['Pendapatan', 'Rp '.number_format($stats['revenue']), 'text-emerald-600'],
        ['Belum Dibayar', 'Rp '.number_format($stats['unpaid']), 'text-red-500'],
        ['Teknisi', $stats['technicians'], 'text-sky-600'],
        ['Customer', $stats['customers'], 'text-amber-600'],
    ])
    @foreach($cards as [$label, $value, $color])
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $label }}</div>
            <div class="mt-1 truncate text-lg font-bold {{ $color }}">{{ $value }}</div>
        </div>
    @endforeach
</div>

<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Work Order Terbaru</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
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
                            <tr>
                                <td class="px-4 py-2.5 font-semibold text-indigo-700">{{ $order->wo_number }}</td>
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
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Pricelist Servis</div>
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pricelists as $p)
                                <tr class="{{ $p->is_active ? '' : 'opacity-40' }}">
                                    <td class="px-4 py-2.5">
                                        {{ $p->service_name }}
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ $p->categoryLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-slate-700">Rp {{ number_format($p->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Katalog Sub-Material</div>
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subMaterials as $s)
                                <tr class="{{ $s->is_active ? '' : 'opacity-40' }}">
                                    <td class="px-4 py-2.5">
                                        {{ $s->name }}
                                        <span class="block text-xs text-slate-400">{{ $s->description }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-slate-700">Rp {{ number_format($s->default_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold text-slate-900">Performa Teknisi</h2>
            <div class="space-y-3">
                @foreach($technicians as $tech)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $tech->name }}</div>
                            <div class="text-xs text-slate-400">{{ $tech->email }}</div>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-indigo-600 shadow-sm">
                            {{ $tech->technician_orders_count }} WO
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
            <h2 class="font-semibold text-indigo-900">Master Data</h2>
            <p class="mt-1 text-sm text-indigo-700">
                Pricelist servis, katalog sub-material, dan data teknisi dikelola oleh Admin.
                Modul lengkap (CRUD + laporan) masuk Milestone berikutnya.
            </p>
        </div>
    </div>
</div>
@endsection
