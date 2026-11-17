<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Koleksi Pilihan</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#1d1d1f]">Produk Favorit Saya</h1>
                <p class="mt-1 text-sm text-[#86868b]">Simpan produk langganan Anda untuk akses belanja lebih cepat.</p>
            </div>
            <a href="{{ route('home') }}#katalog" class="rounded-full bg-[#1d1d1f] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]">
                + Jelajahi Katalog
            </a>
        </div>

        @if ($products->isEmpty())
            <div class="mt-10 rounded-3xl bg-white p-12 text-center shadow-2xs ring-1 ring-black/[0.04]">
                <p class="text-base font-bold text-[#1d1d1f]">Belum ada produk di daftar Favorit Anda.</p>
                <p class="mt-1 text-xs text-[#86868b]">Klik ikon hati pada kartu produk di katalog untuk menyimpannya di sini.</p>
                <a href="{{ route('home') }}#katalog" class="mt-6 inline-block rounded-full bg-[#0071e3] px-6 py-2.5 text-xs font-semibold text-white">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :wishlisted="true" />
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
