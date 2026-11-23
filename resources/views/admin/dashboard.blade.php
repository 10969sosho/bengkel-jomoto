@extends('layouts.app')

@section('title', 'Dashboard Admin Bengkel')
@section('page_title', 'Dashboard Admin')
@section('page_icon', 'layout-dashboard')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Dashboard Utama Bengkel</h1>
            <p class="text-sm text-slate-500">Monitoring operasional, performa teknisi, pendapatan servis, dan master data.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pricelist') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i data-lucide="tags" class="h-3.5 w-3.5"></i> Pricelist Jasa
            </a>
            <a href="{{ route('admin.subMaterials') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i data-lucide="package" class="h-3.5 w-3.5"></i> Sub-Material
            </a>
            <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-lime-300 shadow-sm transition hover:bg-slate-800">
                <i data-lucide="file-text" class="h-3.5 w-3.5"></i> Laporan Servis
            </a>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @php($cards = [
            ['Total WO', $stats['orders'], 'text-slate-900', 'bg-slate-100', 'clipboard-list'],
            ['WO Aktif', $stats['active'], 'text-indigo-600', 'bg-indigo-50', 'hammer'],
            ['Teknisi', $stats['technicians'], 'text-amber-600', 'bg-amber-50', 'hard-hat'],
            ['Pelanggan', $stats['customers'], 'text-blue-600', 'bg-blue-50', 'users'],
            ['Pendapatan', 'Rp ' . number_format($stats['revenue']), 'text-emerald-700', 'bg-emerald-50', 'circle-dollar-sign'],
            ['Belum Bayar', 'Rp ' . number_format($stats['unpaid']), 'text-red-600', 'bg-red-50', 'clock'],
        ])
        @foreach($cards as [$label, $value, $color, $bg, $icon])
            <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <span class="grid h-8 w-8 place-items-center rounded-xl {{ $bg }} {{ $color }}">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
                </span>
                <div class="mt-3 truncate text-lg font-black {{ $color }}">{{ $value }}</div>
                <div class="mt-0.5 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left 2 Columns: Recent Orders & Quick Management --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Work Order Terbaru --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="flex items-center gap-2 font-bold text-slate-900">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                            <i data-lucide="list-ordered" class="h-4 w-4"></i>
                        </span>
                        Work Order Terbaru
                    </h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ $recentOrders->count() }} Terkini</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50/80 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3">WO</th>
                                <th class="px-5 py-3">Customer / Plat</th>
                                <th class="px-5 py-3">Teknisi</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Invoice</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentOrders as $order)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 font-mono text-sm font-bold text-slate-900">{{ $order->wo_number }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-800">{{ $order->customer?->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $order->vehicle?->plate_number }} · {{ $order->vehicle?->fullName() }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $order->technician?->name ?? '—' }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-medium">
                                        @if($order->invoice)
                                            <a href="{{ route('invoices.print', $order->invoice) }}" target="_blank" class="text-purple-600 hover:underline">
                                                Rp {{ number_format($order->invoice->grand_total) }}
                                            </a>
                                            <div class="text-[11px] {{ $order->invoice->isPaid() ? 'text-emerald-600' : 'text-red-500' }}">
                                                {{ $order->invoice->isPaid() ? 'Lunas' : 'Belum Bayar' }}
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada work order tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2 Cards: Pricelist & Sub-Material --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-lime-300 text-black">
                                <i data-lucide="tags" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <h3 class="font-bold text-slate-900">Pricelist Jasa Standar</h3>
                                <p class="text-xs text-slate-400">{{ $pricelists->count() }} layanan aktif &amp; acuan P/S/G</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <span class="text-xs font-semibold text-slate-500">Kelola tarif &amp; tindakan</span>
                        <a href="{{ route('admin.pricelist') }}"
                           class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-lime-300 transition hover:bg-slate-800">
                            Kelola <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-300 text-black">
                                <i data-lucide="package" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <h3 class="font-bold text-slate-900">Katalog Sub-Material</h3>
                                <p class="text-xs text-slate-400">{{ $subMaterials->count() }} tindakan/bahan pendukung</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <span class="text-xs font-semibold text-slate-500">Open custom pricing</span>
                        <a href="{{ route('admin.subMaterials') }}"
                           class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-lime-300 transition hover:bg-slate-800">
                            Kelola <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right 1 Column: Performa Teknisi & Sistem --}}
        <div class="space-y-6">
            <div id="performa" class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="flex items-center gap-2 font-bold text-slate-900">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                            <i data-lucide="chart-no-axes-column-increasing" class="h-4 w-4"></i>
                        </span>
                        Performa Teknisi
                    </h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $technicians->count() }} Teknisi</span>
                </div>
                <div class="space-y-2.5">
                    @forelse($technicians as $tech)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-3 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-900 text-xs font-black text-lime-300">
                                    {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $tech->name), 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-800">{{ $tech->name }}</div>
                                    <div class="truncate text-xs text-slate-400">{{ $tech->email }}</div>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-sm ring-1 ring-slate-200">
                                {{ $tech->technician_orders_count }} WO
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada teknisi terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-lime-200 bg-gradient-to-br from-lime-50 to-emerald-50 p-5 shadow-sm">
                <h3 class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="grid h-6 w-6 place-items-center rounded-lg bg-lime-400 text-black">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    </span>
                    Bengkel Jomoto Operasional
                </h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-600">
                    Sistem bengkel terintegrasi penuh: Mulai dari Check-in awal SA, estimasi biaya akurat dengan diskon &amp; PPN, penerbitan SPK otomatis setelah persetujuan customer, hingga pencatatan timer mekanik &amp; invoice.
                </p>
                <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-bold text-slate-700">
                    <span class="rounded-lg bg-white/80 px-2.5 py-1 shadow-sm">✓ CRUD Pricelist</span>
                    <span class="rounded-lg bg-white/80 px-2.5 py-1 shadow-sm">✓ CRUD Sub-Material</span>
                    <span class="rounded-lg bg-white/80 px-2.5 py-1 shadow-sm">✓ Auto-Issue SPK</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
