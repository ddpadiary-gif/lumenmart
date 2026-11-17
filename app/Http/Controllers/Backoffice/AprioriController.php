<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AprioriRule;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Services\AprioriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AprioriController extends Controller
{
    public function index(AprioriService $apriori): View
    {
        if (AprioriRule::count() === 0 && SalesTransaction::exists()) {
            $apriori->generate();
        }

        $rules = AprioriRule::query()
            ->orderByDesc('lift_ratio')
            ->orderByDesc('confidence')
            ->get();

        $productsById = Product::with('category:id,name')->get()->keyBy('id');
        $totalTransactions = SalesTransaction::count();

        return view('backoffice.analytics.apriori', compact('rules', 'productsById', 'totalTransactions'));
    }

    public function generate(Request $request, AprioriService $apriori): RedirectResponse
    {
        $data = $request->validate([
            'min_support_percent' => ['nullable', 'numeric', 'min:0.5', 'max:50'],
            'min_confidence_percent' => ['nullable', 'numeric', 'min:10', 'max:95'],
        ]);

        $minSupport = isset($data['min_support_percent'])
            ? ((float) $data['min_support_percent']) / 100
            : AprioriService::DEFAULT_MIN_SUPPORT;

        $minConfidence = isset($data['min_confidence_percent'])
            ? ((float) $data['min_confidence_percent']) / 100
            : AprioriService::DEFAULT_MIN_CONFIDENCE;

        $summary = $apriori->generate($minSupport, $minConfidence);

        return redirect()->route('backoffice.apriori.index')->with(
            'status',
            sprintf(
                'Algoritma Apriori selesai: %d transaksi dianalisis (|L1|=%d, |L2|=%d, |L3|=%d) → %d aturan dengan Lift > 1.0.',
                $summary['transactions'],
                $summary['l1'],
                $summary['l2'],
                $summary['l3'],
                $summary['rules'],
            )
        );
    }
}
