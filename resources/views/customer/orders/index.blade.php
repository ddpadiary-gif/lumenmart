<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Riwayat &amp; Pelacakan</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#1d1d1f]">Pesanan Saya</h1>
                <p class="mt-1 text-sm text-[#86868b]">Pantau progres pengiriman pesanan Anda dan unduh Invoice PDF resmi.</p>
            </div>
            <a href="{{ route('home') }}#katalog" class="rounded-full bg-[#1d1d1f] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#0071e3]">
                Belanja Lagi
            </a>
        </div>

        @if ($orders->isEmpty())
            <div class="mt-10 rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-black/[0.04]">
                <p class="text-base font-bold text-[#1d1d1f]">Anda belum memiliki pesanan E-Commerce.</p>
                <a href="{{ route('home') }}#katalog" class="mt-4 inline-block rounded-full bg-[#0071e3] px-6 py-2.5 text-xs font-semibold text-white">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] sm:flex-row sm:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-bold text-[#1d1d1f]">{{ $order->invoice_number }}</span>
                                <span class="rounded-full px-3 py-0.5 text-[11px] font-semibold ring-1 {{ $order->statusBadgeClass() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                                <span class="rounded-full bg-[#f5f5f7] px-2.5 py-0.5 text-[11px] font-medium text-[#86868b]">
                                    {{ $order->paymentLabel() }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-[#86868b]">
                                Tanggal: {{ $order->transaction_date->translatedFormat('d F Y') }} &bull;
                                {{ $order->details_count }} jenis produk
                                @if ($order->discount_amount > 0)
                                    &bull; <span class="font-semibold text-emerald-700">Hemat Bundle {{ rupiah($order->discount_amount) }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-[11px] text-[#86868b]">Total Bayar</p>
                                <p class="text-base font-extrabold text-[#1d1d1f]">{{ rupiah($order->total_amount) }}</p>
                            </div>
                            <a
                                href="{{ route('invoices.pdf', $order) }}"
                                class="rounded-full bg-[#f5f5f7] px-4 py-2 text-xs font-semibold text-[#1d1d1f] hover:bg-gray-200"
                            >
                                PDF
                            </a>
                            <a
                                href="{{ route('orders.show', $order) }}"
                                class="rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0071e3]"
                            >
                                Lacak Pesanan &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
