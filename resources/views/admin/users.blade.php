@extends('layouts.app')

@section('title', 'Kelola User & Role · Admin Bengkel Jomoto')
@section('page_title', 'Kelola User & Role')
@section('page_icon', 'user-cog')

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
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $metricRoles = [
                ['admin', 'Admin / Supervisor', 'shield-check', 'bg-slate-900 text-lime-300'],
                ['sa', 'Sales Adviser', 'clipboard-list', 'bg-indigo-100 text-indigo-700'],
                ['teknisi', 'Teknisi / Mekanik', 'wrench', 'bg-amber-100 text-amber-700'],
                ['customer', 'Customer / Pelanggan', 'users', 'bg-sky-100 text-sky-700'],
            ];
        @endphp
        @foreach($metricRoles as [$rKey, $rLabel, $rIcon, $rStyle])
            <a href="{{ route('admin.users', ['role' => $rKey]) }}"
               class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $role === $rKey ? 'ring-2 ring-lime-400' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl {{ $rStyle }}">
                        <i data-lucide="{{ $rIcon }}" class="h-5 w-5"></i>
                    </span>
                    <span class="text-3xl font-extrabold text-slate-900">{{ $roleCounts[$rKey] ?? 0 }}</span>
                </div>
                <div class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">{{ $rLabel }}</div>
            </a>
        @endforeach
    </div>

    {{-- Main Filter & Table Card --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users', array_merge(request()->except('role', 'page'), ['role' => 'all'])) }}"
                   class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $role === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Role ({{ array_sum($roleCounts) }})
                </a>
                @foreach($roles as $key => $label)
                    <a href="{{ route('admin.users', array_merge(request()->except('role', 'page'), ['role' => $key])) }}"
                       class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $role === $key ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }} ({{ $roleCounts[$key] ?? 0 }})
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.users') }}" class="relative flex-1 sm:w-64">
                    @if($role !== 'all')
                        <input type="hidden" name="role" value="{{ $role }}">
                    @endif
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, email, hp..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-xs outline-none focus:border-slate-400 focus:bg-white">
                    <i data-lucide="search" class="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400"></i>
                </form>
                <button type="button" onclick="openCreateUserModal()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-lime-300 shadow-sm transition hover:bg-slate-800 active:scale-[.98]">
                    <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Tambah User Baru
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Nama &amp; Kontak</th>
                        <th class="px-5 py-3">Role Sistem</th>
                        <th class="px-5 py-3 text-center">Aktivitas / Terkait</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 font-bold text-slate-700">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                                <span class="ml-1.5 rounded-md bg-lime-100 px-1.5 py-0.5 text-[10px] font-extrabold text-lime-800">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">{{ $u->email }}</div>
                                        @if($u->phone)
                                            <div class="text-[11px] text-slate-500"><i data-lucide="phone" class="inline h-3 w-3"></i> {{ $u->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $badgeStyle = match($u->role) {
                                        'admin' => 'bg-slate-900 text-lime-300',
                                        'sa' => 'bg-indigo-100 text-indigo-700',
                                        'teknisi' => 'bg-amber-100 text-amber-700',
                                        'customer' => 'bg-sky-100 text-sky-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeStyle }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $roles[$u->role] ?? ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center text-xs text-slate-500">
                                @if($u->role === 'customer')
                                    <span>{{ $u->vehicles_count }} Motor · {{ $u->customer_orders_count }} WO</span>
                                @elseif($u->role === 'teknisi')
                                    <span>{{ $u->technician_orders_count }} WO Dikerjakan</span>
                                @elseif($u->role === 'sa')
                                    <span>{{ $u->sa_orders_count }} WO Dikelola</span>
                                @else
                                    <span class="text-slate-400">Akses Penuh Supervisor</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            onclick="openEditUserModal(@js($u))"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                        <i data-lucide="edit-3" class="h-3.5 w-3.5 text-slate-400"></i> Edit Role &amp; Data
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Yakin ingin menghapus user {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-white p-1.5 text-xs font-bold text-rose-600 shadow-sm transition hover:bg-rose-50">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-xs text-slate-400">
                                Tidak ada data user yang sesuai dengan filter atau kata kunci pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-slate-100 p-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah User Baru --}}
<div id="createUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-14 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Tambah User Baru &amp; Tentukan Role</h3>
            <button onclick="closeCreateUserModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Role Sistem *</label>
                <select name="role" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-semibold outline-none focus:border-slate-400 focus:bg-white">
                    <option value="sa">Sales Adviser (Kelola Order, Estimasi &amp; Check-in)</option>
                    <option value="teknisi">Teknisi / Mekanik (Pemeriksaan &amp; Pengerjaan Servis)</option>
                    <option value="admin">Admin / Supervisor (Akses Master, Pricelist, Laporan)</option>
                    <option value="customer">Customer / Pelanggan (Booking &amp; Monitoring)</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Ahmad Teknisi"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Email Login *</label>
                <input type="email" name="email" required placeholder="user@jomoto.com"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Password Login *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" placeholder="08123456789"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat</label>
                    <input type="text" name="address" placeholder="Surabaya"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeCreateUserModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan User Baru</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit User & Role --}}
<div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="relative mx-auto mt-14 max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Ubah Data &amp; Role User</h3>
            <button onclick="closeEditUserModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form id="editUserForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Role Sistem *</label>
                <select id="edit_user_role" name="role" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-semibold outline-none focus:border-slate-400 focus:bg-white">
                    <option value="admin">Admin / Supervisor</option>
                    <option value="sa">Sales Adviser</option>
                    <option value="teknisi">Teknisi / Mekanik</option>
                    <option value="customer">Customer / Pelanggan</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama Lengkap *</label>
                <input type="text" id="edit_user_name" name="name" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Email Login *</label>
                <input type="email" id="edit_user_email" name="email" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Ganti Password (Opsional)</label>
                <input type="password" name="password" minlength="6" placeholder="Kosongkan jika password tidak diubah"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="edit_user_phone" name="phone"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat</label>
                    <input type="text" id="edit_user_address" name="address"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:border-slate-400 focus:bg-white">
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEditUserModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-lime-300 hover:bg-slate-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }
    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }
    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = `/admin/users/${user.id}`;
        document.getElementById('edit_user_role').value = user.role || 'customer';
        document.getElementById('edit_user_name').value = user.name || '';
        document.getElementById('edit_user_email').value = user.email || '';
        document.getElementById('edit_user_phone').value = user.phone || user.whatsapp || '';
        document.getElementById('edit_user_address').value = user.address || '';
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
</script>
@endsection
