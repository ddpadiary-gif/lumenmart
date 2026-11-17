<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('backoffice.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($data['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        // Pastikan folder lokal untuk kategori baru tersedia.
        $dir = public_path("assets/products/{$slug}");
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('status', "Kategori {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('status', "Kategori {$category->name} berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', "Kategori {$category->name} tidak dapat dihapus karena masih memiliki produk.");
        }

        $category->delete();

        return back()->with('status', "Kategori {$category->name} berhasil dihapus.");
    }
}
