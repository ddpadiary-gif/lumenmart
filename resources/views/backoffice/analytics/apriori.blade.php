<x-admin-layout title="Algoritma Apriori" heading="Market Basket Analysis & Bundling — Algoritma Apriori">
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <!-- Header & Form Parameter Apriori -->
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] lg:flex-row lg:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Frequent Itemset Mining (L1, L2, L3) &bull; Lift Ratio &gt; 1.0</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-[#1d1d1f]">
                Rekomendasi Paket Bundling &amp; Tata Letak Rak
            </h2>
            <p class="mt-1 text-xs text-[#86868b]">
                Dianalisis dari <strong>{{ number_format($totalTransactions, 0, ',', '.') }}</strong> keranjang transaksi. Aturan yang terbentuk langsung mengaktifkan <strong>Smart Bundle Discount</strong> di keranjang pelanggan dan POS kasir.
            </p>
        </div>

        @if ($isAdmin)
            <form action="{{ route('backoffice.apriori.generate') }}" method="POST" class="flex flex-wrap items-end gap-2.5">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase text-[#86868b]">Min Support (%)</label>
                    <input
                        type="number"
                        step="0.5"
                        min="0.5"
                        max="50"
                        name="min_support_percent"
                        value="3"
                        class="mt-1 w-24 rounded-full border-gray-300 bg-[#f5f5f7] py-1.5 text-center text-xs font-bold focus:border-[#0071e3] focus:ring-[#0071e3]"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-[#86868b]">Min Confidence (%)</label>
                    <input
                        type="number"
                        step="5"
                        min="10"
                        max="95"
                        name="min_confidence_percent"
                        value="40"
                        class="mt-1 w-28 rounded-full border-gray-300 bg-[#f5f5f7] py-1.5 text-center text-xs font-bold focus:border-[#0071e3] focus:ring-[#0071e3]"
                    />
                </div>
                <button
                    type="submit"
                    class="rounded-full bg-[#1d1d1f] px-5 py-2 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    &#8635; Eksekusi Apriori
                </button>
            </form>
        @endif
    </div>

    <!-- Grafik Lift Ratio & Confidence -->
    @if ($rules->isNotEmpty())
        <div class="mt-6 rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
            <h3 class="text-base font-bold text-[#1d1d1f]">Perbandingan Confidence (%) &amp; Lift Ratio Aturan Teratas</h3>
            <div class="mt-4 h-72">
                <canvas id="aprioriChart"></canvas>
            </div>
        </div>
    @endif

    <!-- Tabel Lengkap Aturan Apriori -->
    <div class="mt-6 rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04]">
        <h3 class="text-base font-bold text-[#1d1d1f]">Daftar Aturan Asosiasi ({{ $rules->count() }} Aturan dengan Lift &gt; 1.0)</h3>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                        <th class="pb-3">#</th>
                        <th class="pb-3">Jika Membeli (Antecedent)</th>
                        <th class="pb-3">Maka Membeli (Consequent)</th>
                        <th class="pb-3 text-right">Support</th>
                        <th class="pb-3 text-right">Confidence</th>
                        <th class="pb-3 text-right">Lift Ratio</th>
                        <th class="pb-3 text-center">Diskon</th>
                        <th class="pb-3">Rekomendasi Bundling</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @forelse ($rules as $i => $rule)
                        <tr class="hover:bg-[#f5f5f7]/60">
                            <td class="py-3 font-mono text-[#86868b]">{{ $i + 1 }}</td>
                            <td class="py-3 font-bold text-[#1d1d1f]">{{ $rule->antecedent_names }}</td>
                            <td class="py-3 font-semibold text-[#0071e3]">&rarr; {{ $rule->consequent_names }}</td>
                            <td class="py-3 text-right">{{ number_format($rule->support, 2) }}%</td>
                            <td class="py-3 text-right font-semibold text-[#1d1d1f]">{{ number_format($rule->confidence, 2) }}%</td>
                            <td class="py-3 text-right">
                                <span class="rounded-full bg-[#0071e3]/10 px-2.5 py-0.5 font-bold text-[#0071e3]">
                                    {{ number_format($rule->lift_ratio, 3) }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 font-bold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ $rule->bundle_discount_percent }}%
                                </span>
                            </td>
                            <td class="py-3 text-[#86868b]">{{ $rule->bundling_suggestion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[#86868b]">
                                Tidak ada aturan yang memenuhi ambang Support &amp; Confidence tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($rules->isNotEmpty())
        @php $topChart = $rules->take(10); @endphp
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const labels = @js($topChart->map(fn ($r) => $r->antecedent_names . ' → ' . $r->consequent_names)->values());
                const conf = @js($topChart->pluck('confidence')->values());
                const lift = @js($topChart->pluck('lift_ratio')->values());

                new Chart(document.getElementById('aprioriChart'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Confidence (%)',
                                data: conf,
                                backgroundColor: '#1d1d1f',
                                borderRadius: 6,
                            },
                            {
                                label: 'Lift Ratio',
                                data: lift,
                                type: 'line',
                                yAxisID: 'y1',
                                borderColor: '#0071e3',
                                backgroundColor: '#0071e3',
                                pointRadius: 4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Confidence (%)' } },
                            y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Lift Ratio' } },
                        },
                    },
                });
            });
        </script>
    @endif
</x-admin-layout>
