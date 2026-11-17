<?php

namespace App\Services;

use App\Models\AprioriRule;
use Illuminate\Support\Collection;

/**
 * Smart Bundle Discount — mendeteksi paket hasil Apriori yang lengkap di keranjang.
 *
 * Aturan: setiap aturan yang seluruh item-nya (antecedent ∪ consequent) ada di keranjang
 * memberi potongan `bundle_discount_percent` atas harga 1 set paket × jumlah set
 * (jumlah set = kuantitas minimum di antara item paket). Diproses greedy berdasarkan lift
 * tertinggi dan satu produk hanya boleh dipakai oleh satu paket (tidak ada diskon ganda).
 */
class BundleDiscountService
{
    /** @var Collection<int, AprioriRule>|null */
    private ?Collection $rules = null;

    /** Injeksi aturan secara manual (mis. untuk pengujian). */
    public function withRules(Collection $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    /** @return Collection<int, AprioriRule> */
    public function rules(): Collection
    {
        return $this->rules ??= AprioriRule::query()
            ->orderByDesc('lift_ratio')
            ->orderByDesc('confidence')
            ->get();
    }

    /**
     * @param  array<int, array{price: float|int|string, qty: int, name?: string}>  $lines  [product_id => line]
     * @return array{discount: float, bundles: array<int, array>}
     */
    public function calculate(array $lines): array
    {
        $used = [];
        $bundles = [];
        $discount = 0.0;

        foreach ($this->rules() as $rule) {
            $ids = $rule->productIds();
            if (count($ids) < 2) {
                continue;
            }

            $applicable = true;
            foreach ($ids as $id) {
                if (! isset($lines[$id]) || $lines[$id]['qty'] < 1 || isset($used[$id])) {
                    $applicable = false;
                    break;
                }
            }
            if (! $applicable) {
                continue;
            }

            $sets = min(array_map(fn ($id) => (int) $lines[$id]['qty'], $ids));
            $setPrice = array_sum(array_map(fn ($id) => (float) $lines[$id]['price'], $ids));
            $amount = round($setPrice * $sets * $rule->bundle_discount_percent / 100, 2);

            foreach ($ids as $id) {
                $used[$id] = true;
            }

            $discount += $amount;
            $bundles[] = [
                'rule_id' => $rule->id,
                'product_ids' => $ids,
                'label' => trim($rule->antecedent_names.' + '.$rule->consequent_names),
                'percent' => $rule->bundle_discount_percent,
                'sets' => $sets,
                'amount' => $amount,
            ];
        }

        return ['discount' => round($discount, 2), 'bundles' => $bundles];
    }
}
