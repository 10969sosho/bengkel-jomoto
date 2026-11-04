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
                <p class="text-sm text-slate-500">Catat keluhan customer, pilih motor, tentukan jenis &amp; lokasi service.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('sa.store') }}" class="mt-6 space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Customer</label>
                    <select name="customer_id" id="customer_id" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
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
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="">— Pilih customer dulu —</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis Service</label>
                    <select name="service_type" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="regular">Reguler</option>
                        <option value="free_atpm">Gratis Berkala ATPM</option>
                        <option value="free_jomoto">Gratis Berkala Jomoto</option>
                        <option value="home_service">Home Service (Panggilan)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi Pengerjaan</label>
                    <select name="location_type" required
                            class="w-full cursor-pointer rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                        <option value="onsite">Onsite (di bengkel)</option>
                        <option value="offsite">Offsite (panggilan)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Target Selesai (opsional)</label>
                    <input type="datetime-local" name="estimated_completion" value="{{ old('estimated_completion') }}"
                           class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Keluhan Customer <span class="text-red-500">*</span></label>
                    <textarea name="customer_complaint" rows="4" required placeholder="Contoh: motor sering mati mendadak saat perjalanan jauh, rem depan bunyi..."
                              class="w-full rounded-2xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm outline-none transition focus:border-lime-400 focus:bg-white focus:ring-2 focus:ring-lime-200">{{ old('customer_complaint') }}</textarea>
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
