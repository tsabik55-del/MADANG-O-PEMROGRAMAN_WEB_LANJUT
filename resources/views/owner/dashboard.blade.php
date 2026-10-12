<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Owner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="madang-hero madang-stripes madang-stripes-animated rounded-2xl p-6 sm:p-8 mb-8 text-cream shadow-lg hero-animate">
                <p class="font-term text-xs tracking-[0.25em] text-cream/70">MADANG-O // OWNER</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold mt-1">Pusat Komando Warung</h2>
                <p class="text-cream/80 text-sm mt-1">Kelola menu, pantau pesanan &amp; pengguna dalam satu dasbor.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_MENU</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['total_menus'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_PESANAN</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0v6v0" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_PELANGGAN</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['total_pelanggan'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">PENDAPATAN_LUNAS</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">Rp{{ number_format($stats['total_pendapatan'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Menu Cepat</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('owner.menus.index') }}" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <p class="text-center font-medium">Kelola Menu</p>
                        </a>
                        <a href="{{ route('owner.orders.index') }}" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-center font-medium">Kelola Pesanan</p>
                        </a>
                        <a href="#" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p class="text-center font-medium">Laporan</p>
                        </a>
                        <a href="#" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-center font-medium">Pengaturan</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>