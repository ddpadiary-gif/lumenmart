<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockPrediction;
use App\Services\LinearRegressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegressionController extends Controller
{
    public function index(Request $request, LinearRegressionService $regression): View
    {
        if (StockPrediction::count() === 0 && Product::active()->exists()) {
            $regression->generate();
        }

        $predictions = StockPrediction::with('product.category')
            ->get()
            ->sortBy([
                fn ($p) => $p->product?->movement_type === 'fast_moving' ? 0 : 1,
                fn ($p) => -1 * $p->restock_recommendation,
                fn ($p) => -1 * $p->r_squared,
            ])
            ->values();

        $months = $regression->monthWindow();
        $monthLabels = array_map(fn ($m) => $m->translatedFormat('M Y'), $months);
        $targetLabel = end($months)->addMonthNoOverflow()->translatedFormat('M Y');
        $matrix = $regression->monthlySalesMatrix($months);

        $selectedId = $request->integer('product_id') ?: ($predictions->first()?->product_id);
        $selectedProduct = Product::with('category')->find($selectedId) ?? Product::with('category')->first();

        $detail = null;
        if ($selectedProduct) {
            $series = $matrix[$selectedProduct->id] ?? array_fill(0, count($months), 0);
            $detail = $regression->analyze($series, (int) $selectedProduct->current_stock);
            $detail['actual'] = $series;
        }

        // Data JSON seluruh produk agar grafik & metrik dapat berganti instan tanpa reload (Chart.js update()).
        $regressionData = [];
        foreach ($predictions as $row) {
            $prod = $row->product;
            if (! $prod) {
                continue;
            }
            $series = $matrix[$prod->id] ?? array_fill(0, count($months), 0);
            $analysis = $regression->analyze($series, (int) $prod->current_stock);
            $regressionData[$prod->id] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'sku' => $prod->sku,
                'unit' => $prod->unit,
                'category' => $prod->category?->name,
                'fast' => $prod->isFastMoving(),
                'actual' => array_map('floatval', array_values($series)),
                'fitted' => $analysis['fitted'],
                'equation' => $analysis['equation'],
                'r_squared' => round($analysis['r_squared'], 4),
                'mae' => round($analysis['mae'], 2),
                'rmse' => round($analysis['rmse'], 2),
                'predicted' => round($analysis['predicted_demand'], 2),
                'safety_stock' => $analysis['safety_stock'],
                'restock' => $analysis['restock_recommendation'],
                'stock' => (int) $prod->current_stock,
            ];
        }

        $fastPredictions = $predictions->filter(fn ($p) => $p->product?->movement_type === 'fast_moving');
        $avgFastR2 = (float) $fastPredictions->avg('r_squared');
        $avgFastMae = (float) $fastPredictions->avg('mae');
        $avgFastRmse = (float) $fastPredictions->avg('rmse');
        $totalRestockUnits = (int) $predictions->sum('restock_recommendation');

        return view('backoffice.analytics.regression', compact(
            'predictions',
            'monthLabels',
            'targetLabel',
            'selectedProduct',
            'detail',
            'avgFastR2',
            'avgFastMae',
            'avgFastRmse',
            'totalRestockUnits',
            'regressionData',
        ));
    }

    public function generate(LinearRegressionService $regression): RedirectResponse
    {
        $count = $regression->generate();

        return redirect()->route('backoffice.regression.index')
            ->with('status', "Perhitungan Regresi Linear selesai untuk {$count} produk aktif (split kronologis 80:20).");
    }
}
