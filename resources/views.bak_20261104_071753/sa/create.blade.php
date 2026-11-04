@extends('layouts.app')

@section('title', 'Buat WO Baru')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('sa.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Kembali ke daftar WO</a>

    <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">Buat Work Order Baru</h1>
        <p class="mt-1 text-sm text-slate-500">Catat keluhan customer, pilih motor, tentukan jenis & lokasi service.</p>

        <form method="POST" action="{{ route('sa.store') }}" class="mt-6 space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Customer</label>
                    <select name="customer_id" id="customer_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                        <option value="">— Pilih customer —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-vehicles="{{ $customer->vehicles->map(fn($v) => $v->id.'|'.$v->fullName().' · '.$v->plate_number)->implode(';;') }}">
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kendaraan</label>
                    <select name="vehicle_id" id="vehicle_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                        <option value="">— Pilih customer dulu —</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis Service</label>
                    <select name="service_type" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                        <option value="regular">Reguler</option>
                        <option value="free_atpm">Gratis Berkala ATPM</option>
                        <option value="free_jomoto">Gratis Berkala Jomoto</option>
                        <option value="home_service">Home Service (Panggilan)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi Pengerjaan</label>
                    <select name="location_type" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                        <option value="onsite">Onsite (di bengkel)</option>
                        <option value="offsite">Offsite (panggilan)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Target Selesai (opsional)</label>
                    <input type="datetime-local" name="estimated_completion" value="{{ old('estimated_completion') }}"
                           class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Keluhan Customer <span class="text-red-500">*</span></label>
                    <textarea name="customer_complaint" rows="4" required placeholder="Contoh: motor sering mati mendadak saat perjalanan jauh, rem depan bunyi..."
                              class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">{{ old('customer_complaint') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('sa.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Simpan & Buat WO
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const customerSelect = document.getElementById('customer_id');
    const vehicleSelect = document.getElementById('vehicle_id');

    customerSelect.addEventListener('change', () => {
        const option = customerSelect.selectedOptions[0];
        const vehicles = (option?.dataset.vehicles || '').split(';;').filter(Boolean);

        vehicleSelect.innerHTML = '<option value="">— Pilih kendaraan —</option>';
        vehicles.forEach(entry => {
            const [id, label] = entry.split('|');
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = label;
            vehicleSelect.appendChild(opt);
        });
    });
</script>
@endsection
