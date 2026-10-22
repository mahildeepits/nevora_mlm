<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\UnifiedTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Unified Transactions Ledger
     */
    public function transactions(Request $request)
    {
        $user = authUser();
        $query = $user->unifiedTransactions()->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('slot')) {
            $query->where('slot_number', $request->slot);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('tx_hash', 'LIKE', "%$s%")
                  ->orWhere('description', 'LIKE', "%$s%");
            });
        }

        $transactions = $query->paginate(20)->withQueryString();
        $summary = $user->getEarningsSummary();

        return view('member.wallet.transactions', compact('transactions', 'summary'));
    }
}
