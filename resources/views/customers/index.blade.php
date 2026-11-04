@extends('layouts.app')

@section('title', 'Kelola Pelanggan')
@section('page_title', 'Manajemen Customer & Kendaraan')
@section('page_icon', 'users-round')

@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kelola Pelanggan</h1>
        <p class="text-sm text-slate-500">Data customer, kendaraan, dan riwayat servis.</p>
    </div>
    <button type="button" onclick="document.getElementById('customer-form').classList.toggle('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">
        <i data-lucide="user-plus" class="h-4 w-4"></i> + Tambah Customer
    </button>
</div>

<form id="customer-form" method="POST" action="{{ route('sa.customers.store') }}" class="mb-5 {{ $errors->any() ? '' : 'hidden' }} rounded-3xl border border-lime-200 bg-white p-5 shadow-sm">
    @csrf
    <h2 class="mb-4 font-bold text-slate-900">Customer Baru</h2>
    <div class="grid gap-3 sm:grid-cols-2">
        <input name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Nama lengkap" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
        <input name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="No. telepon" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
        <input name="whatsapp" value="{{ old('whatsapp') }}" maxlength="30" placeholder="No. WhatsApp (opsional)" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
        <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="Email" class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
    </div>
    <div class="mt-4 flex justify-end">
        <button class="rounded-xl bg-lime-300 px-4 py-2.5 text-sm font-bold text-black">Simpan Customer</button>
    </div>
</form>

<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ $search }}" placeholder="Cari nama, telepon, WhatsApp, atau email" class="min-w-0 flex-1 rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm">
    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Cari</button>
</form>

<div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400">
                <tr><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Kontak</th><th class="px-5 py-3">Kendaraan</th><th class="px-5 py-3 text-right">Detail</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $customer)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $customer->name }}<div class="text-xs font-normal text-slate-500">{{ $customer->email }}</div></td>
                        <td class="px-5 py-4 text-slate-600">{{ $customer->phone ?: '—' }}<div class="text-xs text-slate-400">WA {{ $customer->whatsapp ?: '—' }}</div></td>
                        <td class="px-5 py-4 text-slate-600">{{ $customer->vehicles_count }} motor</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('sa.customers.show', $customer) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">Buka Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Customer tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $customers->links() }}</div>@endif
</div>
@endsection
