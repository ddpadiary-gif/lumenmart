<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with(['category:id,name,slug', 'latestPrediction'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('movement_type'), fn ($q) => $q->where('movement_type', $request->string('movement_type')))
            ->when($request->boolean('critical'), fn ($q) => $q->critical())
            ->orderBy('name');

        $products = $query->paginate(15)->withQueryString();

        return view('backoffice.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $product = new Product([
            'unit' => 'pcs',
            'min_stock_threshold' => 10,
            'current_stock' => 20,
            'movement_type' => 'fast_moving',
            'is_active' => true,
        ]);

        return view('backoffice.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $category = Category::findOrFail($data['category_id']);
        $slug = $this->uniqueSlug($data['name']);

        $imagePath = $this->handleImageUpload($request, $category->slug, $slug)
            ?? "assets/products/{$category->slug}/{$slug}.jpg";

        Product::create([
            ...$data,
            'slug' => $slug,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('backoffice.products.index')
            ->with('status', "Produk {$data['name']} berhasil ditambahkan.");
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('backoffice.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product->id);
        $category = Category::findOrFail($data['category_id']);
        $slug = $product->name === $data['name'] ? $product->slug : $this->uniqueSlug($data['name'], $product->id);

        $imagePath = $this->handleImageUpload($request, $category->slug, $slug)
            ?? $product->image_path
            ?? "assets/products/{$category->slug}/{$slug}.jpg";

        $product->update([
            ...$data,
            'slug' => $slug,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('backoffice.products.index')
            ->with('status', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->salesDetails()->exists() || $product->incomingTransactions()->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('status', "Produk {$product->name} memiliki riwayat transaksi sehingga dinonaktifkan dari katalog.");
        }

        $product->delete();

        return back()->with('status', "Produk {$product->name} berhasil dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'min_stock_threshold' => ['required', 'integer', 'min:0'],
            'movement_type' => ['required', Rule::in(['fast_moving', 'slow_moving'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function handleImageUpload(Request $request, string $categorySlug, string $productSlug): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $dir = public_path("assets/products/{$categorySlug}");
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filename = "{$productSlug}.jpg";
        $file->move($dir, $filename);

        return "assets/products/{$categorySlug}/{$filename}";
    }
}
