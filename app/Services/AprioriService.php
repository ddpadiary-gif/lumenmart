<?php

namespace App\Services;

use App\Models\AprioriRule;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Algoritma Apriori murni PHP (Agrawal & Srikant, 1994).
 *
 *  - Ekstraksi frequent itemsets L1, L2, L3 (join + prune berbasis properti anti-monoton).
 *  - Pembentukan association rules A → C dari setiap itemset berukuran ≥ 2.
 *  - Metrik: Support (%), Confidence (%), Lift Ratio; hanya aturan dengan lift > 1.0 yang disimpan.
 */
class AprioriService
{
    public const DEFAULT_MIN_SUPPORT = 0.03;    // 3 %

    public const DEFAULT_MIN_CONFIDENCE = 0.40; // 40 %

    public const MAX_ITEMSET_SIZE = 3;

    /**
     * @param  array<int, array<int, int>>  $transactions  daftar keranjang berisi ID produk
     * @return array{transaction_count:int, itemsets: array<int, array<string, float>>, rules: array<int, array>}
     */
    public function mine(
        array $transactions,
        float $minSupport = self::DEFAULT_MIN_SUPPORT,
        float $minConfidence = self::DEFAULT_MIN_CONFIDENCE,
        int $maxSize = self::MAX_ITEMSET_SIZE,
    ): array {
        // Normalisasi: item unik & terurut, keranjang kosong dibuang.
        $baskets = [];
        foreach ($transactions as $items) {
            $items = array_values(array_unique(array_map('intval', $items)));
            if ($items === []) {
                continue;
            }
            sort($items);
            $baskets[] = array_flip($items); // set → lookup O(1)
        }

        $n = count($baskets);
        $levels = [];  // k => [key => support(fraction)]

        if ($n === 0) {
            return ['transaction_count' => 0, 'itemsets' => [], 'rules' => []];
        }

        // ---------- L1 ----------
        $counts = [];
        foreach ($baskets as $basket) {
            foreach ($basket as $item => $_) {
                $counts[$item] = ($counts[$item] ?? 0) + 1;
            }
        }
        $levels[1] = [];
        foreach ($counts as $item => $count) {
            if ($count / $n >= $minSupport) {
                $levels[1][(string) $item] = $count / $n;
            }
        }
        ksort($levels[1], SORT_NATURAL);

        // ---------- L2 .. Lk ----------
        for ($k = 2; $k <= $maxSize; $k++) {
            $prev = array_map(fn ($key) => $this->decode($key), array_keys($levels[$k - 1] ?? []));
            $candidates = $this->generateCandidates($prev, $levels[$k - 1] ?? [], $k);
            if ($candidates === []) {
                break;
            }

            $candidateCounts = array_fill_keys(array_keys($candidates), 0);
            foreach ($baskets as $basket) {
                if (count($basket) < $k) {
                    continue;
                }
                foreach ($candidates as $key => $items) {
                    foreach ($items as $item) {
                        if (! isset($basket[$item])) {
                            continue 2;
                        }
                    }
                    $candidateCounts[$key]++;
                }
            }

            $levels[$k] = [];
            foreach ($candidateCounts as $key => $count) {
                if ($count / $n >= $minSupport) {
                    $levels[$k][$key] = $count / $n;
                }
            }
            if ($levels[$k] === []) {
                unset($levels[$k]);
                break;
            }
        }

        // Peta support seluruh frequent itemset.
        $supportOf = [];
        foreach ($levels as $level) {
            foreach ($level as $key => $support) {
                $supportOf[$key] = $support;
            }
        }

        // ---------- Association rules ----------
        $rules = [];
        foreach ($levels as $k => $level) {
            if ($k < 2) {
                continue;
            }
            foreach ($level as $key => $supportAC) {
                $items = $this->decode($key);
                foreach ($this->properSubsets($items) as $antecedent) {
                    $consequent = array_values(array_diff($items, $antecedent));
                    $supA = $supportOf[$this->encode($antecedent)] ?? null;
                    $supC = $supportOf[$this->encode($consequent)] ?? null;
                    if (! $supA || ! $supC) {
                        continue;
                    }

                    $confidence = $supportAC / $supA;
                    $lift = $confidence / $supC;

                    if ($confidence >= $minConfidence && $lift > 1.0) {
                        $rules[] = [
                            'antecedent' => $antecedent,
                            'consequent' => $consequent,
                            'support' => $supportAC,
                            'confidence' => $confidence,
                            'lift' => $lift,
                        ];
                    }
                }
            }
        }

        usort($rules, fn ($x, $y) => [$y['lift'], $y['confidence']] <=> [$x['lift'], $x['confidence']]);

        return [
            'transaction_count' => $n,
            'itemsets' => $levels,
            'rules' => $rules,
        ];
    }

