<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->invoice_number }} — LUMEN MART</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1d1d1f; margin: 18px; }
        .header { border-bottom: 2px solid #1d1d1f; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: bold; letter-spacing: -0.5px; }
        .tagline { font-size: 9px; color: #6e6e73; text-transform: uppercase; }
        .meta { width: 100%; margin-bottom: 14px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #1d1d1f; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
        table.items td { border-bottom: 1px solid #e5e5ea; padding: 6px 8px; }
        .right { text-align: right; }
        .summary { width: 100%; margin-top: 12px; }
        .summary td { padding: 3px 0; }
        .total-row { font-size: 13px; font-weight: bold; border-top: 1.5px solid #1d1d1f; padding-top: 6px; }
        .footer { margin-top: 24px; font-size: 9px; color: #6e6e73; text-align: center; border-top: 1px solid #e5e5ea; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">LUMEN MART</div>
        <div class="tagline">Smart Daily Essentials, Intelligently Stocked</div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>No. Invoice:</strong> {{ $order->invoice_number }}<br>
                <strong>Tanggal:</strong> {{ $order->transaction_date->format('d/m/Y') }}<br>
                <strong>Channel:</strong> {{ $order->channelLabel() }}
            </td>
            <td class="right">
                <strong>Pelanggan / Kasir:</strong> {{ $order->user?->name ?? '-' }}<br>
                <strong>Pembayaran:</strong> {{ $order->paymentLabel() }}<br>
                <strong>Status:</strong> {{ $order->statusLabel() }}
            </td>
        </tr>
        @if ($order->shipping_address)
            <tr>
                <td colspan="2" style="padding-top:6px;">
                    <strong>Alamat Pengiriman:</strong> {{ $order->shipping_address }}
                </td>
            </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Qty</th>
                <th class="right">Harga Satuan</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $d)
                <tr>
                    <td>{{ $d->product?->name ?? 'Produk' }}</td>
                    <td class="right">{{ $d->quantity }}</td>
                    <td class="right">{{ rupiah($d->unit_price) }}</td>
                    <td class="right">{{ rupiah($d->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ rupiah($order->subtotalAmount()) }}</td>
        </tr>
        @if ($order->discount_amount > 0)
            <tr>
                <td>Diskon Smart Bundle (Apriori)</td>
                <td class="right">-{{ rupiah($order->discount_amount) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td>Total Pembayaran</td>
            <td class="right">{{ rupiah($order->total_amount) }}</td>
        </tr>
    </table>

    <div class="footer">
        Terima kasih telah berbelanja di LUMEN MART &bull; Sistem Informasi Inventory &amp; E-Commerce UMKM Ritel Cerdas
    </div>
</body>
</html>
