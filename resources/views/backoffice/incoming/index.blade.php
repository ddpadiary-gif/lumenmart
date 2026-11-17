<x-admin-layout title="Barang Masuk (Restock)" heading="Transaksi Barang Masuk (Restock)">
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Form Restock -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <h2 class="text-base font-bold text-[#1d1d1f]">Catat Barang Masuk (Restock)</h2>
            <p class="mt-1 text-xs text-[#86868b]">Stok produk otomatis bertambah setelah transaksi disimpan.</p>

            <form action="{{ route('backoffice.incoming.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Pilih Produk</label>
                    <select name="product_id" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs">
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(old('product_id', $selectedProductId) == $p->id)>
                                {{ $p->isCritical() ? '[KRITIS] ' : '' }}{{ $p->name }} (Stok: {{ $p->current_stock }} {{ $p->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#1d1d1f]">Jumlah Masuk</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 25) }}" min="1" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#1d1d1f]">Harga Beli / Unit (Rp)</label>
                        <input type="number" step="1" min="0" name="unit_cost" value="{{ old('unit_cost', 15000) }}" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Tanggal Masuk</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Nama Pemasok (Supplier)</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name', 'PT Sumber Pangan Nusantara') }}" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes', 'Restock gudang utama') }}" class="mt-1 w-full rounded-2xl border-gray-300 text-xs" />
                </div>

                <button type="submit" class="w-full rounded-full bg-[#1d1d1f] py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3]">
                    + Simpan Transaksi Barang Masuk
                </button>
            </form>
        </div>

        <!-- Riwayat Barang Masuk -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:col-span-2">
            <h2 class="text-base font-bold text-[#1d1d1f]">Riwayat Barang Masuk Terbaru</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3 text-right">Qty</th>
                            <th class="pb-3 text-right">Harga Beli</th>
                            <th class="pb-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach ($transactions as $row)
                            <tr>
                                <td class="py-3 text-[#86868b]">{{ $row->transaction_date->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    <p class="font-bold text-[#1d1d1f]">{{ $row->product?->name }}</p>
                                    <p class="text-[11px] text-[#86868b]">{{ $row->notes }}</p>
                                </td>
                                <td class="py-3 text-[#86868b]">{{ $row->supplier_name }}</td>
                                <td class="py-3 text-right font-bold text-emerald-700">+{{ $row->quantity }} {{ $row->product?->unit }}</td>
                                <td class="py-3 text-right text-[#86868b]">{{ rupiah($row->unit_cost) }}</td>
                                <td class="py-3 text-right font-bold text-[#1d1d1f]">{{ rupiah($row->totalCost()) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
