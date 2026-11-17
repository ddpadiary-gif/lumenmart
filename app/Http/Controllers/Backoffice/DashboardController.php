<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AprioriRule;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\StockPrediction;
use App\Services\LinearRegressionService;
use App\Services\SimulationDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(LinearRegressionService $regression): View
    {
        $totalRevenue = (float) SalesTransaction::sum('total_amount');
        $totalDiscount = (float) SalesTransaction::sum('discount_amount');
        $totalTransactions = SalesTransaction::count();
        $posCount = SalesTransaction::where('channel', 'pos_toko')->count();
        $ecommerceCount = SalesTransaction::where('channel', 'ecommerce')->count();
        $pendingOrdersCount = SalesTransaction::where('channel', 'ecommerce')
            ->whereIn('order_status', ['menunggu_pembayaran', 'diproses', 'dikirim'])
            ->count();

        // Estimasi HPP (COGS) dari penjualan untuk laba kotor.
        $estimatedCogs = (float) DB::table('sales_transaction_details as d')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->selectRaw('COALESCE(SUM(d.quantity * p.purchase_price), 0) as cogs')
            ->value('cogs');
        $grossProfit = max(0.0, $totalRevenue - $estimatedCogs);

        $totalProducts = Product::active()->count();
        $criticalCount = Product::active()->critical()->count();

        $avgFastR2 = (float) StockPrediction::query()
            ->whereHas('product', fn ($q) => $q->where('movement_type', 'fast_moving'))
            ->avg('r_squared');
        $rulesCount = AprioriRule::count();

        // Data grafik 12 bulan (pendapatan & jumlah transaksi per channel).
        $months = $regression->monthWindow();
        $monthKeys = array_map(fn ($m) => $m->format('Y-m'), $months);
        $monthLabels = array_map(fn ($m) => $m->translatedFormat('M Y'), $months);
        $indexOf = array_flip($monthKeys);

        $posRevenueSeries = array_fill(0, count($months), 0.0);
        $ecomRevenueSeries = array_fill(0, count($months), 0.0);
        $txCountSeries = array_fill(0, count($months), 0);

        $start = $months[0]->toDateString();
        $end = end($months)->endOfMonth()->toDateString();

        $txRows = DB::table('sales_transactions')
            ->whereBetween('transaction_date', [$start, $end])
            ->select('channel', 'total_amount', 'transaction_date')
            ->get();

        foreach ($txRows as $row) {
            $key = substr((string) $row->transaction_date, 0, 7);
            if (! isset($indexOf[$key])) {
                continue;
            }
            $idx = $indexOf[$key];
            $txCountSeries[$idx]++;
            if ($row->channel === 'pos_toko') {
                $posRevenueSeries[$idx] += (float) $row->total_amount;
            } else {
                $ecomRevenueSeries[$idx] += (float) $row->total_amount;
            }
        }

        // Top 8 produk terlaris.
        $topProducts = DB::table('sales_transaction_details as d')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.unit',
                'p.movement_type',
                'c.name as category_name',
                DB::raw('SUM(d.quantity) as total_qty'),
                DB::raw('SUM(d.subtotal) as total_sales')
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'p.unit', 'p.movement_type', 'c.name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        // Penjualan per kategori.
        $categorySales = DB::table('sales_transaction_details as d')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->select('c.name', DB::raw('SUM(d.subtotal) as total_sales'), DB::raw('SUM(d.quantity) as total_qty'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total_sales')
            ->get();

        // Rekomendasi restock teratas dari Regresi Linear.
        $topRestocks = StockPrediction::with('product.category')
            ->where('restock_recommendation', '>', 0)
            ->orderByDesc('restock_recommendation')
            ->limit(6)
            ->get();

        // Top aturan Apriori.
        $topRules = AprioriRule::query()
            ->orderByDesc('lift_ratio')
            ->orderByDesc('confidence')
            ->limit(5)
            ->get();

        // Transaksi terbaru.
        $recentTransactions = SalesTransaction::with('user:id,name,role')
            ->withCount('details')
            ->latest('id')
            ->limit(7)
            ->get();

        return view('backoffice.dashboard', compact(
            'totalRevenue',
            'totalDiscount',
            'grossProfit',
            'totalTransactions',
            'posCount',
            'ecommerceCount',
            'pendingOrdersCount',
            'totalProducts',
            'criticalCount',
            'avgFastR2',
            'rulesCount',
            'monthLabels',
            'posRevenueSeries',
            'ecomRevenueSeries',
            'txCountSeries',
            'topProducts',
            'categorySales',
            'topRestocks',
            'topRules',
            'recentTransactions',
        ));
    }

    /**
     * Tombol "Reset & Generate Ulang Simulasi" (khusus Admin).
     */
    public function resetSimulation(SimulationDataService $simulation): RedirectResponse
    {
        $stats = $simulation->generate();

        return redirect()->route('backoffice.dashboard')->with(
            'status',
            sprintf(
                'Simulasi berhasil di-reset & digenerate ulang: %d produk, %d transaksi (%d detail), %d prediksi Regresi Linear, dan %d aturan Apriori.',
                $stats['products'],
                $stats['transactions'],
                $stats['details'],
                $stats['predictions'] ?? 0,
                $stats['apriori']['rules'] ?? 0,
            )
        );
    }
}
