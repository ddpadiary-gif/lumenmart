<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Back-Office' }} — LUMEN MART</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#f5f5f7] font-sans text-[#1d1d1f] antialiased">
        @php
            $user = auth()->user();
            $criticalList = $criticalProducts ?? collect();
            $criticalTotal = $criticalList->count();
        @endphp

        <div class="flex min-h-screen">
            <!-- Mobile Sidebar Backdrop -->
            <div
                x-show="sidebarOpen"
                x-cloak
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden"
            ></div>

            <!-- Dark Apple-Inspired Sidebar (#1d1d1f) -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 flex w-68 shrink-0 flex-col bg-[#1d1d1f] text-white transition-transform duration-200 lg:static"
            >
                <!-- Brand -->
                <div class="flex h-16 items-center justify-between border-b border-white/10 px-6">
                    <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-[#0071e3] text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold tracking-tight text-white">LUMEN MART</p>
                            <p class="text-[10px] font-medium uppercase tracking-widest text-white/50">Intelligent Retail</p>
                        </div>
                    </a>
                </div>

                <!-- Menu Navigation -->
                <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6 text-xs">
                    <div>
                        <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/40">Ringkasan &amp; Analitik</p>
                        <div class="mt-2 space-y-1">
                            <a
                                href="{{ route('backoffice.dashboard') }}"
                                class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.dashboard') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                <span>Dashboard Eksekutif</span>
                            </a>

                            <a
                                href="{{ route('backoffice.regression.index') }}"
                                class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.regression.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>
                                <span>Prediksi Regresi Linear</span>
                            </a>

                            <a
                                href="{{ route('backoffice.apriori.index') }}"
                                class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.apriori.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                </svg>
                                <span>Bundling Apriori</span>
                            </a>

                            <a
                                href="{{ route('backoffice.reports.index') }}"
                                class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.reports.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Laporan (CSV / PDF)</span>
                            </a>
                        </div>
                    </div>

                    @if ($user->isAdmin())
                        <div>
                            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/40">Operasional &amp; Master Data</p>
                            <div class="mt-2 space-y-1">
                                <a
                                    href="{{ route('backoffice.pos.index') }}"
                                    class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.pos.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" />
                                    </svg>
                                    <span>POS Kasir Multi-Item</span>
                                </a>

                                <a
                                    href="{{ route('backoffice.orders.index') }}"
                                    class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.orders.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                    </svg>
                                    <span>Pesanan E-Commerce</span>
                                </a>

                                <a
                                    href="{{ route('backoffice.incoming.index') }}"
                                    class="flex items-center justify-between rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.incoming.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <span class="flex items-center gap-3">
                                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                        <span>Barang Masuk (Restock)</span>
                                    </span>
                                    @if ($criticalTotal > 0)
                                        <span class="rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-[#1d1d1f]">
                                            {{ $criticalTotal }}
                                        </span>
                                    @endif
                                </a>

                                <a
                                    href="{{ route('backoffice.products.index') }}"
                                    class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.products.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                    <span>Katalog Produk (30+)</span>
                                </a>

                                <a
                                    href="{{ route('backoffice.categories.index') }}"
                                    class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 font-medium transition {{ request()->routeIs('backoffice.categories.*') ? 'bg-[#0071e3] text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    </svg>
                                    <span>Kategori Produk</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl bg-white/5 p-3.5 text-[11px] leading-relaxed text-white/70">
                            <p class="font-semibold text-white">Mode Pemilik (Read-Only)</p>
                            <p class="mt-1">Anda dapat memantau seluruh metrik analitik, prediksi Regresi Linear, paket Apriori, dan mengekspor laporan CSV/PDF.</p>
                        </div>
                    @endif
                </nav>

                <!-- Bottom Storefront Link -->
                <div class="border-t border-white/10 p-4">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-white/20"
                    >
                        <span>Lihat Storefront Toko</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Top Navbar with Critical Stock Bell -->
                <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-black/[0.06] bg-white/85 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="rounded-xl p-2 text-[#1d1d1f] hover:bg-[#f5f5f7] lg:hidden"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-sm font-bold tracking-tight text-[#1d1d1f] sm:text-base">
                                {{ $heading ?? 'Back-Office LUMEN MART' }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Lonceng Notifikasi Stok Kritis Otomatis -->
                        <div x-data="{ bellOpen: false }" class="relative">
                            <button
                                @click="bellOpen = !bellOpen"
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#f5f5f7] text-[#1d1d1f] transition hover:bg-gray-200"
                                title="Notifikasi Stok Kritis"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($criticalTotal > 0)
                                    <span class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                        {{ $criticalTotal }}
                                    </span>
                                @endif
                            </button>

                            <div
                                x-show="bellOpen"
                                x-cloak
                                @click.outside="bellOpen = false"
                                class="absolute right-0 mt-2 w-80 overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-black/[0.08] sm:w-96"
                            >
                                <div class="flex items-center justify-between border-b border-black/[0.06] bg-[#1d1d1f] px-5 py-3.5 text-white">
                                    <div>
                                        <p class="text-xs font-bold">Peringatan Stok Kritis</p>
                                        <p class="text-[11px] text-white/70">Produk dengan stok &le; batas minimum</p>
                                    </div>
                                    <span class="rounded-full bg-rose-500 px-2.5 py-0.5 text-[11px] font-bold">
                                        {{ $criticalTotal }} produk
                                    </span>
                                </div>

                                <div class="max-h-80 divide-y divide-black/[0.05] overflow-y-auto">
                                    @forelse ($criticalList as $cp)
                                        <div class="flex items-center justify-between gap-3 px-5 py-3 text-xs">
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-[#1d1d1f]">{{ $cp->name }}</p>
                                                <p class="text-[11px] text-[#86868b]">
                                                    Sisa <span class="font-bold text-rose-600">{{ $cp->current_stock }} {{ $cp->unit }}</span>
                                                    (min {{ $cp->min_stock_threshold }})
                                                </p>
                                            </div>
                                            @if ($user->isAdmin())
                                                <a
                                                    href="{{ route('backoffice.incoming.index', ['product_id' => $cp->id]) }}"
                                                    class="shrink-0 rounded-full bg-[#0071e3] px-3 py-1 text-[11px] font-semibold text-white hover:bg-blue-700"
                                                >
                                                    Restock
                                                </a>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="px-5 py-6 text-center text-xs text-[#86868b]">
                                            Seluruh stok produk berada di atas batas minimum.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- User Info & Logout -->
                        <div class="flex items-center gap-2 rounded-full bg-[#f5f5f7] py-1 pl-3 pr-1.5 text-xs">
                            <span class="hidden font-semibold text-[#1d1d1f] sm:inline">{{ $user->name }}</span>
                            <span class="rounded-full bg-[#1d1d1f] px-2.5 py-0.5 text-[10px] font-semibold text-white">
                                {{ $user->roleLabel() }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="rounded-full px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50"
                                >
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <!-- Flash Alerts -->
                @if (session('status') || session('error') || $errors->any())
                    <div class="mx-auto mt-5 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        @if (session('status'))
                            <div class="rounded-2xl bg-[#1d1d1f] px-5 py-3.5 text-xs font-medium text-white shadow-sm">
                                {{ session('status') }}
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

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
