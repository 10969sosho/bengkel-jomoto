@extends('layouts.app')

@section('title', 'Customer Portal')
@section('page_title', 'Portal Servis Saya')
@section('page_icon', 'bike')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Halo, {{ explode(' ', $user->name)[0] }} 👋</h1>
    <p class="text-sm text-slate-500">Pantau progress servis motor Anda, riwayat perawatan, dan unduh kwitansi.</p>
</div>

{{-- Motor saya --}}
<section class="mb-6">
    <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <i data-lucide="bike" class="h-4 w-4"></i> Motor Saya
    </h2>
    <div class="grid gap-3 sm:grid-cols-3">
        @forelse($vehicles as $vehicle)
            <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black tracking-wider text-lime-300">{{ $vehicle->plate_number }}</span>
                    <span class="rounded-full bg-{{ $vehicle->fuel_type === 'electric' ? 'lime' : 'sky' }}-50 px-2.5 py-1 text-[11px] font-bold text-{{ $vehicle->fuel_type === 'electric' ? 'lime-700' : 'sky-700' }}">
                        {{ $vehicle->fuelLabel() }}
                    </span>
                </div>
                <div class="mt-2.5 font-bold text-slate-900">{{ $vehicle->fullName() }}</div>
                <div class="mt-1 text-xs text-slate-500">Tahun {{ $vehicle->year }} · {{ number_format($vehicle->last_odometer) }} KM</div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-400 sm:col-span-3">
                Belum ada motor terdaftar.
            </div>
        @endforelse
    </div>
</section>

