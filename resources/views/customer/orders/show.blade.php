<x-app-layout>
    @php
        $steps = [
            'menunggu_pembayaran' => ['Menunggu Pembayaran', 'Menunggu konfirmasi pembayaran Anda'],
            'diproses' => ['Diproses', 'Pesanan sedang dikemas di gudang LUMEN MART'],
            'dikirim' => ['Dikirim', 'Kurir sedang mengantar pesanan ke alamat Anda'],
            'selesai' => ['Selesai', 'Pesanan telah diterima dengan baik'],
        ];
        $currentStep = $order->statusStep();
    @endphp

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">&larr; Kembali ke Daftar Pesanan</a>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-[#1d1d1f] sm:text-3xl">
                    Invoice {{ $order->invoice_number }}
                </h1>
                <p class="mt-1 text-xs text-[#86868b]">
                    Dibuat pada {{ $order->transaction_date->translatedFormat('d F Y') }} &bull; Metode {{ $order->paymentLabel() }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('invoices.pdf', $order) }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#1d1d1f] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Unduh Invoice PDF</span>
                </a>
            </div>
        </div>

        <!-- Stepper Visual Status Pesanan -->
        <div class="mt-8 rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
            <h2 class="text-xs font-bold uppercase tracking-widest text-[#86868b]">Pelacakan Status Pesanan</h2>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
                @foreach (array_values($steps) as $idx => [$label, $desc])
                    @php
                        $done = $idx <= $currentStep;
                        $active = $idx === $currentStep;
                    @endphp
                    <div class="relative rounded-2xl p-4 {{ $active ? 'bg-[#0071e3]/10 ring-2 ring-[#0071e3]' : ($done ? 'bg-[#f5f5f7]' : 'bg-white ring-1 ring-black/[0.06] opacity-60') }}">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $done ? 'bg-[#0071e3] text-white' : 'bg-gray-200 text-[#86868b]' }}">
                                {{ $idx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-[#1d1d1f]">{{ $label }}</span>
                        </div>
                        <p class="mt-2 text-[11px] leading-relaxed text-[#86868b]">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Panel Simulasi Pembayaran (Jika Menunggu Pembayaran) -->
            @if ($order->order_status === 'menunggu_pembayaran')
                <div class="mt-6 flex flex-col items-start justify-between gap-4 rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-200 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-sm font-bold text-amber-900">Menunggu Pembayaran ({{ $order->paymentLabel() }})</p>
                        @if ($order->payment_method === 'qris')
                            <p class="mt-1 text-xs text-amber-800">
                                Silakan selesaikan pembayaran QRIS sebesar <strong>{{ rupiah($order->total_amount) }}</strong> lalu klik tombol konfirmasi simulasi di samping.
                            </p>
                        @else
                            <p class="mt-1 text-xs text-amber-800">
                                Transfer sebesar <strong>{{ rupiah($order->total_amount) }}</strong> ke BCA 8810-2026-9900 a.n. PT LUMEN MART RITEL.
                            </p>
                        @endif
                    </div>

                    <form action="{{ route('orders.pay', $order) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="shrink-0 rounded-full bg-[#0071e3] px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-[#1d1d1f]"
                        >
                            Konfirmasi Pembayaran (Simulasi)
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Rincian Item & Alamat -->
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] lg:col-span-2">
                <h2 class="text-base font-bold text-[#1d1d1f]">Rincian Produk</h2>

                <div class="mt-4 divide-y divide-black/[0.05]">
                    @foreach ($order->details as $detail)
                        <div class="flex items-center justify-between py-3.5 text-xs">
                            <div>
                                <p class="font-bold text-[#1d1d1f]">{{ $detail->product?->name ?? 'Produk' }}</p>
                                <p class="text-[#86868b]">{{ $detail->quantity }} x {{ rupiah($detail->unit_price) }}</p>
                            </div>
                            <p class="font-bold text-[#1d1d1f]">{{ rupiah($detail->subtotal) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 space-y-2 border-t border-black/[0.06] pt-4 text-xs">
                    <div class="flex justify-between text-[#86868b]">
                        <span>Subtotal</span>
                        <span class="font-semibold text-[#1d1d1f]">{{ rupiah($order->subtotalAmount()) }}</span>
                    </div>
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between font-semibold text-emerald-700">
                            <span>Diskon Smart Bundle Apriori</span>
                            <span>-{{ rupiah($order->discount_amount) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-black/[0.06] pt-3 text-sm font-extrabold text-[#1d1d1f]">
                        <span>Total Bayar</span>
                        <span>{{ rupiah($order->total_amount) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
                <h2 class="text-base font-bold text-[#1d1d1f]">Informasi Pengiriman</h2>
                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <p class="text-[#86868b]">Penerima</p>
                        <p class="font-semibold text-[#1d1d1f]">{{ $order->user?->name }}</p>
                    </div>
                    <div>
                        <p class="text-[#86868b]">Alamat Tujuan</p>
                        <p class="mt-0.5 leading-relaxed text-[#1d1d1f]">{{ $order->shipping_address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[#86868b]">Metode Pembayaran</p>
                        <p class="font-semibold text-[#1d1d1f]">{{ $order->paymentLabel() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
