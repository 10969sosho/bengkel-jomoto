<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Bengkel Jomoto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        :root { --nav-w: 16rem; }
        body.nav-collapsed { --nav-w: 5rem; }

        #sidebar { width: 16rem; transition: width .3s cubic-bezier(.4,0,.2,1), transform .3s cubic-bezier(.4,0,.2,1); }
        #main { transition: margin-left .3s cubic-bezier(.4,0,.2,1); margin-left: 16rem; }
        body.nav-collapsed #sidebar { width: 5rem; }
        body.nav-collapsed #main { margin-left: 5rem; }

        @media (max-width: 1023px) {
            #sidebar { width: 16rem; transform: translateX(-100%); box-shadow: 0 0 40px rgba(0,0,0,.4); }
            body.nav-open #sidebar { transform: translateX(0); }
            #main { margin-left: 0 !important; }
        }

        .nav-txt {
            max-width: 200px; overflow: hidden; white-space: nowrap; opacity: 1;
            transition: max-width .3s cubic-bezier(.4,0,.2,1), opacity .18s ease;
        }
        @media (min-width: 1024px) {
            body.nav-collapsed .nav-txt { max-width: 0; opacity: 0; }
            body.nav-collapsed .nav-hide { display: none; }
            body.nav-collapsed #brand-link { display: none; }
            body.nav-collapsed #sidebar > div:first-child { justify-content: center; padding-left: 0; padding-right: 0; }
            body.nav-collapsed #nav-toggle { margin-left: 0; }
            body.nav-collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
            body.nav-collapsed .nav-group { justify-content: center; }
            body.nav-collapsed #profile-card { padding: .5rem; justify-content: center; }
        }

        .fade-in { animation: fadeUp .3s ease both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        .pop-in { animation: popIn .18s ease both; }
        @keyframes popIn { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        #sidebar ::-webkit-scrollbar-thumb { background: #3f3f46; }
    </style>
</head>
<body class="min-h-screen bg-zinc-100 text-slate-700 antialiased">

@php
    $user = auth()->user();
    $role = $user?->role;
    $routeName = request()->route()?->getName() ?? '';
    $roleLabels = ['sa' => 'Service Adviser', 'teknisi' => 'Teknisi', 'customer' => 'Customer', 'admin' => 'Admin'];

    $menus = [
        'sa' => [
            ['label' => 'OPERASIONAL', 'items' => [
                ['text' => 'Work Order', 'route' => 'sa.index', 'icon' => 'clipboard-list'],
            ]],
            ['label' => 'LAYANAN & ESTIMASI', 'items' => [
                ['text' => 'Buat WO Baru', 'route' => 'sa.create', 'icon' => 'file-plus-2'],
                ['text' => 'Dokumentasi API', 'route' => 'api-docs', 'icon' => 'braces'],
            ]],
        ],
        'teknisi' => [
            ['label' => 'OPERASIONAL', 'items' => [
                ['text' => 'Tugas Saya', 'route' => 'teknisi.index', 'icon' => 'wrench'],
            ]],
        ],
        'customer' => [
            ['label' => 'OPERASIONAL', 'items' => [
                ['text' => 'Portal Servis', 'route' => 'customer.index', 'icon' => 'layout-dashboard'],
                ['text' => 'Riwayat & Kwitansi', 'route' => 'customer.index', 'anchor' => 'riwayat', 'icon' => 'receipt-text'],
            ]],
        ],
        'admin' => [
            ['label' => 'OPERASIONAL', 'items' => [
                ['text' => 'Dashboard', 'route' => 'admin.index', 'icon' => 'layout-dashboard'],
            ]],
            ['label' => 'MASTER & CUSTOMER', 'items' => [
                ['text' => 'Pricelist & Sub-Material', 'route' => 'admin.index', 'anchor' => 'pricelist', 'icon' => 'tags'],
                ['text' => 'Performa Teknisi', 'route' => 'admin.index', 'anchor' => 'performa', 'icon' => 'chart-no-axes-column-increasing'],
                ['text' => 'Laporan Pendapatan', 'route' => 'admin.reports', 'icon' => 'chart-no-axes-combined'],
                ['text' => 'Dokumentasi API', 'route' => 'api-docs', 'icon' => 'braces'],
            ]],
        ],
    ];
    $menuGroups = $menus[$role] ?? [];
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $user?->name ?? 'J'), 0, 1)) ?: 'J';
    $pageTitle = trim($__env->yieldContent('page_title') ?? '');
    $pageIcon = trim($__env->yieldContent('page_icon') ?? '');
