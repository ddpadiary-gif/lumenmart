<x-app-layout>
    <div
        x-data="{
            q: @js($search ?? ''),
            category: @js($activeCategory ?? 'all'),
            onlyFast: false,
            quickProduct: null,
            qty: 1,
            wishBusy: false,
            catalog: @js($catalog),
            matches(id, name, sku, catSlug, isFast) {
                if (this.category !== 'all' && catSlug !== this.category) return false;
                if (this.onlyFast && !isFast) return false;
                if (!this.q) return true;
                const term = this.q.toLowerCase();
                return name.toLowerCase().includes(term) || sku.toLowerCase().includes(term);
            },
            openQuickView(id) {
                this.quickProduct = this.catalog.find(p => p.id === id) || null;
                this.qty = 1;
            },
            stockPercent(p) {
                const ref = Math.max((p.min_stock || 5) * 4, p.stock, 1);
                return Math.max(4, Math.min(100, Math.round(p.stock / ref * 100)));
            },
            stepQty(delta) {
                if (!this.quickProduct) return;
                this.qty = Math.min(Math.max(1, this.qty + delta), Math.max(1, Math.min(99, this.quickProduct.stock)));
            },
            async quickAdd(form) {
                const ok = await lumen.addToCart(form, this.qty);
                if (ok) this.quickProduct = null;
            },
            async quickWish(form) {
                if (this.wishBusy || !this.quickProduct) return;
                this.wishBusy = true;
                await lumen.toggleWishlist(form, this.quickProduct.id, this.quickProduct.name);
                this.wishBusy = false;
            },
            syncWish(detail) {
                const item = this.catalog.find(p => p.id === detail.id);
                if (item) item.wishlisted = detail.wishlisted;
            }
        }"
        @wishlist-updated.window="syncWish($event.detail)"
    >
        <!-- Apple-Inspired Full-Width Minimal Hero -->
        <section class="border-b border-black/[0.05] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
                <span class="inline-flex items-center gap-2 rounded-full bg-[#f5f5f7] px-4 py-1.5 text-xs font-semibold tracking-tight text-[#1d1d1f]">
                    <span class="h-2 w-2 rounded-full bg-[#0071e3]"></span>
                    <span>LUMEN MART &mdash; Smart Daily Essentials, Intelligently Stocked</span>
                </span>

                <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-extrabold tracking-tight text-[#1d1d1f] sm:text-6xl lg:leading-[1.08]">
                    Belanja Kebutuhan Harian.<br class="hidden sm:inline" />
                    <span class="text-[#86868b]">Lebih Cerdas, Selalu Tersedia.</span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-[#86868b] sm:text-lg">
                    Katalog kurasi 30 produk kebutuhan pokok rumah tangga dengan stok yang dijaga oleh prediksi
                    <strong class="font-semibold text-[#1d1d1f]">Regresi Linear</strong> dan rekomendasi hemat otomatis dari
                    <strong class="font-semibold text-[#1d1d1f]">Algoritma Apriori</strong>.
                </p>

                <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                    <a
                        href="#katalog"
                        class="inline-flex items-center gap-2 rounded-full bg-[#1d1d1f] px-7 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0071e3]"
                    >
                        <span>Jelajahi Katalog</span>
                        <span>&darr;</span>
                    </a>
                    <a
                        href="#bundling"
                        class="inline-flex items-center gap-2 rounded-full bg-[#0071e3] px-7 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                    >
                        <span>Lihat Paket Bundling Hemat</span>
                    </a>
                </div>

                <!-- 3 Pillars Strip -->
                <div class="mx-auto mt-14 grid max-w-4xl grid-cols-1 gap-4 text-left sm:grid-cols-3">
                    <div class="rounded-3xl bg-[#f5f5f7] p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#0071e3]">Smart Bundle Discount</p>
                        <p class="mt-1 text-sm font-semibold text-[#1d1d1f]">Diskon Otomatis di Keranjang</p>
                        <p class="mt-1 text-xs text-[#86868b]">Algoritma Apriori mendeteksi pasangan produk favorit dan memberi potongan hingga 10%.</p>
                    </div>
                    <div class="rounded-3xl bg-[#f5f5f7] p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#0071e3]">Intelligently Stocked</p>
                        <p class="mt-1 text-sm font-semibold text-[#1d1d1f]">Prediksi Permintaan OLS</p>
                        <p class="mt-1 text-xs text-[#86868b]">Stok produk fast-moving diprediksi setiap bulan agar kebutuhan pokok Anda tidak pernah kosong.</p>
                    </div>
                    <div class="rounded-3xl bg-[#f5f5f7] p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#0071e3]">Multi-Metode Bayar</p>
                        <p class="mt-1 text-sm font-semibold text-[#1d1d1f]">COD, Transfer Bank &amp; QRIS</p>
                        <p class="mt-1 text-xs text-[#86868b]">Dilengkapi pelacakan status pesanan real-time dan unduh Invoice PDF resmi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Smart Bundle Apriori Section -->
        @if (count($bundles) > 0)
            <section id="bundling" class="mx-auto max-w-7xl scroll-mt-20 px-4 pt-16 sm:px-6 lg:px-8">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Market Basket Analysis &bull; Apriori</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-[#1d1d1f] sm:text-3xl">
                            Paket Bundling Hemat Pilihan Pelanggan
                        </h2>
                        <p class="mt-1 text-sm text-[#86868b]">
                            Pasangan produk dengan asosiasi kuat (Lift Ratio &gt; 1.0). Masukkan ke keranjang untuk menikmati diskon otomatis.
                        </p>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($bundles as $bundle)
                        @php
                            $rule = $bundle['rule'];
                            $items = $bundle['items'];
                        @endphp
                        <div class="flex flex-col justify-between rounded-3xl bg-white p-6 shadow-[0_4px_24px_rgba(0,0,0,0.04)] ring-1 ring-black/[0.05] transition duration-300 hover:scale-[1.01]">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-full bg-[#0071e3]/10 px-3 py-1 text-xs font-bold text-[#0071e3]">
                                        Hemat {{ $rule->bundle_discount_percent }}%
                                    </span>
                                    <span class="text-[11px] font-medium text-[#86868b]">
                                        Lift {{ number_format($rule->lift_ratio, 2) }}x &bull; Conf {{ number_format($rule->confidence, 0) }}%
                                    </span>
                                </div>

                                <h3 class="mt-4 text-base font-bold tracking-tight text-[#1d1d1f]">
                                    {{ $items->pluck('name')->join(' + ') }}
                                </h3>

                                <div class="mt-4 space-y-2">
                                    @foreach ($items as $item)
                                        <div class="flex items-center justify-between rounded-2xl bg-[#f5f5f7] px-3.5 py-2 text-xs">
                                            <span class="truncate font-medium text-[#1d1d1f]">1x {{ $item->name }}</span>
                                            <span class="shrink-0 font-semibold text-[#86868b]">{{ rupiah($item->selling_price) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-between border-t border-black/[0.06] pt-4">
                                <div>
                                    <p class="text-xs text-[#86868b] line-through">{{ rupiah($bundle['normal']) }}</p>
                                    <p class="text-lg font-extrabold tracking-tight text-[#1d1d1f]">{{ rupiah($bundle['price']) }}</p>
                                </div>

                                @auth
                                    @if (auth()->user()->isCustomer())
                                        <form action="{{ route('cart.bundle', $rule) }}" method="POST" @submit.prevent="lumen.addToCart($el)">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="rounded-full bg-[#0071e3] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#1d1d1f]"
                                            >
                                                + Ambil Paket
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="rounded-full bg-[#1d1d1f] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                                    >
                                        Masuk &amp; Ambil
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Katalog Produk Section -->
        <section id="katalog" class="mx-auto max-w-7xl scroll-mt-20 px-4 pt-16 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#86868b]">Katalog Lengkap</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-[#1d1d1f] sm:text-3xl">
                        Kebutuhan Pokok &amp; Rumah Tangga
                    </h2>
                </div>

                <!-- Pencarian Instan -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-72">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#86868b]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input
                            x-model="q"
                            type="search"
                            placeholder="Cari nama produk atau SKU..."
                            class="w-full rounded-full border-0 bg-white py-2.5 pl-10 pr-4 text-xs text-[#1d1d1f] shadow-sm ring-1 ring-black/[0.08] placeholder:text-[#86868b] focus:ring-2 focus:ring-[#0071e3]"
                        />
                    </div>

                    <button
                        type="button"
                        @click="onlyFast = !onlyFast"
                        :class="onlyFast ? 'bg-[#1d1d1f] text-white' : 'bg-white text-[#1d1d1f] ring-1 ring-black/[0.08]'"
                        class="rounded-full px-4 py-2.5 text-xs font-semibold transition"
                    >
                        Terlaris (Fast-Moving)
                    </button>
                </div>
            </div>

            <!-- Filter Kategori Pill -->
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="category = 'all'"
                    :class="category === 'all' ? 'bg-[#1d1d1f] text-white' : 'bg-white text-[#1d1d1f] hover:bg-gray-100'"
                    class="rounded-full px-4 py-2 text-xs font-semibold shadow-sm ring-1 ring-black/[0.06] transition"
                >
                    Semua Kategori ({{ $products->count() }})
                </button>
                @foreach ($categories as $cat)
                    <button
                        type="button"
                        @click="category = '{{ $cat->slug }}'"
                        :class="category === '{{ $cat->slug }}' ? 'bg-[#1d1d1f] text-white' : 'bg-white text-[#1d1d1f] hover:bg-gray-100'"
                        class="rounded-full px-4 py-2 text-xs font-semibold shadow-sm ring-1 ring-black/[0.06] transition"
                    >
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </button>
                @endforeach
            </div>

            <!-- Grid 30 Produk -->
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <div
                        x-show="matches({{ $product->id }}, @js($product->name), @js($product->sku), @js($product->category->slug), {{ $product->isFastMoving() ? 'true' : 'false' }})"
                        x-transition.opacity
                    >
                        <x-product-card :product="$product" :wishlisted="in_array($product->id, $wishlistIds, true)">
                            <x-slot name="actions">
                                <button
                                    type="button"
                                    @click="openQuickView({{ $product->id }})"
                                    class="rounded-full bg-[#f5f5f7] px-3 py-2 text-xs font-semibold text-[#1d1d1f] transition hover:bg-gray-200"
                                >
                                    Detail
                                </button>
                            </x-slot>
                        </x-product-card>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Apple-Style Split Product Modal (Quick View) -->
        <div
            x-show="quickProduct !== null"
            x-cloak
            @keydown.escape.window="quickProduct = null"
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="quickProduct !== null"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-black/45 backdrop-blur-sm"
            ></div>

            <div
                x-show="quickProduct !== null"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.outside="quickProduct = null"
                class="relative max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white shadow-[0_30px_80px_rgba(0,0,0,0.25)] ring-1 ring-black/[0.08] sm:rounded-3xl"
            >
                <template x-if="quickProduct">
                    <div class="grid gap-0 sm:grid-cols-2">
                        <!-- Kolom Kiri: Foto Produk -->
                        <div class="p-5 sm:p-6 sm:pr-3">
                            <div class="relative aspect-square w-full overflow-hidden rounded-2xl bg-[#f5f5f7]">
                                <template x-if="quickProduct.image">
                                    <img :src="quickProduct.image" :alt="quickProduct.name" class="aspect-square h-full w-full rounded-2xl bg-[#f5f5f7] object-cover">
                                </template>
                                <template x-if="!quickProduct.image">
                                    <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#f5f5f7] to-[#e8e8ed]">
                                        <span class="text-5xl font-extrabold tracking-tight text-[#1d1d1f]/80" x-text="quickProduct.initials"></span>
                                        <span class="mt-2 text-xs font-medium text-[#86868b]" x-text="quickProduct.category"></span>
                                    </div>
                                </template>

                                <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
                                    <span x-show="quickProduct.fast" class="rounded-full bg-[#1d1d1f]/85 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-white backdrop-blur-md">Terlaris (Fast-Moving)</span>
                                    <span x-show="quickProduct.stock <= 0" class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-semibold text-white">Stok Habis</span>
                                    <span x-show="quickProduct.stock > 0 && quickProduct.critical" class="rounded-full bg-amber-500/90 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-md">Stok Kritis</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Detail Produk -->
                        <div class="flex flex-col p-5 pt-0 sm:p-6 sm:pl-3">
                            <div class="flex items-start justify-between gap-3">
                                <span class="rounded-full bg-[#0071e3]/10 px-3 py-1 text-[11px] font-semibold text-[#0071e3]" x-text="quickProduct.category"></span>
                                <button
                                    type="button"
                                    @click="quickProduct = null"
                                    class="-mr-1 -mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#f5f5f7] text-sm text-[#1d1d1f] transition hover:bg-gray-200"
                                    aria-label="Tutup"
                                >&#10005;</button>
                            </div>

                            <h3 class="mt-3 text-xl font-extrabold leading-tight tracking-tight text-[#1d1d1f] sm:text-2xl" x-text="quickProduct.name"></h3>
                            <p class="mt-1 text-[11px] text-[#86868b]">SKU: <span class="font-mono" x-text="quickProduct.sku"></span></p>

                            <p class="mt-3 text-sm leading-relaxed text-[#86868b]" x-text="quickProduct.description"></p>

                            <div class="mt-4">
                                <p class="text-3xl font-extrabold tracking-tight text-[#1d1d1f]" x-text="quickProduct.price_label"></p>
                                <p class="text-[11px] text-[#86868b]">per <span x-text="quickProduct.unit"></span> &bull; <span x-text="quickProduct.fast ? 'Fast-Moving' : 'Slow-Moving'"></span></p>
                            </div>

                            <!-- Progress Ketersediaan Stok -->
                            <div class="mt-4 rounded-2xl bg-[#f5f5f7] p-3.5">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-medium text-[#86868b]">Ketersediaan Stok</span>
                                    <span class="font-semibold" :class="quickProduct.stock <= 0 ? 'text-rose-600' : (quickProduct.critical ? 'text-amber-600' : 'text-emerald-600')">
                                        <span x-text="quickProduct.stock"></span> <span x-text="quickProduct.unit"></span>
                                    </span>
                                </div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-black/[0.08]">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="quickProduct.stock <= 0 ? 'bg-rose-500' : (quickProduct.critical ? 'bg-amber-500' : 'bg-emerald-500')"
                                        :style="`width: ${quickProduct.stock <= 0 ? 0 : stockPercent(quickProduct)}%`"
                                    ></div>
                                </div>
                            </div>

                            @auth
                                @if (auth()->user()->isCustomer())
                                    <form action="{{ route('cart.add') }}" method="POST" class="mt-5 space-y-3" @submit.prevent="quickAdd($el)">
                                        @csrf
                                        <input type="hidden" name="product_id" :value="quickProduct.id">
                                        <input type="hidden" name="quantity" :value="qty">

                                        <div class="flex items-center gap-3">
                                            <div class="inline-flex items-center rounded-full bg-[#f5f5f7] p-1 ring-1 ring-black/[0.06]">
                                                <button type="button" @click="stepQty(-1)" :disabled="qty <= 1" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-base font-semibold text-[#1d1d1f] transition hover:bg-white disabled:opacity-30" aria-label="Kurangi">&minus;</button>
                                                <span class="w-9 text-center text-sm font-bold tabular-nums text-[#1d1d1f]" x-text="qty"></span>
                                                <button type="button" @click="stepQty(1)" :disabled="qty >= Math.min(99, quickProduct.stock)" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-base font-semibold text-[#1d1d1f] transition hover:bg-white disabled:opacity-30" aria-label="Tambah">+</button>
                                            </div>
                                            <p class="text-[11px] text-[#86868b]">Subtotal <span class="font-semibold text-[#1d1d1f]" x-text="lumen.rupiah(quickProduct.price * qty)"></span></p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="submit"
                                                :disabled="quickProduct.stock < 1"
                                                class="flex-1 rounded-full bg-[#1d1d1f] py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3] disabled:cursor-not-allowed disabled:bg-gray-300"
                                            >
                                                + Tambah ke Keranjang
                                            </button>
                                            <button
                                                type="button"
                                                @click="quickWish($refs.quickWishForm)"
                                                :title="quickProduct.wishlisted ? 'Hapus dari Favorit' : 'Simpan ke Favorit'"
                                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f5f5f7] ring-1 ring-black/[0.06] transition hover:scale-105 active:scale-95"
                                            >
                                                <svg class="h-5 w-5 transition-colors" :class="quickProduct.wishlisted ? 'fill-rose-500 text-rose-500' : 'fill-none text-[#1d1d1f]'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </form>
                                    <form x-ref="quickWishForm" :action="'{{ url('/wishlist') }}/' + quickProduct.id" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif
                            @else
                                <div class="mt-5">
                                    <a href="{{ route('login') }}" class="block w-full rounded-full bg-[#1d1d1f] py-3 text-center text-xs font-semibold text-white hover:bg-[#0071e3]">
                                        Masuk untuk Membeli
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
