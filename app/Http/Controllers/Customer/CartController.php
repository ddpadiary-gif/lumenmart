<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AprioriRule;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $summary = $this->cart->summary();

        // Rekomendasi "Sering dibeli bersama" dari aturan Apriori untuk item di keranjang.
        $inCart = array_map(fn ($l) => $l['product']->id, $summary['lines']);
        $suggestionIds = [];
        if ($inCart !== []) {
            foreach (AprioriRule::query()->orderByDesc('lift_ratio')->get() as $rule) {
                if (array_diff($rule->antecedent_product_ids, $inCart) === []) {
                    foreach ($rule->consequent_product_ids as $id) {
                        if (! in_array($id, $inCart, true)) {
                            $suggestionIds[$id] = true;
                        }
                    }
                }
            }
        }
        $suggestions = Product::with('category:id,name,slug')->active()
            ->whereIn('id', array_slice(array_keys($suggestionIds), 0, 4))->get();

        return view('customer.cart', compact('summary', 'suggestions'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        if ($product->current_stock < 1) {
            return back()->with('error', "Maaf, {$product->name} sedang habis.");
        }

        $this->cart->add($product, $data['quantity'] ?? 1);

        return back()->with('status', "{$product->name} masuk ke keranjang.");
    }

    public function addBundle(AprioriRule $rule): RedirectResponse
    {
        $products = Product::active()->whereIn('id', $rule->productIds())->get();
        foreach ($products as $product) {
            if ($product->current_stock > 0) {
                $this->cart->add($product, 1);
            }
        }

        return redirect()->route('cart.index')->with('status', 'Paket bundling ditambahkan. Diskon Smart Bundle otomatis diterapkan!');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);
        $this->cart->update($product, $data['quantity']);

        return back();
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return back()->with('status', "{$product->name} dihapus dari keranjang.");
    }
}