@endphp

<div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden"></div>

{{-- ============ SIDEBAR ============ --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-zinc-800/80 bg-[#0f1117] text-slate-400">

    {{-- Brand + toggle --}}
    <div class="flex items-center gap-2.5 border-b border-zinc-800/80 px-3.5 py-4">
        <a href="{{ route('dashboard') }}" id="brand-link" class="flex min-w-0 items-center gap-2.5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-lime-300 text-lg font-black text-black shadow-[0_4px_14px_rgba(163,230,53,.35)] transition-transform duration-300 hover:scale-105">J</span>
            <span class="nav-txt min-w-0">
                <span class="block truncate text-[13px] font-extrabold tracking-tight text-white">BENGKEL JOMOTO</span>
                <span class="block truncate text-[11px] font-medium text-zinc-500">Service &amp; Maintenance</span>
            </span>
        </a>
        <button id="nav-toggle" type="button" aria-label="Expand / minimize sidebar"
                class="ml-auto grid h-7 w-7 shrink-0 place-items-center rounded-lg border border-zinc-800 bg-zinc-900/70 text-slate-400 transition hover:border-zinc-700 hover:text-lime-300">
            <i data-lucide="panel-left-close" class="h-3.5 w-3.5"></i>
        </button>
    </div>

    {{-- Search --}}
    <div class="nav-hide px-4 pt-4">
        <div class="relative">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-600"></i>
            <input id="menu-search" type="text" placeholder="Cari menu..." autocomplete="off"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-900/70 py-2.5 pl-9 pr-3 text-sm text-slate-200 placeholder-zinc-600 outline-none transition focus:border-lime-400/60 focus:bg-zinc-900">
        </div>
    </div>

    {{-- Navigation --}}
    <nav id="nav-list" class="flex-1 overflow-y-auto px-3 py-4">
        @foreach($menuGroups as $group)
            <div class="mb-5" data-group>
                <div class="nav-txt nav-group mb-2 px-3 text-[10px] font-bold uppercase tracking-[.14em] text-zinc-600">
                    {{ $group['label'] }}
                </div>
                <div class="space-y-1">
                    @foreach($group['items'] as $item)
                        @php
                            $active = $routeName === $item['route'] && ! isset($item['anchor']);
                            $url = route($item['route']) . (isset($item['anchor']) ? '#'.$item['anchor'] : '');
                        @endphp
                        <a href="{{ $url }}" data-label="{{ strtolower($item['text']) }}"
                           class="nav-item group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition duration-200
                                  {{ $active
                                      ? 'bg-white/[.07] text-white shadow-[inset_0_1px_0_rgba(255,255,255,.06)]'
                                      : 'text-slate-400 hover:bg-white/[.05] hover:text-slate-100' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px] shrink-0 transition {{ $active ? 'text-lime-300' : 'text-slate-500 group-hover:text-slate-300' }}"></i>
                            <span class="nav-txt">{{ $item['text'] }}</span>
                            @if($active)
                                <span class="nav-txt ml-auto h-2 w-2 shrink-0 rounded-full bg-lime-400 shadow-[0_0_8px_#a3e635]"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <p id="menu-empty" class="nav-hide hidden px-3 py-6 text-center text-xs text-zinc-600">Menu tidak ditemukan.</p>
    </nav>

    {{-- Profile --}}
    <div class="border-t border-zinc-800/80 p-3">
        <div id="profile-card" class="flex items-center gap-3 rounded-2xl bg-zinc-900/80 p-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-lime-300 text-sm font-black text-black">{{ $initials }}</span>
            <div class="nav-txt min-w-0 flex-1">
                <div class="truncate text-sm font-semibold text-white">{{ $user?->name ?? 'Guest' }}</div>
                <span class="mt-0.5 inline-block rounded-full bg-lime-400/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-lime-300">
                    {{ $roleLabels[$role] ?? ($role ?? '-') }}
                </span>
            </div>
            <button type="button" onclick="toggleRoleMenu(true)" title="Ganti role demo"
                    class="nav-hide grid h-8 w-8 shrink-0 place-items-center rounded-lg border border-zinc-800 text-slate-400 transition hover:border-lime-400/50 hover:text-lime-300">
                <i data-lucide="repeat-2" class="h-4 w-4"></i>
            </button>
        </div>
    </div>
</aside>

{{-- ============ MAIN ============ --}}
<div id="main" class="flex min-h-screen flex-col">

    {{-- Topbar --}}
    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-md">
        <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
            <button type="button" id="mobile-nav" aria-label="Buka menu"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:hidden">
                <i data-lucide="menu" class="h-5 w-5"></i>
            </button>

            <div class="flex min-w-0 items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-lime-300 text-slate-900 shadow-[0_4px_14px_rgba(163,230,53,.35)]">
                    <i data-lucide="{{ $pageIcon ?: 'layout-dashboard' }}" class="h-5 w-5"></i>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400">
                        <span>Bengkel Jomoto</span>
                        <i data-lucide="chevron-right" class="h-3 w-3"></i>
                        <span class="truncate text-slate-500">{{ $pageTitle ?: 'Dashboard' }}</span>
                    </div>
                    <h1 class="truncate text-[15px] font-bold leading-tight text-slate-900">
                        {{ $pageTitle ?: trim($__env->yieldContent('title')) }}
                    </h1>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-2">
                {{-- Role switcher --}}
                <div class="relative">
                    <button type="button" id="role-btn" onclick="toggleRoleMenu()"
                            class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 text-xs font-bold text-slate-600 shadow-sm transition hover:border-slate-300 hover:shadow">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-900 text-[10px] font-black uppercase text-lime-300">
                            {{ strtoupper(substr($roleLabels[$role] ?? 'G', 0, 2)) }}
                        </span>
                        <span class="hidden sm:inline">Switch Role</span>
                        <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400"></i>
                    </button>
                    <div id="role-menu" class="pop-in absolute right-0 z-50 mt-2 hidden w-60 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                        <div class="px-2 pb-1.5 pt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Switcher Role Demo</div>
                        @foreach($roleLabels as $key => $label)
                            <form method="POST" action="{{ route('demo.switch') }}">
                                @csrf
                                <input type="hidden" name="role" value="{{ $key }}">
                                <button type="submit"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2.5 text-left text-sm font-semibold transition
                                            {{ $role === $key ? 'bg-lime-50 text-lime-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-900 text-[10px] font-black uppercase text-lime-300">
                                        {{ strtoupper(substr($label, 0, 2)) }}
                                    </span>
                                    {{ $label }}
                                    @if($role === $key)
                                        <i data-lucide="check" class="ml-auto h-4 w-4 text-lime-500"></i>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                {{-- Notifikasi --}}
                <div class="relative">
                    <button type="button" id="notif-btn" onclick="toggleNotifMenu()"
                            aria-label="Notifikasi"
                            class="relative grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i data-lucide="bell" class="h-[18px] w-[18px]"></i>
                        <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-lime-400 ring-2 ring-white"></span>
                    </button>
                    <div id="notif-menu" class="pop-in absolute right-0 z-50 mt-2 hidden w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl shadow-slate-900/10">
                        <div class="text-sm font-bold text-slate-900">Notifikasi</div>
                        <p class="mt-2 rounded-xl bg-slate-50 px-3 py-3 text-center text-xs text-slate-500">
                            Belum ada notifikasi baru.
                        </p>
                    </div>
                </div>

                {{-- Avatar --}}
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-900 text-sm font-black text-lime-300 ring-2 ring-lime-300/40">
                    {{ $initials }}
                </span>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-lime-50/60 px-4 py-1.5 text-center text-[11px] font-semibold text-lime-700 sm:px-6">
        Demo Mode — gunakan Switch Role di kanan atas untuk pindah role (SA · Teknisi · Customer · Admin) tanpa login.
        </div>
    </header>

    {{-- Content --}}
    <main class="flex-1 px-4 py-6 sm:px-6 fade-in">
        @if(session('success'))
            <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-lime-200 bg-lime-50 px-4 py-3 text-sm font-semibold text-lime-800">
                <i data-lucide="circle-check" class="h-4 w-4 shrink-0"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                <i data-lucide="circle-alert" class="h-4 w-4 shrink-0"></i>{{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="px-6 pb-6 pt-2 text-center text-xs text-slate-400">
        Program Bengkel Jomoto · ERP-ready workshop platform · Laravel + Blade
    </footer>
</div>

{{-- Floating Action Button --}}
<a href="https://web.whatsapp.com/" target="_blank" rel="noopener"
   class="group fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-[#0f1117] py-3 pl-3 pr-4 text-sm font-bold text-white shadow-[0_10px_30px_rgba(15,17,23,.35)] ring-1 ring-white/10 transition duration-300 hover:bg-lime-300 hover:text-black hover:ring-lime-300/40 active:scale-95">
    <span class="grid h-9 w-9 place-items-center rounded-full bg-lime-300 text-black transition group-hover:bg-black group-hover:text-lime-300">
        <i data-lucide="message-circle-more" class="h-5 w-5"></i>
    </span>
    <span class="hidden sm:inline">Jomoto Assistant · WhatsApp Support</span>
    <span class="sm:hidden">Assistant</span>
</a>

<script>
    // ---- Sidebar: expand / minimize (persisted) ----
    if (localStorage.getItem('jomoto.nav') === 'collapsed') {
        document.body.classList.add('nav-collapsed');
    }

    const overlay = document.getElementById('nav-overlay');
    const setMobileNav = open => {
        document.body.classList.toggle('nav-open', open);
        overlay.classList.toggle('hidden', !open);
    };

    document.getElementById('nav-toggle').addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1023px)').matches) {
            setMobileNav(false);
            return;
        }
        document.body.classList.toggle('nav-collapsed');
        localStorage.setItem('jomoto.nav', document.body.classList.contains('nav-collapsed') ? 'collapsed' : 'expanded');
    });

    document.getElementById('mobile-nav').addEventListener('click', () => setMobileNav(true));
    overlay.addEventListener('click', () => setMobileNav(false));

    // ---- Menu search ----
    const search = document.getElementById('menu-search');
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('#nav-list [data-group]').forEach(group => {
            let groupVisible = 0;
            group.querySelectorAll('.nav-item').forEach(item => {
                const match = !q || item.dataset.label.includes(q);
                item.classList.toggle('hidden', !match);
                if (match) groupVisible++;
            });
            group.classList.toggle('hidden', groupVisible === 0);
            visible += groupVisible;
        });
        document.getElementById('menu-empty').classList.toggle('hidden', visible > 0);
    });

    // ---- Dropdowns ----
    function toggleRoleMenu(force) {
        const menu = document.getElementById('role-menu');
        const open = force === true ? true : menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !open);
        document.getElementById('notif-menu').classList.add('hidden');
        if (open && force === true) window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function toggleNotifMenu() {
        const menu = document.getElementById('notif-menu');
        menu.classList.toggle('hidden');
        document.getElementById('role-menu').classList.add('hidden');
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('#role-btn') && !e.target.closest('#role-menu') && !e.target.closest('[onclick*="toggleRoleMenu"]')) {
            document.getElementById('role-menu').classList.add('hidden');
        }
        if (!e.target.closest('#notif-btn') && !e.target.closest('#notif-menu')) {
            document.getElementById('notif-menu').classList.add('hidden');
        }
    });

    // ---- Icons ----
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
