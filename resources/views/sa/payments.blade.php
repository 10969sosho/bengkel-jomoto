@extends('layouts.app')

@section('title', 'Kasir & Pembayaran')
@section('page_title', 'Kasir & Pembayaran')
@section('page_icon', 'wallet-cards')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Kasir & Pembayaran</h1>
    <p class="text-sm text-slate-500">Kelola invoice, konfirmasi pembayaran, dan cetak kwitansi.</p>
</div>

<div class="mb-5 grid gap-3 sm:grid-cols-3">
    @foreach([['Total Tagihan Tertunda', $stats['unpaid'], 'text-red-600', 'credit-card'], ['Pembayaran Lunas Hari Ini', $stats['paid_today'], 'text-emerald-600', 'badge-check'], ['Total Transaksi', $stats['transactions'], 'text-slate-900', 'receipt-text']] as [$label, $value, $color, $icon])
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between"><i data-lucide="{{ $icon }}" class="h-5 w-5 {{ $color }}"></i><span class="text-xl font-extrabold {{ $color }}">{{ $label === 'Total Transaksi' ? number_format($value) : 'Rp '.number_format($value) }}</span></div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <nav class="flex flex-wrap gap-2">
        @foreach(['all' => 'Semua', 'unpaid' => 'Belum Bayar', 'paid' => 'Sudah Lunas'] as $key => $label)
            <a href="{{ route('sa.payments.index', ['status' => $key, 'q' => $search ?: null]) }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ $filter === $key ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <form method="GET" class="flex min-w-64 flex-1 gap-2 sm:max-w-md">
        <input type="hidden" name="status" value="{{ $filter }}">
        <input name="q" value="{{ $search }}" placeholder="No. invoice, customer, atau plat" class="min-w-0 flex-1 rounded-xl border-slate-200 bg-white px-3.5 py-2.5 text-sm">
        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Cari</button>
    </form>
</div>

