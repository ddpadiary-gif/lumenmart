<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * Unduh invoice PDF — pemilik pesanan atau staf (admin/pemilik toko).
     */
    public function __invoke(Request $request, SalesTransaction $order): Response
    {
        $user = $request->user();
        abort_unless($user->isStaff() || (int) $order->user_id === (int) $user->id, 403);

        $order->load(['details.product', 'user']);

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a5', 'portrait')
            ->download("invoice-{$order->invoice_number}.pdf");
    }
}
