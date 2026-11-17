<x-admin-layout :title="$product->exists ? 'Edit Produk' : 'Tambah Produk'" :heading="$product->exists ? 'Edit Produk: ' . $product->name : 'Tambah Produk Baru'">
    <div class="mx-auto max-w-4xl rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] sm:p-8">
        <div class="flex items-center justify-between border-b border-black/[0.06] pb-4">
            <div>
                <h2 class="text-lg font-extrabold text-[#1d1d1f]">
                    {{ $product->exists ? 'Perbarui Data & Gambar Produk' : 'Formulir Produk Baru LUMEN MART' }}
                </h2>
                <p class="text-xs text-[#86868b]">
                    Gambar disimpan ke folder lokal <code class="rounded bg-[#f5f5f7] px-1.5 py-0.5">public/assets/products/&lt;slug-kategori&gt;/</code>
                </p>
            </div>
            <a href="{{ route('backoffice.products.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">
                &larr; Kembali
            </a>
        </div>

        <form
            action="{{ $product->exists ? route('backoffice.products.update', $product) : route('backoffice.products.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="mt-6 space-y-5"
        >
            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Kategori Produk</label>
                    <select name="category_id" required class="mt-1 w-full rounded-2xl border-gray-300 text-xs">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>
                                {{ $cat->name }} (assets/products/{{ $cat->slug }}/)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Kode SKU</label>
                    <input
                        type="text"
                        name="sku"
                        value="{{ old('sku', $product->sku ?? 'LM-NEW-' . rand(100, 999)) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 font-mono text-xs"
                    />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Nama Produk</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Satuan (Unit)</label>
                    <input
                        type="text"
                        name="unit"
                        value="{{ old('unit', $product->unit) }}"
                        required
                        placeholder="pack / botol / kg"
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#1d1d1f]">Deskripsi Produk</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-2xl border-gray-300 text-xs">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Harga Beli (Rp)</label>
                    <input
                        type="number"
                        step="1"
                        min="0"
                        name="purchase_price"
                        value="{{ old('purchase_price', (int) $product->purchase_price) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Harga Jual (Rp)</label>
                    <input
                        type="number"
                        step="1"
                        min="0"
                        name="selling_price"
                        value="{{ old('selling_price', (int) $product->selling_price) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Stok Saat Ini</label>
                    <input
                        type="number"
                        min="0"
                        name="current_stock"
                        value="{{ old('current_stock', $product->current_stock) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Batas Stok Minimum</label>
                    <input
                        type="number"
                        min="0"
                        name="min_stock_threshold"
                        value="{{ old('min_stock_threshold', $product->min_stock_threshold) }}"
                        required
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs"
                    />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Klasifikasi Pergerakan (Movement)</label>
                    <select name="movement_type" class="mt-1 w-full rounded-2xl border-gray-300 text-xs">
                        <option value="fast_moving" @selected(old('movement_type', $product->movement_type) === 'fast_moving')>Fast-Moving (Cepat Laku)</option>
                        <option value="slow_moving" @selected(old('movement_type', $product->movement_type) === 'slow_moving')>Slow-Moving (Reguler)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Upload Foto Produk (.jpg / .png)</label>
                    <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-xs text-[#86868b] file:mr-3 file:rounded-full file:border-0 file:bg-[#1d1d1f] file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-white" />
                    @if ($product->image_path)
                        <p class="mt-1 font-mono text-[11px] text-[#86868b]">Path: {{ $product->image_path }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-black/[0.06] pt-5">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-[#1d1d1f]">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded text-[#0071e3]" />
                    <span>Tampilkan di Katalog Aktif</span>
                </label>

                <button
                    type="submit"
                    class="rounded-full bg-[#1d1d1f] px-7 py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
