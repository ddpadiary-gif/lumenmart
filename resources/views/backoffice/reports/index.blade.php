<x-admin-layout title="Laporan Penjualan & Stok" heading="Laporan Penjualan, Inventori & Ekspor (CSV / PDF)">
    <!-- Filter & Tombol Ekspor -->
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:flex-row lg:items-end">
        <form method="GET" action="{{ route('backoffice.reports.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-[#86868b]">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 rounded-full border-gray-300 bg-[#f5f5f7] px-3.5 py-1.5 text-xs" />
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-[#86868b]">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 rounded-full border-gray-300 bg-[#f5f5f7] px-3.5 py-1.5 text-xs" />
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-[#86868b]">Channel</label>
                <select name="channel" class="mt-1 rounded-full border-gray-300 bg-[#f5f5f7] py-1.5 pl-3.5 pr-8 text-xs">
                    <option value="">Semua Channel</option>
                    <option value="pos_toko" @selected($channel === 'pos_toko')>POS Toko</option>
                    <option value="ecommerce" @selected($channel === 'ecommerce')>E-Commerce</option>
                </select>
            </div>
            <button type="submit" class="rounded-full bg-[#1d1d1f] px-5 py-2 text-xs font-semibold text-white hover:bg-[#0071e3]">
                Terapkan Filter
            </button>
        </form>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('backoffice.reports.sales.csv', ['start_date' => $startDate, 'end_date' => $endDate, 'channel' => $channel]) }}"
                class="rounded-full bg-[#f5f5f7] px-4 py-2 text-xs font-semibold text-[#1d1d1f] ring-1 ring-black/[0.08] hover:bg-gray-200"
            >
                &darr; CSV Penjualan
            </a>
            <a
                href="{{ route('backoffice.reports.inventory.csv') }}"
                class="rounded-full bg-[#f5f5f7] px-4 py-2 text-xs font-semibold text-[#1d1d1f] ring-1 ring-black/[0.08] hover:bg-gray-200"
            >
                &darr; CSV Stok &amp; Prediksi
            </a>
            <a
                href="{{ route('backoffice.reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'channel' => $channel]) }}"
                class="rounded-full bg-[#0071e3] px-5 py-2 text-xs font-semibold text-white hover:bg-[#1d1d1f]"
            >
                &darr; Ekspor Laporan PDF
            </a>
        </div>
    </div>

    <!-- Ringkasan Periode -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase text-[#86868b]">Omzet Periode Terpilih</p>
            <p class="mt-1 text-xl font-extrabold text-[#1d1d1f]">{{ rupiah($totalRevenue) }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase text-[#86868b]">Total Diskon Smart Bundle</p>
            <p class="mt-1 text-xl font-extrabold text-emerald-700">{{ rupiah($totalDiscount) }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase text-[#86868b]">Jumlah Transaksi</p>
            <p class="mt-1 text-xl font-extrabold text-[#1d1d1f]">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/[0.04]">
            <p class="text-[11px] font-semibold uppercase text-[#86868b]">Nilai Aset Stok Gudang</p>
            <p class="mt-1 text-xl font-extrabold text-[#0071e3]">{{ rupiah($inventoryValue) }}</p>
        </div>
    </div>

    <!-- Tabel Transaksi Penjualan -->
    <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
        <h3 class="text-base font-bold text-[#1d1d1f]">Rincian Transaksi Penjualan</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                        <th class="pb-3">Invoice</th>
                        <th class="pb-3">Tanggal</th>
                        <th class="pb-3">Channel</th>
                        <th class="pb-3">Pelanggan / Kasir</th>
                        <th class="pb-3">Pembayaran</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right">Diskon</th>
                        <th class="pb-3 text-right">Total</th>
                        <th class="pb-3 text-right">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @foreach ($transactions as $tx)
                        <tr>
                            <td class="py-3 font-mono font-bold text-[#1d1d1f]">{{ $tx->invoice_number }}</td>
                            <td class="py-3 text-[#86868b]">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 text-[#86868b]">{{ $tx->channelLabel() }}</td>
                            <td class="py-3 text-[#1d1d1f]">{{ $tx->user?->name }}</td>
                            <td class="py-3 text-[#86868b]">{{ $tx->paymentLabel() }}</td>
                            <td class="py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold ring-1 {{ $tx->statusBadgeClass() }}">
                                    {{ $tx->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-3 text-right text-emerald-700">{{ rupiah($tx->discount_amount) }}</td>
                            <td class="py-3 text-right font-bold text-[#1d1d1f]">{{ rupiah($tx->total_amount) }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('invoices.pdf', $tx) }}" class="font-semibold text-[#0071e3] hover:underline">Unduh</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</x-admin-layout>
