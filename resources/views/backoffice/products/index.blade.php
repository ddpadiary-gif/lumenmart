<x-admin-layout title="Katalog Produk" heading="Manajemen Katalog Produk">
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:flex-row lg:items-center">
        <form method="GET" action="{{ route('backoffice.products.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama produk atau SKU..."
                class="w-full rounded-full border-gray-300 bg-[#f5f5f7] px-4 py-2 text-xs sm:w-64"
            />
            <select name="category_id" class="rounded-full border-gray-300 bg-[#f5f5f7] py-2 pl-3 pr-8 text-xs">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="movement_type" class="rounded-full border-gray-300 bg-[#f5f5f7] py-2 pl-3 pr-8 text-xs">
                <option value="">Semua Movement</option>
                <option value="fast_moving" @selected(request('movement_type') === 'fast_moving')>Fast-Moving</option>
                <option value="slow_moving" @selected(request('movement_type') === 'slow_moving')>Slow-Moving</option>
            </select>
            <label class="inline-flex items-center gap-1.5 text-xs text-[#1d1d1f]">
                <input type="checkbox" name="critical" value="1" @checked(request()->boolean('critical')) class="rounded text-[#0071e3]" />
                <span>Stok Kritis</span>
            </label>
            <button type="submit" class="rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white">Filter</button>
        </form>

        <a
            href="{{ route('backoffice.products.create') }}"
            class="shrink-0 rounded-full bg-[#0071e3] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#1d1d1f]"
        >
            + Tambah Produk Baru
        </a>
    </div>

    <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                        <th class="pb-3">Visual</th>
                        <th class="pb-3">Produk &amp; Path Gambar</th>
                        <th class="pb-3">Kategori</th>
                        <th class="pb-3 text-right">Harga Beli</th>
                        <th class="pb-3 text-right">Harga Jual</th>
                        <th class="pb-3 text-right">Stok</th>
                        <th class="pb-3">Movement</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @foreach ($products as $product)
                        <tr class="hover:bg-[#f5f5f7]/60">
                            <td class="py-3">
                                <div class="h-14 w-16 overflow-hidden rounded-2xl bg-[#f5f5f7]">
                                    <x-product-visual :product="$product" />
                                </div>
                            </td>
                            <td class="py-3">
                                <p class="font-bold text-[#1d1d1f]">{{ $product->name }}</p>
                                <p class="font-mono text-[10px] text-[#86868b]">{{ $product->sku }} &bull; {{ $product->image_path }}</p>
                            </td>
                            <td class="py-3 text-[#86868b]">{{ $product->category?->name }}</td>
                            <td class="py-3 text-right text-[#86868b]">{{ rupiah($product->purchase_price) }}</td>
                            <td class="py-3 text-right font-bold text-[#1d1d1f]">{{ rupiah($product->selling_price) }}</td>
                            <td class="py-3 text-right">
                                <span class="font-bold {{ $product->isCritical() ? 'text-rose-600' : 'text-[#1d1d1f]' }}">
                                    {{ $product->current_stock }} {{ $product->unit }}
                                </span>
                                <p class="text-[10px] text-[#86868b]">Min {{ $product->min_stock_threshold }}</p>
                            </td>
                            <td class="py-3">
                                @if ($product->isFastMoving())
                                    <span class="rounded-full bg-[#1d1d1f] px-2.5 py-0.5 text-[10px] font-semibold text-white">Fast-Moving</span>
                                @else
                                    <span class="rounded-full bg-[#f5f5f7] px-2.5 py-0.5 text-[10px] font-semibold text-[#86868b]">Slow-Moving</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('backoffice.incoming.index', ['product_id' => $product->id]) }}"
                                        class="rounded-full bg-[#0071e3]/10 px-3 py-1 text-[11px] font-semibold text-[#0071e3] hover:bg-[#0071e3] hover:text-white"
                                    >
                                        Restock
                                    </a>
                                    <a
                                        href="{{ route('backoffice.products.edit', $product) }}"
                                        class="rounded-full bg-[#f5f5f7] px-3 py-1 text-[11px] font-semibold text-[#1d1d1f] hover:bg-gray-200"
                                    >
                                        Edit
                                    </a>
                                    <form action="{{ route('backoffice.products.destroy', $product) }}" method="POST" @submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Hapus Produk Ini?', message: @js('Produk '.$product->name.' akan dihapus atau dinonaktifkan dari katalog. Riwayat transaksi tetap tersimpan untuk analitik.'), confirmLabel: 'Ya, Hapus Produk' })">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</x-admin-layout>
