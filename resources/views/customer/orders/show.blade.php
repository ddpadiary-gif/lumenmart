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
        <div class="mt-8 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
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

            <!-- Panel & Modal Pembayaran Interaktif (Jika Menunggu Pembayaran) -->
            @if ($order->order_status === 'menunggu_pembayaran')
                @php
                    $isQris = $order->payment_method === 'qris';
                    $amountLabel = rupiah($order->total_amount);
                    $expiresAt = ($order->created_at ?? now())->copy()->addDay();
                    $seed = crc32($order->invoice_number.'|'.$order->total_amount);
                    $vaSuffix = str_pad((string) $order->id, 6, '0', STR_PAD_LEFT).str_pad((string) ($seed % 100000), 5, '0', STR_PAD_LEFT);
                    $vaAccounts = [
                        ['bank' => 'BCA Virtual Account', 'code' => 'BCA', 'number' => '88102'.$vaSuffix, 'color' => 'bg-[#0060af]'],
                        ['bank' => 'Mandiri Virtual Account', 'code' => 'MDR', 'number' => '89608'.$vaSuffix, 'color' => 'bg-[#003d79]'],
                    ];

                    // Ilustrasi QR Code (matriks 29x29 / Versi 3) deterministik dari invoice & nominal.
                    $size = 29;
                    $mods = array_fill(0, $size, array_fill(0, $size, false));
                    $reserved = array_fill(0, $size, array_fill(0, $size, false));
                    $finder = function ($r0, $c0) use (&$mods, &$reserved, $size) {
                        for ($r = -1; $r <= 7; $r++) {
                            for ($c = -1; $c <= 7; $c++) {
                                $rr = $r0 + $r; $cc = $c0 + $c;
                                if ($rr < 0 || $cc < 0 || $rr >= $size || $cc >= $size) continue;
                                $reserved[$rr][$cc] = true;
                                $inRing = $r >= 0 && $r <= 6 && $c >= 0 && $c <= 6 && ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                                $inCore = $r >= 2 && $r <= 4 && $c >= 2 && $c <= 4;
                                $mods[$rr][$cc] = $inRing || $inCore;
                            }
                        }
                    };
                    $finder(0, 0); $finder(0, $size - 7); $finder($size - 7, 0);
                    // Alignment pattern (Versi 3 di koordinat 22,22).
                    for ($r = -2; $r <= 2; $r++) {
                        for ($c = -2; $c <= 2; $c++) {
                            $reserved[22 + $r][22 + $c] = true;
                            $mods[22 + $r][22 + $c] = max(abs($r), abs($c)) !== 1;
                        }
                    }
                    // Timing patterns.
                    for ($i = 8; $i < $size - 8; $i++) {
                        $reserved[6][$i] = $reserved[$i][6] = true;
                        $mods[6][$i] = $mods[$i][6] = $i % 2 === 0;
                    }
                    // Modul data pseudo-acak (LCG) dari seed invoice.
                    $state = $seed ?: 1;
                    for ($r = 0; $r < $size; $r++) {
                        for ($c = 0; $c < $size; $c++) {
                            if ($reserved[$r][$c]) continue;
                            $state = ($state * 1103515245 + 12345) & 0x7fffffff;
                            $mods[$r][$c] = (($state >> 16) & 1) === 1;
                        }
                    }
                    // Area logo di tengah.
                    $logoFrom = 11; $logoTo = 17;
                @endphp

                <div
                    x-data="{
                        open: false,
                        copied: null,
                        expires: {{ $expiresAt->getTimestamp() * 1000 }},
                        now: Date.now(),
                        get remaining() {
                            const s = Math.max(0, Math.floor((this.expires - this.now) / 1000));
                            const pad = n => String(n).padStart(2, '0');
                            return pad(Math.floor(s / 3600)) + ':' + pad(Math.floor(s % 3600 / 60)) + ':' + pad(s % 60);
                        },
                        async copyVa(number) {
                            await lumen.copy(number);
                            this.copied = number;
                            setTimeout(() => { if (this.copied === number) this.copied = null }, 2000);
                        }
                    }"
                    x-init="setInterval(() => now = Date.now(), 1000)"
                    class="mt-6 flex flex-col items-start justify-between gap-4 rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-200 sm:flex-row sm:items-center"
                >
                    <div>
                        <p class="text-sm font-bold text-amber-900">Menunggu Pembayaran ({{ $order->paymentLabel() }})</p>
                        <p class="mt-1 text-xs text-amber-800">
                            Selesaikan pembayaran sebesar <strong>{{ $amountLabel }}</strong> sebelum
                            <strong>{{ $expiresAt->translatedFormat('d F Y, H:i') }}</strong>
                            (<span class="font-mono font-semibold" x-text="remaining"></span> lagi).
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="open = true"
                        class="inline-flex shrink-0 items-center gap-2 rounded-full bg-[#0071e3] px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d1d1f]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.25h1.5v1.5h-1.5zM17.25 14.25h1.5v1.5h-1.5zM15.75 17.25h1.5v1.5h-1.5zM19.5 17.25h.75v1.5h-.75zM13.5 19.5h1.5v.75h-1.5z" /></svg>
                        <span>Bayar Sekarang ({{ $isQris ? 'Buka Kode QRIS' : 'Instruksi VA' }})</span>
                    </button>

                    <!-- Modal Pembayaran -->
                    <template x-teleport="body">
                        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="absolute inset-0 bg-black/45 backdrop-blur-sm"
                            ></div>

                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                @click.outside="open = false"
                                class="relative max-h-[94vh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white p-6 shadow-[0_30px_80px_rgba(0,0,0,0.25)] ring-1 ring-black/[0.08] sm:rounded-3xl"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-[#0071e3]">{{ $isQris ? 'Pembayaran QRIS' : 'Transfer Virtual Account' }}</p>
                                        <h3 class="mt-1 text-lg font-extrabold tracking-tight text-[#1d1d1f]">{{ $order->invoice_number }}</h3>
                                    </div>
                                    <button type="button" @click="open = false" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#f5f5f7] text-sm text-[#1d1d1f] hover:bg-gray-200" aria-label="Tutup">&#10005;</button>
                                </div>

                                @if ($isQris)
                                    <!-- Kartu QRIS Standar Nasional -->
                                    <div class="mt-5 overflow-hidden rounded-2xl bg-white ring-1 ring-black/[0.08] shadow-sm">
                                        <div class="flex items-center justify-between bg-[#1d1d1f] px-4 py-3">
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-black italic tracking-tight text-white">QRIS</span>
                                                <span class="text-[9px] font-medium text-white/60">QR Code Standar Pembayaran Nasional</span>
                                            </div>
                                            <span class="rounded bg-white px-1.5 py-0.5 text-[9px] font-extrabold text-rose-600">GPN</span>
                                        </div>
                                        <div class="px-5 pb-5 pt-4 text-center">
                                            <p class="text-sm font-extrabold uppercase tracking-tight text-[#1d1d1f]">LUMEN MART DIGITAL RETAIL</p>
                                            <p class="mt-0.5 font-mono text-[10px] text-[#86868b]">NMID: ID{{ 1020000000000 + ($seed % 899999999) }}</p>

                                            <div class="relative mx-auto mt-4 w-full max-w-[220px] rounded-xl bg-white p-2 ring-1 ring-black/[0.06]">
                                                <svg viewBox="-2 -2 {{ $size + 4 }} {{ $size + 4 }}" class="h-auto w-full" shape-rendering="crispEdges" role="img" aria-label="Kode QRIS">
                                                    <rect x="-2" y="-2" width="{{ $size + 4 }}" height="{{ $size + 4 }}" fill="#fff" />
                                                    @foreach ($mods as $r => $row)
                                                        @foreach ($row as $c => $on)
                                                            @if ($on && ! ($r >= $logoFrom && $r <= $logoTo && $c >= $logoFrom && $c <= $logoTo))
                                                                <rect x="{{ $c }}" y="{{ $r }}" width="1" height="1" fill="#111" />
                                                            @endif
                                                        @endforeach
                                                    @endforeach
                                                    <rect x="{{ $logoFrom + 0.5 }}" y="{{ $logoFrom + 0.5 }}" width="{{ $logoTo - $logoFrom }}" height="{{ $logoTo - $logoFrom }}" rx="1.2" fill="#0071e3" />
                                                    <text x="{{ $size / 2 }}" y="{{ $size / 2 + 1.1 }}" text-anchor="middle" font-size="3.1" font-weight="800" fill="#fff" font-family="Inter, sans-serif">LM</text>
                                                </svg>
                                            </div>

                                            <p class="mt-4 text-[11px] text-[#86868b]">Total Tagihan</p>
                                            <p class="text-2xl font-extrabold tracking-tight text-[#1d1d1f]">{{ $amountLabel }}</p>
                                            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200">
                                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
                                                Berlaku <span class="font-mono" x-text="remaining"></span>
                                            </div>
                                            <p class="mt-3 text-[10px] leading-relaxed text-[#86868b]">Pindai menggunakan GoPay, OVO, DANA, ShopeePay, LinkAja, atau Mobile Banking apa pun yang mendukung QRIS.</p>
                                        </div>
                                    </div>

                                    <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-5">
                                        @csrf
                                        <button type="submit" class="w-full rounded-full bg-[#0071e3] py-3 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d1d1f]">
                                            Simulasikan Pembayaran Berhasil
                                        </button>
                                    </form>
                                @else
                                    <!-- Instruksi Virtual Account -->
                                    <div class="mt-5 rounded-2xl bg-[#f5f5f7] p-4 text-center">
                                        <p class="text-[11px] text-[#86868b]">Nominal Transfer (tepat hingga digit terakhir)</p>
                                        <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#1d1d1f]">{{ $amountLabel }}</p>
                                        <p class="mt-1 text-[11px] font-semibold text-amber-700">Batas waktu <span class="font-mono" x-text="remaining"></span></p>
                                    </div>

                                    <div class="mt-4 space-y-3">
                                        @foreach ($vaAccounts as $va)
                                            <div class="flex items-center gap-3 rounded-2xl p-3.5 ring-1 ring-black/[0.08]">
                                                <span class="inline-flex h-10 w-12 shrink-0 items-center justify-center rounded-xl {{ $va['color'] }} text-[11px] font-extrabold text-white">{{ $va['code'] }}</span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[11px] text-[#86868b]">{{ $va['bank'] }}</p>
                                                    <p class="font-mono text-sm font-bold tracking-wider text-[#1d1d1f]">{{ trim(chunk_split($va['number'], 4, ' ')) }}</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyVa('{{ $va['number'] }}')"
                                                    class="shrink-0 rounded-full px-3.5 py-2 text-[11px] font-semibold transition"
                                                    :class="copied === '{{ $va['number'] }}' ? 'bg-emerald-500 text-white' : 'bg-[#f5f5f7] text-[#1d1d1f] hover:bg-gray-200'"
                                                    x-text="copied === '{{ $va['number'] }}' ? 'Tersalin!' : 'Salin Nomor VA'"
                                                >Salin Nomor VA</button>
                                            </div>
                                        @endforeach
                                    </div>

                                    <ol class="mt-4 list-inside list-decimal space-y-1 text-[11px] leading-relaxed text-[#86868b]">
                                        <li>Buka m-Banking / ATM, pilih menu <strong>Transfer &rarr; Virtual Account</strong>.</li>
                                        <li>Masukkan nomor VA di atas, pastikan nama tujuan <strong>LUMEN MART</strong>.</li>
                                        <li>Periksa nominal <strong>{{ $amountLabel }}</strong>, lalu konfirmasi dengan PIN.</li>
                                    </ol>

                                    <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-5">
                                        @csrf
                                        <button type="submit" class="w-full rounded-full bg-[#0071e3] py-3 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d1d1f]">
                                            Konfirmasi Transfer (Simulasi)
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        <!-- Rincian Item & Alamat -->
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:col-span-2">
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

            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
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
