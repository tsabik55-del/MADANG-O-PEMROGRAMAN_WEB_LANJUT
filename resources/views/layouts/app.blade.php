<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MADANG-O') }} — Sego Sambel Mbak Tatik</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Tandai bahwa JS aktif, agar animasi reveal tidak menyembunyikan konten tanpa JS --}}
        <script>document.documentElement.classList.add('js-reveal');</script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-cream">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-paper shadow border-b-4 border-brand-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')

        {{-- Animasi muncul saat scroll (seluruh halaman) --}}
        <script>
            (function () {
                const els = document.querySelectorAll('.reveal, .reveal-stagger');
                if (!els.length) return;
                const showAll = () => els.forEach(el => el.classList.add('is-visible'));
                if (!('IntersectionObserver' in window)) { showAll(); return; }
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
                setTimeout(showAll, 2500);
            })();
        </script>
    </body>
</html>
