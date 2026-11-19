@extends('layouts.app')

@section('title', 'Katalog Sub-Material & Open Pricing · Admin Bengkel Jomoto')
@section('page_title', 'Katalog Sub-Material & Open Pricing')
@section('page_icon', 'package')

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

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.subMaterials') }}" class="relative min-w-[200px]">
                    <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama tindakan/bahan..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-1 focus:ring-slate-400">
                </form>

                <button onclick="openCreateSubModal()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-lime-300 shadow-sm transition hover:bg-slate-800 active:scale-95">
                    <i data-lucide="plus" class="h-4 w-4"></i> Tambah Sub-Material
                </button>
            </div>
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
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subMaterials as $index => $item)
                        <tr class="transition hover:bg-slate-50/70 {{ $item->is_active ? '' : 'opacity-60' }}">
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
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button onclick='openEditSubModal(@json($item))'
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.subMaterials.destroy', $item) }}" onsubmit="return confirm('Hapus sub-material {{ addslashes($item->name) }}?')">
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
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada sub-material yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Create Sub-Material --}}
<div id="createSubModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-16 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Tambah Sub-Material Baru</h3>
            <button onclick="closeCreateSubModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.subMaterials.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Tindakan / Bahan *</label>
                <input type="text" name="name" required placeholder="Contoh: Bubut Tromol / Cairan Injector Cleaner"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Default Harga Satuan (Rp) *</label>
                <input type="number" name="default_price" required min="0" step="1000" placeholder="25000"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Deskripsi &amp; Panduan Penggunaan</label>
                <textarea name="description" rows="3" placeholder="Penjelasan kapan sub-material ini dianjurkan digunakan..."
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="create_sub_active" value="1" checked class="h-4 w-4 rounded text-slate-900 focus:ring-slate-400">
                <label for="create_sub_active" class="text-xs font-semibold text-slate-700">Item Aktif (Dapat dipilih di lembar estimasi SA)</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeCreateSubModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan Sub-Material</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Sub-Material --}}
<div id="editSubModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-16 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Edit Sub-Material</h3>
            <button onclick="closeEditSubModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form id="editSubForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Tindakan / Bahan *</label>
                <input type="text" id="edit_sub_name" name="name" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Default Harga Satuan (Rp) *</label>
                <input type="number" id="edit_sub_price" name="default_price" required min="0" step="1000"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Deskripsi &amp; Panduan Penggunaan</label>
                <textarea id="edit_sub_desc" name="description" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_sub_active" value="1" class="h-4 w-4 rounded text-slate-900 focus:ring-slate-400">
                <label for="edit_sub_active" class="text-xs font-semibold text-slate-700">Item Aktif</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEditSubModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateSubModal() {
        document.getElementById('createSubModal').classList.remove('hidden');
    }
    function closeCreateSubModal() {
        document.getElementById('createSubModal').classList.add('hidden');
    }
    function openEditSubModal(item) {
        const form = document.getElementById('editSubForm');
        form.action = `/admin/sub-materials/${item.id}`;
        document.getElementById('edit_sub_name').value = item.name || '';
        document.getElementById('edit_sub_price').value = item.default_price || 0;
        document.getElementById('edit_sub_desc').value = item.description || '';
        document.getElementById('edit_sub_active').checked = !!item.is_active;
        document.getElementById('editSubModal').classList.remove('hidden');
    }
    function closeEditSubModal() {
        document.getElementById('editSubModal').classList.add('hidden');
    }
</script>
@endsection
