<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\SalesTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcommerceOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = SalesTransaction::with(['user:id,name,email', 'details.product'])
            ->where('channel', 'ecommerce')
            ->when($request->filled('status'), fn ($q) => $q->where('order_status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('invoice_number', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = SalesTransaction::where('channel', 'ecommerce')
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status')
            ->all();

        return view('backoffice.orders.index', compact('orders', 'counts'));
    }

    public function updateStatus(Request $request, SalesTransaction $order): RedirectResponse
    {
        $data = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(SalesTransaction::STATUSES))],
        ]);

        $order->update(['order_status' => $data['order_status']]);

        return back()->with('status', "Status pesanan {$order->invoice_number} diperbarui menjadi {$order->statusLabel()}.");
    }
}
