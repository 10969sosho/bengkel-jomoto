@extends('layouts.app')

@section('title', 'Dokumentasi API ERP')
@section('page_title', 'Dokumentasi Integrasi API')
@section('page_icon', 'braces')

@section('content')
@php
    $endpoints = [
        ['GET', '/customers', '/customers', 'Daftar customer & e-commerce ID', null, ['data' => [['id' => 5, 'name' => 'Hendra Wijaya', 'phone' => '0857-1111-2222', 'whatsapp' => '6285711112222', 'email' => 'hendra@gmail.com', 'ecommerce_user_id' => 1001]]]],
        ['GET', '/customers/{id}', '/customers/5', 'Detail customer', null, ['data' => ['id' => 5, 'name' => 'Hendra Wijaya', 'phone' => '0857-1111-2222', 'whatsapp' => '6285711112222', 'email' => 'hendra@gmail.com', 'ecommerce_user_id' => 1001]]],
        ['GET', '/customers/{id}/vehicles', '/customers/5/vehicles', 'Daftar kendaraan customer', null, ['data' => [['id' => 1, 'brand' => 'Gesits', 'model' => 'G1', 'plate_number' => 'L 3021 KX', 'last_odometer' => 8450]]]],
        ['GET', '/customers/{id}/service-history', '/customers/5/service-history', 'Riwayat servis customer', null, ['data' => [['wo_number' => 'WO-2610-0001', 'status' => 'completed']]]],
        ['GET', '/service-orders', '/service-orders', 'Daftar work order', null, ['data' => [['id' => 1, 'wo_number' => 'WO-2610-0001', 'status' => 'draft']]]],
        ['GET', '/service-orders/{id}', '/service-orders/1', 'Detail work order', null, ['data' => ['id' => 1, 'wo_number' => 'WO-2610-0001', 'status' => 'draft']]],
        ['POST', '/service-orders', '/service-orders', 'Buat work order (JSON)', ['customer_id' => 5, 'vehicle_id' => 1, 'customer_complaint' => 'Servis berkala', 'odometer' => 10000, 'location_type' => 'offsite', 'offsite_address' => 'Jl. Raya Darmo No. 10, Surabaya', 'location_notes' => 'Rumah pagar hitam', 'visit_fee' => 50000], ['data' => ['wo_number' => 'WO-2610-0006', 'service_type' => 'free_jomoto', 'status' => 'draft']]],
        ['GET', '/invoices', '/invoices', 'Daftar invoice', null, ['data' => [['invoice_number' => 'INV-20261104-0001', 'payment_status' => 'paid', 'grand_total' => 395000]]]],
        ['GET', '/invoices/{id}', '/invoices/1', 'Detail invoice dan item', null, ['data' => ['invoice_number' => 'INV-20261104-0001', 'grand_total' => 395000]]],
        ['GET', '/spareparts', '/spareparts', 'Katalog sparepart & stok', null, ['data' => [['id' => 1, 'sku' => 'OLI-MPX2-1L', 'name' => 'Oli Mesin MPX 2 1L', 'stock' => 24, 'ecommerce_product_id' => 5001]]]],
        ['GET', '/spareparts/{id}/stock', '/spareparts/1/stock', 'Bridge sinkronisasi stok', null, ['data' => ['id' => 1, 'sku' => 'OLI-MPX2-1L', 'stock' => 24, 'ecommerce_product_id' => 5001]]],
        ['GET', '/reports/revenue', '/reports/revenue?period=month', 'Omzet & breakdown pendapatan', null, ['data' => ['total' => 1250000, 'breakdown' => ['services' => 350000, 'spareparts' => 700000, 'sub_materials' => 200000]]]],
        ['GET', '/reports/service-summary', '/reports/service-summary', 'Ringkasan status dan jenis servis', null, ['data' => ['total' => 25, 'by_status' => ['completed' => 8], 'by_service_type' => ['regular' => 15]]]],
    ];
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    <section class="rounded-3xl bg-[#0f1117] p-5 text-white shadow-lg sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><div class="text-xs font-bold uppercase tracking-[.18em] text-lime-300">Jomoto Integration API · v1</div><h2 class="mt-2 text-xl font-extrabold">Siap untuk demo ERP &amp; E-Commerce</h2><p class="mt-1 text-sm text-slate-400">Semua request memerlukan Bearer token. Rate limit 60 request/menit.</p></div>
            <div class="min-w-64 flex-1 sm:max-w-sm"><label for="api-token" class="mb-1.5 block text-xs font-bold text-slate-300">Bearer Token</label><input id="api-token" type="password" autocomplete="off" value="{{ app()->isProduction() ? '' : config('services.jomoto_api.demo_key') }}" placeholder="Tempel API_KEY" class="w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 font-mono text-sm text-lime-300 outline-none focus:border-lime-400"></div>
        </div>
        <div class="mt-4 rounded-xl bg-black/40 px-3 py-2 font-mono text-xs text-slate-300">Authorization: Bearer &lt;API_KEY&gt; · Accept: application/json</div>
    </section>

    <div class="space-y-3">
        @foreach($endpoints as [$method, $path, $testPath, $title, $body, $example])
            <details class="group rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5" open>
                <summary class="flex cursor-pointer flex-wrap items-center gap-2.5">
                    <span class="rounded-lg px-2.5 py-1 font-mono text-[11px] font-black {{ $method === 'POST' ? 'bg-sky-100 text-sky-700' : 'bg-lime-100 text-lime-800' }}">{{ $method }}</span>
                    <code class="text-sm font-bold text-slate-900">/api/v1{{ $path }}</code>
                    <span class="ml-auto text-sm text-slate-500">{{ $title }}</span>
                </summary>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div>
                        @if($body)
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Request body</div>
                            <pre class="overflow-x-auto rounded-xl bg-slate-950 p-3 text-xs text-lime-200">{{ json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Contoh response</div>
                            <pre class="overflow-x-auto rounded-xl bg-slate-950 p-3 text-xs text-sky-200">{{ json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                    <div>
                        @if($body)
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Contoh response</div>
                            <pre class="overflow-x-auto rounded-xl bg-slate-950 p-3 text-xs text-sky-200">{{ json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" data-method="{{ $method }}" data-path="{{ $testPath }}" data-body="{{ json_encode($body) }}" data-result="result-{{ $loop->index }}" onclick="copyCurl(this)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Copy cURL</button>
                            <button type="button" data-method="{{ $method }}" data-path="{{ $testPath }}" data-body="{{ json_encode($body) }}" data-result="result-{{ $loop->index }}" onclick="testEndpoint(this)" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-lime-300 hover:bg-slate-700">Test endpoint live</button>
                        </div>
                        <pre id="result-{{ $loop->index }}" class="mt-2 hidden max-h-56 overflow-auto rounded-xl bg-slate-950 p-3 text-xs text-lime-200"></pre>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</div>

<script>
    function requestData(button) {
        return {method: button.dataset.method, path: button.dataset.path, body: JSON.parse(button.dataset.body || 'null'), token: document.getElementById('api-token').value};
    }
    function curlCommand({method, path, body, token}) {
        const quote = value => `'${String(value).replaceAll("'", "'\\''")}'`;
        let command = `curl -X ${method} ${quote(`${location.origin}/api/v1${path}`)} -H 'Accept: application/json' -H ${quote(`Authorization: Bearer ${token}`)}`;
        if (body) command += ` -H 'Content-Type: application/json' -d '${JSON.stringify(body)}'`;
        return command;
    }
    async function copyCurl(button) {
        await navigator.clipboard.writeText(curlCommand(requestData(button)));
        button.textContent = 'cURL disalin';
        setTimeout(() => button.textContent = 'Copy cURL', 1400);
    }
    async function testEndpoint(button) {
        const {method, path, body, token} = requestData(button);
        const output = document.getElementById(button.dataset.result);
        output.classList.remove('hidden');
        output.textContent = 'Menghubungi endpoint...';
        try {
            const response = await fetch(`/api/v1${path}`, {method, headers: {'Accept': 'application/json', 'Authorization': `Bearer ${token}`, ...(body ? {'Content-Type': 'application/json'} : {})}, ...(body ? {body: JSON.stringify(body)} : {})});
            const text = await response.text();
            let data;
            try { data = JSON.parse(text); } catch { data = text; }
            output.textContent = `HTTP ${response.status}\n${JSON.stringify(data, null, 2)}`;
        } catch (error) {
            output.textContent = `Request gagal: ${error.message}`;
        }
    }
</script>
@endsection
