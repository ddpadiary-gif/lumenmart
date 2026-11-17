<x-admin-layout title="POS Kasir Multi-Item" heading="POS Kasir Toko Multi-Item">
    <div
        x-data="{
            q: '',
            category: 'all',
            products: @js($products),
            rules: @js($rules),
            cart: [],
            paymentMethod: 'tunai',
            cashReceived: 0,
            filteredProducts() {
                return this.products.filter(p => {
                    if (this.category !== 'all' && p.category_slug !== this.category) return false;
                    if (!this.q) return true;
                    const t = this.q.toLowerCase();
                    return p.name.toLowerCase().includes(t) || p.sku.toLowerCase().includes(t);
                });
            },
            addItem(product) {
                if (product.stock < 1) return;
                const existing = this.cart.find(c => c.id === product.id);
                if (existing) {
                    if (existing.qty < product.stock) existing.qty++;
                } else {
                    this.cart.push({ ...product, qty: 1 });
                }
            },
            removeItem(id) {
                this.cart = this.cart.filter(c => c.id !== id);
            },
            subtotal() {
                return this.cart.reduce((s, c) => s + c.price * c.qty, 0);
            },
            bundleCalc() {
                const used = {};
                const bundles = [];
                let discount = 0;
                const byId = Object.fromEntries(this.cart.map(c => [c.id, c]));

                for (const r of this.rules) {
                    const ids = r.product_ids;
                    if (ids.length < 2) continue;
                    let ok = true;
                    for (const id of ids) {
                        if (!byId[id] || byId[id].qty < 1 || used[id]) { ok = false; break; }
                    }
                    if (!ok) continue;
                    const sets = Math.min(...ids.map(id => byId[id].qty));
                    const setPrice = ids.reduce((s, id) => s + byId[id].price, 0);
                    const amt = Math.round(setPrice * sets * r.percent / 100);
                    ids.forEach(id => used[id] = true);
                    discount += amt;
                    bundles.push({ label: r.label, percent: r.percent, sets, amount: amt });
                }
                return { discount, bundles };
            },
            total() {
                return Math.max(0, this.subtotal() - this.bundleCalc().discount);
            },
            changeAmount() {
                return Math.max(0, (Number(this.cashReceived) || 0) - this.total());
            },
            rp(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val));
            }
        }"
        class="grid gap-6 lg:grid-cols-3"
    >
        <!-- Area Pemilihan Produk -->
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-3xl bg-white p-5 shadow-2xs ring-1 ring-black/[0.04]">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        x-model="q"
                        type="search"
                        placeholder="Scan / ketik nama produk atau SKU..."
                        class="flex-1 rounded-full border-gray-300 bg-[#f5f5f7] px-4 py-2.5 text-xs focus:border-[#0071e3] focus:ring-[#0071e3]"
                    />
                    <select x-model="category" class="rounded-full border-gray-300 bg-[#f5f5f7] py-2.5 pl-4 pr-8 text-xs font-semibold">
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Grid Produk Kasir -->
                <div class="mt-4 grid max-h-[34rem] grid-cols-2 gap-3 overflow-y-auto pr-1 sm:grid-cols-3">
                    <template x-for="p in filteredProducts()" :key="p.id">
                        <button
                            type="button"
                            @click="addItem(p)"
                            :disabled="p.stock < 1"
                            class="flex flex-col justify-between rounded-2xl bg-[#f5f5f7] p-3.5 text-left transition hover:bg-[#1d1d1f] hover:text-white disabled:cursor-not-allowed disabled:opacity-50 group"
                        >
                            <div>
                                <div class="flex items-center justify-between text-[10px] opacity-70">
                                    <span class="font-mono" x-text="p.sku"></span>
                                    <span>Stok: <strong x-text="p.stock"></strong></span>
                                </div>
                                <p class="mt-1 line-clamp-2 text-xs font-bold" x-text="p.name"></p>
                            </div>
                            <p class="mt-3 text-sm font-extrabold text-[#0071e3] group-hover:text-white" x-text="rp(p.price)"></p>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Riwayat POS Terbaru -->
            <div class="rounded-3xl bg-white p-5 shadow-2xs ring-1 ring-black/[0.04]">
                <h3 class="text-sm font-bold text-[#1d1d1f]">Transaksi POS Terbaru</h3>
                <div class="mt-3 divide-y divide-black/[0.05] text-xs">
                    @foreach ($recentPos as $pos)
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <span class="font-mono font-bold text-[#1d1d1f]">{{ $pos->invoice_number }}</span>
                                <span class="ml-2 text-[#86868b]">{{ $pos->details->sum('quantity') }} item &bull; {{ $pos->paymentLabel() }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-[#1d1d1f]">{{ rupiah($pos->total_amount) }}</span>
                                <a href="{{ route('invoices.pdf', $pos) }}" class="rounded-full bg-[#f5f5f7] px-3 py-1 text-[11px] font-semibold text-[#0071e3] hover:bg-gray-200">
                                    Struk PDF
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Panel Keranjang Kasir -->
        <div>
            <form action="{{ route('backoffice.pos.store') }}" method="POST" class="sticky top-24 rounded-3xl bg-white p-6 shadow-[0_8px_30px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05]">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-[#1d1d1f]">Keranjang Kasir</h2>
                    <button
                        type="button"
                        x-show="cart.length > 0"
                        @click="cart = []"
                        class="text-xs font-semibold text-rose-600 hover:underline"
                    >
                        Kosongkan
                    </button>
                </div>

                <div x-show="cart.length === 0" class="my-8 rounded-2xl bg-[#f5f5f7] p-6 text-center text-xs text-[#86868b]">
                    Klik produk di sebelah kiri untuk menambahkan ke struk kasir.
                </div>

                <div x-show="cart.length > 0" class="mt-4 max-h-64 space-y-2.5 overflow-y-auto pr-1">
                    <template x-for="(item, idx) in cart" :key="item.id">
                        <div class="flex items-center justify-between gap-2 rounded-2xl bg-[#f5f5f7] p-3 text-xs">
                            <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.id">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-[#1d1d1f]" x-text="item.name"></p>
                                <p class="text-[11px] text-[#86868b]" x-text="rp(item.price) + ' / ' + item.unit"></p>
                            </div>
                            <input
                                type="number"
                                :name="`items[${idx}][quantity]`"
                                x-model.number="item.qty"
                                min="1"
                                :max="item.stock"
                                class="w-16 rounded-xl border-gray-300 py-1 text-center text-xs font-bold"
                            />
                            <button type="button" @click="removeItem(item.id)" class="text-rose-600 hover:font-bold">&#10005;</button>
                        </div>
                    </template>
                </div>

                <!-- Perhitungan Smart Bundle & Total -->
                <div class="mt-5 space-y-2 border-t border-black/[0.06] pt-4 text-xs">
                    <div class="flex justify-between text-[#86868b]">
                        <span>Subtotal</span>
                        <span class="font-semibold text-[#1d1d1f]" x-text="rp(subtotal())"></span>
                    </div>

                    <template x-if="bundleCalc().discount > 0">
                        <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-800 ring-1 ring-emerald-200">
                            <div class="flex justify-between font-bold">
                                <span>Diskon Smart Bundle</span>
                                <span x-text="'-' + rp(bundleCalc().discount)"></span>
                            </div>
                        </div>
                    </template>

                    <div class="flex items-center justify-between border-t border-black/[0.06] pt-3 text-sm">
                        <span class="font-bold text-[#1d1d1f]">Total Tagihan</span>
                        <span class="text-xl font-extrabold text-[#1d1d1f]" x-text="rp(total())"></span>
                    </div>
                </div>

                <!-- Metode Bayar -->
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-[#1d1d1f]">Metode Pembayaran</label>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                        <label
                            @click="paymentMethod = 'tunai'"
                            :class="paymentMethod === 'tunai' ? 'bg-[#1d1d1f] text-white' : 'bg-[#f5f5f7] text-[#1d1d1f]'"
                            class="cursor-pointer rounded-full py-2 text-center font-semibold transition"
                        >
                            <input type="radio" name="payment_method" value="tunai" x-model="paymentMethod" class="sr-only">
                            Tunai (Cash)
                        </label>
                        <label
                            @click="paymentMethod = 'qris'"
                            :class="paymentMethod === 'qris' ? 'bg-[#0071e3] text-white' : 'bg-[#f5f5f7] text-[#1d1d1f]'"
                            class="cursor-pointer rounded-full py-2 text-center font-semibold transition"
                        >
                            <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                            QRIS Toko
                        </label>
                    </div>
                </div>

                <div x-show="paymentMethod === 'tunai'" class="mt-4 grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-[11px] text-[#86868b]">Uang Diterima (Rp)</label>
                        <input type="number" x-model.number="cashReceived" class="mt-1 w-full rounded-xl border-gray-300 py-1.5 text-xs font-bold" />
                    </div>
                    <div>
                        <label class="block text-[11px] text-[#86868b]">Kembalian</label>
                        <p class="mt-2 text-sm font-extrabold text-emerald-700" x-text="rp(changeAmount())"></p>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="cart.length === 0"
                    class="mt-6 w-full rounded-full bg-[#0071e3] py-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d1d1f] disabled:cursor-not-allowed disabled:bg-gray-300"
                >
                    Selesaikan &amp; Simpan Transaksi POS
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
