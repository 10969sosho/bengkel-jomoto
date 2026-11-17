<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Kerja (SPK) - {{ $serviceOrder->spk_number ?? $serviceOrder->wo_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            font-size: 11.5px;
            line-height: 1.45;
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
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .brand h1 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; }
        .brand p { margin: 2px 0 0 0; font-size: 10px; color: #64748b; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; }
        .doc-title .spk-no { font-family: monospace; font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 3px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
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
        .info-row { display: flex; margin-bottom: 4px; }
        .info-label { width: 120px; color: #64748b; }
        .info-val { flex: 1; font-weight: 600; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        th { background: #f1f5f9; font-weight: 700; text-align: left; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 700; text-transform: uppercase; }
        .badge-service { background: #e0e7ff; color: #3730a3; }
        .badge-sparepart { background: #e0f2fe; color: #0369a1; }
        .badge-sub { background: #fef3c7; color: #92400e; }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 25px;
            text-align: center;
        }
        .sign-col { border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px; }
        .sign-space { height: 60px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="no-print" style="background: #f1f5f9; padding: 10px; text-align: center; border-bottom: 1px solid #cbd5e1;">
    <button onclick="window.print()" style="background: #0f172a; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">
        🖨️ Cetak Surat Perintah Kerja (SPK)
    </button>
    <a href="{{ route('sa.show', $serviceOrder) }}" style="margin-left: 12px; color: #475569; text-decoration: none; font-size: 12px; font-weight: 600;">
        ← Kembali ke WO
    </a>
</div>

<div class="container">
    <div class="header">
        <div class="brand">
            <h1>BENGKEL JOMOTO CENTER</h1>
            <p>Workshop Resmi Perawatan &amp; Perbaikan Sepeda Motor</p>
            <p>Alamat: Workshop Center · WA: 0812-3456-7890</p>
        </div>
        <div class="doc-title">
            <h2>SURAT PERINTAH KERJA (SPK)</h2>
            <div class="spk-no">No: {{ $serviceOrder->spk_number ?? 'SOJ/'.now()->format('Y/m').'/00001' }}</div>
            <div style="font-size: 10.5px; color: #64748b; margin-top: 3px;">
                WO: <b style="color: #0f172a;">{{ $serviceOrder->wo_number }}</b>
            </div>
            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                Tanggal Terbit: {{ optional($serviceOrder->spk_issued_at)->format('d M Y H:i') ?? now()->format('d M Y H:i') }}
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="box">
            <div class="box-title">Data Pelanggan &amp; Layanan</div>
            <div class="info-row"><span class="info-label">Nama Pelanggan:</span><span class="info-val">{{ $serviceOrder->customer?->name }}</span></div>
            <div class="info-row"><span class="info-label">No. Telepon / HP:</span><span class="info-val">{{ $serviceOrder->customer?->phone ?: '—' }}</span></div>
            <div class="info-row"><span class="info-label">Tipe Layanan:</span><span class="info-val">{{ $serviceOrder->serviceTypeLabel() }} ({{ $serviceOrder->location_type === 'offsite' ? 'Home Service' : 'Di Bengkel' }})</span></div>
            @if($serviceOrder->location_type === 'offsite')
                <div class="info-row"><span class="info-label">Alamat:</span><span class="info-val">{{ $serviceOrder->offsite_address }}</span></div>
            @endif
        </div>

        <div class="box">
            <div class="box-title">Data Kendaraan</div>
            <div class="info-row"><span class="info-label">No. Polisi:</span><span class="info-val" style="font-family: monospace;">{{ $serviceOrder->vehicle?->plate_number }}</span></div>
            <div class="info-row"><span class="info-label">Merk / Tipe:</span><span class="info-val">{{ $serviceOrder->vehicle?->fullName() }}</span></div>
            <div class="info-row"><span class="info-label">Tahun / BBM:</span><span class="info-val">{{ $serviceOrder->vehicle?->year }} · {{ $serviceOrder->vehicle?->fuelLabel() }}</span></div>
            <div class="info-row"><span class="info-label">Odometer:</span><span class="info-val">{{ number_format($serviceOrder->odometer ?? $serviceOrder->vehicle?->last_odometer ?? 0) }} KM</span></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="box">
            <div class="box-title">Personil Pelaksana</div>
            <div class="info-row"><span class="info-label">Sales Adviser (SA):</span><span class="info-val">{{ $serviceOrder->sa?->name ?? 'Sales Adviser' }}</span></div>
            <div class="info-row"><span class="info-label">Mekanik / Teknisi:</span><span class="info-val" style="color: #0369a1;">{{ $serviceOrder->technician?->name ?? 'Belum ditentukan' }}</span></div>
        </div>

        <div class="box">
            <div class="box-title">Waktu Pelaksanaan Servis</div>
            <div class="info-row"><span class="info-label">Waktu Mulai:</span><span class="info-val">{{ $serviceOrder->work_start_time ?: 'Mulai saat timer diaktifkan' }}</span></div>
            <div class="info-row"><span class="info-label">Target Selesai:</span><span class="info-val">{{ optional($serviceOrder->estimated_completion)->format('d M Y H:i') ?: 'Sesuai estimasi kerja' }}</span></div>
        </div>
    </div>

    <div class="box" style="margin-bottom: 12px;">
        <div class="box-title">Keluhan Pelanggan &amp; Hasil Pemeriksaan Awal</div>
        <p style="margin: 4px 0; font-size: 11px;"><b>Keluhan:</b> {{ $serviceOrder->customer_complaint }}</p>
        @if($serviceOrder->inspection?->findings)
            <p style="margin: 4px 0 0 0; font-size: 11px; color: #475569;"><b>Temuan Mekanik:</b> {{ $serviceOrder->inspection->findings }}</p>
        @endif
        @if($serviceOrder->inspection?->recommendations)
            <p style="margin: 2px 0 0 0; font-size: 11px; color: #475569;"><b>Rekomendasi:</b> {{ $serviceOrder->inspection->recommendations }}</p>
        @endif
    </div>

    {{-- Daftar Pekerjaan / Item SPK --}}
    <table>
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th style="width: 100px;">Jenis</th>
                <th>Deskripsi Pekerjaan / Sparepart / Sub Material</th>
                <th style="width: 50px;" class="text-center">Qty</th>
                <th style="width: 150px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($serviceOrder->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <span class="badge {{ $item->item_type === 'service' ? 'badge-service' : ($item->item_type === 'sparepart' ? 'badge-sparepart' : 'badge-sub') }}">
                            {{ $item->typeLabel() }}
                        </span>
                    </td>
                    <td>
                        <b>{{ $item->item_name }}</b>
                        @if($item->is_free)
                            <span style="color: #166534; font-size: 10px;">(GRATIS - {{ $item->free_reason }})</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $item->quantity }}</td>
                    <td style="font-size: 10px; color: #64748b;">
                        {{ $item->is_free ? 'Disetujui gratis' : 'Sesuai persetujuan estimasi' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 12px;">Belum ada rincian pekerjaan yang terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div class="sign-col">
            <div style="font-weight: 700; color: #475569;">Sales Adviser (Pemberi Tugas)</div>
            <div class="sign-space"></div>
            <div style="font-weight: 700; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 4px; min-width: 160px;">
                {{ $serviceOrder->sa?->name ?? 'Sales Adviser' }}
            </div>
        </div>
        <div class="sign-col">
            <div style="font-weight: 700; color: #475569;">Mekanik / Teknisi (Pelaksana)</div>
            <div class="sign-space"></div>
            <div style="font-weight: 700; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 4px; min-width: 160px;">
                {{ $serviceOrder->technician?->name ?? 'Teknisi Pelaksana' }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
