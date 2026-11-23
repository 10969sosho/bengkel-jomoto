<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Estimasi Biaya — {{ $serviceOrder->estimate_number ?? $serviceOrder->wo_number }}</title>
</head>
<body>

@include('sa.partials.print-form', [
    'title' => 'LEMBAR ESTIMASI BIAYA BENGKEL',
    'refLabel' => 'NO. ESTIMASI',
    'refNumber' => $serviceOrder->estimate_number ?? 'PW/'.now()->format('Y/m').'/00001',
    'order' => $serviceOrder,
    'inspection' => $serviceOrder->inspection,
    'docDate' => $serviceOrder->created_at,
    'showApprovals' => true,
])

</body>
</html>
