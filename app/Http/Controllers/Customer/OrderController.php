<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SalesTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->salesTransactions()
            ->where('channel', 'ecommerce')
            ->with('details.product')
            ->withCount('details')
            ->latest('id')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, SalesTransaction $order): View
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
        $order->load('details.product.category');

        return view('customer.orders.show', compact('order'));
    }

    /** Simulasi konfirmasi pembayaran (Transfer Bank / QRIS). */
    public function pay(Request $request, SalesTransaction $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        if ($order->order_status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu pembayaran.');
        }

        $order->update(['order_status' => 'diproses']);

        return back()->with('status', 'Pembayaran berhasil dikonfirmasi (simulasi). Pesanan sedang diproses.');
    }
}
