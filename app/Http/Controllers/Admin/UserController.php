<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Services\MatrixIncomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected MatrixIncomeService $matrixIncomeService;

    public function __construct(MatrixIncomeService $matrixIncomeService)
    {
        $this->matrixIncomeService = $matrixIncomeService;
    }

    public function index(Request $request)
    {
        $query = User::where('role_id', '!=', 1)->latest();

        if ($request->filled('status')) {
            $query->where('is_paid', $request->status === 'paid');
        }

        if ($request->filled('slot')) {
            $query->where('current_slot_id', $request->slot);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%$s%")
                  ->orWhere('member_id', 'LIKE', "%$s%")
                  ->orWhere('email', 'LIKE', "%$s%")
                  ->orWhere('wallet_address', 'LIKE', "%$s%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'wallet_address' => 'nullable|string',
            'is_paid' => 'required|boolean',
        ]);

        $user->update($request->only([
            'name', 'mobile', 'email', 'wallet_address', 'is_paid'
        ]));

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function loginAsUser(User $user)
    {
        Auth::guard('web')->login($user);
        return redirect()->route('member.dashboard')->with('success', "Logged in as {$user->name} ({$user->member_id})");
    }

    public function manualActivate(User $user)
    {
        if ($user->is_paid) {
            return back()->with('error', 'User is already activated.');
        }

        $slot1 = Slot::where('slot_number', 1)->firstOrFail();

        $verification = [
            'status' => true,
            'tx_hash' => '0xadmin_manual_' . time() . '_' . $user->id,
            'from' => $user->wallet_address ?: 'offline_manual',
            'to' => 'company_manual',
            'amount' => 15.00,
        ];

        $this->matrixIncomeService->processSlotActivation($user, $slot1, $verification);

        return back()->with('success', "Member {$user->member_id} manually activated into Slot 1.");
    }
}