    /**
     * Ambil keranjang dari database, jalankan Apriori, dan simpan aturan.
     *
     * @return array ringkasan hasil (jumlah transaksi, |L1|, |L2|, |L3|, jumlah aturan)
     */
    public function generate(
        float $minSupport = self::DEFAULT_MIN_SUPPORT,
        float $minConfidence = self::DEFAULT_MIN_CONFIDENCE,
    ): array {
        $baskets = [];
        DB::table('sales_transaction_details')
            ->select('sales_transaction_id', 'product_id')
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$baskets) {
                foreach ($rows as $row) {
                    $baskets[$row->sales_transaction_id][] = (int) $row->product_id;
                }
            });

        $result = $this->mine(array_values($baskets), $minSupport, $minConfidence);
        $names = Product::query()->pluck('name', 'id')->all();
        $now = now();

        $rows = [];
        foreach ($result['rules'] as $rule) {
            $antNames = implode(', ', array_map(fn ($id) => $names[$id] ?? "#$id", $rule['antecedent']));
            $conNames = implode(', ', array_map(fn ($id) => $names[$id] ?? "#$id", $rule['consequent']));
            $discount = $this->discountForLift($rule['lift']);

            $rows[] = [
                'antecedent_product_ids' => json_encode($rule['antecedent']),
                'consequent_product_ids' => json_encode($rule['consequent']),
                'antecedent_names' => mb_substr($antNames, 0, 255),
                'consequent_names' => mb_substr($conNames, 0, 255),
                'support' => round($rule['support'] * 100, 4),
                'confidence' => round($rule['confidence'] * 100, 4),
                'lift_ratio' => round($rule['lift'], 4),
                'bundling_suggestion' => mb_substr(sprintf(
                    'Paket Hemat: %s + %s — diskon %d%% bila dibeli bersamaan',
                    $antNames, $conNames, $discount
                ), 0, 255),
                'bundle_discount_percent' => $discount,
                'generated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rows) {
            AprioriRule::query()->delete();
            foreach (array_chunk($rows, 100) as $chunk) {
                AprioriRule::insert($chunk);
            }
        });

        return [
            'transactions' => $result['transaction_count'],
            'l1' => count($result['itemsets'][1] ?? []),
            'l2' => count($result['itemsets'][2] ?? []),
            'l3' => count($result['itemsets'][3] ?? []),
            'rules' => count($rows),
            'min_support' => $minSupport,
            'min_confidence' => $minConfidence,
        ];
    }

    /** Diskon bundling adaptif: makin kuat asosiasi, makin menarik paketnya. */
    public function discountForLift(float $lift): int
    {
        return match (true) {
            $lift >= 3.0 => 10,
            $lift >= 2.0 => 7,
            default => 5,
        };
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Join L(k-1) ⋈ L(k-1) (prefix k-2 sama) lalu prune kandidat yang subset-(k-1)-nya tidak frequent.
     *
     * @param  array<int, array<int,int>>  $prev
     * @return array<string, array<int,int>>
     */
    private function generateCandidates(array $prev, array $prevSupport, int $k): array
    {
        $candidates = [];
        $count = count($prev);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $prev[$i];
                $b = $prev[$j];
                if (array_slice($a, 0, $k - 2) !== array_slice($b, 0, $k - 2)) {
                    continue;
                }
                $merged = array_values(array_unique(array_merge($a, $b)));
                if (count($merged) !== $k) {
                    continue;
                }
                sort($merged);

                // Prune (Apriori property)
                $valid = true;
                foreach ($merged as $drop) {
                    $subset = array_values(array_diff($merged, [$drop]));
                    if (! isset($prevSupport[$this->encode($subset)])) {
                        $valid = false;
                        break;
                    }
                }
                if ($valid) {
                    $candidates[$this->encode($merged)] = $merged;
                }
            }
        }

        return $candidates;
    }

    /** @return array<int, array<int,int>> semua subset tak kosong & bukan himpunan penuh */
    private function properSubsets(array $items): array
    {
        $n = count($items);
        $subsets = [];
        for ($mask = 1; $mask < (1 << $n) - 1; $mask++) {
            $subset = [];
            for ($i = 0; $i < $n; $i++) {
                if ($mask & (1 << $i)) {
                    $subset[] = $items[$i];
                }
            }
            $subsets[] = $subset;
        }

        return $subsets;
    }

    private function encode(array $items): string
    {
        $items = array_map('intval', $items);
        sort($items);

        return implode(',', $items);
    }

    private function decode(string $key): array
    {
        return array_map('intval', explode(',', $key));
    }
}
