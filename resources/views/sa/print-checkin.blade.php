<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemeriksaan Awal (Check-in) - {{ $order->wo_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .brand h1 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; }
        .brand p { margin: 2px 0 0 0; font-size: 10px; color: #64748b; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; }
        .doc-title .meta { font-family: monospace; font-size: 11px; font-weight: 700; margin-top: 2px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px; }
        .box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
        }
        .box-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { width: 110px; color: #64748b; }
        .info-val { flex: 1; font-weight: 600; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; }
        th { background: #f1f5f9; font-weight: 700; text-align: left; }
        .text-center { text-align: center; }
        .badge-g { background: #dcfce7; color: #166534; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; }
        .badge-r { background: #ffe4e6; color: #9f1239; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
            text-align: center;
        }
        .sign-col { border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px; }
        .sign-space { height: 50px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="no-print" style="background: #f1f5f9; padding: 10px; text-align: center; border-bottom: 1px solid #cbd5e1;">
    <button onclick="window.print()" style="background: #0f172a; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">
        🖨️ Cetak Lembar Pemeriksaan Awal (Check-in)
    </button>
    <a href="{{ route('sa.show', $order) }}" style="margin-left: 12px; color: #475569; text-decoration: none; font-size: 12px; font-weight: 600;">
        ← Kembali ke WO
    </a>
</div>

<div class="container">
    <div class="header">
        <div class="brand">
            <h1>BENGKEL JOMOTO CENTER</h1>
            <p>Spesialis Sepeda Motor &amp; Service Berkala Resmi · Alamat: Workshop Center</p>
            <p>Pengelola: <b>{{ $inspection->manager_name ?? 'ARTI DEWI' }}</b></p>
        </div>
        <div class="doc-title">
            <h2>PEMERIKSAAN AWAL (CHECK-IN)</h2>
            <div class="meta">No: {{ $inspection->inspection_number ?? 'CI/2026/11/00001' }}</div>
            <div class="meta" style="color: #64748b; font-size: 10px;">WO: {{ $order->wo_number }}</div>
            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                Tanggal: {{ optional($inspection?->inspection_date)->format('d M Y') ?? now()->format('d M Y') }}
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="box">
            <div class="box-title">Data Pelanggan</div>
            <div class="info-row"><span class="info-label">Nama Pelanggan:</span><span class="info-val">{{ $order->customer?->name }}</span></div>
            <div class="info-row"><span class="info-label">No. Telepon / HP:</span><span class="info-val">{{ $order->customer?->phone ?: '—' }}</span></div>
            <div class="info-row"><span class="info-label">Tipe Servis:</span><span class="info-val">{{ $order->location_type === 'offsite' ? 'Home Service (Panggilan)' : 'Di Bengkel' }}</span></div>
            @if($order->location_type === 'offsite')
                <div class="info-row"><span class="info-label">Alamat:</span><span class="info-val">{{ $order->offsite_address }}</span></div>
            @endif
        </div>

        <div class="box">
            <div class="box-title">Data Kendaraan</div>
            <div class="info-row"><span class="info-label">No. Polisi:</span><span class="info-val" style="font-family: monospace;">{{ $order->vehicle?->plate_number }}</span></div>
            <div class="info-row"><span class="info-label">Merk / Tipe:</span><span class="info-val">{{ $order->vehicle?->fullName() }}</span></div>
            <div class="info-row"><span class="info-label">Tahun / BBM:</span><span class="info-val">{{ $order->vehicle?->year }} · {{ $order->vehicle?->fuelLabel() }}</span></div>
            <div class="info-row"><span class="info-label">Odometer Masuk:</span><span class="info-val">{{ number_format($order->odometer ?? $order->vehicle?->last_odometer ?? 0) }} KM</span></div>
            <div class="info-row"><span class="info-label">Bahan Bakar:</span><span class="info-val">{{ $inspection->fuel_level ?? '1/2' }} Tangki</span></div>
        </div>
    </div>

    <div class="box" style="margin-bottom: 10px;">
        <div class="box-title">Keluhan Pelanggan &amp; Analisis Awal</div>
        <p style="margin: 4px 0 6px 0; font-size: 11px;"><b>Keluhan:</b> {{ $order->customer_complaint }}</p>
        @if($order->sa_diagnosis)
            <p style="margin: 0; font-size: 10.5px; color: #475569;"><b>Catatan SA:</b> {{ $order->sa_diagnosis }}</p>
        @endif
    </div>

    {{-- Dokumen Checklist --}}
    <table style="margin-bottom: 10px;">
        <thead>
            <tr>
                <th colspan="4" style="background: #e2e8f0; text-transform: uppercase;">Checklist Dokumen &amp; Perlengkapan Kendaraan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @php($docs = $inspection->documents_checklist ?? [])
                @foreach($documentsChecklist as $docKey => $docLabel)
                    <td style="width: 25%;">
                        <b>{{ $docLabel }}:</b>
                        <span style="float: right; font-weight: bold; color: {{ ($docs[$docKey]['status'] ?? 'ada') === 'ada' ? '#166534' : '#991b1b' }};">
                            {{ strtoupper($docs[$docKey]['status'] ?? 'ADA') }}
                        </span>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    {{-- Kondisi Fisik / Eksterior --}}
    <table>
        <thead>
            <tr>
                <th colspan="4" style="background: #e2e8f0; text-transform: uppercase;">Kondisi Fisik / Eksterior Kendaraan (G = Baik / R = Rusak)</th>
            </tr>
            <tr>
                <th style="width: 25%;">Bagian / Komponen</th>
                <th style="width: 25%;">Bagian / Komponen</th>
                <th style="width: 25%;">Bagian / Komponen</th>
                <th style="width: 25%;">Bagian / Komponen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chunks as $chunk)
                <tr>
                    @foreach($chunk as $item)
                        <td>
                            {{ $item['label'] }}
                            <span style="float: right;">
                                <span class="{{ $item['cond'] === 'G' ? 'badge-g' : 'badge-r' }}">{{ $item['cond'] }}</span>
                            </span>
                        </td>
                    @endforeach
                    @for($i = count($chunk); $i < 4; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="sign-col">
            <div style="font-weight: 700; color: #475569;">Pelanggan</div>
            <div class="sign-space"></div>
            <div style="font-weight: 700; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 4px; min-width: 150px;">
                {{ $order->customer?->name }}
            </div>
        </div>
        <div class="sign-col">
            <div style="font-weight: 700; color: #475569;">Sales Adviser (SA) / Bengkel</div>
            <div class="sign-space"></div>
            <div style="font-weight: 700; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 4px; min-width: 150px;">
                {{ $order->sa?->name ?? 'Sales Adviser' }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
