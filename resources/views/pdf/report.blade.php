<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Eksekutif LUMEN MART</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1d1d1f; margin: 16px; }
        h1 { font-size: 16px; margin: 0; }
        h2 { font-size: 12px; margin: 16px 0 6px; }
        .sub { color: #6e6e73; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #1d1d1f; color: #fff; text-align: left; padding: 5px 6px; font-size: 9px; }
        td { border-bottom: 1px solid #e5e5ea; padding: 4px 6px; }
        .right { text-align: right; }
        .crit { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h1>LUMEN MART — Laporan Penjualan, Stok &amp; Prediksi Regresi Linear</h1>
    <div class="sub">
        Periode: {{ $startDate }} s/d {{ $endDate }} &bull;
        Total Transaksi: {{ number_format($totalCount, 0, ',', '.') }} &bull;
        Total Omzet: {{ rupiah($totalRevenue) }} &bull;
        Total Diskon Smart Bundle: {{ rupiah($totalDiscount) }}
    </div>

    <h2>1. Status Stok &amp; Rekomendasi Restock (Regresi Linear OLS)</h2>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Movement</th>
                <th class="right">Stok Kini</th>
                <th class="right">Min</th>
                <th>Persamaan Regresi</th>
                <th class="right">R&sup2;</th>
                <th class="right">Prediksi</th>
                <th class="right">Saran Restock</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $p)
                @php $pred = $p->latestPrediction; @endphp
                <tr>
                    <td>{{ $p->sku }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->category?->name }}</td>
                    <td>{{ $p->movement_type }}</td>
                    <td class="right {{ $p->isCritical() ? 'crit' : '' }}">{{ $p->current_stock }} {{ $p->unit }}</td>
                    <td class="right">{{ $p->min_stock_threshold }}</td>
                    <td>{{ $pred?->regression_equation ?? '-' }}</td>
                    <td class="right">{{ $pred ? number_format($pred->r_squared, 3) : '-' }}</td>
                    <td class="right">{{ $pred ? number_format($pred->predicted_demand, 1) : '-' }}</td>
                    <td class="right">{{ $pred?->restock_recommendation ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
