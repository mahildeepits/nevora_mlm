<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;

class SlotManagerController extends Controller
{
    public function index()
    {
        $slots = Slot::orderBy('slot_number', 'asc')->get();
        return view('admin.slots.index', compact('slots'));
    }

    public function edit(Slot $slot)
    {
        return view('admin.slots.edit', compact('slot'));
    }

    public function update(Request $request, Slot $slot)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'entry_fee' => 'required|numeric|min:0',
            'reserved_unit_amount' => 'required|numeric|min:0',
            'company_deduction' => 'required|numeric|min:0',
            'net_payout_amount' => 'required|numeric|min:0',
            'max_recycles' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $slot->update($request->only([
            'name',
            'entry_fee',
            'reserved_unit_amount',
            'company_deduction',
            'net_payout_amount',
            'max_recycles',
            'is_active',
        ]));

        return redirect()->route('admin.slots.index')->with('success', "Slot {$slot->slot_number} configuration updated successfully.");
    }
}
