<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'MADANG-O') }} — Sego Sambel Mbak Tatik</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Tandai JS aktif agar animasi reveal tidak menyembunyikan konten tanpa JS --}}
        <script>document.documentElement.classList.add('js-reveal');</script>
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased">
        <!-- Bilah atas -->
        <header class="bg-red-900 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-9 h-9 text-amber-400" />
                    <span class="font-term text-base font-bold tracking-[0.25em]">MADANG-O</span>
                </a>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-red-950 rounded-md text-sm font-semibold transition">Dasbor</a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-white/70 hover:text-white transition">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-red-950 rounded-md text-sm font-semibold transition">Daftar</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Hero -->
        <section class="bg-gradient-to-br from-red-800 via-red-900 to-red-950 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-24 hero-animate">
                <div class="max-w-3xl">
                    <p class="font-term text-xs tracking-[0.3em] text-amber-300">SEGO SAMBEL MBAK TATIK · JOMBANG</p>
                    <h1 class="text-4xl sm:text-6xl font-extrabold leading-[1.05] mt-4">Lapar?<br>Pesan sego sambel<br>semakin gampang.</h1>
                    <p class="mt-5 text-white/80 max-w-md">Katalog harian &amp; paket katering — nasi sambel ayam, lele, tahu, tempe, telor, nasi kotak, sampai tumpeng. Bayar tunai, transfer, atau QRIS.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-3 bg-white text-red-900 font-bold rounded-md hover:bg-amber-50 transition">Buka Dasbor</a>
                        @else
                            <a href="{{ route('register') }}" class="px-6 py-3 bg-white text-red-900 font-bold rounded-md hover:bg-amber-50 transition">Pesan Sekarang</a>
                            <a href="{{ route('login') }}" class="px-6 py-3 border border-white/40 text-white font-semibold rounded-md hover:bg-white/10 transition">Masuk</a>
                        @endauth
                    </div>
                    <p class="font-term text-xs text-white/60 mt-6">v{{ app()->version() }} · Laravel 13 · Tailwind</p>
                </div>
            </div>
        </section>

        <!-- Fitur bernomor -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-14">
            <p class="font-term text-xs tracking-[0.3em] text-red-700"># FITUR_UTAMA</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold mt-2">Satu aplikasi, tiga peran.</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-8 reveal-stagger">
                @php
                    $fitur = [
                        ['01', 'Katalog & Harga', '7 menu harian dan katering dengan harga jelas. Pilih, tambah ke keranjang.'],
                        ['02', 'Keranjang & Checkout', 'Atur jumlah, jadwal ambil, catatan — lalu konfirmasi pesanan.'],
                        ['03', 'Status Pesanan', 'Alur 6 tahap: menunggu pembayaran → diproses → siap → selesai. Batalkan sebelum diproses.'],
                        ['04', 'Pembayaran Fleksibel', 'Tunai, transfer, atau QRIS — tercatat rapi per pesanan.'],
                        ['05', 'Kelola Pengguna', 'Owner mengatur akun pelanggan, karyawan, dan owner.'],
                        ['06', 'Rekap & Laporan', 'Total pesanan dan pendapatan terpantau di dasbor.'],
                    ];
                @endphp
                @foreach($fitur as [$no, $judul, $deskripsi])
                    <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover-lift hover:border-red-600 hover:shadow-[6px_6px_0_0_#B91C1C] transition">
                        <p class="madang-index text-sm text-red-700">#{{ $no }}</p>
                        <h3 class="font-bold text-lg mt-1">{{ $judul }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $deskripsi }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Pratinjau menu -->
        <section class="bg-red-950 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14">
                <p class="font-term text-xs tracking-[0.3em] text-amber-400"># PRATINJAU_MENU</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold mt-2">Favorit warung.</h2>
                <div class="grid sm:grid-cols-3 gap-5 mt-8 reveal-stagger">
                    @php
                        $menu = [
                            ['Nasi Sambel Ayam', 'Rp15.000', '🍗'],
                            ['Nasi Kotak', 'Rp18.000', '🍱'],
                            ['Tumpeng', 'Rp250.000', '🎉'],
                        ];
                    @endphp
                    @foreach($menu as [$nama, $harga, $emoji])
                        <div class="bg-white/5 border border-white/15 rounded-xl p-6 text-center hover-lift hover:border-amber-400 transition">
                            <p class="text-5xl">{{ $emoji }}</p>
                            <h3 class="font-bold mt-3">{{ $nama }}</h3>
                            <p class="font-term text-amber-400 mt-1">{{ $harga }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-block px-6 py-3 bg-amber-500 hover:bg-amber-400 text-red-950 font-bold rounded-md transition">Lihat Katalog Lengkap</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-amber-500 hover:bg-amber-400 text-red-950 font-bold rounded-md transition">Daftar untuk Memesan</a>
                    @endauth
                </div>
            </div>
        </section>

        <footer class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="font-term text-xs tracking-[0.25em] text-gray-500">MADANG-O · SEGO SAMBEL MBAK TATIK</p>
            <p class="text-xs text-gray-400">Dibuat dengan Laravel · Tugas Pemrograman Web Lanjut</p>
        </footer>
    <script>
            // Animasi muncul saat scroll
            (function () {
                const els = document.querySelectorAll('.reveal, .reveal-stagger');
                if (!els.length) return;
                if (!('IntersectionObserver' in window)) {
                    els.forEach(el => el.classList.add('is-visible'));
                    return;
                }
                const io = new IntersectionObserver((entries) => {
                    entries.forEach(e => {
                        if (e.isIntersecting) {
                            e.target.classList.add('is-visible');
                            io.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
                els.forEach(el => io.observe(el));
                // Jaring pengaman: pastikan semua konten muncul maksimal 2,5 detik
                setTimeout(() => els.forEach(el => el.classList.add('is-visible')), 2500);
            })();
        </script>
    </body>
</html>
