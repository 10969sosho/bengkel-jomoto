@extends('layouts.app')

@section('title', 'Booking Servis Baru')
@section('page_title', 'Booking Servis Baru')
@section('page_icon', 'calendar-plus')

@section('content')
<a href="{{ route('customer.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
    <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke portal servis
</a>

<div class="mx-auto mt-4 max-w-3xl">
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-lime-300 text-black shadow-[0_4px_14px_rgba(163,230,53,.35)]">
                <i data-lucide="calendar-plus" class="h-5 w-5"></i>
            </span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Booking Servis Baru</h1>
                <p class="text-sm text-slate-500">Pilih motor, tulis keluhan, dan tentukan layanan yang dibutuhkan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kendaraan <span class="text-red-500">*</span></label>
                    <select name="vehicle_id" id="booking-vehicle" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">Pilih kendaraan</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)
                                    data-odometer="{{ $vehicle->last_odometer }}"
                                    data-claimed="{{ $vehicle->serviceOrders->pluck('coupon_milestone')->join(',') }}">
                                {{ $vehicle->fullName() }} · {{ $vehicle->plate_number }}
                            </option>
                        @endforeach
                    </select>
                    @if($vehicles->isEmpty())
                        <p class="mt-1.5 text-xs font-semibold text-amber-600">Belum ada motor — daftarkan dulu di menu Daftar Kendaraan.</p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">KM Saat Ini <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="odometer" id="booking-odometer" required
                           value="{{ old('odometer') }}" placeholder="KM saat ini"
                           class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi Service</label>
                    <select name="location_type" id="booking-location"
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="onsite" @selected(old('location_type', 'onsite') === 'onsite')>Service di Bengkel</option>
                        <option value="offsite" @selected(old('location_type') === 'offsite')>Service Panggilan (Home Service)</option>
                    </select>
                </div>

                <div id="booking-coupon" class="hidden rounded-2xl border border-lime-200 bg-lime-50 p-3 text-sm font-bold text-lime-800 sm:col-span-2"></div>

                <div id="booking-home" class="hidden space-y-2 rounded-2xl border border-lime-200 bg-lime-50 p-4 sm:col-span-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-lime-900">Alamat Service Panggilan <span class="text-red-500">*</span></label>
                        <textarea name="offsite_address" id="booking-address" rows="2" placeholder="Alamat lengkap service"
                                  class="w-full rounded-xl border-lime-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:ring-2 focus:ring-lime-200">{{ old('offsite_address') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-lime-900">Catatan Patokan Lokasi</label>
                        <input name="location_notes" value="{{ old('location_notes') }}" placeholder="Patokan lokasi / akses masuk"
                               class="w-full rounded-xl border-lime-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-lime-400 focus:ring-2 focus:ring-lime-200">
                    </div>
                    <p class="text-xs font-semibold text-lime-700">Biaya kunjungan ditentukan oleh Sales Adviser saat memproses booking Anda.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Keluhan Motor <span class="text-red-500">*</span></label>
                    <textarea name="customer_complaint" rows="3" required placeholder="Contoh: motor sering mati mendadak, rem depan bunyi..."
                              class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">{{ old('customer_complaint') }}</textarea>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                    Katalog Layanan <span class="text-red-500">*</span>
                </label>
                <p class="mb-2 text-xs text-slate-400">Pilih layanan yang dibutuhkan. Badge aksi: <b>P</b> Perbaikan · <b>S</b> Servis · <b>G</b> Ganti. Harga mengikuti pricelist bengkel dan dikonfirmasi lewat lembar estimasi.</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($pricelists->groupBy('category') as $category => $rows)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:col-span-2">
                            <div class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $rows->first()->categoryLabel() }}</div>
                            <div class="grid gap-1.5 sm:grid-cols-2">
                                @foreach($rows as $pricelist)
                                    @php($actions = $pricelist->actionPrices())
                                    <label class="flex cursor-pointer items-start gap-2 rounded-lg bg-white px-2.5 py-2 text-xs text-slate-700 transition hover:border-lime-300 hover:ring-1 hover:ring-lime-200">
                                        <input type="checkbox" name="requested_services[]" value="{{ $pricelist->id }}"
                                               @checked(in_array((string) $pricelist->id, array_map('strval', old('requested_services', []))))
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-lime-500 focus:ring-lime-400">
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-semibold text-slate-800">{{ $pricelist->service_name }}</span>
                                        </span>
                                        <span class="flex shrink-0 gap-1">
                                            @foreach($actions as $action => $price)
                                                <span class="grid h-5 w-5 place-items-center rounded-md bg-slate-900 text-[10px] font-black text-lime-300"
                                                      title="Aksi {{ $action }}">{{ $action }}</span>
                                            @endforeach
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
                <p class="mt-1 text-[11px] text-slate-400">Format JPG/PNG/WEBP, maksimal 5 MB. Data pendukung untuk diproses Sales Adviser.</p>
                @error('stnk_photo')
                    <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('customer.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Batal</a>
                <button class="inline-flex items-center gap-2 rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-lime-400 active:scale-[.98]">
                    <i data-lucide="send" class="h-4 w-4"></i> Kirim Booking
                </button>
            </div>
        </form>
    </div>
</div>

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

    const presetVehicle = new URLSearchParams(location.search).get('vehicle_id');
    if (presetVehicle) bookingVehicle.value = presetVehicle;
    if (bookingVehicle.value) bookingVehicle.dispatchEvent(new Event('change'));
    bookingLocation.dispatchEvent(new Event('change'));
    if (window.lucide) lucide.createIcons();
</script>
@endsection
