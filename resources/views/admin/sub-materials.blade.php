@extends('layouts.app')

@section('title', 'Katalog Sub-Material & Open Pricing · Admin Bengkel Jomoto')
@section('page_title', 'Katalog Sub-Material & Open Pricing')
@section('page_icon', 'package')

@section('content')
<div class="space-y-6">
    {{-- Header & Metrics --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-lime-300 text-black">
                    <i data-lucide="package" class="h-5 w-5"></i>
                </span>
                <span class="text-3xl font-extrabold text-slate-900">{{ $subMaterials->count() }}</span>
            </div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Total Item Sub-Material</div>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i data-lucide="circle-check" class="h-5 w-5"></i>
                </span>
                <span class="text-3xl font-extrabold text-emerald-700">{{ $subMaterials->where('is_active', true)->count() }}</span>
            </div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Item Aktif Siap Digunakan SA</div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="rounded-2xl border border-blue-200 bg-blue-50/80 p-4 text-xs text-blue-900">
        <div class="flex items-start gap-2.5">
            <i data-lucide="info" class="h-4 w-4 shrink-0 text-blue-600 mt-0.5"></i>
            <div>
                <span class="font-bold">Tentang Sub-Material &amp; Open Pricing:</span>
                Sub-material adalah tindakan atau bahan pendukung pengerjaan di luar jasa standar dan suku cadang (misal pelumasan khusus, las, bubut, cairan pembersih, dsb). SA dapat menginput dan menyesuaikan harga secara fleksibel pada lembar estimasi.
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-bold text-slate-900">Daftar Sub-Material &amp; Bahan Pendukung</h2>

            <form method="GET" action="{{ route('admin.subMaterials') }}" class="relative min-w-[240px]">
                <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama tindakan/bahan..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-1 focus:ring-slate-400">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">Nama Tindakan / Bahan</th>
                        <th class="px-5 py-3.5">Deskripsi &amp; Panduan Penggunaan</th>
                        <th class="px-5 py-3.5 text-right">Default Harga Satuan</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subMaterials as $index => $item)
                        <tr class="transition hover:bg-slate-50/70 {{ $item->is_active ? '' : 'opacity-50' }}">
                            <td class="px-5 py-3.5 text-xs text-slate-400 tabular-nums">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                {{ $item->name }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $item->description ?: '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-slate-800 tabular-nums">
                                Rp {{ number_format($item->default_price) }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($item->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-400">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada sub-material yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
