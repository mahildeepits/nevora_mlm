<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\UnifiedTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMembers = User::where('role_id', '!=', 1)->count();
        $paidMembers = User::where('role_id', '!=', 1)->where('is_paid', true)->count();
        $unpaidMembers = $totalMembers - $paidMembers;

        $totalTurnover = (float) UnifiedTransaction::where('status', 'Completed')->sum('amount');
        $companyDeductions = (float) UnifiedTransaction::where('category', 'Company Deduction')
            ->where('status', 'Completed')
            ->sum('amount');

        $slots = Slot::orderBy('slot_number', 'asc')->get();

        // Calculate member count per slot
        $slotStats = [];
        foreach ($slots as $slot) {
            $count = SlotPosition::where('slot_id', $slot->id)->count();
            $slotStats[] = [
                'slot' => $slot,
                'count' => $count,
            ];
        }

        $recentTransactions = UnifiedTransaction::with(['user', 'fromUser'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalMembers',
            'paidMembers',
            'unpaidMembers',
            'totalTurnover',
            'companyDeductions',
            'slotStats',
            'recentTransactions'
        ));
    }
}
