<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\StockPrediction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Regresi Linear Sederhana (Ordinary Least Squares) murni PHP.
 *
 * Alur:
 *  1. Agregasi penjualan bulanan per produk selama 12 bulan (X = 1..12).
 *  2. Split kronologis 80:20 → Bulan 1–10 data latih, Bulan 11–12 data uji.
 *  3. Hitung OLS  Y = a + bX  pada data latih.
 *  4. Evaluasi: R² (goodness-of-fit data latih), MAE & RMSE (data uji).
 *  5. Prediksi permintaan Bulan ke-13 dan rekomendasi restock (+ safety stock).
 */
class LinearRegressionService
{
    public const MONTHS = 12;

    public const TRAIN_MONTHS = 10;

    /** Z-score service level ±95% untuk safety stock. */
    public const SAFETY_Z = 1.65;

    /**
     * Fit OLS sederhana.
     *
     * @param  array<int, float|int>  $xs
     * @param  array<int, float|int>  $ys
     * @return array{a: float, b: float}
     */
    public function fit(array $xs, array $ys): array
    {
        $xs = array_values($xs);
        $ys = array_values($ys);
        $n = count($xs);

        if ($n === 0 || $n !== count($ys)) {
            throw new \InvalidArgumentException('Data X dan Y harus memiliki panjang yang sama dan tidak kosong.');
        }

        $meanX = array_sum($xs) / $n;
        $meanY = array_sum($ys) / $n;

        $sxy = 0.0;
        $sxx = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sxy += ($xs[$i] - $meanX) * ($ys[$i] - $meanY);
            $sxx += ($xs[$i] - $meanX) ** 2;
        }

        $b = $sxx == 0.0 ? 0.0 : $sxy / $sxx;
        $a = $meanY - $b * $meanX;

