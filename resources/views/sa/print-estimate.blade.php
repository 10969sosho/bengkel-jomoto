<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estimasi {{ $serviceOrder->wo_number }} — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: none !important; }
            body.paper-narrow .print-card {
                max-width: 100% !important;
                width: 100% !important;
                padding: 4mm !important;
                font-size: 11px !important;
            }
            body.paper-narrow .print-card [class*="grid-cols-"] {
                grid-template-columns: 1fr !important;
            }
            body.paper-narrow .print-card .flex {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
        }
        .sign-box { min-height: 72px; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 sm:p-8">

@include('partials.print-toolbar')

@php
    $items = $serviceOrder->items;
    $subtotal = (int) $items->sum('subtotal') + (int) $serviceOrder->visit_fee;
    $tax = (int) round($subtotal * \App\Models\Invoice::TAX_RATE);
    $grandTotal = $subtotal + $tax;
@endphp

<div class="print-card mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-900 pb-5">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-indigo-600 text-xl font-bold text-white">J</span>
            <div>
                <div class="text-lg font-black tracking-tight text-slate-900">BENGKEL JOMOTO CENTER</div>
                <div class="text-xs text-slate-500">Jl. Raya Contoh No. 88, Surabaya · Telp 031-555-0188</div>
                <div class="text-xs text-slate-500">bengkel.solusisurabaya.com</div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Lembar Estimasi Servis</div>
            <div class="text-xl font-black text-slate-900">{{ $serviceOrder->wo_number }}</div>
            <div class="mt-1 text-xs text-slate-500">Tanggal: {{ $serviceOrder->created_at->format('d F Y H:i') }}</div>
        </div>
    </div>

    {{-- Info customer, kendaraan & layanan --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-slate-50 p-4 text-sm">
            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Customer</div>
            <div class="font-bold text-slate-900">{{ $serviceOrder->customer?->name }}</div>
            <div class="text-slate-600">{{ $serviceOrder->customer?->email }}</div>
            <div class="text-slate-600">WA: {{ $serviceOrder->customer?->whatsapp ?: $serviceOrder->customer?->phone ?: '—' }}</div>
            @if($serviceOrder->estimated_completion)
                <div class="mt-2 text-xs text-slate-500">Target selesai: {{ $serviceOrder->estimated_completion->format('d M Y H:i') }}</div>
            @endif
        </div>
        <div class="rounded-xl bg-slate-50 p-4 text-sm">
            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Kendaraan</div>
            <div class="font-bold text-slate-900">{{ $serviceOrder->vehicle?->fullName() }}</div>
            <div class="text-slate-600">Plat: {{ $serviceOrder->vehicle?->plate_number }} · {{ $serviceOrder->vehicle?->fuelLabel() }}</div>
            <div class="text-slate-600">Tahun {{ $serviceOrder->vehicle?->year }} · Odo {{ number_format($serviceOrder->vehicle?->last_odometer ?? 0) }} KM</div>
            <div class="text-slate-600">Teknisi: <b>{{ $serviceOrder->technician?->name ?? '—' }}</b></div>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-100 p-4 text-sm">
        <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Keluhan Customer</div>
        <p class="mt-1 text-slate-700">{{ $serviceOrder->customer_complaint }}</p>
        @if($requestedServices !== [])
            <div class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Layanan yang Diminta</div>
            <p class="mt-1 text-slate-700">{{ implode(' · ', $requestedServices) }}</p>
        @endif
        @if($serviceOrder->stnkUrl())
            <div class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Foto STNK</div>
            <img src="{{ $serviceOrder->stnkUrl() }}" alt="Foto STNK" class="mt-1 h-24 rounded border border-slate-200">
        @endif
        @if($serviceOrder->sa_diagnosis)
            <p class="mt-2 text-slate-600"><b>Diagnosis SA:</b> {{ $serviceOrder->sa_diagnosis }}</p>
        @endif
    </div>

    {{-- Hasil pemeriksaan awal --}}
    @if($serviceOrder->inspection && ($serviceOrder->inspection->findings || $serviceOrder->inspection->recommendations))
        <div class="mt-4 rounded-xl border border-slate-100 p-4 text-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Hasil Pemeriksaan Mekanik</div>
            @if($serviceOrder->inspection->findings)
                <p class="mt-1 text-slate-700"><b>Temuan:</b> {{ $serviceOrder->inspection->findings }}</p>
            @endif
            @if($serviceOrder->inspection->recommendations)
                <p class="mt-1 text-slate-700"><b>Rekomendasi:</b> {{ $serviceOrder->inspection->recommendations }}</p>
            @endif
        </div>
    @endif

    {{-- Rincian estimasi --}}
    <table class="mt-5 w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-800 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                <th class="py-2">Item</th>
                <th class="py-2 text-center">Qty</th>
                <th class="py-2 text-right">Harga</th>
                <th class="py-2 text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($items as $item)
                <tr>
                    <td class="py-2.5">
                        <span class="mr-2 rounded px-1.5 py-0.5 text-[10px] font-bold uppercase
                            {{ $item->item_type === 'service' ? 'bg-indigo-100 text-indigo-700' : ($item->item_type === 'sparepart' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $item->typeLabel() }}
                        </span>
                        {{ $item->item_name }}
                        @if($item->is_free)
                            <div class="text-xs font-semibold text-emerald-600">GRATIS — {{ $item->free_reason }}</div>
                        @endif
                    </td>
                    <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                    <td class="py-2.5 text-right">Rp {{ number_format($item->unit_price) }}</td>
                    <td class="py-2.5 text-right font-semibold">{{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-slate-400">Estimasi belum diisi Service Advisor.</td>
                </tr>
            @endforelse
            @if($serviceOrder->visit_fee > 0)
                <tr>
                    <td class="py-2.5"><span class="mr-2 rounded bg-lime-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-lime-700">Home Service</span>Biaya kunjungan teknisi</td>
                    <td class="py-2.5 text-center">1</td>
                    <td class="py-2.5 text-right">Rp {{ number_format($serviceOrder->visit_fee) }}</td>
                    <td class="py-2.5 text-right font-semibold">Rp {{ number_format($serviceOrder->visit_fee) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="mt-5 flex justify-end">
        <div class="w-full max-w-xs space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-semibold">Rp {{ number_format($subtotal) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Pajak PPN 11%</span><span class="font-semibold">Rp {{ number_format($tax) }}</span></div>
            <div class="flex justify-between border-t-2 border-slate-900 pt-2 text-base font-black text-slate-900">
                <span>TOTAL ESTIMASI</span>
                <span>Rp {{ number_format($grandTotal) }}</span>
            </div>
        </div>
    </div>

    {{-- Dua tahap persetujuan --}}
    <div class="mt-6 grid gap-4 text-xs sm:grid-cols-2">
        <div class="rounded-xl border-2 border-dashed border-slate-300 p-4">
            <div class="text-[11px] font-black uppercase tracking-widest text-slate-700">1 · Persetujuan Estimasi</div>
            <p class="mt-1 text-slate-600">Saya menyetujui jenis pekerjaan, sparepart, dan estimasi biaya di atas sebelum pengerjaan dimulai.</p>
            <div class="sign-box mt-4 border-b border-slate-400"></div>
            <div class="mt-1 text-slate-500">Nama &amp; tanda tangan customer</div>
            <div class="mt-2 text-slate-500">
                Status:
                <b>{{ $serviceOrder->estimate_approved_at ? 'Disetujui '.$serviceOrder->estimate_approved_at->format('d M Y H:i') : 'Belum disetujui' }}</b>
            </div>
        </div>
        <div class="rounded-xl border-2 border-dashed border-slate-300 p-4">
            <div class="text-[11px] font-black uppercase tracking-widest text-slate-700">2 · Persetujuan Hasil Pengerjaan</div>
            <p class="mt-1 text-slate-600">Saya mengonfirmasi hasil servis sudah diperiksa SA dan sesuai dengan yang disetujui.</p>
            <div class="sign-box mt-4 border-b border-slate-400"></div>
            <div class="mt-1 text-slate-500">Nama &amp; tanda tangan customer</div>
            <div class="mt-2 text-slate-500">
                Status:
                <b>{{ $serviceOrder->result_confirmed_at ? 'Dikonfirmasi '.$serviceOrder->result_confirmed_at->format('d M Y H:i') : 'Belum dikonfirmasi' }}</b>
            </div>
        </div>
    </div>

    @if($serviceOrder->final_check_at)
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-600">
            <b class="text-slate-800">Pemeriksaan Akhir SA · {{ $serviceOrder->final_check_at->format('d M Y H:i') }}</b>
            <p class="mt-1">{{ $serviceOrder->final_check_notes }}</p>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-end justify-between gap-4 border-t border-slate-100 pt-4 text-sm">
        <div class="text-xs text-slate-400">
            Estimasi ini bersifat perkiraan dan dapat berubah setelah pemeriksaan lebih lanjut.<br>
            Disusun oleh: <b class="text-slate-600">{{ $serviceOrder->sa?->name ?? 'Service Adviser' }}</b> · Jomoto Center
        </div>
        <div class="text-center">
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Hormat kami,</div>
            <div class="mt-10 inline-block border-t border-slate-400 pt-1 font-semibold text-slate-700">
                {{ $serviceOrder->sa?->name ?? 'Service Adviser' }}
                <div class="text-xs font-normal text-slate-400">Service Adviser · Jomoto Center</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
