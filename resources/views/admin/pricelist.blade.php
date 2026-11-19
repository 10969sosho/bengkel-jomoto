@extends('layouts.app')

@section('title', 'Pricelist Jasa Standar · Admin Bengkel Jomoto')
@section('page_title', 'Pricelist Jasa Standar')
@section('page_icon', 'tags')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            <i data-lucide="check-circle" class="h-5 w-5 shrink-0 text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div class="font-bold">Terjadi kesalahan:</div>
            <ul class="mt-1 list-inside list-disc">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.pricelist') }}" class="relative min-w-[200px]">
                    @if($category !== 'all')
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari layanan..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-1 focus:ring-slate-400">
                </form>

                <button onclick="openCreateModal()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-lime-300 shadow-sm transition hover:bg-slate-800 active:scale-95">
                    <i data-lucide="plus" class="h-4 w-4"></i> Tambah Layanan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">Nama Layanan Servis</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Harga Acuan Dasar</th>
                        <th class="px-5 py-3.5">Harga P (Periksa)</th>
                        <th class="px-5 py-3.5">Harga S (Setel/Bersih)</th>
                        <th class="px-5 py-3.5">Harga G (Ganti/Perbaiki)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pricelists as $index => $item)
                        <tr class="transition hover:bg-slate-50/70 {{ $item->is_active ? '' : 'opacity-60' }}">
                            <td class="px-5 py-3.5 text-xs text-slate-400 tabular-nums">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                {{ $item->service_name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    {{ $item->categoryLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 tabular-nums">Rp {{ number_format($item->price) }}</td>
                            <td class="px-5 py-3.5 tabular-nums">
                                {{ $item->price_p > 0 ? 'Rp ' . number_format($item->price_p) : '-' }}
                            </td>
                            <td class="px-5 py-3.5 tabular-nums">
                                {{ $item->price_s > 0 ? 'Rp ' . number_format($item->price_s) : '-' }}
                            </td>
                            <td class="px-5 py-3.5 tabular-nums">
                                {{ $item->price_g > 0 ? 'Rp ' . number_format($item->price_g) : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($item->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-400">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button onclick='openEditModal(@json($item))'
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.pricelist.destroy', $item) }}" onsubmit="return confirm('Hapus layanan {{ addslashes($item->service_name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition hover:bg-rose-50">
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada layanan pricelist yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Create --}}
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-16 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Tambah Layanan Jasa Baru</h3>
            <button onclick="closeCreateModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.pricelist.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Layanan Servis *</label>
                <input type="text" name="service_name" required placeholder="Contoh: Tune Up Mesin 4-Tak"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kategori *</label>
                    <select name="category" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                        @foreach(\App\Models\ServicePricelist::SERVICE_CATEGORIES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                        <option value="regular">Reguler / Umum</option>
                        <option value="free_atpm">Gratis ATPM</option>
                        <option value="free_jomoto">Gratis Jomoto</option>
                        <option value="home_service">Home Service</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Harga Acuan (Rp) *</label>
                    <input type="number" name="price" required min="0" step="1000" placeholder="50000"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
                <div class="mb-2 text-xs font-bold text-slate-700">Harga Berdasarkan Tingkat Aksi (Opsional)</div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi P (Periksa)</label>
                        <input type="number" name="price_p" min="0" placeholder="Rp"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi S (Setel)</label>
                        <input type="number" name="price_s" min="0" placeholder="Rp"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi G (Ganti)</label>
                        <input type="number" name="price_g" min="0" placeholder="Rp"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="create_active" value="1" checked class="h-4 w-4 rounded text-slate-900 focus:ring-slate-400">
                <label for="create_active" class="text-xs font-semibold text-slate-700">Layanan Aktif (Tersedia di Lembar Estimasi SA)</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeCreateModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan Layanan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-16 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Edit Layanan Pricelist</h3>
            <button onclick="closeEditModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form id="editForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Layanan Servis *</label>
                <input type="text" id="edit_service_name" name="service_name" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kategori *</label>
                    <select id="edit_category" name="category" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                        @foreach(\App\Models\ServicePricelist::SERVICE_CATEGORIES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                        <option value="regular">Reguler / Umum</option>
                        <option value="free_atpm">Gratis ATPM</option>
                        <option value="free_jomoto">Gratis Jomoto</option>
                        <option value="home_service">Home Service</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Harga Acuan (Rp) *</label>
                    <input type="number" id="edit_price" name="price" required min="0" step="1000"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
                <div class="mb-2 text-xs font-bold text-slate-700">Harga Berdasarkan Tingkat Aksi (Opsional)</div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi P (Periksa)</label>
                        <input type="number" id="edit_price_p" name="price_p" min="0"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi S (Setel)</label>
                        <input type="number" id="edit_price_s" name="price_s" min="0"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500">Aksi G (Ganti)</label>
                        <input type="number" id="edit_price_g" name="price_g" min="0"
                               class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none">
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_active" value="1" class="h-4 w-4 rounded text-slate-900 focus:ring-slate-400">
                <label for="edit_active" class="text-xs font-semibold text-slate-700">Layanan Aktif</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEditModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }
    function openEditModal(item) {
        const form = document.getElementById('editForm');
        form.action = `/admin/pricelist/${item.id}`;
        document.getElementById('edit_service_name').value = item.service_name || '';
        document.getElementById('edit_category').value = item.category || 'mesin';
        document.getElementById('edit_price').value = item.price || 0;
        document.getElementById('edit_price_p').value = item.price_p || '';
        document.getElementById('edit_price_s').value = item.price_s || '';
        document.getElementById('edit_price_g').value = item.price_g || '';
        document.getElementById('edit_active').checked = !!item.is_active;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endsection