        return ['a' => $a, 'b' => $b];
    }

    public function predict(array $model, float $x): float
    {
        return $model['a'] + $model['b'] * $x;
    }

    public function mae(array $actual, array $predicted): float
    {
        $actual = array_values($actual);
        $predicted = array_values($predicted);
        $n = count($actual);
        if ($n === 0) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($actual as $i => $y) {
            $sum += abs($y - $predicted[$i]);
        }

        return $sum / $n;
    }

    public function rmse(array $actual, array $predicted): float
    {
        $actual = array_values($actual);
        $predicted = array_values($predicted);
        $n = count($actual);
        if ($n === 0) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($actual as $i => $y) {
            $sum += ($y - $predicted[$i]) ** 2;
        }

        return sqrt($sum / $n);
    }

    public function rSquared(array $actual, array $predicted): float
    {
        $actual = array_values($actual);
        $predicted = array_values($predicted);
        $n = count($actual);
        if ($n === 0) {
            return 0.0;
        }

        $mean = array_sum($actual) / $n;
        $ssRes = 0.0;
        $ssTot = 0.0;
        foreach ($actual as $i => $y) {
            $ssRes += ($y - $predicted[$i]) ** 2;
            $ssTot += ($y - $mean) ** 2;
        }

        if ($ssTot == 0.0) {
            return $ssRes == 0.0 ? 1.0 : 0.0;
        }

        return 1 - ($ssRes / $ssTot);
    }

    /**
     * Analisis lengkap 1 deret penjualan bulanan (12 nilai, indeks 0 = Bulan 1).
     *
     * @param  array<int, float|int>  $monthly
     */
    public function analyze(array $monthly, int $currentStock = 0): array
    {
        $monthly = array_values($monthly);
        $n = count($monthly);
        $trainN = (int) round($n * 0.8); // 12 → 10
        $xs = range(1, $n);

        $trainX = array_slice($xs, 0, $trainN);
        $trainY = array_slice($monthly, 0, $trainN);
        $testX = array_slice($xs, $trainN);
        $testY = array_slice($monthly, $trainN);

        $model = $this->fit($trainX, $trainY);

        $trainPred = array_map(fn ($x) => $this->predict($model, $x), $trainX);
        $testPred = array_map(fn ($x) => $this->predict($model, $x), $testX);

        $mae = $this->mae($testY, $testPred);
        $rmse = $this->rmse($testY, $testPred);
        $r2 = $this->rSquared($trainY, $trainPred);

        $nextX = $n + 1;
        $predicted = max(0.0, $this->predict($model, $nextX));
        $safetyStock = (int) ceil(self::SAFETY_Z * $rmse);
        $restock = max(0, (int) ceil($predicted + $safetyStock - $currentStock));

        return [
            'a' => $model['a'],
            'b' => $model['b'],
            'equation' => $this->formatEquation($model['a'], $model['b']),
            'train_x' => $trainX,
            'train_y' => $trainY,
            'test_x' => $testX,
            'test_y' => $testY,
            'fitted' => array_map(fn ($x) => round($this->predict($model, $x), 2), $xs),
            'mae' => $mae,
            'rmse' => $rmse,
            'r_squared' => $r2,
            'next_x' => $nextX,
            'predicted_demand' => $predicted,
            'safety_stock' => $safetyStock,
            'restock_recommendation' => $restock,
        ];
    }

    public function formatEquation(float $a, float $b): string
    {
        $sign = $b < 0 ? '−' : '+';

        return sprintf('Y = %s %s %sX', number_format($a, 2, '.', ''), $sign, number_format(abs($b), 2, '.', ''));
    }

    /**
     * Jendela 12 bulan yang berakhir di bulan transaksi terakhir.
     *
     * @return array<int, CarbonImmutable> daftar awal bulan (urut lama → baru)
     */
    public function monthWindow(): array
    {
        $latest = SalesTransaction::max('transaction_date');
        $end = CarbonImmutable::parse($latest ?? now())->startOfMonth();

        $months = [];
        for ($i = self::MONTHS - 1; $i >= 0; $i--) {
            $months[] = $end->subMonthsNoOverflow($i);
        }

        return $months;
    }

    /**
     * Agregasi kuantitas terjual per produk per bulan (DB-agnostic, diolah di PHP).
     *
     * @return array<int, array<int, int>> [product_id => [12 nilai bulanan]]
     */
    public function monthlySalesMatrix(?array $months = null): array
    {
        $months ??= $this->monthWindow();
        $keys = array_map(fn ($m) => $m->format('Y-m'), $months);
        $indexOf = array_flip($keys);

        $start = $months[0]->toDateString();
        $end = end($months)->endOfMonth()->toDateString();

        $rows = DB::table('sales_transaction_details as d')
            ->join('sales_transactions as t', 't.id', '=', 'd.sales_transaction_id')
            ->whereBetween('t.transaction_date', [$start, $end])
            ->select('d.product_id', 'd.quantity', 't.transaction_date')
            ->get();

        $matrix = [];
        foreach ($rows as $row) {
            $key = substr((string) $row->transaction_date, 0, 7);
            if (! isset($indexOf[$key])) {
                continue;
            }
            $matrix[$row->product_id] ??= array_fill(0, count($months), 0);
            $matrix[$row->product_id][$indexOf[$key]] += (int) $row->quantity;
        }

        return $matrix;
    }

    /**
     * Jalankan regresi untuk seluruh produk aktif & simpan ke stock_predictions.
     *
     * @return int jumlah prediksi yang dihasilkan
     */
    public function generate(): int
    {
        $months = $this->monthWindow();
        $matrix = $this->monthlySalesMatrix($months);
        $targetPeriod = end($months)->addMonthNoOverflow()->format('Y-m');
        $now = now();

        $products = Product::query()->where('is_active', true)->get(['id', 'current_stock']);
        $rows = [];

        foreach ($products as $product) {
            $series = $matrix[$product->id] ?? array_fill(0, count($months), 0);
            $result = $this->analyze($series, (int) $product->current_stock);

            $rows[] = [
                'product_id' => $product->id,
                'target_period' => $targetPeriod,
                'regression_equation' => $result['equation'],
                'predicted_demand' => round($result['predicted_demand'], 2),
                'mae' => round($result['mae'], 4),
                'rmse' => round($result['rmse'], 4),
                'r_squared' => round($result['r_squared'], 4),
                'restock_recommendation' => $result['restock_recommendation'],
                'generated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rows) {
            StockPrediction::query()->delete();
            foreach (array_chunk($rows, 100) as $chunk) {
                StockPrediction::insert($chunk);
            }
        });

        return count($rows);
    }
}
