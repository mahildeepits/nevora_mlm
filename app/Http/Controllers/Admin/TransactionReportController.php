<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnifiedTransaction;
use Illuminate\Http\Request;

class TransactionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = UnifiedTransaction::with(['user', 'fromUser'])->latest();

        // Transaction Type Filter (defaults to 'Credit' for platform value transfers, preventing double-counting with mirror debits)
        if ($request->filled('type') && $request->type !== 'All') {
            $query->where('transaction_type', $request->type);
        } elseif (!$request->filled('type') && !$request->filled('category')) {
            $query->where('transaction_type', 'Credit');
        }

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
                  ->orWhere('from_address', 'LIKE', "%$s%")
                  ->orWhere('to_address', 'LIKE', "%$s%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'LIKE', "%$s%")->orWhere('member_id', 'LIKE', "%$s%");
                  });
            });
        }

        $transactions = $query->paginate(25)->withQueryString();

        $totalVolume = (float) UnifiedTransaction::where('transaction_type', 'Credit')
            ->where('status', 'Completed')
            ->sum('amount');
        $companyDeductions = (float) UnifiedTransaction::where('category', 'Company Deduction')
            ->where('status', 'Completed')
            ->sum('amount');
        $reservedTotal = (float) UnifiedTransaction::where('category', 'Reserved for Next Slot')
            ->where('status', 'Completed')
            ->sum('amount');

        return view('admin.transactions.index', compact('transactions', 'totalVolume', 'companyDeductions', 'reservedTotal'));
    }
}
