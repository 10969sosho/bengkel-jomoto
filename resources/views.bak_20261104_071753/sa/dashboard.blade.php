@extends('layouts.app')

@section('title', 'SA Dashboard')

@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Dashboard Service Adviser</h1>
        <p class="text-sm text-slate-500">Kelola Work Order, assign teknisi, estimasi biaya, dan pembayaran.</p>
    </div>
    <a href="{{ route('sa.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
        + Buat WO Baru
    </a>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
    @php($cards = [
        ['Total WO', $stats['total'], 'text-slate-900'],
        ['Antrian Baru', $stats['draft'], 'text-slate-600'],
        ['Menunggu Persetujuan', $stats['waiting'], 'text-amber-600'],
        ['Sedang Dikerjakan', $stats['working'], 'text-indigo-600'],
        ['Belum Dibayar', $stats['unpaid'], 'text-red-600'],
    ])
    @foreach($cards as [$label, $value, $color])
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</div>
            <div class="mt-1 text-2xl font-bold {{ $color }}">{{ $value }}</div>
        </div>
    @endforeach
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-900">Semua Work Order</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
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
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3">
                            <span class="font-semibold text-indigo-700">{{ $order->wo_number }}</span>
                            <span class="ml-2 text-xs text-slate-400">{{ $order->serviceTypeLabel() }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $order->customer?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $order->vehicle?->fullName() }} · {{ $order->vehicle?->plate_number }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $order->technician?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        </td>
                        <td class="px-5 py-3">
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
                        <td class="px-5 py-3 text-right">
                            @if($order->customer)
                                <a href="{{ $order->waLink() }}" target="_blank" rel="noopener"
                                   class="mr-2 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                   title="Kirim reminder WhatsApp">
                                    WA Reminder
                                </a>
                            @endif
                            <a href="{{ route('sa.show', $order) }}"
                               class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                                Kelola
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada work order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
