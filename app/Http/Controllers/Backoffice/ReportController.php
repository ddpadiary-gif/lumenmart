<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate, $channel] = $this->filters($request);

        $salesQuery = SalesTransaction::with(['user:id,name,role', 'details.product'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel));

        $totalRevenue = (float) (clone $salesQuery)->sum('total_amount');
        $totalDiscount = (float) (clone $salesQuery)->sum('discount_amount');
        $totalTransactions = (clone $salesQuery)->count();

        $transactions = (clone $salesQuery)
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::with(['category:id,name', 'latestPrediction'])
            ->withSum('salesDetails as total_sold_qty', 'quantity')
            ->orderBy('name')
            ->get();

        $inventoryValue = (float) $products->sum(fn (Product $p) => $p->current_stock * (float) $p->purchase_price);

        return view('backoffice.reports.index', compact(
            'startDate',
            'endDate',
            'channel',
            'totalRevenue',
            'totalDiscount',
            'totalTransactions',
            'transactions',
            'products',
            'inventoryValue',
        ));
    }

    public function exportSalesCsv(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $channel] = $this->filters($request);

        $rows = SalesTransaction::with(['user:id,name', 'details.product'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $filename = "laporan-penjualan-lumenmart-{$startDate}-sd-{$endDate}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM agar Excel langsung rapi
            fputcsv($out, ['No. Invoice', 'Tanggal', 'Channel', 'Pelanggan/Kasir', 'Metode Bayar', 'Status', 'Jumlah Item', 'Diskon Bundle (Rp)', 'Total Bayar (Rp)']);

            foreach ($rows as $tx) {
                fputcsv($out, [
                    $tx->invoice_number,
                    $tx->transaction_date->format('Y-m-d'),
                    $tx->channelLabel(),
                    $tx->user?->name ?? '-',
                    $tx->paymentLabel(),
                    $tx->statusLabel(),
                    $tx->details->sum('quantity'),
                    (float) $tx->discount_amount,
                    (float) $tx->total_amount,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportInventoryCsv(): StreamedResponse
    {
        $products = Product::with(['category:id,name', 'latestPrediction'])
            ->withSum('salesDetails as total_sold_qty', 'quantity')
            ->orderBy('sku')
            ->get();

        $filename = 'laporan-stok-prediksi-lumenmart-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'SKU', 'Nama Produk', 'Kategori', 'Movement', 'Satuan',
                'Harga Beli (Rp)', 'Harga Jual (Rp)', 'Stok Saat Ini', 'Batas Minimum', 'Status Stok',
                'Total Terjual (12 Bln)', 'Persamaan Regresi', 'Prediksi Bulan Depan', 'R2', 'Rekomendasi Restock',
            ]);

            foreach ($products as $p) {
                $pred = $p->latestPrediction;
                fputcsv($out, [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? '-',
                    $p->movement_type,
                    $p->unit,
                    (float) $p->purchase_price,
                    (float) $p->selling_price,
                    $p->current_stock,
                    $p->min_stock_threshold,
                    $p->isCritical() ? 'KRITIS' : 'AMAN',
                    (int) ($p->total_sold_qty ?? 0),
                    $pred?->regression_equation ?? '-',
                    $pred ? round($pred->predicted_demand, 2) : 0,
                    $pred ? round($pred->r_squared, 4) : 0,
                    $pred?->restock_recommendation ?? 0,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request): Response
    {
        [$startDate, $endDate, $channel] = $this->filters($request);

        $transactions = SalesTransaction::with(['user:id,name', 'details.product'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->orderByDesc('transaction_date')
            ->limit(150)
            ->get();

        $totalRevenue = (float) SalesTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->sum('total_amount');

        $totalDiscount = (float) SalesTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->sum('discount_amount');

        $totalCount = SalesTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->count();

        $products = Product::with(['category:id,name', 'latestPrediction'])
            ->orderByRaw('CASE WHEN current_stock <= min_stock_threshold THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get();

        return Pdf::loadView('pdf.report', compact(
            'startDate',
            'endDate',
            'channel',
            'transactions',
            'totalRevenue',
            'totalDiscount',
            'totalCount',
            'products',
        ))->setPaper('a4', 'landscape')->download("laporan-lumenmart-{$startDate}-sd-{$endDate}.pdf");
    }

    private function filters(Request $request): array
    {
        $defaultStart = now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString();
        $defaultEnd = now()->toDateString();

        $startDate = $request->date('start_date')?->toDateString() ?? $defaultStart;
        $endDate = $request->date('end_date')?->toDateString() ?? $defaultEnd;
        $channel = in_array($request->query('channel'), ['pos_toko', 'ecommerce'], true)
            ? $request->query('channel')
            : null;

        return [$startDate, $endDate, $channel];
    }
}
