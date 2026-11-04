@extends('layouts.app')

@section('title', 'Customer Portal')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Halo, {{ explode(' ', $user->name)[0] }} 👋</h1>
    <p class="text-sm text-slate-500">Pantau progress servis motor Anda, riwayat perawatan, dan unduh kwitansi.</p>
</div>

{{-- Motor saya --}}
<section class="mb-6">
    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Motor Saya</h2>
    <div class="grid gap-3 sm:grid-cols-3">
        @forelse($vehicles as $vehicle)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="rounded-lg bg-slate-900 px-2 py-1 text-xs font-bold tracking-wider text-white">{{ $vehicle->plate_number }}</span>
                    <span class="rounded-full bg-{{ $vehicle->fuel_type === 'electric' ? 'emerald' : 'sky' }}-50 px-2 py-1 text-[11px] font-semibold text-{{ $vehicle->fuel_type === 'electric' ? 'emerald' : 'sky' }}-700">
                        {{ $vehicle->fuelLabel() }}
                    </span>
                </div>
                <div class="mt-2 font-semibold text-slate-900">{{ $vehicle->fullName() }}</div>
                <div class="mt-1 text-xs text-slate-500">Tahun {{ $vehicle->year }} · {{ number_format($vehicle->last_odometer) }} KM</div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-400 sm:col-span-3">
                Belum ada motor terdaftar.
            </div>
        @endforelse
    </div>
</section>

{{-- Progress servis aktif --}}
<section class="mb-6">
    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Servis Aktif</h2>

    @forelse($active as $order)
        @php($progress = $order->progressPercent())
        <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-indigo-700">{{ $order->wo_number }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }}) ·
                        {{ $order->serviceTypeLabel() }}
                    </div>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">“{{ $order->customer_complaint }}”</p>
                </div>
                <div class="text-right text-sm">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Teknisi</div>
                    <div class="font-semibold text-slate-700">{{ $order->technician?->name ?? 'Belum ditugaskan' }}</div>
                    @if($order->estimated_completion)
                        <div class="mt-1 text-xs text-slate-400">Target: {{ $order->estimated_completion->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <div class="mb-1.5 flex justify-between text-xs font-semibold text-slate-500">
                    <span>Progres Pengerjaan</span>
                    <span class="text-indigo-600">{{ $progress }}%</span>
                </div>
                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-indigo-400 transition-all duration-700"
                         style="width: {{ $progress }}%"></div>
                </div>
                <div class="mt-2 flex justify-between text-[10px] font-medium uppercase tracking-wide text-slate-300">
                    <span class="{{ $progress >= 10 ? 'text-indigo-500' : '' }}">Terdaftar</span>
                    <span class="{{ $progress >= 35 ? 'text-indigo-500' : '' }}">Estimasi</span>
                    <span class="{{ $progress >= 50 ? 'text-indigo-500' : '' }}">Disetujui</span>
                    <span class="{{ $progress >= 70 ? 'text-indigo-500' : '' }}">Dikerjakan</span>
                    <span class="{{ $progress >= 90 ? 'text-indigo-500' : '' }}">Selesai</span>
                    <span class="{{ $progress >= 100 ? 'text-indigo-500' : '' }}">Invoice</span>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400">
            Tidak ada servis yang sedang berjalan. 🎉
        </div>
    @endforelse
</section>

{{-- Riwayat --}}
<section>
    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Riwayat Servis</h2>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">WO</th>
                        <th class="px-5 py-3">Motor</th>
                        <th class="px-5 py-3">Tanggal Selesai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $order)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3 font-semibold text-indigo-700">{{ $order->wo_number }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $order->vehicle?->fullName() }} · {{ $order->vehicle?->plate_number }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ optional($order->actual_completion)->format('d M Y') ?? $order->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($order->invoice)
                                    <a href="{{ route('invoices.print', $order->invoice) }}"
                                       class="rounded-lg bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-100">
                                        🖨 Cetak ({{ $order->invoice->isPaid() ? 'Lunas' : 'Belum Bayar' }})
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat servis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
