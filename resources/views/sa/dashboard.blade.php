@extends('layouts.app')

@section('title', 'SA Dashboard')
@section('page_title', 'Dashboard Service Adviser')
@section('page_icon', 'clipboard-list')

@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Dashboard Service Adviser</h1>
        <p class="text-sm text-slate-500">Kelola Work Order, assign teknisi, estimasi biaya, dan pembayaran.</p>
    </div>
    <a href="{{ route('sa.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700 active:scale-[.98]">
        <i data-lucide="plus" class="h-4 w-4"></i> Buat WO Baru
    </a>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @php($cards = [
        ['Total WO', $stats['total'], 'text-slate-900', 'bg-slate-100', 'clipboard-list'],
        ['Antrian Baru', $stats['draft'], 'text-slate-700', 'bg-zinc-100', 'inbox'],
        ['Menunggu Persetujuan', $stats['waiting'], 'text-amber-600', 'bg-amber-50', 'clock'],
        ['Sedang Dikerjakan', $stats['working'], 'text-indigo-600', 'bg-indigo-50', 'hammer'],
        ['Menunggu Konfirmasi', $stats['confirmation'], 'text-orange-600', 'bg-orange-50', 'badge-check'],
        ['Belum Dibayar', $stats['unpaid'], 'text-red-500', 'bg-red-50', 'credit-card'],
    ])
    @foreach($cards as [$label, $value, $color, $bg, $icon])
        <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="grid h-9 w-9 place-items-center rounded-xl {{ $bg }} {{ $color }}">
                    <i data-lucide="{{ $icon }}" class="h-[18px] w-[18px]"></i>
                </span>
                <span class="text-2xl font-extrabold leading-none {{ $color }}">{{ $value }}</span>
            </div>
            <div class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</div>
        </div>
    @endforeach
</div>

<section class="mb-6 overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-100 bg-amber-50/70 px-5 py-4">
        <div>
            <h2 class="font-bold text-slate-900">Booking Service Masuk</h2>
            <p class="text-xs text-slate-500">Booking customer yang belum diproses menjadi work order.</p>
        </div>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">{{ $bookings->count() }} booking</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">Customer / Motor</th>
                    <th class="px-5 py-3">Layanan Diminta</th>
                    <th class="px-5 py-3">Keluhan</th>
                    <th class="px-5 py-3">KM / Lokasi</th>
                    <th class="px-5 py-3">Data Pendukung</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($bookings as $booking)
                    <tr>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800">{{ $booking->customer?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $booking->vehicle?->fullName() }} · {{ $booking->vehicle?->plate_number }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($booking->requestedServiceLabels($pricelistNames) as $serviceLabel)
                                    <span class="rounded-lg bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">{{ $serviceLabel }}</span>
                                @empty
                                    <span class="text-xs text-slate-300">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="max-w-sm px-5 py-3.5 text-slate-600">{{ $booking->customer_complaint }}</td>
                        <td class="px-5 py-3.5 text-slate-600">
                            {{ number_format($booking->odometer ?? $booking->vehicle?->last_odometer ?? 0) }} KM
                            <div class="text-xs text-slate-400">{{ $booking->location_type === 'offsite' ? 'Home Service' : 'Di Bengkel' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($booking->stnkUrl())
                                <a href="{{ $booking->stnkUrl() }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-100">
                                    <i data-lucide="image" class="h-3.5 w-3.5"></i> Foto STNK
                                </a>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('sa.create', ['booking_id' => $booking->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-amber-600">
                                Proses jadi WO <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Tidak ada booking yang menunggu diproses.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <h2 class="flex items-center gap-2 font-bold text-slate-900">
            <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                <i data-lucide="rows-3" class="h-4 w-4"></i>
            </span>
            Semua Work Order
        </h2>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ $orders->count() }} WO</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50/80 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                <tr>
                    <th class="px-5 py-3">WO Number</th>
                    <th class="px-5 py-3">Customer / Motor</th>
                    <th class="px-5 py-3">Teknisi</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Invoice</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-sm font-bold text-slate-900">{{ $order->wo_number }}</span>
                            <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ $order->serviceTypeLabel() }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800">{{ $order->customer?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $order->vehicle?->fullName() }} · {{ $order->vehicle?->plate_number }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $order->technician?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($order->invoice)
                                <a href="{{ route('invoices.print', $order->invoice) }}" class="font-medium text-purple-600 hover:underline">
                                    {{ $order->invoice->invoice_number }}
                                </a>
                                <div class="text-xs {{ $order->invoice->isPaid() ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $order->invoice->isPaid() ? 'Lunas · '.strtoupper($order->invoice->payment_method) : 'Belum Bayar' }}
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex gap-2">
                                @if($order->customer)
                                    <a href="{{ $order->waLink() }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100 active:scale-95"
                                       title="Kirim reminder WhatsApp">
                                        <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> WA
                                    </a>
                                @endif
                                <a href="{{ route('sa.show', $order) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-slate-700 active:scale-95">
                                    Kelola <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada work order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
