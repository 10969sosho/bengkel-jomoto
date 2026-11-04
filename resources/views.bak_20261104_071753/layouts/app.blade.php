<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in { animation: fade .25s ease; }
        @keyframes fade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">

<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 text-lg font-bold text-white">J</span>
            <span class="text-sm font-bold leading-tight sm:text-base">
                Bengkel Jomoto
                <span class="block text-[11px] font-medium text-slate-400">Program Bengkel — Phase 1 MVP</span>
            </span>
        </a>

        <nav class="ml-auto flex flex-wrap items-center gap-1 text-sm">
            @php($role = auth()->user()?->role)
            @if($role === 'sa')
                <a href="{{ route('sa.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">Daftar WO</a>
                <a href="{{ route('sa.create') }}" class="rounded-lg px-3 py-1.5 font-medium text-indigo-600 hover:bg-indigo-50">+ WO Baru</a>
            @elseif($role === 'teknisi')
                <a href="{{ route('teknisi.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">Tugas Saya</a>
            @elseif($role === 'customer')
                <a href="{{ route('customer.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">Portal Saya</a>
            @else
                <a href="{{ route('admin.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">Admin</a>
            @endif
        </nav>

        <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
            <span class="hidden text-right text-xs leading-tight sm:block">
                <span class="block font-semibold text-slate-700">{{ auth()->user()?->name ?? 'Guest' }}</span>
                <span class="inline-block rounded bg-slate-200 px-1.5 py-0.5 uppercase tracking-wide text-slate-600">{{ $role ?? '-' }}</span>
            </span>
            <form method="POST" action="{{ route('demo.switch') }}" class="flex flex-wrap gap-1" title="Demo: pindah role dalam 1 klik">
                @csrf
                @foreach(['sa' => 'SA', 'teknisi' => 'Teknisi', 'customer' => 'Customer', 'admin' => 'Admin'] as $key => $label)
                    <button type="submit" name="role" value="{{ $key }}"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold transition {{ ($role ?? '') === $key ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>
    </div>

    <div class="bg-indigo-50/70 px-4 py-1 text-center text-[11px] font-medium text-indigo-500">
        Demo Mode — tombol di atas langsung beralih role (SA · Teknisi · Customer · Admin) tanpa login.
    </div>
</header>

<main class="mx-auto max-w-6xl px-4 py-6 fade-in">
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="mx-auto max-w-6xl px-4 pb-8 pt-2 text-center text-xs text-slate-400">
    Program Bengkel Jomoto · Phase 1 Core MVP · Laravel + Blade + Tailwind
</footer>

</body>
</html>
