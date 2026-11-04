@extends('layouts.app')

@section('title', 'Kelola '.$serviceOrder->wo_number)
@section('page_title', 'Kelola '.$serviceOrder->wo_number)
@section('page_icon', 'clipboard-pen-line')

@section('content')
<a href="{{ route('sa.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
    <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke daftar WO
</a>

<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="font-mono text-xl font-extrabold text-slate-900">{{ $serviceOrder->wo_number }}</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $serviceOrder->serviceTypeLabel() }} · {{ $serviceOrder->location_type === 'onsite' ? 'Di Bengkel' : 'Panggilan' }} ·
            Dibuat {{ $serviceOrder->created_at->format('d M Y H:i') }}
        </p>
    </div>
    <span class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $serviceOrder->statusColor() }}">{{ $serviceOrder->statusLabel() }}</span>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-3">
    {{-- Info ringkas --}}
    <div class="space-y-5 lg:col-span-1">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="user-round" class="h-4 w-4"></i> Customer &amp; Motor
            </h2>
            <div class="text-sm">
                <div class="font-semibold text-slate-900">{{ $serviceOrder->customer?->name }}</div>
                <div class="text-slate-500">{{ $serviceOrder->customer?->email }}</div>
                <div class="mt-1 text-slate-500">WA: {{ $serviceOrder->customer?->whatsapp ?: $serviceOrder->customer?->phone ?: '—' }}</div>

                <div class="mt-4 rounded-2xl bg-slate-50 p-3.5">
                    <div class="font-semibold text-slate-800">{{ $serviceOrder->vehicle?->fullName() }}</div>
                    <div class="mt-1.5 grid grid-cols-2 gap-1.5 text-xs text-slate-500">
                        <span class="rounded-lg bg-white px-2 py-1">Plat: <b class="text-slate-700">{{ $serviceOrder->vehicle?->plate_number }}</b></span>
                        <span class="rounded-lg bg-white px-2 py-1">{{ $serviceOrder->vehicle?->fuelLabel() }}</span>
                        <span class="rounded-lg bg-white px-2 py-1">Tahun: {{ $serviceOrder->vehicle?->year }}</span>
                        <span class="rounded-lg bg-white px-2 py-1">Odo: {{ number_format($serviceOrder->vehicle?->last_odometer ?? 0) }} KM</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Keluhan</div>
                    <p class="mt-1 text-slate-700">{{ $serviceOrder->customer_complaint }}</p>
                </div>

                @if($serviceOrder->sa_diagnosis)
                    <div class="mt-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Diagnosis SA</div>
                        <p class="mt-1 text-slate-700">{{ $serviceOrder->sa_diagnosis }}</p>
                    </div>
                @endif

                <a href="{{ $serviceOrder->waLink() }}" target="_blank" rel="noopener"
                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600 active:scale-[.98]">
                    <i data-lucide="message-circle" class="h-4 w-4"></i> Kirim Reminder WhatsApp
                </a>
            </div>
        </div>

        {{-- Assign teknisi --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="hard-hat" class="h-4 w-4"></i> Assign Teknisi
            </h2>
            <form method="POST" action="{{ route('sa.assign', $serviceOrder) }}" class="space-y-3">
                @csrf
                <select name="technician_id" required
                        class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                    <option value="">— Pilih teknisi —</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" @selected($serviceOrder->technician_id === $tech->id)>{{ $tech->name }}</option>
                    @endforeach
                </select>
                <input type="datetime-local" name="estimated_completion"
                       value="{{ optional($serviceOrder->estimated_completion)->format('Y-m-d\TH:i') }}"
                       placeholder="Target selesai"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700 active:scale-[.98]">
                    Simpan Assignment
                </button>
            </form>
            <div class="mt-3 text-sm text-slate-500">
                Teknisi aktif: <b class="text-slate-800">{{ $serviceOrder->technician?->name ?? 'Belum ada' }}</b>
                @if($serviceOrder->estimated_completion)
                    <br>Target selesai: <b>{{ $serviceOrder->estimated_completion->format('d M Y H:i') }}</b>
                @endif
            </div>
        </div>

        {{-- Inspeksi --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="clipboard-check" class="h-4 w-4"></i> Lembar Pemeriksaan
            </h2>
            @if($serviceOrder->inspection)
                @php($checklist = $serviceOrder->inspection->checklist ?? [])
                <div class="flex flex-wrap gap-1.5">
                    @foreach($checklist as $item)
                        @php($color = $item['status'] === 'ok' ? 'bg-lime-50 text-lime-700' : ($item['status'] === 'attention' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'))
                        <span class="rounded-lg px-2 py-1 text-[11px] font-semibold {{ $color }}">{{ $item['label'] }}</span>
                    @endforeach
                </div>
                @if($serviceOrder->inspection->findings)
                    <p class="mt-3 text-sm text-slate-600"><b>Temuan:</b> {{ $serviceOrder->inspection->findings }}</p>
                @endif
                @if($serviceOrder->inspection->recommendations)
                    <p class="mt-1 text-sm text-slate-600"><b>Rekomendasi:</b> {{ $serviceOrder->inspection->recommendations }}</p>
                @endif
            @else
                <p class="text-sm text-slate-400">Belum diisi teknisi.</p>
            @endif
        </div>

        @if($serviceOrder->workLogs->isNotEmpty())
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <i data-lucide="timer" class="h-4 w-4"></i> Waktu Pengerjaan
            </h2>
            @foreach($serviceOrder->workLogs as $log)
                <div class="flex items-center justify-between border-b border-slate-50 py-1.5 text-sm last:border-0">
                    <span class="text-slate-500">{{ $log->started_at->format('d M H:i') }}</span>
                    <span class="font-semibold {{ $log->isOpen() ? 'text-indigo-600' : 'text-slate-700' }}">
                        @if($log->isOpen()) ● Berjalan @else {{ gmdate('H:i:s', $log->duration_seconds) }} @endif
                    </span>
                </div>
            @endforeach
            <div class="mt-2 flex justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm font-bold">
                <span class="text-slate-500">Total</span>
                <span class="font-mono text-slate-900" id="total-elapsed">{{ gmdate('H:i:s', $serviceOrder->elapsedSeconds()) }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Kolom kanan: estimasi & invoice --}}
    <div class="space-y-5 lg:col-span-2">
        @if(in_array($serviceOrder->status, ['draft', 'waiting_approval']))
            {{-- Lembar Estimasi Biaya --}}
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="flex items-center gap-2 font-bold text-slate-900">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-lime-300 text-black">
                                <i data-lucide="calculator" class="h-4 w-4"></i>
                            </span>
                            Lembar Estimasi Biaya
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">Jasa dari pricelist · Sparepart HET · Sub-Material custom (open item).</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="addRow('service')" class="rounded-xl bg-lime-50 px-3 py-2 text-xs font-bold text-lime-700 transition hover:bg-lime-100 active:scale-95">+ Jasa</button>
                        <button type="button" onclick="addRow('sparepart')" class="rounded-xl bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 transition hover:bg-sky-100 active:scale-95">+ Sparepart</button>
                        <button type="button" onclick="addRow('sub_material')" class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100 active:scale-95">+ Sub-Material</button>
                    </div>
                </div>

                <form method="POST" action="{{ route('sa.estimate', $serviceOrder) }}" id="estimate-form">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Diagnosis SA</label>
                        <textarea name="sa_diagnosis" rows="2"
                                  class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200"
                                  placeholder="Hasil analisa SA setelah melihat inspeksi teknisi...">{{ old('sa_diagnosis', $serviceOrder->sa_diagnosis) }}</textarea>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="items-table">
                            <thead class="border-b border-slate-200 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="pb-2 pr-2">Jenis</th>
                                    <th class="pb-2 pr-2">Nama Item</th>
                                    <th class="pb-2 pr-2 text-center">Qty</th>
                                    <th class="pb-2 pr-2 text-right">Harga</th>
                                    <th class="whitespace-nowrap pb-2 pr-2 text-center">Gratis</th>
                                    <th class="whitespace-nowrap pb-2 text-right">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="items-body" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <div class="text-xs text-slate-400" id="empty-hint">Belum ada item — tambahkan minimal 1 jasa/sparepart/sub-material.</div>
                        <div class="min-w-[260px] rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm">
                            <div class="flex justify-between py-0.5"><span class="text-slate-500">Total Jasa</span><span id="sum-service" class="font-semibold">Rp 0</span></div>
                            <div class="flex justify-between py-0.5"><span class="text-slate-500">Total Sparepart</span><span id="sum-sparepart" class="font-semibold">Rp 0</span></div>
                            <div class="flex justify-between py-0.5"><span class="text-slate-500">Total Sub-Material</span><span id="sum-sub_material" class="font-semibold">Rp 0</span></div>
                            <div class="mt-1 flex justify-between border-t border-slate-200 pt-1.5"><span class="font-semibold text-slate-700">Subtotal</span><span id="sum-total" class="font-bold text-slate-900">Rp 0</span></div>
                            <div class="flex justify-between text-xs text-slate-400"><span>Pajak 11% (dihitung saat invoice)</span><span id="sum-tax">Rp 0</span></div>
                            <div class="mt-1 flex justify-between rounded-xl bg-slate-900 px-3 py-2 text-base"><span class="font-bold text-white">Estimasi + Pajak</span><span id="sum-grand" class="font-mono font-bold text-lime-300">Rp 0</span></div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-lime-400 active:scale-[.98]">
                            <i data-lucide="send" class="h-4 w-4"></i> Simpan Estimasi &amp; Minta Persetujuan
                        </button>
                    </div>
                </form>

                <div class="mt-3 rounded-2xl bg-slate-50 p-3.5 text-xs leading-relaxed text-slate-500">
                    <b class="text-slate-700">Data pricelist:</b><br>
                    @foreach($pricelists->groupBy('category') as $cat => $rows)
                        <span class="mr-2 font-semibold text-slate-600">{{ $rows->first()->categoryLabel() }}:</span>
                        {{ $rows->map(fn($p) => $p->service_name.' (Rp '.number_format($p->price).')')->join(' · ') }}<br>
                    @endforeach
                    <b class="text-slate-600">Sub-material:</b>
                    {{ $subMaterials->map(fn($s) => $s->name.' (Rp '.number_format($s->default_price).')')->join(' · ') }}
                </div>
            </div>
        @else
            {{-- Rincian item read-only --}}
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="mb-4 font-bold text-slate-900">Rincian Biaya</h2>
                @if($serviceOrder->items->isEmpty())
                    <p class="text-sm text-slate-400">Belum ada rincian biaya.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-200 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="pb-2">Jenis</th>
                                    <th class="pb-2">Item</th>
                                    <th class="pb-2 text-center">Qty</th>
                                    <th class="pb-2 text-right">Harga</th>
                                    <th class="pb-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($serviceOrder->items as $item)
                                    <tr>
                                        <td class="py-2.5">
                                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold
                                                {{ $item->item_type === 'service' ? 'bg-lime-50 text-lime-700' : ($item->item_type === 'sparepart' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700') }}">
                                                {{ $item->typeLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-2.5">
                                            {{ $item->item_name }}
                                            @if($item->is_free)
                                                <div class="text-xs text-emerald-600">GRATIS — {{ $item->free_reason }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                                        <td class="py-2.5 text-right">Rp {{ number_format($item->unit_price) }}</td>
                                        <td class="py-2.5 text-right font-semibold {{ $item->is_free ? 'text-emerald-600' : '' }}">
                                            {{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Aksi persetujuan / invoice --}}
        @if($serviceOrder->status === 'waiting_approval')
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-amber-900">
                    <i data-lucide="hourglass" class="h-4 w-4"></i> Menunggu Persetujuan Customer
                </h3>
                <p class="mt-1 text-sm text-amber-700">Tombol di bawah mensimulasikan persetujuan customer atas lembar estimasi.</p>
                <form method="POST" action="{{ route('sa.approve', $serviceOrder) }}" class="mt-3">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-2.5 text-sm font-bold text-amber-950 transition hover:bg-amber-500 active:scale-[.98]">
                        <i data-lucide="check-check" class="h-4 w-4"></i> Simulasikan Persetujuan Customer
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->status === 'completed')
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                <h3 class="flex items-center gap-2 font-bold text-emerald-900">
                    <i data-lucide="badge-check" class="h-4 w-4"></i> Pengerjaan Selesai
                </h3>
                <p class="mt-1 text-sm text-emerald-700">Terbitkan invoice / kwitansi resmi untuk customer.</p>
                <form method="POST" action="{{ route('sa.invoice', $serviceOrder) }}" class="mt-3">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[.98]">
                        <i data-lucide="receipt-text" class="h-4 w-4"></i> Terbitkan Invoice / Kwitansi
                    </button>
                </form>
            </div>
        @endif

        @if($serviceOrder->invoice)
            @php($invoice = $serviceOrder->invoice)
            <div class="rounded-3xl border border-purple-200 bg-purple-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="flex items-center gap-2 font-bold text-purple-900">
                            <i data-lucide="receipt" class="h-4 w-4"></i> {{ $invoice->invoice_number }}
                        </h3>
                        <p class="mt-1 text-sm text-purple-700">
                            Grand total: <b>Rp {{ number_format($invoice->grand_total) }}</b> ·
                            Status:
                            <b class="rounded-full {{ $invoice->isPaid() ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }} px-2 py-0.5 text-[11px] uppercase">
                                {{ $invoice->isPaid() ? 'Lunas' : 'Belum Bayar' }}
                            </b>
                        </p>
                    </div>
                    <a href="{{ route('invoices.print', $invoice) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-purple-700 active:scale-[.98]">
                        <i data-lucide="printer" class="h-4 w-4"></i> Cetak Kwitansi
                    </a>
                </div>

                @if(! $invoice->isPaid())
                    <form method="POST" action="{{ route('sa.invoices.pay', $invoice) }}" class="mt-4 flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-purple-600">Metode Bayar</label>
                            <select name="payment_method" required
                                    class="cursor-pointer rounded-xl border-purple-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-purple-400 focus:ring-2 focus:ring-purple-200">
                                <option value="qris">QRIS</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-purple-700 active:scale-[.98]">
                            <i data-lucide="credit-card" class="h-4 w-4"></i> Tandai Sudah Bayar
                        </button>
                    </form>
                    <div class="mt-3 inline-flex items-center gap-2 rounded-2xl border border-dashed border-purple-300 bg-white px-4 py-2.5 text-xs font-bold tracking-widest text-purple-600">
                        <i data-lucide="qr-code" class="h-4 w-4"></i> QRIS · {{ $invoice->invoice_number }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Data untuk lembar estimasi --}}
<script>
    const pricelist = @json($pricelists->map(fn($p) => ['name' => $p->service_name, 'price' => (int) $p->price]));
    const subMaterials = @json($subMaterials->map(fn($s) => ['name' => $s->name, 'price' => (int) $s->default_price]));
    @php($existingItems = $serviceOrder->items->map(fn($i) => ['item_type' => $i->item_type, 'item_name' => $i->item_name, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price, 'is_free' => $i->is_free, 'free_reason' => $i->free_reason])->values())
    const existingItems = @json($existingItems);

    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const body = document.getElementById('items-body');
    if (!body) throw new Error('no estimate form');

    function optionsFor(type) {
        const source = type === 'service' ? pricelist : (type === 'sub_material' ? subMaterials : []);
        return source.map(o => `<option value="${o.name}" data-price="${o.price}">${o.name} — ${rupiah(o.price)}</option>`).join('');
    }

    function addRow(type, item = {}) {
        const idx = body.children.length;
        const tr = document.createElement('tr');
        tr.dataset.type = type;

        const customName = type === 'sparepart';
        const presetPrice = item.unit_price ?? (type === 'service' ? (pricelist[0]?.price ?? 0) : (subMaterials[0]?.price ?? 0));

        tr.innerHTML = `
            <td class="py-2 pr-2 align-top">
                <span class="rounded-md px-2 py-0.5 text-xs font-semibold ${type === 'service' ? 'bg-lime-50 text-lime-700' : (type === 'sparepart' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700')}">
                    ${type === 'service' ? 'Jasa' : (type === 'sparepart' ? 'Sparepart' : 'Sub-Material')}
                </span>
                <input type="hidden" name="items[${idx}][item_type]" value="${type}">
            </td>
            <td class="py-2 pr-2 align-top">
                ${customName
                    ? `<input required name="items[${idx}][item_name]" value="${item.item_name ?? ''}" placeholder="Nama sparepart (HET)"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">`
                    : `<select required name="items[${idx}][item_name]" onchange="onNameChange(this)"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-lime-200">
                        ${optionsFor(type)}
                        ${type === 'service' && !pricelist.find(p => p.name === item.item_name) && item.item_name ? `<option selected value="${item.item_name}">${item.item_name}</option>` : ''}
                        ${type === 'sub_material' && item.item_name ? `<option selected value="${item.item_name}">${item.item_name}</option>` : ''}
                       </select>`}
                <input type="hidden" name="items[${idx}][free_reason]" class="free-reason"
                       value="${item.free_reason ?? ''}" placeholder="Alasan gratis">
            </td>
            <td class="py-2 pr-2 align-top">
                <input type="number" name="items[${idx}][quantity]" min="1" value="${item.quantity ?? 1}" oninput="recalc()"
                    class="w-16 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-center text-sm outline-none focus:ring-2 focus:ring-lime-200">
            </td>
            <td class="py-2 pr-2 align-top text-right">
                <input type="number" name="items[${idx}][unit_price]" min="0" value="${item.unit_price ?? presetPrice}" oninput="recalc()"
                    class="w-28 rounded-lg border-slate-200 bg-slate-50 px-2 py-1.5 text-right text-sm outline-none focus:ring-2 focus:ring-lime-200">
            </td>
            <td class="py-2 pr-2 align-top text-center">
                <input type="checkbox" name="items[${idx}][is_free]" value="1" ${item.is_free ? 'checked' : ''} onchange="recalc()"
                    class="h-4 w-4 rounded border-slate-300 text-lime-500 focus:ring-lime-400">
            </td>
            <td class="whitespace-nowrap py-2 align-top text-right font-semibold row-subtotal">Rp 0</td>
            <td class="py-2 align-top text-right">
                <button type="button" onclick="this.closest('tr').remove(); reindex(); recalc()"
                    class="text-xs font-bold text-red-400 transition hover:text-red-600">✕</button>
            </td>`;

        body.appendChild(tr);

        if (type === 'service' && item.item_name && !pricelist.find(p => p.name === item.item_name)) {
            const sel = tr.querySelector('select');
            const opt = document.createElement('option');
            opt.value = item.item_name;
            opt.textContent = item.item_name;
            opt.selected = true;
            sel.appendChild(opt);
        }

        if (item.is_free) {
            const reason = tr.querySelector('.free-reason');
            reason.type = 'text';
            if (!reason.value) reason.value = 'Tanpa biaya';
        }

        document.getElementById('empty-hint').style.display = 'none';
        recalc();
    }

    function onNameChange(sel) {
        const opt = sel.selectedOptions[0];
        const price = opt?.dataset.price;
        if (price !== undefined) {
            const row = sel.closest('tr');
            row.querySelector('[name$="[unit_price]"]').value = price;
        }
        recalc();
    }

    function reindex() {
        [...body.children].forEach((tr, i) => {
            tr.querySelector('[name$="[item_type]"]').name = `items[${i}][item_type]`;
            tr.querySelector('[name$="[item_name]"]').name = `items[${i}][item_name]`;
            tr.querySelector('[name$="[quantity]"]').name = `items[${i}][quantity]`;
            tr.querySelector('[name$="[unit_price]"]').name = `items[${i}][unit_price]`;
            tr.querySelector('[name$="[is_free]"]').name = `items[${i}][is_free]`;
            tr.querySelector('.free-reason').name = `items[${i}][free_reason]`;
        });
        if (!body.children.length) document.getElementById('empty-hint').style.display = '';
    }

    function recalc() {
        const sums = { service: 0, sparepart: 0, sub_material: 0 };

        [...body.children].forEach(tr => {
            const free = tr.querySelector('[name$="[is_free]"]').checked;
            const qty = Number(tr.querySelector('[name$="[quantity]"]').value || 0);
            const price = Number(tr.querySelector('[name$="[unit_price]"]').value || 0);
            const subtotal = free ? 0 : qty * price;
            tr.querySelector('.row-subtotal').textContent = rupiah(subtotal);
            sums[tr.dataset.type] += subtotal;

            const reasonInput = tr.querySelector('.free-reason');
            if (free) { reasonInput.type = 'text'; if (!reasonInput.value) reasonInput.value = 'Tanpa biaya'; }
            else { reasonInput.value = ''; }
        });

        const total = sums.service + sums.sparepart + sums.sub_material;
        const tax = Math.round(total * 0.11);

        document.getElementById('sum-service').textContent = rupiah(sums.service);
        document.getElementById('sum-sparepart').textContent = rupiah(sums.sparepart);
        document.getElementById('sum-sub_material').textContent = rupiah(sums.sub_material);
        document.getElementById('sum-total').textContent = rupiah(total);
        document.getElementById('sum-tax').textContent = rupiah(tax);
        document.getElementById('sum-grand').textContent = rupiah(total + tax);
    }

    existingItems.forEach(item => addRow(item.item_type, item));

    document.getElementById('estimate-form').addEventListener('submit', e => {
        if (!body.children.length) {
            e.preventDefault();
            alert('Tambahkan minimal 1 item estimasi.');
        }
    });
</script>

@if($serviceOrder->elapsedSeconds() > 0)
<script>
    const elapsedBase = {{ $serviceOrder->elapsedSeconds() }};
    const elapsedStartedAt = Date.now();
    const tick = () => {
        const el = document.getElementById('total-elapsed');
        if (el) el.textContent = new Date((elapsedBase + Math.floor((Date.now() - elapsedStartedAt) / 1000)) * 1000).toISOString().substr(11, 8);
    };
    setInterval(tick, 1000);
</script>
@endif
@endsection
