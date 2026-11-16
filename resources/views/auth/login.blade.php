<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 text-slate-800 antialiased">
<div class="w-full max-w-md fade-in">
    <div class="mb-6 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-lime-400 text-2xl font-black text-slate-900 shadow-[0_4px_14px_rgba(163,230,53,.45)]">J</span>
        <h1 class="mt-4 text-xl font-extrabold tracking-tight text-slate-900">BENGKEL JOMOTO</h1>
        <p class="mt-1 text-sm text-slate-500">Program Bengkel · Service &amp; Maintenance</p>
    </div>

    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/50">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Masuk ke akun Anda</h2>

        @if($errors->any())
            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-3.5">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-lime-500 focus:bg-white focus:ring-2 focus:ring-lime-400/20">
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-lime-500 focus:bg-white focus:ring-2 focus:ring-lime-400/20">
            </div>
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 font-semibold text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-lime-500 focus:ring-lime-400/30">
                    Ingat saya
                </label>
            </div>
            <button class="w-full rounded-xl bg-lime-400 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-lime-500 active:scale-[.98] shadow-sm">
                Masuk
            </button>
        </form>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Login cepat akun demo</p>
            <div class="mt-2.5 grid grid-cols-2 gap-2">
                @foreach([
                    ['email' => 'admin@jomoto.com', 'label' => 'Admin', 'icon' => 'shield-check'],
                    ['email' => 'sa@jomoto.com', 'label' => 'Sales Adviser', 'icon' => 'clipboard-list'],
                    ['email' => 'budi.teknisi@jomoto.com', 'label' => 'Teknisi', 'icon' => 'wrench'],
                    ['email' => 'hendra@gmail.com', 'label' => 'Customer', 'icon' => 'bike'],
                ] as $demo)
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $demo['email'] }}">
                        <input type="hidden" name="password" value="password">
                        <button class="flex w-full items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-left text-xs font-bold text-slate-700 transition hover:border-lime-500 hover:bg-lime-50 hover:text-slate-900 active:scale-[.98]">
                            <i data-lucide="{{ $demo['icon'] }}" class="h-4 w-4 shrink-0 text-slate-500"></i>
                            <span class="truncate">{{ $demo['label'] }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">Program Bengkel Jomoto · Laravel + Blade</p>
</div>

<style>
    .fade-in { animation: fadeUp .3s ease both; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
</style>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>