<nav x-data="{ open: false }" class="bg-coal border-b-4 border-brand-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @if(auth()->check())
                        @php
                            $homeRoute = match(auth()->user()->role) {
                                'owner' => 'owner.dashboard',
                                'karyawan' => 'karyawan.dashboard',
                                default => 'pelanggan.dashboard',
                            };
                        @endphp
                        <a href="{{ route($homeRoute) }}">
                            <span class="flex items-center gap-2"><x-application-logo class="block h-9 w-auto text-brand-400" /><span class="font-term text-sm font-bold tracking-[0.25em] text-cream">MADANG-O</span></span>
                        </a>
                    @else
                        <a href="{{ route('login') }}">
                            <span class="flex items-center gap-2"><x-application-logo class="block h-9 w-auto text-brand-400" /><span class="font-term text-sm font-bold tracking-[0.25em] text-cream">MADANG-O</span></span>
                        </a>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->check())
                        @php
                            $dashboardRoute = match(auth()->user()->role) {
                                'owner' => 'owner.dashboard',
                                'karyawan' => 'karyawan.dashboard',
                                default => 'pelanggan.dashboard',
                            };
                        @endphp
                        <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        @if(auth()->user()->role === 'owner')
                            <x-nav-link :href="route('owner.menus.index')" :active="request()->routeIs('owner.menus*')">
                                {{ __('Menu') }}
                            </x-nav-link>
                            <x-nav-link :href="route('owner.orders.index')" :active="request()->routeIs('owner.orders*')">
                                {{ __('Pesanan') }}
                            </x-nav-link>
                            <x-nav-link :href="route('owner.users.index')" :active="request()->routeIs('owner.users*')">
                                {{ __('Pengguna') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role === 'karyawan')
                            <x-nav-link :href="route('karyawan.orders.index')" :active="request()->routeIs('karyawan.orders*')">
                                {{ __('Pesanan') }}
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('pelanggan.orders.index')" :active="request()->routeIs('pelanggan.orders*')">
                                {{ __('Pesanan Saya') }}
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            @if(auth()->check())
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-cream/80 bg-transparent hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center gap-2">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    @if(Auth::user()->role === 'owner')
                                        bg-purple-100 text-purple-800
                                    @elseif(Auth::user()->role === 'karyawan')
                                        bg-blue-100 text-blue-800
                                    @else
                                        bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst(Auth::user()->role) }}
                                </span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @else
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <a href="{{ route('login') }}" class="text-cream/70 hover:text-white px-3 py-2 text-sm font-medium">
                    {{ __('Log in') }}
                </a>
                <a href="{{ route('register') }}" class="bg-brand-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-brand-600">
                    {{ __('Register') }}
                </a>
            </div>
            @endif

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-cream/70 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->check())
                @php
                    $dashboardRoute = match(auth()->user()->role) {
                        'owner' => 'owner.dashboard',
                        'karyawan' => 'karyawan.dashboard',
                        default => 'pelanggan.dashboard',
                    };
                @endphp
                <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                @if(auth()->user()->role === 'owner')
                    <x-responsive-nav-link :href="route('owner.menus.index')" :active="request()->routeIs('owner.menus*')">
                        {{ __('Menu') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('owner.orders.index')" :active="request()->routeIs('owner.orders*')">
                        {{ __('Pesanan') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('owner.users.index')" :active="request()->routeIs('owner.users*')">
                        {{ __('Pengguna') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->role === 'karyawan')
                    <x-responsive-nav-link :href="route('karyawan.orders.index')" :active="request()->routeIs('karyawan.orders*')">
                        {{ __('Pesanan') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('pelanggan.orders.index')" :active="request()->routeIs('pelanggan.orders*')">
                        {{ __('Pesanan Saya') }}
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/10">
            @if(auth()->check())
                <div class="px-4">
                    <div class="font-medium text-base text-cream">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-cream/60">{{ Auth::user()->email }}</div>
                    <div class="mt-1 inline-flex">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            @if(Auth::user()->role === 'owner')
                                bg-purple-100 text-purple-800
                            @elseif(Auth::user()->role === 'karyawan')
                                bg-blue-100 text-blue-800
                            @else
                                bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst(Auth::user()->role) }}
                        </span>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 space-y-2">
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-cream/70 hover:text-white">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-white bg-brand-700 rounded-md hover:bg-brand-600">
                        {{ __('Register') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</nav>