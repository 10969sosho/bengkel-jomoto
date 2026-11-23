<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Pemeriksaan Sepeda Motor Service — {{ $order->wo_number }}</title>
</head>
<body>

@include('sa.partials.print-form', [
    'title' => 'LEMBAR PEMERIKSAAN SEPEDA MOTOR SERVICE',
    'refLabel' => 'NO. LEMBAR',
    'refNumber' => $order->inspection?->inspection_number ?? ('CI/'.now()->format('Y/m').'/00001'),
    'order' => $order,
    'inspection' => $order->inspection,
    'docDate' => $order->inspection?->inspection_date ?? $order->created_at,
    'showApprovals' => false,
])

</body>
</html>