<div class="space-y-3">
    @forelse($invoices as $invoice)
        @php($order = $invoice->serviceOrder)
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2"><span class="font-mono font-bold text-slate-900">{{ $invoice->invoice_number }}</span><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $invoice->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $invoice->isPaid() ? 'LUNAS' : 'BELUM BAYAR' }}</span></div>
                    <div class="mt-1 text-sm font-semibold text-slate-700">{{ $order?->customer?->name }} · {{ $order?->vehicle?->plate_number }}</div>
                    <div class="text-xs text-slate-400">{{ $order?->wo_number }} · {{ $invoice->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="text-right"><div class="text-xs text-slate-400">Total tagihan</div><div class="text-lg font-extrabold text-slate-900">Rp {{ number_format($invoice->grand_total) }}</div></div>
            </div>

            @if($invoice->isPaid())
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-emerald-50/60 px-5 py-3">
                    <div class="text-xs font-semibold text-emerald-800">{{ strtoupper($invoice->payment_method) }}{{ $invoice->paid_at ? ' · '.$invoice->paid_at->format('d/m/Y H:i') : '' }}{{ $invoice->payment_reference ? ' · Ref '.$invoice->payment_reference : '' }}</div>
                    <a href="{{ route('invoices.print', $invoice) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700"><i data-lucide="printer" class="h-4 w-4"></i> Cetak Kwitansi</a>
                </div>
            @else
                <details class="border-t border-slate-100">
                    <summary class="cursor-pointer bg-slate-50 px-5 py-3 text-sm font-bold text-purple-700">Proses Pembayaran</summary>
                    <form method="POST" action="{{ route('sa.payments.pay', $invoice) }}" data-payment-form class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)]">
                        @csrf
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Metode Pembayaran</label>
                            <select name="payment_method" data-payment-method class="w-full rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm">
                                <option value="qris">QRIS</option><option value="cash">Tunai (Cash)</option><option value="transfer">Transfer Bank</option>
                            </select>

                            <div data-payment-panel="qris" class="mt-4 rounded-2xl border border-purple-100 bg-purple-50 p-4">
                                <div class="flex flex-wrap items-center gap-4">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&amp;data={{ urlencode('JOMOTO|QRIS|'.$invoice->invoice_number.'|'.$invoice->grand_total) }}" alt="QR code pembayaran {{ $invoice->invoice_number }}" width="128" height="128" class="rounded-xl border border-white bg-white p-2">
                                    <div class="text-sm"><b class="text-purple-900">Scan QRIS</b><div class="mt-1 text-xs text-purple-700">Rp {{ number_format($invoice->grand_total) }}</div><div class="mt-2 text-xs text-slate-500">Referensi: <b>QRIS-{{ $invoice->invoice_number }}</b></div></div>
                                </div>
                                <input type="hidden" name="payment_reference" value="QRIS-{{ $invoice->invoice_number }}">
                                <p class="mt-3 text-[11px] text-purple-700">Demo: konfirmasi setelah pembayaran berhasil diterima.</p>
                            </div>

                            <div data-payment-panel="cash" class="mt-4 hidden rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <label class="mb-1.5 block text-sm font-semibold text-amber-900">Nominal uang diterima (Rp)</label>
                                <input type="number" name="cash_received" min="{{ $invoice->grand_total }}" step="1" data-cash-received class="w-full rounded-xl border-amber-200 bg-white px-3 py-2.5 text-sm" placeholder="Masukkan nominal">
                                <div class="mt-3 flex justify-between text-sm"><span class="text-amber-800">Uang kembalian</span><b data-cash-change class="text-amber-950">Rp 0</b></div>
                            </div>

                            <div data-payment-panel="transfer" class="mt-4 hidden space-y-3 rounded-2xl border border-sky-100 bg-sky-50 p-4">
                                <div><label class="mb-1.5 block text-sm font-semibold text-sky-900">Rekening tujuan</label>
                                    <select name="bank_account" class="w-full rounded-xl border-sky-200 bg-white px-3 py-2.5 text-sm"><option value="">Pilih rekening bank</option>@foreach($bankAccounts as $account)<option value="{{ $account }}">{{ $account }}</option>@endforeach</select>
                                </div>
                                <div><label class="mb-1.5 block text-sm font-semibold text-sky-900">No. referensi transfer</label><input name="payment_reference" maxlength="100" placeholder="Contoh: TRX-20261104-001" class="w-full rounded-xl border-sky-200 bg-white px-3 py-2.5 text-sm"></div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-end gap-2">
                            <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Konfirmasi pembayaran akan mengubah status invoice menjadi lunas.</div>
                            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 px-4 py-3 text-sm font-bold text-white hover:bg-purple-700"><i data-lucide="circle-check" class="h-4 w-4"></i> Konfirmasi Pembayaran</button>
                        </div>
                    </form>
                </details>
            @endif
        </article>
    @empty
        <div class="rounded-3xl border border-slate-200 bg-white px-5 py-12 text-center text-sm text-slate-400">Tidak ada invoice yang sesuai filter.</div>
    @endforelse
</div>
@if($invoices->hasPages())<div class="mt-5">{{ $invoices->links() }}</div>@endif

<script>
document.querySelectorAll('[data-payment-form]').forEach(form => {
    const method = form.querySelector('[data-payment-method]');
    const updateMethod = () => {
        form.querySelectorAll('[data-payment-panel]').forEach(panel => {
            const active = panel.dataset.paymentPanel === method.value;
            panel.classList.toggle('hidden', !active);
            panel.querySelectorAll('input, select').forEach(input => {
                input.disabled = !active;
                input.required = active && input.name !== 'payment_reference' ? true : (active && input.name === 'payment_reference' && method.value === 'transfer');
            });
            const refInput = panel.querySelector('[name="payment_reference"]');
            if (refInput && method.value === 'qris') {
                refInput.disabled = false;
            }
        });
    };
    method.addEventListener('change', updateMethod);
    const received = form.querySelector('[data-cash-received]');
    received.addEventListener('input', () => {
        const due = Number(received.min);
        const change = Math.max(0, Number(received.value || 0) - due);
        form.querySelector('[data-cash-change]').textContent = `Rp ${change.toLocaleString('id-ID')}`;
    });
    updateMethod();
});
</script>
@endsection