<section class="mb-6 grid gap-4 lg:grid-cols-2">
    <details class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer font-bold text-slate-900">+ Daftarkan kendaraan baru</summary>
        <form method="POST" action="{{ route('customer.vehicles.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
            @csrf
            <input name="brand" required placeholder="Merk" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="model" required placeholder="Model" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="plate_number" required placeholder="Plat nomor" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="engine_number" placeholder="Nomor mesin" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="chassis_number" placeholder="Nomor rangka" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <select name="fuel_type" required class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"><option value="gasoline">Bensin</option><option value="electric">Listrik</option></select>
            <input type="number" min="0" name="last_odometer" required placeholder="KM awal" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-lime-300 sm:col-span-2">Simpan Kendaraan</button>
        </form>
    </details>

    <details class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer font-bold text-slate-900">Booking servis baru</summary>
        <form method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <select name="vehicle_id" id="booking-vehicle" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                <option value="">Pilih kendaraan</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" data-odometer="{{ $vehicle->last_odometer }}" data-claimed="{{ $vehicle->serviceOrders->pluck('coupon_milestone')->join(',') }}">{{ $vehicle->fullName() }} · {{ $vehicle->plate_number }}</option>
                @endforeach
            </select>
            <div class="grid gap-3 sm:grid-cols-2">
                <input type="number" min="0" name="odometer" id="booking-odometer" required placeholder="KM saat ini" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                <select name="location_type" id="booking-location" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="onsite">Service di Bengkel</option><option value="offsite">Service Panggilan (Home Service)</option>
                </select>
            </div>
            <div id="booking-coupon" class="hidden rounded-xl border border-lime-200 bg-lime-50 p-3 text-sm font-bold text-lime-800"></div>
            <div id="booking-home" class="hidden space-y-2 rounded-xl border border-lime-200 bg-lime-50 p-3">
                <textarea name="offsite_address" id="booking-address" rows="2" placeholder="Alamat lengkap service" class="w-full rounded-lg border-lime-200 bg-white px-3 py-2 text-sm"></textarea>
                <input name="location_notes" placeholder="Catatan patokan lokasi" class="w-full rounded-lg border-lime-200 bg-white px-3 py-2 text-sm">
                <label class="block text-xs font-semibold text-lime-900">Biaya kunjungan (Rp)</label>
                <input type="number" min="0" name="visit_fee" value="50000" class="w-full rounded-lg border-lime-200 bg-white px-3 py-2 text-sm">
            </div>
            <textarea name="customer_complaint" required rows="2" placeholder="Keluhan motor" class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"></textarea>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                    Jenis Layanan / Servis yang Dibutuhkan <span class="text-red-500">*</span>
                </label>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($pricelists->where('is_active', true)->groupBy('category') as $category => $rows)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 sm:col-span-2">
                            <div class="mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $rows->first()->categoryLabel() }}</div>
                            <div class="grid gap-1.5 sm:grid-cols-2">
                                @foreach($rows as $pricelist)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-lg bg-white px-2.5 py-2 text-xs text-slate-700 transition hover:border-lime-300 hover:ring-1 hover:ring-lime-200">
                                        <input type="checkbox" name="requested_services[]" value="{{ $pricelist->id }}"
                                               @checked(in_array((string) $pricelist->id, array_map('strval', old('requested_services', []))))
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-lime-500 focus:ring-lime-400">
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-slate-800">{{ $pricelist->service_name }}</span>
                                            <span class="text-slate-400">Rp {{ number_format($pricelist->price) }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('requested_services')
                    <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                    Foto STNK Kendaraan <span class="text-red-500">*</span>
                </label>
                <input type="file" name="stnk_photo" accept="image/png,image/jpeg,image/webp" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-lime-300">
                <p class="mt-1 text-[11px] text-slate-400">Format JPG/PNG/WEBP, maksimal 5 MB. Data pendukung untuk diproses Service Advisor.</p>
                @error('stnk_photo')
                    <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-xl bg-lime-300 px-4 py-2.5 text-sm font-bold text-black">Kirim Booking</button>
        </form>
    </details>
</section>

{{-- Progress servis aktif --}}
<section class="mb-6">
    <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <i data-lucide="activity" class="h-4 w-4"></i> Servis Aktif
    </h2>

    @forelse($active as $order)
        @php($progress = $order->progressPercent())
        <div class="mb-3 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-300 hover:shadow-md">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-sm font-extrabold text-slate-900">{{ $order->wo_number }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                    </div>
                    <div class="mt-1.5 text-sm text-slate-600">
                        <b>{{ $order->vehicle?->fullName() }}</b> ({{ $order->vehicle?->plate_number }}) ·
                        {{ $order->serviceTypeLabel() }}
                    </div>
                    @if($order->location_type === 'offsite')
                        <div class="mt-2 rounded-xl bg-lime-50 px-3 py-2 text-xs font-semibold text-lime-800">
                            HOME SERVICE · {{ $order->offsite_address }} · Biaya kunjungan Rp {{ number_format($order->visit_fee) }}
                        </div>
                    @endif
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">“{{ $order->customer_complaint }}”</p>

                    @if($order->requestedServiceLabels($pricelistNames) !== [])
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($order->requestedServiceLabels($pricelistNames) as $serviceLabel)
                                <span class="rounded-lg bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">{{ $serviceLabel }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($order->stnkUrl())
                        <a href="{{ $order->stnkUrl() }}" target="_blank" rel="noopener"
                           class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 underline decoration-dotted transition hover:text-slate-800">
                            <i data-lucide="image" class="h-3.5 w-3.5"></i> Lihat Foto STNK
                        </a>
                    @endif
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-right text-sm">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Teknisi</div>
                    <div class="mt-0.5 font-semibold text-slate-700">{{ $order->technician?->name ?? 'Belum ditugaskan' }}</div>
                    @if($order->estimated_completion)
                        <div class="mt-1 text-xs text-slate-400">Target: {{ $order->estimated_completion->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                @include('partials.service-flow', ['order' => $order])
            </div>

            <div class="mt-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                <div class="mb-2 flex justify-between text-xs font-bold text-slate-500">
                    <span>Progres Pengerjaan</span>
                    <span class="rounded-full bg-slate-900 px-2 py-0.5 text-lime-300">{{ $progress }}%</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full bg-gradient-to-r from-lime-300 to-lime-500 transition-all duration-700"
                         style="width: {{ $progress }}%"></div>
                </div>
                <div class="mt-2.5 flex flex-wrap justify-between gap-x-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                    <span class="{{ $progress >= 10 ? 'text-lime-600' : '' }}">Terdaftar</span>
                    <span class="{{ $progress >= 35 ? 'text-lime-600' : '' }}">Estimasi</span>
                    <span class="{{ $progress >= 50 ? 'text-lime-600' : '' }}">Disetujui</span>
                    <span class="{{ $progress >= 70 ? 'text-lime-600' : '' }}">Dikerjakan</span>
                    <span class="{{ $progress >= 85 ? 'text-lime-600' : '' }}">Selesai</span>
                    <span class="{{ $progress >= 90 ? 'text-lime-600' : '' }}">Konfirmasi</span>
                    <span class="{{ $progress >= 100 ? 'text-lime-600' : '' }}">Invoice</span>
                </div>
            </div>

            @if($order->status === 'waiting_approval')
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-amber-400 text-amber-950">
                            <i data-lucide="file-check-2" class="h-4 w-4"></i>
                        </span>
                        <h4 class="font-bold text-amber-900">Persetujuan Estimasi · Tahap 1 dari 2</h4>
                    </div>
                    <p class="mt-1 text-xs text-amber-700">Setujui jenis pekerjaan, sparepart, dan estimasi biaya sebelum mekanik mulai bekerja.</p>

                    <div class="mt-3 overflow-x-auto rounded-xl bg-white">
                        <table class="w-full text-sm">
                            <thead class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Item</th>
                                    <th class="px-3 py-2 text-center">Qty</th>
                                    <th class="px-3 py-2 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($order->items as $item)
                                    <tr>
                                        <td class="px-3 py-2">
                                            {{ $item->item_name }}
                                            <span class="text-xs text-slate-400">({{ $item->typeLabel() }})</span>
                                            @if($item->is_free)
                                                <div class="text-xs text-emerald-600">GRATIS — {{ $item->free_reason }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">
                                            {{ $item->is_free ? 'Rp 0' : 'Rp '.number_format($item->subtotal) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-xs text-slate-400">Estimasi masih disiapkan oleh SA.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('customer.orders.approve', $order) }}" class="mt-3">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600 active:scale-[.98]">
                            <i data-lucide="check-check" class="h-4 w-4"></i> Setujui Estimasi &amp; Biaya
                        </button>
                    </form>
                </div>
            @endif

            @if($order->status === 'waiting_confirmation')
                <div class="mt-4 rounded-2xl border border-teal-200 bg-teal-50 p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-teal-500 text-white">
                            <i data-lucide="badge-check" class="h-4 w-4"></i>
                        </span>
                        <h4 class="font-bold text-teal-900">Persetujuan Hasil Pengerjaan · Tahap 2 dari 2</h4>
                    </div>
                    <p class="mt-1 text-xs text-teal-700">Pengerjaan sudah diperiksa SA. Konfirmasi bahwa hasil servis sudah sesuai sebelum invoice diterbitkan.</p>

                    @if($order->final_check_at)
                        <div class="mt-3 rounded-xl bg-white p-3 text-xs text-slate-600">
                            <div class="font-bold text-slate-800">Pemeriksaan Akhir SA · {{ $order->final_check_at->format('d M Y H:i') }}</div>
                            <p class="mt-1">{{ $order->final_check_notes }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customer.orders.confirm', $order) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="confirmation_note" rows="2" required maxlength="500"
                                  placeholder="Contoh: hasil pengerjaan sudah sesuai dan disetujui."
                                  class="w-full rounded-xl border-teal-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200">{{ old('confirmation_note') }}</textarea>
                        <button class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700 active:scale-[.98]">
                            <i data-lucide="check-check" class="h-4 w-4"></i> Konfirmasi Hasil Sesuai
                        </button>
                    </form>
                </div>
            @endif

            @if($order->status === 'confirmed')
                <div class="mt-4 rounded-2xl border border-cyan-200 bg-cyan-50 p-4 text-sm text-cyan-900">
                    Hasil pengerjaan sudah dikonfirmasi{{ $order->result_confirmed_at ? ' pada '.$order->result_confirmed_at->format('d M Y H:i') : '' }} — SA akan menerbitkan invoice.
                    @if($order->result_confirmation_notes)
                        <div class="mt-1 text-xs text-cyan-700">Catatan: {{ $order->result_confirmation_notes }}</div>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400">
            <i data-lucide="party-popper" class="mx-auto mb-2 h-8 w-8 text-lime-300"></i>
            Tidak ada servis yang sedang berjalan. 🎉
        </div>
    @endforelse
</section>

{{-- Riwayat --}}
<section id="riwayat" class="scroll-mt-28">
    <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <i data-lucide="history" class="h-4 w-4"></i> Riwayat Servis
    </h2>
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-5 py-3">WO</th>
                        <th class="px-5 py-3">Motor</th>
                        <th class="px-5 py-3">Tanggal Selesai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $order)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 font-mono text-sm font-bold text-slate-900">{{ $order->wo_number }}</td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $order->vehicle?->fullName() }} · {{ $order->vehicle?->plate_number }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ optional($order->actual_completion)->format('d M Y') ?? $order->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($order->invoice)
                                    <a href="{{ route('invoices.print', $order->invoice) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-bold text-purple-700 transition hover:bg-purple-100 active:scale-95">
                                        <i data-lucide="printer" class="h-3.5 w-3.5"></i>
                                        Cetak ({{ $order->invoice->isPaid() ? 'Lunas' : 'Belum Bayar' }})
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada riwayat servis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    const bookingVehicle = document.getElementById('booking-vehicle');
    const bookingOdometer = document.getElementById('booking-odometer');
    const bookingLocation = document.getElementById('booking-location');
    const bookingHome = document.getElementById('booking-home');
    const bookingAddress = document.getElementById('booking-address');
    const bookingCoupon = document.getElementById('booking-coupon');
    const updateCoupon = () => {
        const km = Number(bookingOdometer.value);
        const atpm = [1000, 4000, 7000].find(value => km >= value && km <= value + 500);
        const jomoto = km >= 10000 ? Math.floor(km / 10000) * 10000 : null;
        const candidate = atpm || (jomoto && km <= jomoto + 500 ? jomoto : null);
        const claimed = (bookingVehicle.selectedOptions[0]?.dataset.claimed || '').split(',').map(Number);
        const milestone = candidate && !claimed.includes(candidate) ? candidate : null;
        bookingCoupon.classList.toggle('hidden', !milestone);
        bookingCoupon.textContent = milestone ? (atpm
            ? `Klaim Gratis ATPM ${milestone.toLocaleString('id-ID')} KM · oli, sparepart dasar & jasa gratis`
            : `Klaim Gratis Jomoto ${milestone.toLocaleString('id-ID')} KM · jasa gratis, sparepart bayar`) : '';
    };
    bookingVehicle.addEventListener('change', () => {
        bookingOdometer.value = bookingVehicle.selectedOptions[0]?.dataset.odometer || '';
        updateCoupon();
    });
    bookingOdometer.addEventListener('input', updateCoupon);
    bookingLocation.addEventListener('change', () => {
        const offsite = bookingLocation.value === 'offsite';
        bookingHome.classList.toggle('hidden', !offsite);
        bookingAddress.required = offsite;
    });
</script>
@endsection
