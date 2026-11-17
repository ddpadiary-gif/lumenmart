<x-app-layout>
    <div
        x-data="{
            q: @js($search ?? ''),
            category: @js($activeCategory ?? 'all'),
            onlyFast: false,
            quickProduct: null,
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
            }
        }"
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
            <section id="bundling" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
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
                                        <form action="{{ route('cart.bundle', $rule) }}" method="POST">
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
        <section id="katalog" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
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
                            class="w-full rounded-full border-0 bg-white py-2.5 pl-10 pr-4 text-xs text-[#1d1d1f] shadow-2xs ring-1 ring-black/[0.08] placeholder:text-[#86868b] focus:ring-2 focus:ring-[#0071e3]"
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
                    class="rounded-full px-4 py-2 text-xs font-semibold shadow-2xs ring-1 ring-black/[0.06] transition"
                >
                    Semua Kategori ({{ $products->count() }})
                </button>
                @foreach ($categories as $cat)
                    <button
                        type="button"
                        @click="category = '{{ $cat->slug }}'"
                        :class="category === '{{ $cat->slug }}' ? 'bg-[#1d1d1f] text-white' : 'bg-white text-[#1d1d1f] hover:bg-gray-100'"
                        class="rounded-full px-4 py-2 text-xs font-semibold shadow-2xs ring-1 ring-black/[0.06] transition"
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

        <!-- Quick View Modal (Alpine.js) -->
        <div
            x-show="quickProduct !== null"
            x-cloak
            @keydown.escape.window="quickProduct = null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
        >
            <div
                @click.outside="quickProduct = null"
                class="w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-black/[0.08]"
            >
                <template x-if="quickProduct">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="rounded-full bg-[#f5f5f7] px-3 py-1 text-[11px] font-semibold text-[#86868b]" x-text="quickProduct.category"></span>
                                <h3 class="mt-2 text-xl font-extrabold tracking-tight text-[#1d1d1f]" x-text="quickProduct.name"></h3>
                                <p class="text-xs text-[#86868b]">SKU: <span class="font-mono" x-text="quickProduct.sku"></span></p>
                            </div>
                            <button
                                type="button"
                                @click="quickProduct = null"
                                class="rounded-full bg-[#f5f5f7] p-2 text-[#1d1d1f] hover:bg-gray-200"
                            >
                                &#10005;
                            </button>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-[#86868b]" x-text="quickProduct.description"></p>

                        <div class="mt-5 grid grid-cols-3 gap-3 rounded-2xl bg-[#f5f5f7] p-4 text-xs">
                            <div>
                                <p class="text-[#86868b]">Harga Satuan</p>
                                <p class="mt-0.5 text-sm font-bold text-[#1d1d1f]" x-text="quickProduct.price_label"></p>
                            </div>
                            <div>
                                <p class="text-[#86868b]">Ketersediaan</p>
                                <p class="mt-0.5 font-semibold text-[#1d1d1f]"><span x-text="quickProduct.stock"></span> <span x-text="quickProduct.unit"></span></p>
                            </div>
                            <div>
                                <p class="text-[#86868b]">Klasifikasi</p>
                                <p class="mt-0.5 font-semibold text-[#0071e3]" x-text="quickProduct.fast ? 'Fast-Moving' : 'Slow-Moving'"></p>
                            </div>
                        </div>

                        @auth
                            @if (auth()->user()->isCustomer())
                                <form action="{{ route('cart.add') }}" method="POST" class="mt-6 flex items-center gap-3">
                                    @csrf
                                    <input type="hidden" name="product_id" :value="quickProduct.id">
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="1"
                                        min="1"
                                        :max="quickProduct.stock"
                                        class="w-24 rounded-full border-gray-300 text-center text-sm font-semibold focus:border-[#0071e3] focus:ring-[#0071e3]"
                                    >
                                    <button
                                        type="submit"
                                        :disabled="quickProduct.stock < 1"
                                        class="flex-1 rounded-full bg-[#1d1d1f] py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3] disabled:bg-gray-300"
                                    >
                                        + Tambah ke Keranjang
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="mt-6">
                                <a href="{{ route('login') }}" class="block w-full rounded-full bg-[#1d1d1f] py-3 text-center text-xs font-semibold text-white hover:bg-[#0071e3]">
                                    Masuk untuk Membeli
                                </a>
                            </div>
                        @endauth
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
