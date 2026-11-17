<x-admin-layout title="Prediksi Regresi Linear" heading="Prediksi Permintaan & Restock — Regresi Linear (OLS)">
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <!-- Header & Metodologi -->
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:flex-row lg:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Ordinary Least Squares (Y = a + bX) &bull; Split Kronologis 80:20</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-[#1d1d1f]">
                Peramalan Permintaan Bulan ke-13 ({{ $targetLabel }})
            </h2>
            <p class="mt-1 text-xs text-[#86868b]">
                Bulan 1&ndash;10 digunakan sebagai data latih (80%) dan Bulan 11&ndash;12 sebagai data uji (20%) untuk evaluasi MAE &amp; RMSE. Rekomendasi restock menyertakan <em>safety stock</em> (Z=1.65 &times; RMSE).
            </p>
        </div>

        @if ($isAdmin)
            <form action="{{ route('backoffice.regression.generate') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="shrink-0 rounded-full bg-[#1d1d1f] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    &#8635; Hitung Ulang Regresi Linear
                </button>
            </form>
        @endif
    </div>

    <!-- 4 Metrik Evaluasi Model -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Rata-rata R&sup2; (Fast-Moving)</p>
            <p class="mt-2 text-2xl font-extrabold text-[#0071e3]">{{ number_format($avgFastR2, 4) }}</p>
            <p class="mt-1 text-[11px] text-emerald-700">Memenuhi syarat R&sup2; &ge; 0.70</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Rata-rata MAE (Uji 20%)</p>
            <p class="mt-2 text-2xl font-extrabold text-[#1d1d1f]">{{ number_format($avgFastMae, 2) }}</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Mean Absolute Error (Bulan 11&ndash;12)</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Rata-rata RMSE (Uji 20%)</p>
            <p class="mt-2 text-2xl font-extrabold text-[#1d1d1f]">{{ number_format($avgFastRmse, 2) }}</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Root Mean Squared Error</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Total Rekomendasi Restock</p>
            <p class="mt-2 text-2xl font-extrabold text-amber-700">{{ number_format($totalRestockUnits, 0, ',', '.') }} unit</p>
            <p class="mt-1 text-[11px] text-[#86868b]">Seluruh produk yang perlu ditambah</p>
        </div>
    </div>

    <!-- Grafik Detail Per Produk & Tabel (pergantian instan tanpa reload) -->
    <div x-data="regressionPage(@js((object) $regressionData), {{ (int) ($selectedProduct?->id ?? 0) }})">
        @if ($selectedProduct && $detail)
            <div x-ref="chartCard" class="mt-6 scroll-mt-24 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                    <div class="min-w-0">
                        <span class="rounded-full bg-[#f5f5f7] px-3 py-1 text-[11px] font-semibold text-[#1d1d1f]">
                            <span x-text="current.category">{{ $selectedProduct->category?->name }}</span> &bull;
                            <span x-text="current.fast ? 'Fast-Moving' : 'Slow-Moving'">{{ $selectedProduct->isFastMoving() ? 'Fast-Moving' : 'Slow-Moving' }}</span>
                        </span>
                        <h3 class="mt-2 text-lg font-extrabold text-[#1d1d1f]">
                            Kurva Regresi Linear: <span x-text="current.name">{{ $selectedProduct->name }}</span>
                        </h3>
                        <p class="text-xs text-[#86868b]">
                            Persamaan: <strong class="font-mono text-[#1d1d1f]" x-text="current.equation">{{ $detail['equation'] }}</strong>
                        </p>
                    </div>

                    <form method="GET" action="{{ route('backoffice.regression.index') }}" class="flex items-center gap-2" @submit.prevent>
                        <select
                            name="product_id"
                            x-model.number="selectedId"
                            @change="select(selectedId, false)"
                            class="max-w-xs rounded-full border-gray-300 bg-[#f5f5f7] py-2 pl-4 pr-8 text-xs font-semibold text-[#1d1d1f] focus:border-[#0071e3] focus:ring-[#0071e3]"
                        >
                            @foreach ($predictions as $p)
                                <option value="{{ $p->product_id }}" @selected($p->product_id === $selectedProduct->id)>
                                    {{ $p->product?->name }} (R²={{ number_format($p->r_squared, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- Kartu Metrik Produk Terpilih -->
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-2xl bg-[#f5f5f7] p-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#86868b]">R&sup2;</p>
                        <p class="mt-1 text-lg font-extrabold" :class="current.r_squared >= 0.70 ? 'text-emerald-700' : 'text-[#86868b]'" x-text="current.r_squared.toFixed(4)">{{ number_format($detail['r_squared'], 4) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f5f7] p-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#86868b]">MAE</p>
                        <p class="mt-1 text-lg font-extrabold text-[#1d1d1f]" x-text="current.mae.toFixed(2)">{{ number_format($detail['mae'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f5f7] p-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#86868b]">RMSE</p>
                        <p class="mt-1 text-lg font-extrabold text-[#1d1d1f]" x-text="current.rmse.toFixed(2)">{{ number_format($detail['rmse'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#0071e3]/10 p-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#0071e3]">Prediksi Bln ke-13</p>
                        <p class="mt-1 text-lg font-extrabold text-[#0071e3]"><span x-text="current.predicted.toFixed(1)">{{ number_format($detail['predicted_demand'], 1) }}</span> <span class="text-xs font-semibold" x-text="current.unit">{{ $selectedProduct->unit }}</span></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f5f7] p-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#86868b]">Stok Kini</p>
                        <p class="mt-1 text-lg font-extrabold text-[#1d1d1f]"><span x-text="current.stock">{{ $selectedProduct->current_stock }}</span> <span class="text-xs font-semibold" x-text="current.unit">{{ $selectedProduct->unit }}</span></p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-3.5 ring-1 ring-amber-200">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-800">Saran Restock</p>
                        <p class="mt-1 text-lg font-extrabold text-amber-800" x-text="current.restock > 0 ? '+' + current.restock + ' ' + current.unit : 'Stok Cukup'">{{ $detail['restock_recommendation'] > 0 ? '+'.$detail['restock_recommendation'].' '.$selectedProduct->unit : 'Stok Cukup' }}</p>
                    </div>
                </div>

                <div class="mt-5 h-72">
                    <canvas id="regressionChart"></canvas>
                </div>
            </div>
        @endif

        <!-- Tabel Hasil Prediksi 30 Produk -->
        <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-[#1d1d1f]">Tabel Hasil Regresi Linear Seluruh Produk ({{ $predictions->count() }})</h3>
                <a href="{{ route('backoffice.reports.inventory.csv') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">
                    Unduh CSV Prediksi &rarr;
                </a>
            </div>
            <p class="mt-1 text-[11px] text-[#86868b]">Klik baris produk untuk menampilkan kurva regresinya secara instan.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Tipe</th>
                            <th class="pb-3">Persamaan OLS</th>
                            <th class="pb-3 text-right">R&sup2;</th>
                            <th class="pb-3 text-right">MAE</th>
                            <th class="pb-3 text-right">RMSE</th>
                            <th class="pb-3 text-right">Prediksi (X=13)</th>
                            <th class="pb-3 text-right">Stok Kini</th>
                            <th class="pb-3 text-right">Saran Restock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach ($predictions as $row)
                            @php $prod = $row->product; @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-[#f5f5f7]/60"
                                :class="selectedId === {{ (int) $row->product_id }} ? 'bg-[#0071e3]/[0.06]' : ''"
                                @click="select({{ (int) $row->product_id }})"
                            >
                                <td class="py-3">
                                    <a href="{{ route('backoffice.regression.index', ['product_id' => $row->product_id]) }}" @click.prevent class="font-bold text-[#1d1d1f] hover:text-[#0071e3]">
                                        {{ $prod?->name }}
                                    </a>
                                    <p class="text-[11px] text-[#86868b]">{{ $prod?->sku }} &bull; {{ $prod?->category?->name }}</p>
                                </td>
                                <td class="py-3">
                                    @if ($prod?->isFastMoving())
                                        <span class="rounded-full bg-[#1d1d1f] px-2.5 py-0.5 text-[10px] font-semibold text-white">Fast-Moving</span>
                                    @else
                                        <span class="rounded-full bg-[#f5f5f7] px-2.5 py-0.5 text-[10px] font-semibold text-[#86868b]">Slow-Moving</span>
                                    @endif
                                </td>
                                <td class="py-3 font-mono text-[11px] text-[#1d1d1f]">{{ $row->regression_equation }}</td>
                                <td class="py-3 text-right font-bold {{ $row->r_squared >= 0.70 ? 'text-emerald-700' : 'text-[#86868b]' }}">
                                    {{ number_format($row->r_squared, 3) }}
                                </td>
                                <td class="py-3 text-right text-[#86868b]">{{ number_format($row->mae, 2) }}</td>
                                <td class="py-3 text-right text-[#86868b]">{{ number_format($row->rmse, 2) }}</td>
                                <td class="py-3 text-right font-bold text-[#0071e3]">
                                    {{ number_format($row->predicted_demand, 1) }} {{ $prod?->unit }}
                                </td>
                                <td class="py-3 text-right font-semibold {{ $prod?->isCritical() ? 'text-rose-600' : 'text-[#1d1d1f]' }}">
                                    {{ $prod?->current_stock }} {{ $prod?->unit }}
                                </td>
                                <td class="py-3 text-right">
                                    @if ($row->restock_recommendation > 0)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 font-bold text-amber-800 ring-1 ring-amber-200">
                                            +{{ $row->restock_recommendation }} {{ $prod?->unit }}
                                        </span>
                                    @else
                                        <span class="text-[#86868b]">Stok Cukup</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        window.regressionPage = function (data, initialId) {
            const labels = [...@js($monthLabels), @js($targetLabel . ' (Prediksi)')];
            let chart = null; // Disimpan di luar proxy reaktif Alpine agar Chart.js tetap stabil.

            const blank = { name: '-', category: '-', fast: false, equation: '-', r_squared: 0, mae: 0, rmse: 0, predicted: 0, stock: 0, unit: '', restock: 0, actual: [], fitted: [] };

            return {
                data,
                selectedId: initialId,
                get current() {
                    return this.data[this.selectedId] || blank;
                },
                datasets(p) {
                    return {
                        actual: [...p.actual, null],
                        fitted: [...p.fitted, p.predicted],
                    };
                },
                init() {
                    const canvas = document.getElementById('regressionChart');
                    if (!canvas || typeof Chart === 'undefined' || !this.data[this.selectedId]) return;
                    const ds = this.datasets(this.current);
                    chart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [
                                { label: 'Penjualan Aktual (X=1..12)', data: ds.actual, borderColor: '#1d1d1f', backgroundColor: '#1d1d1f', tension: 0.2, pointRadius: 4 },
                                { label: 'Garis Regresi OLS & Prediksi Bulan 13', data: ds.fitted, borderColor: '#0071e3', backgroundColor: '#0071e3', borderDash: [5, 5], pointRadius: 4 },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 550, easing: 'easeOutQuart' },
                            scales: { y: { beginAtZero: true } },
                        },
                    });
                },
                select(id, scroll = true) {
                    if (!this.data[id]) return;
                    this.selectedId = id;
                    const url = new URL(window.location.href);
                    url.searchParams.set('product_id', id);
                    window.history.replaceState({}, '', url);
                    if (chart) {
                        const ds = this.datasets(this.data[id]);
                        chart.data.datasets[0].data = ds.actual;
                        chart.data.datasets[1].data = ds.fitted;
                        chart.update();
                    }
                    if (scroll && this.$refs.chartCard) {
                        this.$refs.chartCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                },
            };
        };
    </script>
</x-admin-layout>
