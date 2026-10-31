<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Pesan Menu' }} — Sego Sambel Mbak Tatik</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800" x-data="{ sidebarBuka: false }">

    {{-- Overlay gelap saat sidebar dibuka di layar kecil --}}
    <div x-show="sidebarBuka" x-cloak x-transition.opacity
         @click="sidebarBuka = false"
         class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

    {{-- 1. SIDEBAR KIRI --}}
    <aside :class="sidebarBuka ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col justify-between bg-gradient-to-b from-brand-800 via-brand-900 to-brand-950 p-4 text-white shadow-2xl transition-transform duration-300 lg:translate-x-0">

        <div>
            {{-- Logo & branding --}}
            <div class="mb-6 flex items-center space-x-3">
                <img src="{{ asset('image/logo.jpeg') }}" alt="Logo Mbak Tatik"
                     class="h-12 w-12 shrink-0 rounded-full border-2 border-amber-400 object-cover shadow-lg">
                <div class="min-w-0">
                    <h1 class="text-base font-black leading-tight tracking-wide text-amber-300">SEGO SAMBEL</h1>
                    <p class="text-xs font-bold text-white">Mbak Tatik</p>
                    <span class="text-[9px] bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full border border-amber-400/30 font-medium">Pedas Gurih Mantap</span>
                </div>
            </div>

            {{-- Navigasi --}}
            <nav class="space-y-1.5">
                <a href="{{ route('pelanggan.dashboard') }}"
                   class="w-full flex items-center space-x-2.5 px-3.5 py-2.5 rounded-xl text-xs transition
                          {{ request()->routeIs('pelanggan.dashboard')
                                ? 'bg-brand-700/80 border-l-4 border-amber-400 text-white font-bold shadow-md'
                                : 'text-red-100 hover:bg-brand-800/50 border-l-4 border-transparent' }}">
                    <span class="text-amber-400 font-bold">🍴</span>
                    <span>Menu &amp; Pesan</span>
                </a>

                <a href="{{ route('pelanggan.orders.index') }}"
                   class="w-full flex items-center space-x-2.5 px-3.5 py-2.5 rounded-xl text-xs transition
                          {{ request()->routeIs('pelanggan.orders.*')
                                ? 'bg-brand-700/80 border-l-4 border-amber-400 text-white font-bold shadow-md'
                                : 'text-red-100 hover:bg-brand-800/50 border-l-4 border-transparent' }}">
                    <span class="text-amber-400 font-bold">🔄</span>
                    <span>Riwayat Pesanan</span>
                </a>
            </nav>
        </div>

        {{-- Profil & keluar --}}
        <div class="space-y-2 border-t border-brand-700/60 pt-3" x-data="{ keluar: false }">
            <div class="flex items-center space-x-2.5 rounded-xl border border-brand-700/40 bg-brand-950/40 p-2">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-amber-500 text-xs font-black text-amber-950 shadow">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'P', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="truncate text-xs font-bold text-white">{{ auth()->user()->name }}</h4>
                    <p class="truncate text-[10px] text-red-200">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <button type="button" @click="keluar = true"
                    class="flex w-full items-center justify-center space-x-1.5 rounded-xl border border-amber-500/30 bg-brand-950 py-2 text-xs font-bold text-amber-200 transition hover:bg-brand-900">
                <span>↪</span>
                <span>Keluar / Ganti Akun</span>
            </button>

            {{-- Modal konfirmasi keluar --}}
            <template x-if="keluar">
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                    <div class="w-full max-w-xs space-y-3 rounded-2xl bg-white p-5 text-center shadow-2xl">
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-red-100 text-2xl font-bold text-brand-800 shadow-inner">🚪</div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800">Konfirmasi Keluar</h3>
                            <p class="mt-1 text-xs text-gray-500">Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk memesan.</p>
                        </div>
                        <div class="flex justify-center space-x-2 pt-2">
                            <button type="button" @click="keluar = false"
                                    class="w-full rounded-xl bg-gray-100 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-200">
                                Batal
                            </button>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-brand-800 py-2 text-xs font-bold text-white shadow transition hover:bg-brand-700">
                                    Ya, Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </aside>

    {{-- 2. KONTEN UTAMA --}}
    <main class="min-h-screen min-w-0 bg-gray-50/50 p-4 sm:p-6 lg:ml-64">
        {{-- Tombol buka sidebar di layar kecil, tersedia di semua halaman --}}
        <button type="button" @click="sidebarBuka = true"
                class="mb-3 grid h-9 w-9 place-items-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm lg:hidden"
                aria-label="Buka menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
