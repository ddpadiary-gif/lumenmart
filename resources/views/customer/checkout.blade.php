<x-app-layout>
    <div x-data="{ method: '{{ old('payment_method', 'qris') }}' }" class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Langkah Terakhir</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#1d1d1f]">Checkout Pesanan</h1>
            <p class="mt-1 text-sm text-[#86868b]">Pilih metode pembayaran (COD, Transfer Bank, atau QRIS Simulasi) dan lengkapi alamat pengiriman.</p>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST" class="mt-8 grid gap-8 lg:grid-cols-3">
            @csrf

            <div class="space-y-6 lg:col-span-2">
                <!-- Alamat Pengiriman -->
                <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
                    <h2 class="text-base font-bold text-[#1d1d1f]">1. Alamat Pengiriman Lengkap</h2>
                    <p class="mt-1 text-xs text-[#86868b]">Cantumkan nama jalan, nomor rumah, kelurahan, kecamatan, dan nomor telepon penerima.</p>

                    <textarea
                        name="shipping_address"
                        rows="3"
                        required
                        placeholder="Contoh: Jl. Melati No. 12, Kel. Sukamaju, Kec. Cibeunying, Kota Bandung 40121 (0812-3456-7890)"
                        class="mt-4 w-full rounded-2xl border-gray-300 text-sm focus:border-[#0071e3] focus:ring-[#0071e3]"
                    >{{ old('shipping_address', 'Jl. Melati No. 12, Kel. Sukamaju, Kec. Cibeunying, Kota Bandung 40121 (0812-3456-7890)') }}</textarea>
                </div>

                <!-- Metode Pembayaran -->
                <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
                    <h2 class="text-base font-bold text-[#1d1d1f]">2. Pilih Metode Pembayaran</h2>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <label
                            @click="method = 'qris'"
                            :class="method === 'qris' ? 'ring-2 ring-[#0071e3] bg-[#0071e3]/5' : 'ring-1 ring-black/[0.08] bg-white'"
                            class="cursor-pointer rounded-2xl p-4 transition"
                        >
                            <input type="radio" name="payment_method" value="qris" x-model="method" class="sr-only">
                            <p class="text-xs font-bold text-[#1d1d1f]">QRIS Simulasi</p>
                            <p class="mt-1 text-[11px] text-[#86868b]">Scan kode QRIS instan dari semua e-wallet &amp; m-banking.</p>
                        </label>

                        <label
                            @click="method = 'transfer_bank'"
                            :class="method === 'transfer_bank' ? 'ring-2 ring-[#0071e3] bg-[#0071e3]/5' : 'ring-1 ring-black/[0.08] bg-white'"
                            class="cursor-pointer rounded-2xl p-4 transition"
                        >
                            <input type="radio" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                            <p class="text-xs font-bold text-[#1d1d1f]">Transfer Bank</p>
                            <p class="mt-1 text-[11px] text-[#86868b]">Transfer ke Virtual Account BCA / Mandiri LUMEN MART.</p>
                        </label>

                        <label
                            @click="method = 'cod'"
                            :class="method === 'cod' ? 'ring-2 ring-[#0071e3] bg-[#0071e3]/5' : 'ring-1 ring-black/[0.08] bg-white'"
                            class="cursor-pointer rounded-2xl p-4 transition"
                        >
                            <input type="radio" name="payment_method" value="cod" x-model="method" class="sr-only">
                            <p class="text-xs font-bold text-[#1d1d1f]">COD (Bayar di Tempat)</p>
                            <p class="mt-1 text-[11px] text-[#86868b]">Bayar tunai kepada kurir saat pesanan tiba di lokasi.</p>
                        </label>
                    </div>

                    <!-- Preview Instruksi Pembayaran -->
                    <div class="mt-5 rounded-2xl bg-[#f5f5f7] p-4 text-xs">
                        <div x-show="method === 'qris'">
                            <p class="font-bold text-[#1d1d1f]">Simulasi Pembayaran QRIS Nasional (NMID: ID102600088991)</p>
                            <p class="mt-1 text-[#86868b]">Setelah membuat pesanan, kode QRIS simulasi akan tampil di halaman detail pesanan dan dapat dikonfirmasi dengan 1 klik.</p>
                        </div>
                        <div x-show="method === 'transfer_bank'" x-cloak>
                            <p class="font-bold text-[#1d1d1f]">Rekening Tujuan: Bank BCA 8810-2026-9900 a.n. PT LUMEN MART RITEL</p>
                            <p class="mt-1 text-[#86868b]">Setelah membuat pesanan, Anda dapat menekan tombol Konfirmasi Pembayaran Simulasi pada halaman detail pesanan.</p>
                        </div>
                        <div x-show="method === 'cod'" x-cloak>
                            <p class="font-bold text-[#1d1d1f]">Bayar di Tempat (COD)</p>
                            <p class="mt-1 text-[#86868b]">Status pesanan langsung masuk ke tahap <strong>Diproses</strong> oleh tim gudang LUMEN MART.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pesanan -->
            <div>
                <div class="sticky top-24 rounded-3xl bg-white p-6 shadow-[0_8px_30px_rgba(0,0,0,0.05)] ring-1 ring-black/[0.05]">
                    <h2 class="text-lg font-extrabold tracking-tight text-[#1d1d1f]">Item Pesanan ({{ $summary['count'] }})</h2>

                    <div class="mt-4 max-h-64 space-y-2.5 overflow-y-auto pr-1 text-xs">
                        @foreach ($summary['lines'] as $line)
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate text-[#1d1d1f]">{{ $line['qty'] }}x {{ $line['product']->name }}</span>
                                <span class="shrink-0 font-semibold text-[#1d1d1f]">{{ rupiah($line['subtotal']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 space-y-2 border-t border-black/[0.06] pt-4 text-xs">
                        <div class="flex justify-between text-[#86868b]">
                            <span>Subtotal</span>
                            <span class="font-semibold text-[#1d1d1f]">{{ rupiah($summary['subtotal']) }}</span>
                        </div>
                        @if ($summary['discount'] > 0)
                            <div class="flex justify-between font-semibold text-emerald-700">
                                <span>Diskon Smart Bundle Apriori</span>
                                <span>-{{ rupiah($summary['discount']) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-black/[0.06] pt-3 text-sm">
                            <span class="font-bold text-[#1d1d1f]">Total Bayar</span>
                            <span class="text-xl font-extrabold text-[#1d1d1f]">{{ rupiah($summary['total']) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="mt-6 w-full rounded-full bg-[#1d1d1f] py-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#0071e3]"
                    >
                        Buat Pesanan Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
