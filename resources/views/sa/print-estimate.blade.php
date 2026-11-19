<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Estimasi Biaya {{ $serviceOrder->estimate_number ?? $serviceOrder->wo_number }} — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: none !important; max-width: 100% !important; }
        }
        .sign-box { min-height: 65px; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 sm:p-8">

@include('partials.print-toolbar')

@php
    $items = $serviceOrder->items;
    $totalBiaya = (int) $items->sum(fn($it) => $it->quantity * $it->unit_price) + (int) $serviceOrder->visit_fee;
    $totalDiskon = max((int) ($serviceOrder->estimate_discount ?? 0), (int) $items->sum('discount'));
    $totalSetelahDiskon = max(0, $totalBiaya - $totalDiskon);
    $ppn = (int) round($totalSetelahDiskon * 0.11);
    $totalKeseluruhan = $totalSetelahDiskon + $ppn;
    $inspection = $serviceOrder->inspection;
@endphp

<div class="print-card mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-900 pb-4">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-slate-900 text-xl font-bold text-lime-300">J</span>
            <div>
                <div class="text-lg font-black tracking-tight text-slate-900">BENGKEL JOMOTO CENTER</div>
                <div class="text-xs text-slate-500">Workshop Resmi Perawatan &amp; Perbaikan Sepeda Motor · Telepon / WA: 0812-3456-7890</div>
                <div class="text-xs text-slate-500">Website: bengkel.solusisurabaya.com · Surabaya</div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs font-black uppercase tracking-widest text-slate-700">Lembar Estimasi Servis</div>
            <div class="font-mono text-base font-black text-slate-900">No: {{ $serviceOrder->estimate_number ?? 'PW/'.now()->format('Y/m').'/00001' }}</div>
            <div class="text-xs font-semibold text-slate-500">Tanggal Estimasi: <b>{{ $serviceOrder->created_at->format('d/m/Y H:i') }}</b></div>
            <div class="text-xs text-slate-500">Ref Pemeriksaan: <b class="font-mono text-slate-800">{{ $inspection?->inspection_number ?? '—' }}</b></div>
            <div class="text-xs text-slate-400">WO Ref: {{ $serviceOrder->wo_number }}</div>
        </div>
    </div>

    {{-- Block Data Pelanggan, Petugas & Kendaraan (Terhubung dari Pemeriksaan Awal) --}}
    <div class="mt-4 grid gap-4 text-xs sm:grid-cols-2">
        {{-- Data Pelanggan & Petugas --}}
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-1.5">
            <div class="border-b border-slate-200 pb-1 text-[11px] font-black uppercase tracking-wider text-slate-700">
                Data Pelanggan &amp; Petugas (Terhubung dari Pemeriksaan Awal)
            </div>
            <div class="flex justify-between"><span class="text-slate-500">Nama Pelanggan:</span><span class="font-bold text-slate-900">{{ $serviceOrder->customer?->name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Alamat:</span><span class="font-medium text-slate-800 text-right">{{ $serviceOrder->customer?->address ?: ($serviceOrder->offsite_address ?: '—') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Nomor Kontak (HP/WA):</span><span class="font-bold text-slate-800">{{ $serviceOrder->customer?->phone ?: ($serviceOrder->customer?->whatsapp ?: '—') }}</span></div>
            <div class="flex justify-between border-t border-slate-200/80 pt-1"><span class="text-slate-500">Nama Service Advisor (SA):</span><span class="font-bold text-slate-900">{{ $inspection?->sa_name ?: ($serviceOrder->sa?->name ?? 'Sales Adviser') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Nama Mekanik:</span><span class="font-bold text-sky-800">{{ $serviceOrder->technician?->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Nama Manager:</span><span class="font-bold text-slate-900">{{ $inspection?->manager_name ?? 'ARTI DEWI' }}</span></div>
        </div>

        {{-- Data Kendaraan --}}
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-1.5">
            <div class="border-b border-slate-200 pb-1 text-[11px] font-black uppercase tracking-wider text-slate-700">
                Data Kendaraan (Terhubung dari Pemeriksaan Awal)
            </div>
            <div class="flex justify-between"><span class="text-slate-500">Nomor Polisi:</span><span class="font-mono font-black text-slate-900">{{ $serviceOrder->vehicle?->plate_number }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Merk / Tipe (Model):</span><span class="font-bold text-slate-800">{{ $serviceOrder->vehicle?->brand }} / {{ $serviceOrder->vehicle?->model }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">No. Seri Kendaraan / Mesin:</span><span class="font-mono font-medium text-slate-800">{{ $serviceOrder->vehicle?->serial_number ?: ($serviceOrder->vehicle?->engine_number ?: '—') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Nomor Rangka:</span><span class="font-mono font-medium text-slate-800">{{ $serviceOrder->vehicle?->chassis_number ?: '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Tahun Rakitan / Transmisi:</span><span class="font-medium text-slate-800">Tahun {{ $serviceOrder->vehicle?->year }} · {{ $serviceOrder->vehicle?->transmission ?: 'Otomatis / CVT' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Jenis Mesin:</span><span class="font-medium text-slate-800">{{ $serviceOrder->vehicle?->engine_type ?: ($serviceOrder->vehicle?->fuel_type === 'electric' ? 'Listrik BLDC' : 'Bensin 4-Tak') }}</span></div>
            <div class="flex justify-between border-t border-slate-200/80 pt-1"><span class="text-slate-500">Odometer:</span><span class="font-bold text-slate-900">{{ number_format($serviceOrder->odometer ?: ($serviceOrder->vehicle?->last_odometer ?? 0)) }} KM</span></div>
        </div>
    </div>

    {{-- Keluhan & Diagnosis --}}
    <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 text-xs">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <span class="font-bold text-slate-500 uppercase tracking-wide text-[10px]">Kategori Keluhan:</span>
                <span class="ml-1 inline-block rounded bg-amber-100 px-2 py-0.5 font-bold text-amber-900">{{ $serviceOrder->complaint_category ?: 'Servis Berkala &amp; Perawatan' }}</span>
                <div class="mt-1 text-slate-700"><b>Deskripsi Keluhan:</b> {{ $serviceOrder->customer_complaint }}</div>
            </div>
            @if($serviceOrder->sa_diagnosis)
                <div class="flex-1 min-w-[200px] border-t sm:border-t-0 sm:border-l border-slate-100 sm:pl-3">
                    <span class="font-bold text-slate-500 uppercase tracking-wide text-[10px]">Diagnosis Sales Advisor:</span>
                    <p class="mt-0.5 text-slate-700">{{ $serviceOrder->sa_diagnosis }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel Rincian Transaksi / Biaya --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
        <div class="bg-slate-100 px-3.5 py-2 text-[11px] font-black uppercase tracking-wider text-slate-700 border-b border-slate-200">
            Rincian Transaksi / Biaya Servis
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-left font-bold text-slate-600">
                    <th class="py-2 px-3 text-center w-10">No</th>
                    <th class="py-2 px-3">Nama Barang / Jasa</th>
                    <th class="py-2 px-3 text-center w-16">Qty</th>
                    <th class="py-2 px-3 text-right">Harga Satuan</th>
                    <th class="py-2 px-3 text-right">Diskon</th>
                    <th class="py-2 px-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $idx => $item)
                    <tr>
                        <td class="py-2 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3">
                            <span class="mr-1.5 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase
                                {{ $item->item_type === 'service' ? 'bg-indigo-100 text-indigo-700' : ($item->item_type === 'sparepart' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $item->typeLabel() }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ $item->item_name }}</span>
                            @if($item->is_free)
                                <span class="ml-1 text-[10px] font-bold text-emerald-600">(GRATIS — {{ $item->free_reason }})</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-center font-semibold text-slate-700">{{ $item->quantity }}</td>
                        <td class="py-2 px-3 text-right text-slate-700">Rp {{ number_format($item->unit_price) }}</td>
                        <td class="py-2 px-3 text-right text-red-600">{{ $item->discount > 0 ? '-Rp '.number_format($item->discount) : 'Rp 0' }}</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900">
                            {{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400">Belum ada item rincian transaksi/biaya.</td>
                    </tr>
                @endforelse

                @if($serviceOrder->visit_fee > 0)
                    <tr>
                        <td class="py-2 px-3 text-center font-mono text-slate-400">{{ count($items) + 1 }}</td>
                        <td class="py-2 px-3">
                            <span class="mr-1.5 rounded bg-lime-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-lime-800">Home Service</span>
                            <span class="font-semibold text-slate-800">Biaya Kunjungan Teknisi</span>
                        </td>
                        <td class="py-2 px-3 text-center font-semibold text-slate-700">1</td>
                        <td class="py-2 px-3 text-right text-slate-700">Rp {{ number_format($serviceOrder->visit_fee) }}</td>
                        <td class="py-2 px-3 text-right text-slate-400">Rp 0</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900">Rp {{ number_format($serviceOrder->visit_fee) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Ringkasan Biaya di Kanan Bawah --}}
    <div class="mt-4 flex justify-end">
        <div class="w-full max-w-sm rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Total Biaya (Sebelum Diskon):</span>
                <span class="font-bold text-slate-900">Rp {{ number_format($totalBiaya) }}</span>
            </div>
            <div class="flex justify-between text-red-600 font-medium">
                <span>Total Diskon:</span>
                <span>-Rp {{ number_format($totalDiskon) }}</span>
            </div>
            <div class="flex justify-between text-slate-700 font-semibold border-t border-slate-200 pt-1">
                <span>Total Setelah Diskon:</span>
                <span>Rp {{ number_format($totalSetelahDiskon) }}</span>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>PPN (11%):</span>
                <span class="font-medium text-slate-800">Rp {{ number_format($ppn) }}</span>
            </div>
            <div class="flex justify-between rounded-lg bg-slate-900 px-3 py-2 text-sm font-black text-white">
                <span class="uppercase tracking-wide">TOTAL ESTIMASI (Total Keseluruhan):</span>
                <span class="font-mono text-lime-300">Rp {{ number_format($totalKeseluruhan) }}</span>
            </div>
        </div>
    </div>

    {{-- Kolom Tanda Tangan (TTD Pelanggan, Service Advisor, Mekanik) --}}
    <div class="mt-6 border-t border-slate-200 pt-4">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 text-center">
            Persetujuan Estimasi Biaya Pekerjaan
        </div>
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            {{-- TTD Pelanggan --}}
            <div class="rounded-xl border border-dashed border-slate-300 p-3 bg-white">
                <div class="font-bold text-slate-700">Pelanggan (Persetujuan Estimasi)</div>
                <div class="sign-box flex items-end justify-center pb-1 text-[11px] text-slate-400">
                    @if($serviceOrder->estimate_approved_at)
                        <span class="font-semibold text-emerald-700">✓ Disetujui {{ $serviceOrder->estimate_approved_at->format('d/m/Y H:i') }}</span>
                    @else
                        (Tanda Tangan)
                    @endif
                </div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800 truncate">
                    {{ $serviceOrder->customer?->name }}
                </div>
            </div>

            {{-- TTD Service Advisor --}}
            <div class="rounded-xl border border-dashed border-slate-300 p-3 bg-white">
                <div class="font-bold text-slate-700">Service Advisor (SA)</div>
                <div class="sign-box flex items-end justify-center pb-1 text-[11px] text-slate-400">
                    <span class="font-semibold text-indigo-700">✓ Dibuat oleh SA</span>
                </div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800 truncate">
                    {{ $inspection?->sa_name ?: ($serviceOrder->sa?->name ?? 'Sales Adviser') }}
                </div>
            </div>

            {{-- TTD Mekanik --}}
            <div class="rounded-xl border border-dashed border-slate-300 p-3 bg-white">
                <div class="font-bold text-slate-700">Mekanik (Persetujuan Hasil Pengerjaan)</div>
                <div class="sign-box flex items-end justify-center pb-1 text-[11px] text-slate-400">
                    @if($inspection?->mechanic_confirmed_at)
                        <span class="font-semibold text-sky-700">✓ Dikonfirmasi Mekanik</span>
                    @else
                        (Tanda Tangan)
                    @endif
                </div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800 truncate">
                    {{ $serviceOrder->technician?->name ?? 'Mekanik Bengkel' }}
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
