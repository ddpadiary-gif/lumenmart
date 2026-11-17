<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AprioriRule;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
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

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        if ($product->current_stock < 1) {
            $message = "Maaf, {$product->name} sedang habis.";

            return $request->expectsJson()
                ? response()->json(['message' => $message, 'cartCount' => $this->cart->count()], 422)
                : back()->with('error', $message);
        }

        $this->cart->add($product, $data['quantity'] ?? 1);
        $message = "{$product->name} ditambahkan ke keranjang.";

        if ($request->expectsJson()) {
            return $this->jsonCart($message);
        }

        return back()->with('status', "{$product->name} masuk ke keranjang.");
    }

    public function addBundle(Request $request, AprioriRule $rule): RedirectResponse|JsonResponse
    {
        $products = Product::active()->whereIn('id', $rule->productIds())->get();
        foreach ($products as $product) {
            if ($product->current_stock > 0) {
                $this->cart->add($product, 1);
            }
        }

        if ($request->expectsJson()) {
            return $this->jsonCart('Paket bundling ditambahkan. Diskon Smart Bundle otomatis diterapkan!');
        }

        return redirect()->route('cart.index')->with('status', 'Paket bundling ditambahkan. Diskon Smart Bundle otomatis diterapkan!');
    }

    /** Respons JSON ringkas untuk interaksi AJAX (badge keranjang, toast, mini cart). */
    private function jsonCart(string $message): JsonResponse
    {
        $summary = $this->cart->summary();
        $summary['lines'] = array_map(fn ($line) => [
            'id' => $line['product']->id,
            'name' => $line['product']->name,
            'image' => $line['product']->imageUrl(),
            'price' => (float) $line['product']->selling_price,
            'quantity' => $line['qty'],
            'subtotal' => $line['subtotal'],
        ], $summary['lines']);

        return response()->json([
            'message' => $message,
            'cartCount' => $this->cart->count(),
            'summary' => $summary,
        ]);
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
