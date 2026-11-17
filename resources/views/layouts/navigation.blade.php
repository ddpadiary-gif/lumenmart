<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-black/[0.06] bg-white/80 backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center">
                    <x-application-logo />
                </a>

                <div class="hidden items-center gap-6 md:flex">
                    <a
                        href="{{ route('home') }}#katalog"
                        class="text-xs font-medium tracking-tight text-[#1d1d1f]/80 transition hover:text-[#1d1d1f]"
                    >
                        Katalog Produk
                    </a>
                    <a
                        href="{{ route('home') }}#bundling"
                        class="text-xs font-medium tracking-tight text-[#1d1d1f]/80 transition hover:text-[#0071e3]"
                    >
                        Paket Bundling Hemat
                    </a>

                    @auth
                        @if (auth()->user()->isCustomer())
                            <a
                                href="{{ route('wishlist.index') }}"
                                class="text-xs font-medium tracking-tight {{ request()->routeIs('wishlist.*') ? 'text-[#0071e3] font-semibold' : 'text-[#1d1d1f]/80 hover:text-[#1d1d1f]' }}"
                            >
                                Favorit
                            </a>
                            <a
                                href="{{ route('orders.index') }}"
                                class="text-xs font-medium tracking-tight {{ request()->routeIs('orders.*') ? 'text-[#0071e3] font-semibold' : 'text-[#1d1d1f]/80 hover:text-[#1d1d1f]' }}"
                            >
                                Pesanan Saya
                            </a>
                        @else
                            <a
                                href="{{ route('backoffice.dashboard') }}"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#1d1d1f] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                            >
                                <span>Buka Back-Office ({{ auth()->user()->roleLabel() }})</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Actions -->
            <div class="hidden items-center gap-3 sm:flex">
                @auth
                    @if (auth()->user()->isCustomer())
                        <a
                            href="{{ route('cart.index') }}"
                            class="relative inline-flex items-center gap-2 rounded-full bg-[#f5f5f7] px-4 py-2 text-xs font-semibold text-[#1d1d1f] transition hover:bg-gray-200"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                            </svg>
                            <span>Keranjang</span>
                            <span x-show="$store.cart.count > 0" x-text="$store.cart.count" @if (($cartCount ?? 0) < 1) style="display: none" @endif class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#0071e3] px-1.5 text-[10px] font-bold text-white">{{ $cartCount ?? 0 }}</span>
                        </a>
                    @endif

                    <!-- User Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-full border border-black/[0.08] bg-white px-3.5 py-1.5 text-xs font-medium text-[#1d1d1f] transition hover:bg-[#f5f5f7]">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="rounded-full bg-[#f5f5f7] px-2 py-0.5 text-[10px] font-semibold text-[#86868b]">
                                    {{ Auth::user()->roleLabel() }}
                                </span>
                                <svg class="h-3.5 w-3.5 text-[#86868b]" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (Auth::user()->isStaff())
                                <x-dropdown-link :href="route('backoffice.dashboard')">
                                    Dashboard Back-Office
                                </x-dropdown-link>
                            @else
                                <x-dropdown-link :href="route('orders.index')">
                                    Pesanan Saya
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('wishlist.index')">
                                    Favorit Saya
                                </x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="route('profile.edit')">
                                Pengaturan Akun
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-full px-4 py-2 text-xs font-semibold text-[#1d1d1f] transition hover:bg-[#f5f5f7]"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                    >
                        Daftar Pelanggan
                    </a>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center gap-2 sm:hidden">
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('cart.index') }}" class="relative rounded-full bg-[#f5f5f7] p-2 text-[#1d1d1f]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                            </svg>
                            <span x-show="$store.cart.count > 0" x-text="$store.cart.count" @if (($cartCount ?? 0) < 1) style="display: none" @endif class="absolute -right-1 -top-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[#0071e3] px-1 text-[10px] font-bold text-white">{{ $cartCount ?? 0 }}</span>
                        </a>
                    @endif
                @endauth

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-xl p-2 text-[#1d1d1f] hover:bg-[#f5f5f7]"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-black/[0.06] bg-white px-4 pb-4 pt-3 sm:hidden">
        <div class="space-y-1.5">
            <a href="{{ route('home') }}#katalog" class="block rounded-xl px-3 py-2 text-sm font-medium text-[#1d1d1f] hover:bg-[#f5f5f7]">Katalog Produk</a>
            <a href="{{ route('home') }}#bundling" class="block rounded-xl px-3 py-2 text-sm font-medium text-[#1d1d1f] hover:bg-[#f5f5f7]">Paket Bundling Hemat</a>
            @auth
                @if (auth()->user()->isCustomer())
                    <a href="{{ route('wishlist.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-[#1d1d1f] hover:bg-[#f5f5f7]">Favorit</a>
                    <a href="{{ route('cart.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-[#1d1d1f] hover:bg-[#f5f5f7]">Keranjang (<span x-text="$store.cart.count">{{ $cartCount ?? 0 }}</span>)</a>
                    <a href="{{ route('orders.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-[#1d1d1f] hover:bg-[#f5f5f7]">Pesanan Saya</a>
                @else
                    <a href="{{ route('backoffice.dashboard') }}" class="block rounded-xl bg-[#1d1d1f] px-3 py-2 text-sm font-semibold text-white">Buka Back-Office</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-[#f5f5f7] px-3 py-2 text-left text-sm font-medium text-rose-600">Keluar</button>
                </form>
            @else
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('login') }}" class="flex-1 rounded-full bg-[#f5f5f7] py-2 text-center text-xs font-semibold text-[#1d1d1f]">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 rounded-full bg-[#1d1d1f] py-2 text-center text-xs font-semibold text-white">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
