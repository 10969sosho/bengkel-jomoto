<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Order Penjualan (SPK) — {{ $serviceOrder->spk_number ?? $serviceOrder->wo_number }}</title>
</head>
<body>

@include('sa.partials.print-form', [
    'title' => 'SURAT ORDER PENJUALAN',
    'refLabel' => 'NO. ORDER JUAL',
    'refNumber' => $serviceOrder->spk_number ?? ('OJ/'.now()->format('Y/m').'/00001'),
    'order' => $serviceOrder,
    'inspection' => $serviceOrder->inspection,
    'docDate' => $serviceOrder->spk_issued_at ?? $serviceOrder->created_at,
    'showApprovals' => false,
])

</body>
</html>
