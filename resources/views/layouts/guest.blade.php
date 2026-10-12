<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MADANG-O') }} — Masuk</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-cream flex items-center justify-center p-4 sm:p-8">
            <div class="w-full max-w-4xl grid md:grid-cols-2 overflow-hidden rounded-2xl shadow-2xl">
                <!-- Panel merek: full-bleed sambal -->
                <div class="madang-hero madang-stripes hidden md:flex flex-col justify-between p-8 text-cream">
                    <div>
                        <a href="/" class="flex items-center gap-3">
                            <x-application-logo class="w-11 h-11 text-cream" />
                            <span class="font-term text-lg font-bold tracking-[0.25em]">MADANG-O</span>
                        </a>
                        <p class="font-term text-xs tracking-[0.25em] text-cream/70 mt-8">SEGO SAMBEL MBAK TATIK</p>
                        <h1 class="text-3xl font-extrabold leading-tight mt-2">Pesan sego sambel<br>praktis, tanpa<br>antre di loket.</h1>
                    </div>
                    <ul class="space-y-3.5 text-sm mt-10">
                        <li class="flex items-start gap-3">
                            <span class="text-xl">🍚</span>
                            <span class="text-cream/85">Menu harian &amp; paket katering lengkap dengan harga.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-xl">🛒</span>
                            <span class="text-cream/85">Masukkan keranjang, atur jadwal ambil, konfirmasi.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-xl">💵</span>
                            <span class="text-cream/85">Bayar tunai, transfer, atau QRIS saat taking.</span>
                        </li>
                    </ul>
                </div>

                <!-- Panel formulir -->
                <div class="bg-paper p-6 sm:p-10">
                    <div class="md:hidden flex items-center gap-2 mb-6">
                        <a href="/" class="flex items-center gap-2">
                            <x-application-logo class="w-9 h-9 text-brand-700" />
                            <span class="font-term text-base font-bold tracking-[0.25em] text-coal">MADANG-O</span>
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
