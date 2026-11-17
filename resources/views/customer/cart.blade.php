<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Keranjang Cerdas</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#1d1d1f]">Keranjang Belanja</h1>
                <p class="mt-1 text-sm text-[#86868b]">Sistem otomatis mendeteksi kombinasi paket Apriori dan memotong harga Anda.</p>
            </div>
            <a href="{{ route('home') }}#katalog" class="text-xs font-semibold text-[#0071e3] hover:underline">
                &larr; Lanjut Belanja
            </a>
        </div>

        @if (empty($summary['lines']))
            <div class="mt-10 rounded-3xl bg-white p-12 text-center shadow-2xs ring-1 ring-black/[0.04]">
                <p class="text-base font-bold text-[#1d1d1f]">Keranjang belanja Anda masih kosong.</p>
                <p class="mt-1 text-xs text-[#86868b]">Pilih produk kebutuhan harian atau ambil Paket Bundling Hemat dari katalog.</p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('home') }}#katalog" class="rounded-full bg-[#1d1d1f] px-6 py-2.5 text-xs font-semibold text-white">
                        Buka Katalog
                    </a>
                    <a href="{{ route('home') }}#bundling" class="rounded-full bg-[#0071e3] px-6 py-2.5 text-xs font-semibold text-white">
                        Lihat Paket Bundling
                    </a>
                </div>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-3">
                <!-- Daftar Item Keranjang -->
                <div class="space-y-4 lg:col-span-2">
                    @foreach ($summary['lines'] as $line)
                        @php
                            /** @var \App\Models\Product $p */
                            $p = $line['product'];
                        @endphp
                        <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-5 shadow-2xs ring-1 ring-black/[0.04] sm:flex-row sm:items-center">
                            <div class="flex items-center gap-4">
                                <div class="h-20 w-24 shrink-0 overflow-hidden rounded-2xl bg-[#f5f5f7]">
                                    <x-product-visual :product="$p" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-medium text-[#86868b]">{{ $p->category->name }}</p>
                                    <h3 class="text-base font-bold tracking-tight text-[#1d1d1f]">{{ $p->name }}</h3>
                                    <p class="mt-0.5 text-xs font-semibold text-[#1d1d1f]">{{ rupiah($p->selling_price) }} <span class="font-normal text-[#86868b]">/ {{ $p->unit }}</span></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <form action="{{ route('cart.update', $p) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="{{ $line['qty'] }}"
                                        min="1"
                                        max="{{ $p->current_stock }}"
                                        onchange="this.form.submit()"
                                        class="w-20 rounded-full border-gray-300 py-1.5 text-center text-xs font-bold focus:border-[#0071e3] focus:ring-[#0071e3]"
                                    />
                                </form>

                                <div class="w-28 text-right">
                                    <p class="text-sm font-bold text-[#1d1d1f]">{{ rupiah($line['subtotal']) }}</p>
                                </div>

                                <form action="{{ route('cart.remove', $p) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full p-2 text-[#86868b] hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                        &#10005;
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <!-- Rekomendasi Apriori: Sering Dibeli Bersama -->
                    @if ($suggestions->isNotEmpty())
                        <div class="mt-8 rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
                            <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Rekomendasi Algoritma Apriori</p>
                            <h3 class="mt-1 text-base font-bold text-[#1d1d1f]">Lengkapi Paket Anda agar Dapat Diskon Smart Bundle</h3>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($suggestions as $sp)
                                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-[#f5f5f7] p-3.5">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-[#1d1d1f]">{{ $sp->name }}</p>
                                            <p class="text-xs text-[#86868b]">{{ rupiah($sp->selling_price) }}</p>
                                        </div>
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $sp->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="shrink-0 rounded-full bg-[#1d1d1f] px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-[#0071e3]">
                                                + Tambah
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Ringkasan Pembayaran & Smart Bundle Discount -->
                <div>
                    <div class="sticky top-24 rounded-3xl bg-white p-6 shadow-[0_8px_30px_rgba(0,0,0,0.05)] ring-1 ring-black/[0.05]">
                        <h2 class="text-lg font-extrabold tracking-tight text-[#1d1d1f]">Ringkasan Belanja</h2>

                        <div class="mt-5 space-y-3 text-xs">
                            <div class="flex justify-between text-[#86868b]">
                                <span>Subtotal ({{ $summary['count'] }} item)</span>
                                <span class="font-semibold text-[#1d1d1f]">{{ rupiah($summary['subtotal']) }}</span>
                            </div>

                            @if ($summary['discount'] > 0)
                                <div class="rounded-2xl bg-emerald-50 p-3.5 text-emerald-800 ring-1 ring-emerald-200">
                                    <div class="flex items-center justify-between font-bold">
                                        <span>Smart Bundle Apriori</span>
                                        <span>-{{ rupiah($summary['discount']) }}</span>
                                    </div>
                                    <ul class="mt-2 space-y-1 text-[11px] text-emerald-700">
                                        @foreach ($summary['bundles'] as $b)
                                            <li>&bull; {{ $b['label'] }} ({{ $b['sets'] }} set, -{{ $b['percent'] }}%)</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="rounded-2xl bg-[#f5f5f7] p-3 text-[11px] text-[#86868b]">
                                    Belum ada kombinasi Smart Bundle aktif. Tambahkan pasangan produk Apriori untuk diskon otomatis.
                                </div>
                            @endif

                            <div class="flex items-center justify-between border-t border-black/[0.06] pt-4 text-sm">
                                <span class="font-bold text-[#1d1d1f]">Total Pembayaran</span>
                                <span class="text-xl font-extrabold text-[#1d1d1f]">{{ rupiah($summary['total']) }}</span>
                            </div>
                        </div>

                        <a
                            href="{{ route('checkout.index') }}"
                            class="mt-6 block w-full rounded-full bg-[#0071e3] py-3.5 text-center text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d1d1f]"
                        >
                            Lanjut ke Checkout &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
