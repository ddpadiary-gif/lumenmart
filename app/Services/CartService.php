<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;

/**
 * Keranjang belanja berbasis session: [product_id => qty].
 */
class CartService
{
    private const KEY = 'lumen_cart';

    public function __construct(
        private Session $session,
        private BundleDiscountService $bundles,
    ) {}

    /** @return array<int, int> */
    public function items(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    public function add(Product $product, int $qty = 1): void
    {
        $items = $this->items();
        $items[$product->id] = min(($items[$product->id] ?? 0) + max(1, $qty), max(0, $product->current_stock));
        if ($items[$product->id] <= 0) {
            unset($items[$product->id]);
        }
        $this->session->put(self::KEY, $items);
    }

    public function update(Product $product, int $qty): void
    {
        $items = $this->items();
        $qty = min($qty, max(0, $product->current_stock));
        if ($qty <= 0) {
            unset($items[$product->id]);
        } else {
            $items[$product->id] = $qty;
        }
        $this->session->put(self::KEY, $items);
    }

    public function remove(Product $product): void
    {
        $items = $this->items();
        unset($items[$product->id]);
        $this->session->put(self::KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * Ringkasan keranjang lengkap dengan Smart Bundle Discount.
     */
    public function summary(): array
    {
        $items = $this->items();
        $products = $items === []
            ? collect()
            : Product::with('category')->active()->whereIn('id', array_keys($items))->get()->keyBy('id');

        $lines = [];
        $priceLines = [];
        $subtotal = 0.0;

        foreach ($items as $productId => $qty) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }
            $lineTotal = (float) $product->selling_price * $qty;
            $subtotal += $lineTotal;
            $lines[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineTotal];
            $priceLines[$productId] = ['price' => (float) $product->selling_price, 'qty' => $qty];
        }

        $bundle = $this->bundles->calculate($priceLines);

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount' => $bundle['discount'],
            'bundles' => $bundle['bundles'],
            'total' => max(0, $subtotal - $bundle['discount']),
            'count' => array_sum(array_column($lines, 'qty')),
        ];
    }
}
