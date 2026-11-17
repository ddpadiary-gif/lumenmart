<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pembuatan transaksi penjualan (POS & E-Commerce) yang atomik:
 * validasi stok (row lock), Smart Bundle Discount, detail transaksi, dan pengurangan stok.
 */
class SalesService
{
    public function __construct(private BundleDiscountService $bundles) {}

    /**
     * @param  array<int, int>  $items  [product_id => qty]
     */
    public function create(
        User $user,
        array $items,
        string $channel,
        string $paymentMethod,
        string $status,
        ?string $shippingAddress = null,
    ): SalesTransaction {
        $items = array_filter(array_map('intval', $items), fn ($q) => $q > 0);
        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
        }

        return DB::transaction(function () use ($user, $items, $channel, $paymentMethod, $status, $shippingAddress) {
            $products = Product::query()
                ->whereIn('id', array_keys($items))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            foreach ($items as $productId => $qty) {
                $product = $products->get($productId);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan atau tidak aktif.']);
                }
                if ($product->current_stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi (tersisa {$product->current_stock}).",
                    ]);
                }
                $lines[$productId] = ['price' => (float) $product->selling_price, 'qty' => $qty];
            }

            $subtotal = array_sum(array_map(fn ($l) => $l['price'] * $l['qty'], $lines));
            $discount = $this->bundles->calculate($lines)['discount'];

            $transaction = SalesTransaction::create([
                'invoice_number' => SalesTransaction::generateInvoiceNumber($channel === 'pos_toko' ? 'POS' : 'INV'),
                'user_id' => $user->id,
                'channel' => $channel,
                'payment_method' => $paymentMethod,
                'order_status' => $status,
                'discount_amount' => $discount,
                'total_amount' => max(0, $subtotal - $discount),
                'shipping_address' => $shippingAddress,
                'transaction_date' => now()->toDateString(),
            ]);

            foreach ($lines as $productId => $line) {
                $transaction->details()->create([
                    'product_id' => $productId,
                    'quantity' => $line['qty'],
                    'unit_price' => $line['price'],
                    'subtotal' => $line['price'] * $line['qty'],
                ]);
                $products[$productId]->decrement('current_stock', $line['qty']);
            }

            return $transaction->load('details.product');
        });
    }
}
