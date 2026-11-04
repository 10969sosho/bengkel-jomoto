@extends('layouts.app')

@section('title', 'Buat WO Baru')
@section('page_title', 'Buat Work Order Baru')
@section('page_icon', 'file-plus-2')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('sa.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke daftar WO
    </a>

    <div class="mt-4 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-lime-300 text-black shadow-[0_4px_14px_rgba(163,230,53,.35)]">
                <i data-lucide="file-plus-2" class="h-5 w-5"></i>
            </span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Buat Work Order Baru</h1>
                <p class="text-sm text-slate-500">Catat keluhan customer, pilih motor, tentukan lokasi, dan tugaskan teknisi.</p>
            </div>
        </div>
        @if($selectedBooking)
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                Memproses booking {{ $selectedBooking->wo_number }} · data booking sudah diisi otomatis.
            </div>
        @endif

        <form method="POST" action="{{ route('sa.store') }}" class="mt-6 space-y-5">
            @csrf
            @if($selectedBooking)<input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">@endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Customer</label>
                    <select name="customer_id" id="customer_id" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih customer —</option>
                        @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected($selectedCustomerId === $customer->id) data-vehicles="{{ $customer->vehicles->map(fn($v) => $v->id.'|'.$v->fullName().' · '.$v->plate_number.'|'.$v->last_odometer.'|'.$v->serviceOrders->pluck('coupon_milestone')->join(','))->implode(';;') }}">
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kendaraan</label>
                    <select name="vehicle_id" id="vehicle_id" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih customer dulu —</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tugaskan ke Teknisi</label>
                    <select name="technician_id" required class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih teknisi —</option>
                        @foreach($technicians as $technician)
                            <option value="{{ $technician->id }}" @selected(old('technician_id') == $technician->id)>{{ $technician->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi Service</label>
                    <select name="location_type" id="location_type" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="onsite" @selected(old('location_type', $selectedBooking?->location_type ?? 'onsite') === 'onsite')>Service di Bengkel</option>
                        <option value="offsite" @selected(old('location_type', $selectedBooking?->location_type) === 'offsite')>Service Panggilan (Home Service)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">KM Saat Ini</label>
                    <input type="number" min="0" name="odometer" id="odometer" value="{{ old('odometer', $selectedBooking?->odometer ?? $selectedBooking?->vehicle?->last_odometer) }}" required
                           class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                </div>

                <div id="coupon-badge" class="hidden rounded-2xl border border-lime-200 bg-lime-50 p-3 text-sm font-bold text-lime-800 sm:col-span-2"></div>

                <div id="home-fields" class="hidden space-y-3 rounded-2xl border border-lime-200 bg-lime-50 p-4 sm:col-span-2">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-lime-900">Alamat Service Panggilan</label>
                            <textarea name="offsite_address" rows="2" class="w-full rounded-xl border-lime-200 bg-white px-3 py-2.5 text-sm" placeholder="Alamat lengkap customer">{{ old('offsite_address', $selectedBooking?->offsite_address) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-lime-900">Catatan Patokan</label>
                            <textarea name="location_notes" rows="2" class="w-full rounded-xl border-lime-200 bg-white px-3 py-2.5 text-sm" placeholder="Patokan lokasi / akses masuk">{{ old('location_notes', $selectedBooking?->location_notes) }}</textarea>
                        </div>
                    </div>
                    <div class="max-w-xs">
                        <label class="mb-1 block text-sm font-semibold text-lime-900">Biaya Kunjungan (Rp)</label>
                        <input type="number" min="0" name="visit_fee" value="{{ old('visit_fee', $selectedBooking?->visit_fee ?? 50000) }}" class="w-full rounded-xl border-lime-200 bg-white px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Target Selesai (opsional)</label>
                    <input type="datetime-local" name="estimated_completion" value="{{ old('estimated_completion') }}"
                           class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Keluhan Customer <span class="text-red-500">*</span></label>
                    <textarea name="customer_complaint" rows="4" required placeholder="Contoh: motor sering mati mendadak saat perjalanan jauh, rem depan bunyi..."
                              class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">{{ old('customer_complaint', $selectedBooking?->customer_complaint) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('sa.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-lime-400 active:scale-[.98]">
                    <i data-lucide="save" class="h-4 w-4"></i> Simpan &amp; Buat WO
                </button>
            </div>
        </form>
    </div>

    <details class="mt-4 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer font-bold text-slate-800">+ Tambah kendaraan customer</summary>
        <form method="POST" id="vehicle-form" class="mt-4 grid gap-3 sm:grid-cols-2">
            @csrf
            <select id="vehicle-customer" required class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                <option value="">Pilih customer</option>
                @foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
            </select>
            <input name="brand" required placeholder="Merk" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="model" required placeholder="Model" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="plate_number" required placeholder="Plat nomor" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input type="number" name="year" min="1950" max="{{ now()->year + 1 }}" required placeholder="Tahun" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="engine_number" placeholder="Nomor mesin" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <input name="chassis_number" placeholder="Nomor rangka" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <select name="fuel_type" required class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"><option value="gasoline">Bensin</option><option value="electric">Listrik</option></select>
            <input type="number" min="0" name="last_odometer" required placeholder="KM awal" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-lime-300 sm:col-span-2">Simpan Kendaraan</button>
        </form>
    </details>
</div>

<script>
const customerSelect = document.getElementById('customer_id');
const vehicleSelect = document.getElementById('vehicle_id');
const odometerInput = document.getElementById('odometer');
const locationSelect = document.getElementById('location_type');
const homeFields = document.getElementById('home-fields');
const couponBadge = document.getElementById('coupon-badge');

    customerSelect.addEventListener('change', () => {
        const option = customerSelect.selectedOptions[0];
        const vehicles = (option?.dataset.vehicles || '').split(';;').filter(Boolean);

        vehicleSelect.innerHTML = '<option value="">— Pilih kendaraan —</option>';
        vehicles.forEach(entry => {
            const [id, label, odometer, claimed] = entry.split('|');
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = label;
            opt.dataset.odometer = odometer;
            opt.dataset.claimed = claimed || '';
            vehicleSelect.appendChild(opt);
        });
        if (vehicleSelect.options.length > 1) vehicleSelect.selectedIndex = 1;
        odometerInput.value = vehicleSelect.selectedOptions[0]?.dataset.odometer || '';
        showCoupon();
    });

vehicleSelect.addEventListener('change', () => {
    odometerInput.value = vehicleSelect.selectedOptions[0]?.dataset.odometer || '';
    showCoupon();
});
odometerInput.addEventListener('input', showCoupon);
locationSelect.addEventListener('change', () => homeFields.classList.toggle('hidden', locationSelect.value !== 'offsite'));

function showCoupon() {
    const km = Number(odometerInput.value);
    const atpm = [1000, 4000, 7000].find(target => km >= target && km <= target + 500);
    const jomoto = km >= 10000 ? Math.floor(km / 10000) * 10000 : null;
    const candidate = atpm || (jomoto && km <= jomoto + 500 ? jomoto : null);
    const claimed = (vehicleSelect.selectedOptions[0]?.dataset.claimed || '').split(',').map(Number);
    const milestone = candidate && !claimed.includes(candidate) ? candidate : null;
    couponBadge.classList.toggle('hidden', !milestone);
    couponBadge.textContent = milestone
        ? (atpm ? `Klaim Gratis ATPM · ${milestone.toLocaleString('id-ID')} KM · Oli, sparepart dasar & jasa gratis` : `Klaim Gratis Jomoto · ${milestone.toLocaleString('id-ID')} KM · Jasa gratis, sparepart bayar`)
        : '';
}

const vehicleCustomer = document.getElementById('vehicle-customer');
vehicleCustomer.addEventListener('change', () => {
    document.getElementById('vehicle-form').action = `{{ url('/sa/customers') }}/${vehicleCustomer.value}/vehicles`;
});
if (customerSelect.value) customerSelect.dispatchEvent(new Event('change'));
@if($selectedBooking)
vehicleSelect.value = '{{ $selectedBooking->vehicle_id }}';
odometerInput.value = '{{ $selectedBooking->odometer ?? $selectedBooking->vehicle?->last_odometer }}';
showCoupon();
@endif
locationSelect.dispatchEvent(new Event('change'));
</script>
@endsection
