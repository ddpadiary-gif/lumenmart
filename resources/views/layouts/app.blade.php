<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="cart-count" content="{{ $cartCount ?? 0 }}">
        @auth
            @if (auth()->user()->isCustomer())
                <meta name="cart-url" content="{{ route('cart.index') }}">
                <meta name="checkout-url" content="{{ route('checkout.index') }}">
            @endif
        @endauth

        <title>{{ $title ?? 'LUMEN MART — Smart Daily Essentials, Intelligently Stocked' }}</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f5f7] font-sans text-[#1d1d1f] antialiased selection:bg-[#0071e3] selection:text-white">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            <!-- Flash Messages -->
            @if (session('status') || session('error') || $errors->any())
                <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="flex items-center justify-between rounded-2xl bg-[#1d1d1f] px-5 py-3.5 text-xs font-medium text-white shadow-sm">
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mt-2 rounded-2xl bg-rose-600 px-5 py-3.5 text-xs font-medium text-white shadow-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mt-2 rounded-2xl bg-rose-50 px-5 py-3.5 text-xs text-rose-700 ring-1 ring-rose-200">
                            <ul class="list-inside list-disc space-y-0.5">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @isset($header)
                <header class="border-b border-black/[0.05] bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Apple-Inspired Minimalist Footer -->
            <footer class="mt-20 border-t border-black/[0.06] bg-white">
                <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div class="grid gap-8 md:grid-cols-3">
                        <div>
                            <x-application-logo />
                            <p class="mt-3 max-w-sm text-xs leading-relaxed text-[#86868b]">
                                Smart Daily Essentials, Intelligently Stocked. Sistem Informasi Inventory &amp; E-Commerce UMKM Ritel Cerdas berbasis Laravel 13 dengan integrasi Regresi Linear (OLS) dan Algoritma Apriori.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-[#1d1d1f]">Navigasi Cepat</h4>
                            <ul class="mt-3 space-y-2 text-xs text-[#86868b]">
                                <li><a href="{{ route('home') }}#katalog" class="hover:text-[#1d1d1f]">Katalog 30 Produk</a></li>
                                <li><a href="{{ route('home') }}#bundling" class="hover:text-[#1d1d1f]">Smart Bundle Apriori</a></li>
                                @auth
                                    @if (auth()->user()->isCustomer())
                                        <li><a href="{{ route('cart.index') }}" class="hover:text-[#1d1d1f]">Keranjang Belanja</a></li>
                                        <li><a href="{{ route('orders.index') }}" class="hover:text-[#1d1d1f]">Lacak Pesanan</a></li>
                                    @else
                                        <li><a href="{{ route('backoffice.dashboard') }}" class="hover:text-[#1d1d1f]">Dashboard Eksekutif</a></li>
                                    @endif
                                @else
                                    <li><a href="{{ route('login') }}" class="hover:text-[#1d1d1f]">Masuk Akun</a></li>
                                @endauth
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-[#1d1d1f]">Akun Demo Siap Pakai</h4>
                            <div class="mt-3 space-y-1.5 text-xs text-[#86868b]">
                                <p><span class="font-semibold text-[#1d1d1f]">Admin:</span> admin@lumenmart.test / password</p>
                                <p><span class="font-semibold text-[#1d1d1f]">Pemilik:</span> pemilik@lumenmart.test / password</p>
                                <p><span class="font-semibold text-[#1d1d1f]">Pelanggan:</span> pelanggan@lumenmart.test / password</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-black/[0.06] pt-6 text-xs text-[#86868b] sm:flex-row">
                        <p>&copy; {{ now()->year }} LUMEN MART. Powered by Laravel 13 &amp; Pure PHP Analytics Engine.</p>
                        <p>Regresi Linear (OLS 80:20) &bull; Market Basket Analysis (Apriori)</p>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Apple-Style Floating Pill Toast Notification (global, dipicu event window "notify") -->
        <div
            x-data="{
                toasts: [],
                push(detail) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, ...detail });
                    setTimeout(() => this.remove(id), 4500);
                },
                remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
                act(t) {
                    if (t.action?.drawer) { $store.cart.open(); }
                    else if (t.action?.url) { window.location.href = t.action.url; }
                    this.remove(t.id);
                }
            }"
            @notify.window="push($event.detail)"
            class="pointer-events-none fixed inset-x-4 bottom-6 z-[70] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6"
            aria-live="polite"
        >
            <template x-for="t in toasts" :key="t.id">
                <div class="lm-toast pointer-events-auto flex max-w-md items-center gap-3 rounded-full bg-[#1d1d1f]/90 py-2.5 pl-3 pr-2.5 text-xs font-medium text-white shadow-[0_18px_50px_rgba(0,0,0,0.28)] ring-1 ring-white/10 backdrop-blur-xl">
                    <span
                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[13px] font-bold"
                        :class="{
                            'bg-emerald-500 text-white': t.type === 'success',
                            'bg-rose-500 text-white': t.type === 'error' || t.type === 'wishlist',
                            'bg-white/15 text-white': t.type === 'info'
                        }"
                        x-text="t.type === 'error' ? '!' : (t.type === 'wishlist' ? '♥' : (t.type === 'info' ? 'i' : '✓'))"
                    ></span>
                    <span class="line-clamp-2 pr-1" x-text="t.message.replace(/^✓\s*/, '')"></span>
                    <template x-if="t.action">
                        <button type="button" @click="act(t)" class="shrink-0 rounded-full bg-white px-3.5 py-1.5 text-[11px] font-semibold text-[#1d1d1f] transition hover:bg-[#f5f5f7]" x-text="t.action.label"></button>
                    </template>
                    <button type="button" @click="remove(t.id)" class="shrink-0 rounded-full p-1.5 text-white/60 transition hover:bg-white/10 hover:text-white" aria-label="Tutup">&#10005;</button>
                </div>
            </template>
        </div>

        @auth
            @if (auth()->user()->isCustomer())
                <!-- Slide-Over Mini Cart Drawer -->
                <div x-data x-show="$store.cart.drawer" x-cloak class="fixed inset-0 z-[60]" @keydown.escape.window="$store.cart.drawer = false">
                    <div
                        x-show="$store.cart.drawer"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="$store.cart.drawer = false"
                        class="absolute inset-0 bg-black/30 backdrop-blur-sm"
                    ></div>

                    <aside
                        x-show="$store.cart.drawer"
                        x-transition:enter="transform transition ease-out duration-300"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl"
                    >
                        <div class="flex items-center justify-between border-b border-black/[0.06] px-6 py-5">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-widest text-[#0071e3]">Mini Cart</p>
                                <h3 class="text-lg font-extrabold tracking-tight text-[#1d1d1f]">Keranjang Anda (<span x-text="$store.cart.count"></span>)</h3>
                            </div>
                            <button type="button" @click="$store.cart.drawer = false" class="rounded-full bg-[#f5f5f7] p-2 text-[#1d1d1f] hover:bg-gray-200" aria-label="Tutup">&#10005;</button>
                        </div>

                        <div class="flex-1 space-y-3 overflow-y-auto px-6 py-5">
                            <template x-for="line in ($store.cart.summary?.lines || [])" :key="line.id">
                                <div class="flex items-center gap-3 rounded-2xl bg-[#f5f5f7] p-3">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-white">
                                        <template x-if="line.image">
                                            <img :src="line.image" :alt="line.name" class="h-full w-full object-cover">
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-[#1d1d1f]" x-text="line.name"></p>
                                        <p class="text-[11px] text-[#86868b]"><span x-text="line.quantity"></span> &times; <span x-text="lumen.rupiah(line.price)"></span></p>
                                    </div>
                                    <p class="text-xs font-bold text-[#1d1d1f]" x-text="lumen.rupiah(line.subtotal)"></p>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-2 border-t border-black/[0.06] px-6 py-5 text-xs">
                            <div class="flex justify-between text-[#86868b]"><span>Subtotal</span><span x-text="lumen.rupiah($store.cart.summary?.subtotal)"></span></div>
                            <div class="flex justify-between text-emerald-600" x-show="($store.cart.summary?.discount || 0) > 0">
                                <span>Smart Bundle Discount</span><span x-text="'− ' + lumen.rupiah($store.cart.summary?.discount)"></span>
                            </div>
                            <div class="flex justify-between pt-1 text-base font-extrabold text-[#1d1d1f]"><span>Total</span><span x-text="lumen.rupiah($store.cart.summary?.total)"></span></div>
                            <div class="grid grid-cols-2 gap-2 pt-3">
                                <a :href="$store.cart.url" class="rounded-full bg-[#f5f5f7] py-3 text-center font-semibold text-[#1d1d1f] hover:bg-gray-200">Buka Keranjang</a>
                                <a :href="$store.cart.checkoutUrl" class="rounded-full bg-[#0071e3] py-3 text-center font-semibold text-white hover:bg-[#1d1d1f]">Checkout</a>
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        @endauth
    </body>
</html>
