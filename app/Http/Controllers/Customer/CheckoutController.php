<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View|RedirectResponse
    {
        $summary = $this->cart->summary();
        if ($summary['lines'] === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda masih kosong.');
        }

        return view('customer.checkout', compact('summary'));
    }

    public function store(Request $request, SalesService $sales): RedirectResponse
    {
        $data = $request->validate([
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', Rule::in(['cod', 'transfer_bank', 'qris'])],
        ]);

        $items = $this->cart->items();

        $order = $sales->create(
            user: $request->user(),
            items: $items,
            channel: 'ecommerce',
            paymentMethod: $data['payment_method'],
            status: $data['payment_method'] === 'cod' ? 'diproses' : 'menunggu_pembayaran',
            shippingAddress: $data['shipping_address'],
        );

        $this->cart->clear();

        return redirect()->route('orders.show', $order)->with('status', 'Pesanan berhasil dibuat. Terima kasih telah berbelanja di LUMEN MART!');
    }
}
