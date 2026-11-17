<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

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
    </body>
</html>
