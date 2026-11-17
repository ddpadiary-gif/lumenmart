<x-admin-layout title="Pesanan E-Commerce" heading="Manajemen Status Pesanan E-Commerce">
    <!-- Filter Status -->
    <div class="flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04] lg:flex-row lg:items-center">
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('backoffice.orders.index') }}"
                class="rounded-full px-4 py-2 text-xs font-semibold {{ !request('status') ? 'bg-[#1d1d1f] text-white' : 'bg-[#f5f5f7] text-[#1d1d1f]' }}"
            >
                Semua ({{ array_sum($counts) }})
            </a>
            @foreach (\App\Models\SalesTransaction::STATUSES as $key => $label)
                <a
                    href="{{ route('backoffice.orders.index', ['status' => $key]) }}"
                    class="rounded-full px-4 py-2 text-xs font-semibold {{ request('status') === $key ? 'bg-[#0071e3] text-white' : 'bg-[#f5f5f7] text-[#1d1d1f]' }}"
                >
                    {{ $label }} ({{ $counts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('backoffice.orders.index') }}" class="flex items-center gap-2">
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari no. invoice / nama pelanggan..."
                class="rounded-full border-gray-300 bg-[#f5f5f7] px-4 py-2 text-xs"
            />
            <button type="submit" class="rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white">Cari</button>
        </form>
    </div>

    <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/[0.04]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] text-[11px] uppercase text-[#86868b]">
                        <th class="pb-3">Invoice &amp; Tanggal</th>
                        <th class="pb-3">Pelanggan &amp; Alamat</th>
                        <th class="pb-3">Metode</th>
                        <th class="pb-3 text-right">Total Bayar</th>
                        <th class="pb-3">Status Saat Ini</th>
                        <th class="pb-3 text-right">Ubah Status / Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @foreach ($orders as $order)
                        <tr>
                            <td class="py-3.5">
                                <p class="font-mono font-bold text-[#1d1d1f]">{{ $order->invoice_number }}</p>
                                <p class="text-[11px] text-[#86868b]">{{ $order->transaction_date->format('d/m/Y') }} &bull; {{ $order->details->sum('quantity') }} item</p>
                            </td>
                            <td class="py-3.5">
                                <p class="font-semibold text-[#1d1d1f]">{{ $order->user?->name }}</p>
                                <p class="max-w-xs truncate text-[11px] text-[#86868b]">{{ $order->shipping_address }}</p>
                            </td>
                            <td class="py-3.5 text-[#86868b]">{{ $order->paymentLabel() }}</td>
                            <td class="py-3.5 text-right font-bold text-[#1d1d1f]">
                                {{ rupiah($order->total_amount) }}
                                @if ($order->discount_amount > 0)
                                    <p class="text-[10px] font-normal text-emerald-700">Diskon -{{ rupiah($order->discount_amount) }}</p>
                                @endif
                            </td>
                            <td class="py-3.5">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold ring-1 {{ $order->statusBadgeClass() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form action="{{ route('backoffice.orders.status', $order) }}" method="POST" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <select name="order_status" onchange="this.form.submit()" class="rounded-full border-gray-300 bg-[#f5f5f7] py-1 pl-3 pr-7 text-xs font-semibold">
                                            @foreach (\App\Models\SalesTransaction::STATUSES as $stKey => $stLabel)
                                                <option value="{{ $stKey }}" @selected($order->order_status === $stKey)>{{ $stLabel }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <a
                                        href="{{ route('invoices.pdf', $order) }}"
                                        class="rounded-full bg-[#1d1d1f] px-3 py-1 text-[11px] font-semibold text-white hover:bg-[#0071e3]"
                                    >
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</x-admin-layout>
