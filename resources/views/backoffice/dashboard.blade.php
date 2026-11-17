<x-admin-layout title="Dashboard Eksekutif" heading="Dashboard Eksekutif & Analitik Cerdas">
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <!-- Top Banner & Quick Actions -->
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:flex-row lg:items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-[#f5f5f7] px-3 py-1 text-[11px] font-semibold text-[#1d1d1f]">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>LUMEN MART Intelligence Center &bull; {{ auth()->user()->roleLabel() }}</span>
            </span>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-[#1d1d1f]">
                Ringkasan Performa Ritel &amp; Prediksi Stok
            </h2>
            <p class="mt-0.5 text-xs text-[#86868b]">
                Data agregasi 12 bulan terakhir dari POS Toko &amp; E-Commerce dengan Regresi Linear (OLS 80:20) dan Algoritma Apriori.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if ($isAdmin)
                <a
                    href="{{ route('backoffice.pos.index') }}"
                    class="rounded-full bg-[#0071e3] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700"
                >
                    + Buka POS Kasir
                </a>

                <form
                    action="{{ route('backoffice.simulation.reset') }}"
                    method="POST"
                    @submit.prevent="$dispatch('confirm-action', {
                        form: $el,
                        title: 'Reset & Generate Ulang Simulasi?',
                        message: 'Seluruh data 30 produk, 800 transaksi 12 bulan, prediksi Regresi Linear, dan aturan Apriori akan dihapus lalu dibuat ulang. Tindakan ini tidak dapat dibatalkan.',
                        confirmLabel: 'Ya, Reset Data'
                    })"
                >
                    @csrf
                    <button
                        type="submit"
                        class="rounded-full bg-[#1d1d1f] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-rose-600"
                    >
                        &#8635; Reset &amp; Generate Ulang Simulasi
                    </button>
                </form>
            @else
                <a
                    href="{{ route('backoffice.reports.index') }}"
                    class="rounded-full bg-[#1d1d1f] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    Ekspor Laporan CSV / PDF &rarr;
                </a>
            @endif
        </div>
    </div>

    <!-- 6 KPI Cards -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Total Pendapatan</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight text-[#1d1d1f]">{{ rupiah($totalRevenue) }}</p>
            <p class="mt-1 text-[11px] text-emerald-700">Diskon bundle: {{ rupiah($totalDiscount) }}</p>
        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Estimasi Laba Kotor</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight text-emerald-700">{{ rupiah($grossProfit) }}</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Pendapatan &minus; HPP</p>
        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Total Transaksi</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight text-[#1d1d1f]">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-[#86868b]">POS {{ $posCount }} &bull; Online {{ $ecommerceCount }}</p>
        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Status Inventori</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight {{ $criticalCount > 0 ? 'text-rose-600' : 'text-[#1d1d1f]' }}">
                {{ $criticalCount }} Stok Kritis
            </p>
            <p class="mt-1 text-[11px] text-[#86868b]">Dari {{ $totalProducts }} produk aktif</p>
        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">R&sup2; Fast-Moving (OLS)</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight text-[#0071e3]">{{ number_format($avgFastR2, 3) }}</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Target &ge; 0.700 (Akurat)</p>
        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Aturan Apriori</p>
            <p class="mt-2 text-lg font-extrabold tracking-tight text-[#1d1d1f]">{{ $rulesCount }} Aturan</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Lift Ratio &gt; 1.0</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#1d1d1f]">Tren Pendapatan &amp; Volume Transaksi (12 Bulan)</h3>
                    <p class="text-xs text-[#86868b]">Perbandingan kontribusi POS Toko vs E-Commerce per bulan</p>
                </div>
            </div>
            <div class="mt-4 h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <h3 class="text-base font-bold text-[#1d1d1f]">Kontribusi Kategori Produk</h3>
            <p class="text-xs text-[#86868b]">Distribusi nilai penjualan per kategori</p>
            <div class="mt-4 h-60">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 space-y-1.5 text-xs">
                @foreach ($categorySales as $cs)
                    <div class="flex items-center justify-between">
                        <span class="text-[#86868b]">{{ $cs->name }}</span>
                        <span class="font-semibold text-[#1d1d1f]">{{ rupiah($cs->total_sales) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Regresi Linear Restock & Apriori Rules -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Rekomendasi Restock Regresi Linear -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#0071e3]">Regresi Linear OLS (Split 80:20)</p>
                    <h3 class="text-base font-bold text-[#1d1d1f]">Prioritas Restock Bulan Depan</h3>
                </div>
                <a href="{{ route('backoffice.regression.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">
                    Detail Analitik &rarr;
                </a>
            </div>

            <div class="mt-4 divide-y divide-black/[0.05]">
                @forelse ($topRestocks as $pred)
                    <div class="flex items-center justify-between gap-3 py-3 text-xs">
                        <div class="min-w-0">
                            <p class="truncate font-bold text-[#1d1d1f]">{{ $pred->product?->name }}</p>
                            <p class="text-[11px] text-[#86868b]">
                                <span class="font-mono">{{ $pred->regression_equation }}</span> &bull;
                                R&sup2;={{ number_format($pred->r_squared, 2) }} &bull;
                                Stok: {{ $pred->product?->current_stock }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-amber-50 px-3 py-1 font-bold text-amber-800 ring-1 ring-amber-200">
                                +{{ $pred->restock_recommendation }} {{ $pred->product?->unit }}
                            </span>
                            @if ($isAdmin && $pred->product)
                                <a
                                    href="{{ route('backoffice.incoming.index', ['product_id' => $pred->product_id]) }}"
                                    class="rounded-full bg-[#1d1d1f] px-3 py-1 font-semibold text-white hover:bg-[#0071e3]"
                                >
                                    Restock
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-[#86868b]">Seluruh stok mencukupi prediksi permintaan bulan depan.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Aturan Bundling Apriori -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#0071e3]">Market Basket Analysis</p>
                    <h3 class="text-base font-bold text-[#1d1d1f]">Aturan Asosiasi Apriori Terkuat</h3>
                </div>
                <a href="{{ route('backoffice.apriori.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">
                    Semua Aturan &rarr;
                </a>
            </div>

            <div class="mt-4 divide-y divide-black/[0.05]">
                @forelse ($topRules as $rule)
                    <div class="py-3 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-[#1d1d1f]">
                                {{ $rule->antecedent_names }} <span class="text-[#0071e3]">&rarr;</span> {{ $rule->consequent_names }}
                            </p>
                            <span class="shrink-0 rounded-full bg-[#0071e3]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#0071e3]">
                                Lift {{ number_format($rule->lift_ratio, 2) }}x
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-[#86868b]">
                            Support {{ number_format($rule->support, 1) }}% &bull;
                            Confidence {{ number_format($rule->confidence, 1) }}% &bull;
                            Diskon Bundle {{ $rule->bundle_discount_percent }}%
                        </p>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-[#86868b]">Belum ada aturan Apriori.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Produk & Transaksi Terbaru -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <h3 class="text-base font-bold text-[#1d1d1f]">8 Produk Terlaris (12 Bulan)</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                            <th class="pb-2.5">Produk</th>
                            <th class="pb-2.5">Kategori</th>
                            <th class="pb-2.5 text-right">Terjual</th>
                            <th class="pb-2.5 text-right">Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach ($topProducts as $tp)
                            <tr>
                                <td class="py-2.5 font-semibold text-[#1d1d1f]">{{ $tp->name }}</td>
                                <td class="py-2.5 text-[#86868b]">{{ $tp->category_name }}</td>
                                <td class="py-2.5 text-right font-bold text-[#1d1d1f]">{{ number_format($tp->total_qty, 0, ',', '.') }} {{ $tp->unit }}</td>
                                <td class="py-2.5 text-right font-semibold text-[#1d1d1f]">{{ rupiah($tp->total_sales) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-[#1d1d1f]">Transaksi Penjualan Terbaru</h3>
                <a href="{{ route('backoffice.reports.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">Laporan Lengkap &rarr;</a>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                            <th class="pb-2.5">Invoice</th>
                            <th class="pb-2.5">Channel</th>
                            <th class="pb-2.5">Status</th>
                            <th class="pb-2.5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach ($recentTransactions as $tx)
                            <tr>
                                <td class="py-2.5">
                                    <a href="{{ route('invoices.pdf', $tx) }}" class="font-mono font-semibold text-[#0071e3] hover:underline">
                                        {{ $tx->invoice_number }}
                                    </a>
                                    <p class="text-[10px] text-[#86868b]">{{ $tx->transaction_date->format('d/m/Y') }}</p>
                                </td>
                                <td class="py-2.5 text-[#86868b]">{{ $tx->channelLabel() }}</td>
                                <td class="py-2.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold ring-1 {{ $tx->statusBadgeClass() }}">
                                        {{ $tx->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right font-bold text-[#1d1d1f]">{{ rupiah($tx->total_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const labels = @js($monthLabels);
            const posRev = @js($posRevenueSeries);
            const ecomRev = @js($ecomRevenueSeries);
            const txCounts = @js($txCountSeries);

            new Chart(document.getElementById('revenueChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'POS Toko (Rp)',
                            data: posRev,
                            backgroundColor: '#1d1d1f',
                            borderRadius: 6,
                        },
                        {
                            label: 'E-Commerce (Rp)',
                            data: ecomRev,
                            backgroundColor: '#0071e3',
                            borderRadius: 6,
                        },
                        {
                            label: 'Jumlah Transaksi',
                            data: txCounts,
                            type: 'line',
                            yAxisID: 'y1',
                            borderColor: '#10b981',
                            backgroundColor: '#10b981',
                            tension: 0.35,
                            pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true },
                        y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } },
                    },
                },
            });

            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: @js($categorySales->pluck('name')),
                    datasets: [{
                        data: @js($categorySales->pluck('total_sales')),
                        backgroundColor: ['#1d1d1f', '#0071e3', '#4f46e5', '#10b981', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                },
            });
        });
    </script>
</x-admin-layout>
