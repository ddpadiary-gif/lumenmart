<x-admin-layout :title="$product->exists ? 'Edit Produk' : 'Tambah Produk'" :heading="$product->exists ? 'Edit Produk: ' . $product->name : 'Tambah Produk Baru'">
    <div class="mx-auto max-w-4xl rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] sm:p-8">
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

                <div
                    x-data="{
                        preview: @js($product->exists ? $product->imageUrl() : null),
                        fileName: '',
                        dragging: false,
                        error: '',
                        pick(files) {
                            const file = files && files[0];
                            if (!file) return;
                            if (!file.type.startsWith('image/')) { this.error = 'File harus berupa gambar (.jpg / .png / .webp).'; return; }
                            this.error = '';
                            if (this.preview && this.preview.startsWith('blob:')) URL.revokeObjectURL(this.preview);
                            this.preview = URL.createObjectURL(file);
                            this.fileName = file.name + ' • ' + (file.size / 1024).toFixed(0) + ' KB';
                        },
                        drop(e) {
                            this.dragging = false;
                            if (!e.dataTransfer.files.length) return;
                            this.$refs.file.files = e.dataTransfer.files;
                            this.pick(e.dataTransfer.files);
                        }
                    }"
                >
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Foto Produk (.jpg / .png)</label>
                    <div
                        @click="$refs.file.click()"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="drop($event)"
                        :class="dragging ? 'border-[#0071e3] bg-[#0071e3]/5' : 'border-black/[0.12] bg-[#f5f5f7] hover:border-[#0071e3]/50'"
                        class="mt-1 flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-dashed p-3 transition"
                    >
                        <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-white ring-1 ring-black/[0.06]">
                            <template x-if="preview">
                                <img :src="preview" alt="Pratinjau foto produk" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <div class="flex h-full w-full items-center justify-center text-[#86868b]">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21zm9.75-12.75h.008v.008H13.5V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                </div>
                            </template>
                        </div>
                        <div class="min-w-0 text-xs">
                            <p class="font-semibold text-[#1d1d1f]" x-text="fileName ? 'Foto baru dipilih' : (preview ? 'Foto saat ini' : 'Belum ada foto')"></p>
                            <p class="mt-0.5 truncate text-[11px] text-[#86868b]" x-text="fileName || 'Seret & lepas gambar ke sini, atau klik untuk memilih file.'"></p>
                            <span class="mt-2 inline-flex rounded-full bg-[#1d1d1f] px-3.5 py-1.5 text-[11px] font-semibold text-white">Pilih Foto</span>
                        </div>
                    </div>
                    <input x-ref="file" type="file" name="image" accept="image/*" class="sr-only" @change="pick($event.target.files)" />
                    <p x-show="error" x-text="error" class="mt-1 text-[11px] font-semibold text-rose-600"></p>
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
