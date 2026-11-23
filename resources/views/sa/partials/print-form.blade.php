{{--
    Kerangka form cetak presisi 1 halaman (border hitam rapat #000).
    Dipakai oleh: sa.print.spk, sa.print.estimate, sa.print.checkin.

    Variabel:
      $title        judul dokumen (mis. SURAT ORDER PENJUALAN)
      $refLabel     label nomor referensi (mis. NO. ESTIMASI)
      $refNumber    nomor referensi dokumen
      $order        ServiceOrder
      $inspection   Inspection|null
      $docDate      Carbon|string|null — tanggal dokumen
      $showApprovals  bool — tampilkan caption persetujuan estimasi
--}}
@php
    use App\Models\Inspection;

    $vehicle = $order->vehicle;
    $customer = $order->customer;
    $inspection = $inspection ?? $order->inspection;
    $saName = $inspection?->sa_name ?: ($order->sa?->name ?? 'Sales Adviser');
    $mechanicName = $order->technician?->name ?? '—';
    $managerName = $inspection?->manager_name ?? 'ARTI DEWI';
    $showApprovals = $showApprovals ?? false;

    $items = $order->items->values();
    $totalBiaya = (int) $items->sum(fn ($it) => $it->quantity * $it->unit_price) + (int) $order->visit_fee;
    $totalDiskon = max((int) ($order->estimate_discount ?? 0), (int) $items->sum('discount'));
    $totalSetelahDiskon = max(0, $totalBiaya - $totalDiskon);
    $ppn = (int) round($totalSetelahDiskon * 0.11);
    $totalKeseluruhan = $totalSetelahDiskon + $ppn;

    $minRows = 16;
    $padRows = max(0, $minRows - $items->count() - ($order->visit_fee > 0 ? 1 : 0));

    $exteriorChecklist = $inspection?->exterior_checklist ?? [];
    $complaintMarks = [];
    foreach (Inspection::EXTERIOR_SECTIONS as $sectionName => $sectionItems) {
        $conditions = array_values(array_filter(array_map(
            fn ($key) => $exteriorChecklist[$key]['condition'] ?? null,
            array_keys($sectionItems)
        )));
        $complaintMarks[$sectionName] = in_array('R', $conditions, true)
            ? 'R'
            : (in_array('G', $conditions, true) ? 'G' : '');
    }

    $odometerPhoto = $inspection?->odometer_photo ? asset('storage/'.$inspection->odometer_photo) : null;
    $fuelPhoto = $inspection?->fuel_photo ? asset('storage/'.$inspection->fuel_photo) : null;

    $tanggal = filled($docDate ?? null)
        ? \Carbon\Carbon::parse($docDate)->format('d/m/Y H:i').' WIB'
        : now()->format('d/m/Y H:i').' WIB';
@endphp

@include('partials.print-toolbar')

<style>
    @page { size: A4; margin: 8mm; }
    * { box-sizing: border-box; }
    body {
        margin: 0; padding: 0; background: #fff; color: #000;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 8.5pt; line-height: 1.25;
    }
    @media print { .no-print { display: none !important; } body { background: #fff !important; } }
    .form { width: 100%; border: 2px solid #000; }
    .form table { width: 100%; border-collapse: collapse; }
    .form td, .form th { border: 1px solid #000; padding: 2px 5px; vertical-align: top; }
    /* Tabel bersarang menyatu dengan border sel induk (garis tetap 1px rapat). */
    .form td > table { border: none; }
    .form td > table tr:first-child > * { border-top: none; }
    .form td > table tr:last-child > * { border-bottom: none; }
    .form td > table tr > *:first-child { border-left: none; }
    .form td > table tr > *:last-child { border-right: none; }
    /* Pemisah antar tabel berturutan cukup 1 garis. */
    .form table + table tr:first-child > * { border-top: none; }
    .lbl { font-weight: 700; text-transform: uppercase; font-size: 7.5pt; white-space: nowrap; }
    .val { font-weight: 400; }
    .right { text-align: right; }
    .center { text-align: center; }
    .doc-title {
        text-align: center; font-weight: 700; font-size: 13pt;
        letter-spacing: 2px; text-transform: uppercase; padding: 4px 6px;
    }
    .brand-logo {
        display: inline-grid; place-items: center; width: 34px; height: 34px;
        background: #F8B803; color: #000; font-weight: 900; font-size: 18pt;
        border: 1px solid #000; float: left; margin-right: 7px; line-height: 1;
    }
    .brand-name { font-weight: 900; font-size: 12pt; letter-spacing: 1px; }
    .brand-sub { font-size: 7.5pt; }
    .section-head { background: #fff; font-weight: 700; text-transform: uppercase; font-size: 7.5pt; letter-spacing: .5px; }
    .items th { background: #fff; font-weight: 700; text-transform: uppercase; font-size: 7.5pt; text-align: center; padding: 3px 4px; }
    .items td { padding: 2px 4px; height: 15px; }
    .items .num { text-align: center; }
    .items .amt { text-align: right; white-space: nowrap; }
    .tot td { padding: 2px 6px; font-size: 8pt; }
    .tot .grand { font-weight: 900; font-size: 9pt; }
    .sign td { text-align: center; padding: 4px; height: 64px; }
    .sign .role { font-weight: 700; text-transform: uppercase; font-size: 7.5pt; }
    .sign .caption { font-size: 6.5pt; color: #333; min-height: 9px; }
    .sign .name { border-top: 1px solid #000; margin-top: 26px; padding-top: 2px; font-weight: 700; font-size: 7.5pt; }
    .foot { font-size: 7pt; text-align: center; padding: 3px 6px; }
    .photo { height: 30px; text-align: center; }
    .photo img { max-height: 28px; max-width: 100%; border: 1px solid #000; }
    .nb { font-size: 6.5pt; }
    .chk { text-align: center; font-weight: 900; }
</style>

<div class="form no-print-hide">

    {{-- Baris 1: Kop CV. JOWIMAR MANDIRI MAKMUR (kiri) + PIHAK PELANGGAN (kanan) --}}
    <table>
        <tr>
            <td style="width: 55%; padding: 4px 6px; vertical-align: middle;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="{{ asset('images/kop-jomoto.jpg') }}" alt="KOP CV. JOWIMAR MANDIRI MAKMUR"
                         style="max-height: 58px; width: auto; object-fit: contain; display: block;">
                </div>
            </td>
            <td style="width: 45%; padding: 0;">
                <table>
                    <tr>
                        <td class="section-head center" colspan="2">PIHAK PELANGGAN</td>
                    </tr>
                    <tr>
                        <td class="lbl" style="width: 38%;">Nama</td>
                        <td class="val">: {{ $customer?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="val">: {{ $customer?->address ?: ($order->offsite_address ?: '—') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nomor Kontak</td>
                        <td class="val">: {{ $customer?->phone ?: ($customer?->whatsapp ?: '—') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Judul dokumen --}}
    <table>
        <tr><td class="doc-title">{{ $title }}</td></tr>
    </table>

    {{-- Baris bawah header: SA, Mekanik, Manager, REF, NB --}}
    <table>
        <tr>
            <td style="width: 19%;"><span class="lbl">Nama Service Advisor</span><br>{{ $saName }}</td>
            <td style="width: 17%;"><span class="lbl">Nama Mekanik</span><br>{{ $mechanicName }}</td>
            <td style="width: 15%;"><span class="lbl">Nama Manager</span><br>{{ $managerName }}</td>
            <td style="width: 30%;">
                <span class="lbl">{{ $refLabel }} (REF)</span><br>
                <b>{{ $refNumber }}</b>
                <span class="nb"> · WO: {{ $order->wo_number }}</span>
            </td>
            <td class="nb" style="width: 19%;">
                <b>NB:</b> Perubahan biaya hanya sah bila disetujui pelanggan &amp; manager.<br>
                Tanggal: <b>{{ $tanggal }}</b>
            </td>
        </tr>
    </table>

    {{-- DATA KENDARAAN --}}
    <table>
        <tr><td class="section-head" colspan="4">DATA KENDARAAN</td></tr>
        <tr>
            <td class="lbl" style="width: 17%;">Merek</td>
            <td class="val" style="width: 33%;">: {{ $vehicle?->brand ?? '—' }}</td>
            <td class="lbl" style="width: 17%;">Tipe / Model</td>
            <td class="val">: {{ $vehicle?->model ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">No Seri Kendaraan</td>
            <td class="val">: {{ $vehicle?->serial_number ?: ($vehicle?->engine_number ?: '—') }}</td>
            <td class="lbl">Nomor Rangka</td>
            <td class="val">: {{ $vehicle?->chassis_number ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Tahun Rakitan</td>
            <td class="val">: {{ $vehicle?->year ?? '—' }}</td>
            <td class="lbl">Transmisi</td>
            <td class="val">: {{ $vehicle?->transmission ?: 'Otomatis / CVT' }}</td>
        </tr>
        <tr>
            <td class="lbl">Jenis Mesin</td>
            <td class="val">: {{ $vehicle?->engine_type ?: ($vehicle?->fuel_type === 'electric' ? 'Listrik BLDC' : 'Bensin 4-Tak') }}</td>
            <td class="lbl">Tanggal SPK/Kunjungan</td>
            <td class="val">: {{ $tanggal }}</td>
        </tr>
        <tr>
            <td class="lbl">Nomor Polisi</td>
            <td class="val"><b>: {{ $vehicle?->plate_number ?? '—' }}</b></td>
            <td class="lbl">Jam Mulai Kerja / Odometer</td>
            <td class="val">: {{ $order->work_start_time ?: '—' }} / {{ number_format($order->odometer ?: ($vehicle?->last_odometer ?? 0)) }} KM</td>
        </tr>
        <tr>
            <td class="lbl">Foto Odometer</td>
            <td class="photo">@if($odometerPhoto)<img src="{{ $odometerPhoto }}" alt="Foto Odometer">@else—@endif</td>
            <td class="lbl">Foto Indikator BBM</td>
            <td class="photo">@if($fuelPhoto)<img src="{{ $fuelPhoto }}" alt="Foto Indikator BBM">@else—@endif</td>
        </tr>
    </table>

    {{-- KATEGORI KELUHAN (G/R) + DESKRIPSI KELUHAN --}}
    <table>
        <tr>
            <td style="width: 50%; padding: 0;">
                <table style="border: none;">
                    <tr>
                        <td class="section-head" colspan="3">KATEGORI KELUHAN</td>
                    </tr>
                    <tr>
                        <th style="width: auto;">Kategori</th>
                        <th style="width: 34px;">G</th>
                        <th style="width: 34px;">R</th>
                    </tr>
                    @foreach($complaintMarks as $category => $mark)
                        <tr>
                            <td class="lbl" style="font-weight: 400; text-transform: uppercase;">{{ $category }}</td>
                            <td class="chk">{{ $mark === 'G' ? '✓' : '' }}</td>
                            <td class="chk">{{ $mark === 'R' ? '✓' : '' }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td style="width: 50%;">
                <div class="section-head center">DESKRIPSI KELUHAN</div>
                <div style="min-height: 74px; padding: 3px 4px;">
                    {{ $order->customer_complaint ?: '—' }}
                    @if($order->sa_diagnosis)
                        <div style="margin-top: 4px;"><b>Diagnosis SA:</b> {{ $order->sa_diagnosis }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- RINCIAN TRANSAKSI / BIAYA --}}
    <table class="items">
        <tr>
            <td class="section-head" colspan="6">RINCIAN TRANSAKSI / BIAYA</td>
        </tr>
        <tr>
            <th style="width: 34px;">No</th>
            <th>Nama Barang / Jasa</th>
            <th style="width: 44px;">Qty</th>
            <th style="width: 96px;">Harga Satuan / Jenis</th>
            <th style="width: 62px;">Disc (%)</th>
            <th style="width: 96px;">Subtotal</th>
        </tr>
        @foreach($items as $index => $item)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $item->item_name }}@if($item->is_free) <span style="font-size: 7pt;">(GRATIS — {{ $item->free_reason }})</span>@endif</td>
                <td class="num">{{ $item->quantity }}</td>
                <td class="amt">{{ number_format($item->unit_price) }}<span style="font-size: 7pt;"> / {{ $item->actionLabel() }}</span></td>
                <td class="amt">{{ $item->discount > 0 ? number_format($item->discount) : '-' }}</td>
                <td class="amt"><b>{{ $item->is_free ? '0' : number_format($item->subtotal) }}</b></td>
            </tr>
        @endforeach
        @if($order->visit_fee > 0)
            <tr>
                <td class="num">{{ $items->count() + 1 }}</td>
                <td>Biaya Kunjungan Teknisi (Home Service)</td>
                <td class="num">1</td>
                <td class="amt">{{ number_format($order->visit_fee) }}</td>
                <td class="amt">-</td>
                <td class="amt"><b>{{ number_format($order->visit_fee) }}</b></td>
            </tr>
        @endif
        @for($i = 0; $i < $padRows; $i++)
            <tr>
                <td class="num">{{ $items->count() + ($order->visit_fee > 0 ? 1 : 0) + $i + 1 }}</td>
                <td></td><td class="num"></td><td></td><td class="amt"></td><td class="amt"></td>
            </tr>
        @endfor
    </table>

    {{-- TOTAL BIAYA s/d TOTAL KESELURUHAN --}}
    <table>
        <tr>
            <td style="width: 55%; font-size: 7.5pt;">
                <b>CATATAN:</b> Harga mengikuti pricelist bengkel yang berlaku.
                Diskon item sudah termasuk dalam subtotal; diskon global tercantum pada baris DISKON.
                PPN 11% dihitung setelah diskon.
            </td>
            <td style="width: 45%; padding: 0;">
                <table class="tot">
                    <tr><td>TOTAL BIAYA</td><td class="right"><b>Rp {{ number_format($totalBiaya) }}</b></td></tr>
                    <tr><td>DISKON</td><td class="right">- Rp {{ number_format($totalDiskon) }}</td></tr>
                    <tr><td>TOTAL SETELAH DISKON</td><td class="right"><b>Rp {{ number_format($totalSetelahDiskon) }}</b></td></tr>
                    <tr><td>PPN (11%)</td><td class="right">Rp {{ number_format($ppn) }}</td></tr>
                    <tr><td class="grand">TOTAL KESELURUHAN</td><td class="right grand">Rp {{ number_format($totalKeseluruhan) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Blok Tanda Tangan --}}
    <table class="sign">
        <tr>
            <td>
                <div class="role">Pelanggan</div>
                @if($showApprovals)<div class="caption">(Persetujuan Estimasi)</div>@endif
                <div class="caption">
                    @if($order->estimate_approved_at)
                        ✓ Disetujui {{ $order->estimate_approved_at->format('d/m/Y H:i') }} WIB
                    @endif
                </div>
                <div class="name">{{ $customer?->name ?? '________________' }}</div>
            </td>
            <td>
                <div class="role">Manager</div>
                <div class="caption">&nbsp;</div>
                <div class="caption">&nbsp;</div>
                <div class="name">{{ $managerName }}</div>
            </td>
            <td>
                <div class="role">Service Advisor</div>
                <div class="caption">&nbsp;</div>
                <div class="caption">&nbsp;</div>
                <div class="name">{{ $saName }}</div>
            </td>
            <td>
                <div class="role">Mekanik</div>
                @if($showApprovals)<div class="caption">(Persetujuan Hasil Pengerjaan)</div>@endif
                <div class="caption">
                    @if($inspection?->mechanic_confirmed_at)
                        ✓ Dikonfirmasi {{ $inspection->mechanic_confirmed_at->format('d/m/Y H:i') }} WIB
                    @endif
                </div>
                <div class="name">{{ $mechanicName }}</div>
            </td>
        </tr>
    </table>

    {{-- Catatan kaki --}}
    <table>
        <tr>
            <td class="foot">
                * Lembaran Asli (Untuk Pelanggan) · Salinan 2 warna Biru (Untuk Front Office) · Salinan 3 warna Hijau (Untuk Adm Bengkel)
            </td>
        </tr>
    </table>

</div>
