@extends('layouts.app')

@section('title', 'Pricelist Jasa Standar · Admin Bengkel Jomoto')
@section('page_title', 'Pricelist Jasa Standar')
@section('page_icon', 'tags')

@section('content')
<div class="space-y-6">
    {{-- Header & Metrics --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-lime-300 text-black">
                    <i data-lucide="tags" class="h-5 w-5"></i>
                </span>
                <span class="text-3xl font-extrabold text-slate-900">{{ $pricelists->count() }}</span>
            </div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Total Layanan Pricelist</div>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i data-lucide="circle-check" class="h-5 w-5"></i>
                </span>
                <span class="text-3xl font-extrabold text-emerald-700">{{ $pricelists->where('is_active', true)->count() }}</span>
            </div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Layanan Aktif</div>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-sky-100 text-sky-700">
                    <i data-lucide="layers" class="h-5 w-5"></i>
                </span>
                <span class="text-3xl font-extrabold text-sky-700">{{ count($categories) }}</span>
            </div>
            <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Kategori Servis</div>
        </div>
    </div>

    {{-- Main Filter & Table Card --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.pricelist') }}"
                   class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $category === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Kategori ({{ $pricelists->count() }})
                </a>
                @foreach($categories as $cat)
                    @php($label = \App\Models\ServicePricelist::SERVICE_CATEGORIES[$cat] ?? match ($cat) { 'free_atpm' => 'Gratis ATPM', 'free_jomoto' => 'Gratis Jomoto', 'home_service' => 'Home Service', default => ucfirst($cat) })
                    <a href="{{ route('admin.pricelist', ['category' => $cat, 'q' => $search]) }}"
                       class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $category === $cat ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.pricelist') }}" class="relative min-w-[240px]">
                @if($category !== 'all')
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama layanan..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-1 focus:ring-slate-400">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">Nama Layanan Servis</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Harga Acuan Dasar</th>
                        <th class="px-5 py-3.5">Rincian Aksi &amp; Biaya</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pricelists as $index => $item)
                        @php($actions = $item->actionPrices())
                        <tr class="transition hover:bg-slate-50/70 {{ $item->is_active ? '' : 'opacity-50' }}">
                            <td class="px-5 py-3.5 text-xs text-slate-400 tabular-nums">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                {{ $item->service_name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    {{ $item->categoryLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 tabular-nums">
                                Rp {{ number_format($item->price) }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($actions !== [])
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($actions as $action => $price)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-0.5 text-xs">
                                                <span class="grid h-4 w-4 place-items-center rounded bg-slate-900 text-[9px] font-black text-lime-300">{{ $action }}</span>
                                                <span class="font-bold text-slate-700">Rp {{ number_format($price) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
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
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada layanan pricelist yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
