<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kwitansi {{ $invoice->invoice_number }} — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 sm:p-8">

<div class="no-print mx-auto mb-4 flex max-w-3xl flex-wrap items-center justify-between gap-2">
    <a href="{{ url()->previous() }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Kembali</a>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">Dashboard</a>
        <button onclick="window.print()" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            🖨 Cetak / Simpan PDF
        </button>
    </div>
</div>

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
            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice / Kwitansi</div>
            <div class="text-xl font-black text-slate-900">{{ $invoice->invoice_number }}</div>
            <div class="mt-1 text-xs text-slate-500">Tanggal: {{ $invoice->created_at->format('d F Y') }}</div>
        </div>
    </div>

    {{-- Info WO & customer --}}
    @php($order = $invoice->serviceOrder)
    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-slate-50 p-4 text-sm">
            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Diterima dari</div>
            <div class="font-bold text-slate-900">{{ $order->customer?->name }}</div>
            <div class="text-slate-600">{{ $order->customer?->email }}</div>
            <div class="text-slate-600">WA: {{ $order->customer?->whatsapp ?: $order->customer?->phone ?: '—' }}</div>
            <div class="mt-2 text-xs text-slate-400">No. WO: <b class="text-slate-600">{{ $order->wo_number }}</b></div>
        </div>
        <div class="rounded-xl bg-slate-50 p-4 text-sm">
            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Kendaraan</div>
            <div class="font-bold text-slate-900">{{ $order->vehicle?->fullName() }}</div>
            <div class="text-slate-600">Plat: {{ $order->vehicle?->plate_number }} · {{ $order->vehicle?->fuelLabel() }}</div>
            <div class="text-slate-600">Tahun {{ $order->vehicle?->year }} · Odo {{ number_format($order->vehicle?->last_odometer ?? 0) }} KM</div>
            <div class="mt-2 text-xs text-slate-400">Teknisi: <b class="text-slate-600">{{ $order->technician?->name ?? '—' }}</b></div>
        </div>
    </div>

    @if($order->customer_complaint)
        <div class="mt-4 rounded-xl border border-slate-100 p-4 text-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Keluhan / Pekerjaan</div>
            <p class="mt-1 text-slate-700">{{ $order->customer_complaint }}</p>
            @if($order->sa_diagnosis)
                <p class="mt-1 text-slate-600"><b>Diagnosis:</b> {{ $order->sa_diagnosis }}</p>
            @endif
        </div>
    @endif

    {{-- Rincian biaya --}}
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
            @foreach($order->items as $item)
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
                    <td class="py-2.5 text-right font-semibold">
                        {{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}
                    </td>
                </tr>
            @endforeach
            @if($order->visit_fee > 0)
                <tr>
                    <td class="py-2.5"><span class="mr-2 rounded bg-lime-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-lime-700">Home Service</span>Biaya kunjungan teknisi</td>
                    <td class="py-2.5 text-center">1</td>
                    <td class="py-2.5 text-right">Rp {{ number_format($order->visit_fee) }}</td>
                    <td class="py-2.5 text-right font-semibold">Rp {{ number_format($order->visit_fee) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Total --}}
    <div class="mt-5 flex justify-end">
        <div class="w-full max-w-xs space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Total Jasa</span><span class="font-semibold">Rp {{ number_format($invoice->total_services) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Total Sparepart</span><span class="font-semibold">Rp {{ number_format($invoice->total_spareparts) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Total Sub-Material</span><span class="font-semibold">Rp {{ number_format($invoice->total_submaterials) }}</span></div>
            @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-600"><span>Diskon</span><span class="font-semibold">- Rp {{ number_format($invoice->discount_amount) }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-slate-500">Pajak PPN 11%</span><span class="font-semibold">Rp {{ number_format($invoice->tax_amount) }}</span></div>
            <div class="flex justify-between border-t-2 border-slate-900 pt-2 text-base font-black text-slate-900">
                <span>GRAND TOTAL</span>
                <span>Rp {{ number_format($invoice->grand_total) }}</span>
            </div>
        </div>
    </div>

    {{-- Payment badge --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-dashed border-slate-300 p-4">
        <div class="flex items-center gap-3">
            @if($invoice->isPaid() && $invoice->payment_method === 'qris')
                <span class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-black tracking-widest text-white">QRIS</span>
            @elseif($invoice->isPaid())
                <span class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-black tracking-widest text-white">{{ strtoupper($invoice->payment_method) }}</span>
            @else
                <span class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-black tracking-widest text-amber-700">BELUM DIBAYAR</span>
            @endif
            <div class="text-sm">
                <div class="font-bold text-slate-800">
                    {{ $invoice->isPaid() ? 'LUNAS' : 'Menunggu Pembayaran' }}
                </div>
                <div class="text-xs text-slate-500">
                    {{ $invoice->isPaid() ? 'Dibayar pada '.$invoice->paid_at->format('d F Y H:i') : 'Metode: QRIS / Cash / Transfer via SA' }}
                </div>
                @if($invoice->isPaid() && ($invoice->payment_reference || $invoice->bank_account))
                    <div class="mt-1 text-xs text-slate-500">{{ $invoice->bank_account }}{{ $invoice->payment_reference ? ' · Ref '.$invoice->payment_reference : '' }}</div>
                @endif
            </div>
        </div>

        <div class="grid h-20 w-20 grid-cols-5 gap-0.5 rounded bg-white p-1 ring-1 ring-slate-200" aria-hidden="true">
            @for($i = 0; $i < 25; $i++)
                <div class="{{ (crc32($invoice->invoice_number.$i) % 3) === 0 ? 'bg-slate-900' : 'bg-white' }}"></div>
            @endfor
        </div>
    </div>

    {{-- Terbilang & tanda tangan --}}
    <div class="mt-6 grid gap-6 text-sm sm:grid-cols-2">
        <div>
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Terbilang</div>
            <div class="mt-1 font-semibold italic text-slate-700">{{ \App\Models\Invoice::terbilang((int) $invoice->grand_total) }} rupiah</div>
            <div class="mt-4 text-xs text-slate-400">No. Faktur: <b class="text-slate-600">{{ $invoice->invoice_number }}</b></div>
        </div>
        <div class="text-center">
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Hormat kami,</div>
            <div class="mt-10 inline-block border-t border-slate-400 pt-1 font-semibold text-slate-700">
                {{ $order->sa?->name ?? 'Service Adviser' }}
                <div class="text-xs font-normal text-slate-400">Service Adviser · Jomoto Center</div>
            </div>
        </div>
    </div>

    <div class="mt-8 border-t border-slate-100 pt-3 text-center text-[11px] text-slate-400">
        Kwitansi ini sah tanpa tanda tangan basah. Terima kasih telah mempercayakan servis motor Anda di Jomoto Center. 🔧
    </div>
</div>

</body>
</html>
