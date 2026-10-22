<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\Announcement;
use App\Services\PaymentRouteService;
use App\Services\P2PBlockchainService;
use App\Services\MatrixIncomeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected PaymentRouteService $paymentRouteService;
    protected P2PBlockchainService $blockchainService;
    protected MatrixIncomeService $matrixIncomeService;

    public function __construct(
        PaymentRouteService $paymentRouteService,
        P2PBlockchainService $blockchainService,
        MatrixIncomeService $matrixIncomeService
    ) {
        $this->paymentRouteService = $paymentRouteService;
        $this->blockchainService = $blockchainService;
        $this->matrixIncomeService = $matrixIncomeService;
    }

    public function index()
    {
        $user = authUser();
        $earnings = $user->getEarningsSummary();

        // Slots Progression
        $allSlots = Slot::orderBy('slot_number', 'asc')->get();
        $userPositions = SlotPosition::where('user_id', $user->id)
            ->get()
            ->keyBy('slot_number');

        // Current Active Slot Position
        $currentSlot = Slot::where('slot_number', $user->current_slot_id)->first() ?? $allSlots->first();
        $currentPosition = $userPositions->get($currentSlot->slot_number);

        // Calculate assigned payee for activation (if unpaid in current slot or ready for next)
        $assignedPayee = null;
        if ($currentPosition && !$user->is_paid) {
            $assignedPayee = $this->paymentRouteService->resolvePayee($currentPosition, $currentSlot);
        }

        // Active announcements for modal
        $announcements = Announcement::where('is_active', true)->latest()->get();

        // Team counts
        $directsCount = $user->directs()->count();
        $activeDirectsCount = $user->activeDirects()->count();
        $totalDownlineCount = $user->getDownlineUsers()->count();

        // Recent Incomes
        $recentIncomes = $user->unifiedTransactions()
            ->where('status', 'Completed')
            ->take(5)
            ->get();

        return view('member.dashboard', compact(
            'user',
            'earnings',
            'allSlots',
            'userPositions',
            'currentSlot',
            'currentPosition',
            'assignedPayee',
            'announcements',
            'directsCount',
            'activeDirectsCount',
            'totalDownlineCount',
            'recentIncomes'
        ));
    }

    /**
     * Handle "Track Payment" action from member dashboard
     */
    public function trackPayment(Request $request)
    {
        $user = authUser();

        if (!$user->wallet_address) {
            return back()->with('error', 'Please bind your personal BEP-20 USDT wallet address in your Profile before verifying payments.');
        }

        $slotNumber = (int) $request->input('slot_number', $user->current_slot_id);
        $slot = Slot::where('slot_number', $slotNumber)->firstOrFail();

        $position = SlotPosition::where('user_id', $user->id)
            ->where('slot_id', $slot->id)
            ->firstOrFail();

        // Resolve assigned payee
        $routing = $this->paymentRouteService->resolvePayee($position, $slot);
        $payeeAddress = $routing['wallet_address'];
        $amount = (float) $routing['amount'];
        $providedHash = $request->input('tx_hash');

        // Verify on-chain transfer
        $verification = $this->blockchainService->verifyTransfer($user->wallet_address, $payeeAddress, $amount, $providedHash);

        if (!$verification['status']) {
            return back()->with('error', $verification['message']);
        }

        // Process slot activation & distribute income
        $result = $this->matrixIncomeService->processSlotActivation($user, $slot, $verification);

        return back()->with('success', $result['message']);
    }
}
