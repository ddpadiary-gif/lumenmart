<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->wishlistProducts()->with('category:id,name,slug')->orderBy('name')->get();

        return view('customer.wishlist', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $result = $request->user()->wishlistProducts()->toggle($product->id);
        $added = count($result['attached']) > 0;

        if ($request->expectsJson()) {
            return response()->json(['wishlisted' => $added]);
        }

        return back()->with('status', $added
            ? "{$product->name} ditambahkan ke Favorit."
            : "{$product->name} dihapus dari Favorit.");
    }
}
