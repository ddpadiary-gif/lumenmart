<?php

namespace App\Http\Controllers;

use App\Models\AprioriRule;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->withCount(['products' => fn ($q) => $q->active()])->orderBy('name')->get();

        $products = Product::query()
            ->with('category:id,name,slug')
            ->active()
            ->orderByRaw("CASE WHEN movement_type = 'fast_moving' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $wishlistIds = $request->user()?->isCustomer()
            ? $request->user()->wishlists()->pluck('product_id')->all()
            : [];

        $bundles = $this->bundles($products->keyBy('id'));

        // Data ringkas untuk pencarian instan & Quick View (Alpine.js).
        $catalog = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'sku' => $p->sku,
            'category' => $p->category->name,
            'category_slug' => $p->category->slug,
            'description' => $p->description,
            'unit' => $p->unit,
            'price' => (float) $p->selling_price,
            'price_label' => rupiah($p->selling_price),
            'stock' => $p->current_stock,
            'fast' => $p->isFastMoving(),
            'image' => $p->imageUrl(),
            'initials' => $p->initials(),
            'wishlisted' => in_array($p->id, $wishlistIds, true),
        ])->values();

        return view('storefront.index', [
            'categories' => $categories,
            'products' => $products,
            'catalog' => $catalog,
            'bundles' => $bundles,
            'wishlistIds' => $wishlistIds,
            'activeCategory' => $request->query('kategori'),
            'search' => $request->query('q'),
        ]);
    }

    /**
     * Paket bundling unik dari aturan Apriori (deduplikasi berdasarkan himpunan produk).
     */
    private function bundles($productsById): array
    {
        $bundles = [];
        foreach (AprioriRule::query()->orderByDesc('lift_ratio')->orderByDesc('confidence')->get() as $rule) {
            $ids = $rule->productIds();
            $key = implode('-', $ids);
            if (isset($bundles[$key])) {
                continue;
            }
            $items = collect($ids)->map(fn ($id) => $productsById->get($id))->filter();
            if ($items->count() !== count($ids)) {
                continue;
            }
            $normal = $items->sum(fn ($p) => (float) $p->selling_price);
            $bundles[$key] = [
                'rule' => $rule,
                'items' => $items,
                'normal' => $normal,
                'price' => $normal * (100 - $rule->bundle_discount_percent) / 100,
            ];
            if (count($bundles) >= 6) {
                break;
            }
        }

        return array_values($bundles);
    }
}
