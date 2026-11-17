<x-admin-layout title="Kategori Produk" heading="Manajemen Kategori Produk">
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Form Tambah Kategori -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
            <h2 class="text-base font-bold text-[#1d1d1f]">Tambah Kategori Baru</h2>
            <p class="mt-1 text-xs text-[#86868b]">Folder lokal <code class="rounded bg-[#f5f5f7] px-1.5 py-0.5">public/assets/products/&lt;slug&gt;</code> otomatis disiapkan.</p>

            <form action="{{ route('backoffice.categories.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Nama Kategori</label>
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Contoh: Produk Segar"
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs focus:border-[#0071e3] focus:ring-[#0071e3]"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Deskripsi</label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Deskripsi singkat kategori..."
                        class="mt-1 w-full rounded-2xl border-gray-300 text-xs focus:border-[#0071e3] focus:ring-[#0071e3]"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="w-full rounded-full bg-[#1d1d1f] py-2.5 text-xs font-semibold text-white transition hover:bg-[#0071e3]"
                >
                    + Simpan Kategori
                </button>
            </form>
        </div>

        <!-- Daftar Kategori -->
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:col-span-2">
            <h2 class="text-base font-bold text-[#1d1d1f]">Daftar Kategori ({{ $categories->count() }})</h2>

            <div class="mt-4 divide-y divide-black/[0.05]">
                @foreach ($categories as $category)
                    <div x-data="{ editing: false }" class="py-4">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-[#1d1d1f]">{{ $category->name }}</h3>
                                    <span class="rounded-full bg-[#f5f5f7] px-2.5 py-0.5 font-mono text-[10px] text-[#86868b]">
                                        assets/products/{{ $category->slug }}/
                                    </span>
                                    <span class="rounded-full bg-[#0071e3]/10 px-2.5 py-0.5 text-[10px] font-bold text-[#0071e3]">
                                        {{ $category->products_count }} produk
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-[#86868b]">{{ $category->description }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="editing = !editing"
                                    class="rounded-full bg-[#f5f5f7] px-3.5 py-1.5 text-xs font-semibold text-[#1d1d1f] hover:bg-gray-200"
                                >
                                    Edit
                                </button>
                                @if ($category->products_count === 0)
                                    <form action="{{ route('backoffice.categories.destroy', $category) }}" method="POST" @submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Hapus Kategori Ini?', message: @js('Kategori '.$category->name.' akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.'), confirmLabel: 'Ya, Hapus Kategori' })">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <form
                            x-show="editing"
                            x-cloak
                            action="{{ route('backoffice.categories.update', $category) }}"
                            method="POST"
                            class="mt-3 grid gap-3 rounded-2xl bg-[#f5f5f7] p-4 sm:grid-cols-3"
                        >
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" required class="rounded-xl border-gray-300 text-xs" />
                            <input type="text" name="description" value="{{ $category->description }}" class="rounded-xl border-gray-300 text-xs" />
                            <button type="submit" class="rounded-full bg-[#1d1d1f] py-2 text-xs font-semibold text-white">Simpan Perubahan</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
