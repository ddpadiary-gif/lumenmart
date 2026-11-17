<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\IncomingTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncomingTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category:id,name')
            ->orderByRaw('CASE WHEN current_stock <= min_stock_threshold THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get();

        $transactions = IncomingTransaction::with(['product.category', 'user:id,name'])
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $selectedProductId = $request->integer('product_id') ?: null;

        return view('backoffice.incoming.index', compact('products', 'transactions', 'selectedProductId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'supplier_name' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            IncomingTransaction::create([
                ...$data,
                'user_id' => $request->user()->id,
            ]);

            $product->increment('current_stock', (int) $data['quantity']);
            $product->update(['purchase_price' => $data['unit_cost']]);

            return $product->fresh();
        });

        return redirect()->route('backoffice.incoming.index')
            ->with('status', "Restock {$data['quantity']} {$product->unit} {$product->name} berhasil dicatat. Stok kini: {$product->current_stock} {$product->unit}.");
    }
}
