<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AprioriRule;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);

        $products = Product::with('category:id,name,slug')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'unit' => $p->unit,
                'category' => $p->category->name,
                'category_slug' => $p->category->slug,
                'price' => (float) $p->selling_price,
                'stock' => (int) $p->current_stock,
                'image' => $p->imageUrl(),
                'initials' => $p->initials(),
            ])
            ->values();

        $rules = AprioriRule::query()
            ->orderByDesc('lift_ratio')
            ->orderByDesc('confidence')
            ->get()
            ->map(fn (AprioriRule $r) => [
                'id' => $r->id,
                'product_ids' => $r->productIds(),
                'label' => trim($r->antecedent_names.' + '.$r->consequent_names),
                'percent' => (int) $r->bundle_discount_percent,
            ])
            ->values();

        $recentPos = SalesTransaction::with('details.product')
            ->where('channel', 'pos_toko')
            ->latest('id')
            ->limit(6)
            ->get();

        // Transaksi yang baru saja disimpan (untuk modal konfirmasi struk).
        $lastTransaction = session('last_invoice')
            ? SalesTransaction::with('details.product')->where('invoice_number', session('last_invoice'))->first()
            : null;

        return view('backoffice.pos.index', compact('categories', 'products', 'rules', 'recentPos', 'lastTransaction'));
    }

    public function store(Request $request, SalesService $sales): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['tunai', 'qris'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $items = [];
        foreach ($data['items'] as $row) {
            $pid = (int) $row['product_id'];
            $items[$pid] = ($items[$pid] ?? 0) + (int) $row['quantity'];
        }

        $transaction = $sales->create(
            user: $request->user(),
            items: $items,
            channel: 'pos_toko',
            paymentMethod: $data['payment_method'],
            status: 'selesai',
        );

        return redirect()->route('backoffice.pos.index')
            ->with('status', "Transaksi POS {$transaction->invoice_number} sebesar ".rupiah($transaction->total_amount).' berhasil disimpan.')
            ->with('last_invoice', $transaction->invoice_number);
    }
}
