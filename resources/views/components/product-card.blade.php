@props(['product', 'wishlisted' => false])

@php
    $hasFile = filled($product->image_path) && file_exists(public_path($product->image_path));
    $catSlug = $product->category->slug ?? 'sembako';
    $catName = $product->category->name ?? 'Produk';
@endphp

<article
    {{ $attributes->merge([
        'class' => 'group relative flex flex-col justify-between rounded-3xl bg-white p-5 shadow-[0_4px_24px_rgba(0,0,0,0.04)] ring-1 ring-black/[0.04] transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_14px_36px_rgba(0,0,0,0.08)]',
    ]) }}
>
    <div>
        <!-- Area Visual Produk (Anti-Broken-Image) -->
        <div class="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-[#f5f5f7]">
            @if ($hasFile)
                <img
                    src="{{ asset($product->image_path) }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
            @else
                <x-product-visual :product="$product" />
            @endif

            <!-- Badge Movement & Stok -->
            <div class=" absolute left-3 top-3 flex flex-wrap items-center gap-1.5">
                @if ($product->isFastMoving())
                    <span class="rounded-full bg-[#1d1d1f]/85 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-white backdrop-blur-md">
                        Terlaris
                    </span>
                @endif
                @if ($product->current_stock <= 0)
                    <span class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-semibold text-white">
                        Stok Habis
                    </span>
                @elseif ($product->isCritical())
                    <span class="rounded-full bg-amber-500/90 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-md">
                        Sisa {{ $product->current_stock }}
                    </span>
                @endif
            </div>

            <!-- Tombol Wishlist (Customer) -->
            @auth
                @if (auth()->user()->isCustomer())
                    <form action="{{ route('wishlist.toggle', $product) }}" method="POST" class="absolute right-3 top-3">
                        @csrf
                        <button
                            type="submit"
                            title="Simpan ke Favorit"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-[#1d1d1f] shadow-sm backdrop-blur-md transition hover:scale-110"
                        >
                            <svg class="h-4 w-4 {{ $wishlisted ? 'fill-rose-500 text-rose-500' : 'fill-none text-[#1d1d1f]' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>
                    </form>
                @endif
            @endauth
        </div>

        <!-- Informasi Produk -->
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs text-[#86868b]">
                <span class="font-medium">{{ $catName }}</span>
                <span>Stok {{ $product->current_stock }} {{ $product->unit }}</span>
            </div>

            <h3 class="mt-1 line-clamp-1 text-base font-semibold tracking-tight text-[#1d1d1f]">
                {{ $product->name }}
            </h3>

            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-[#86868b]">
                {{ $product->description }}
            </p>
        </div>
    </div>

    <!-- Harga & Tombol Aksi -->
    <div class="mt-5 flex items-center justify-between gap-2 border-t border-black/[0.05] pt-4">
        <div>
            <p class="text-[11px] text-[#86868b]">Harga / {{ $product->unit }}</p>
            <p class="text-lg font-bold tracking-tight text-[#1d1d1f]">{{ rupiah($product->selling_price) }}</p>
        </div>

        <div class="flex items-center gap-1.5">
            {{ $actions ?? '' }}

            @auth
                @if (auth()->user()->isCustomer())
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button
                            type="submit"
                            @disabled($product->current_stock < 1)
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#0071e3] disabled:cursor-not-allowed disabled:bg-gray-300"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Keranjang</span>
                        </button>
                    </form>
                @elseif (auth()->user()->isAdmin())
                    <a
                        href="{{ route('backoffice.products.edit', $product) }}"
                        class="inline-flex items-center rounded-full bg-[#f5f5f7] px-3.5 py-2 text-xs font-semibold text-[#1d1d1f] transition hover:bg-gray-200"
                    >
                        Kelola
                    </a>
                @endif
            @else
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5 rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    <span>Beli</span>
                </a>
            @endauth
        </div>
    </div>
</article>
