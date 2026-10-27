<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\Announcement;
use App\Services\PaymentRouteService;
use App\Services\P2PBlockchainService;
use App\Services\MatrixIncomeService;
use App\Services\TreePlacementService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected PaymentRouteService $paymentRouteService;
    protected P2PBlockchainService $blockchainService;
    protected MatrixIncomeService $matrixIncomeService;
    protected TreePlacementService $treePlacementService;

    public function __construct(
        PaymentRouteService $paymentRouteService,
        P2PBlockchainService $blockchainService,
        MatrixIncomeService $matrixIncomeService,
        TreePlacementService $treePlacementService
    ) {
        $this->paymentRouteService = $paymentRouteService;
        $this->blockchainService = $blockchainService;
        $this->matrixIncomeService = $matrixIncomeService;
        $this->treePlacementService = $treePlacementService;
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

        // Self-heal: ensure unpaid member has a valid slot position
        if (!$currentPosition && !$user->is_paid) {
            $sponsor = $user->sponsor ?? User::where('member_id', 'COMPANY01')->first();
            if ($sponsor) {
                $currentPosition = $this->treePlacementService->placeInWorkingTree($user, $sponsor);
                $userPositions->put($currentSlot->slot_number, $currentPosition);
            }
        }

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
     * Get dynamic Web3 routing info for 1-click payment execution
     */
    public function paymentRouteInfo(Request $request)
    {
        $user = authUser();
        $slotNumber = (int) $request->input('slot_number', $user->current_slot_id ?: 1);
        $slot = Slot::where('slot_number', $slotNumber)->firstOrFail();

        $position = SlotPosition::where('user_id', $user->id)
            ->where('slot_id', $slot->id)
            ->first();

        // Self-heal: ensure position exists for unpaid user
        if (!$position && !$user->is_paid) {
            $sponsor = $user->sponsor ?? User::where('member_id', 'COMPANY01')->first();
            if ($sponsor) {
                $position = $this->treePlacementService->placeInWorkingTree($user, $sponsor);
            }
        }

        if (!$position) {
            return response()->json([
                'status' => false,
                'message' => 'Position not found in slot matrix.'
            ], 404);
        }

        $routing = $this->paymentRouteService->resolvePayee($position, $slot);
        $payeeUser = $routing['payee_user'] ?? null;
        $payeeWallet = $routing['wallet_address'];

        return response()->json([
            'status' => true,
            'slot_number' => $slot->slot_number,
            'slot_name' => $slot->name,
            'amount' => (float) $routing['amount'],
            'amount_wei' => bcmul((string) $routing['amount'], '1000000000000000000'),
            'payee_name' => $payeeUser ? $payeeUser->name : 'Company Root',
            'payee_member_id' => $payeeUser ? $payeeUser->member_id : 'COMPANY01',
            'payee_address' => $payeeWallet,
            'payee_masked_address' => maskAddress($payeeWallet),
            'category' => $routing['category'],
            'is_company_wallet' => $routing['is_company_wallet'] ?? false,
            'usdt_contract' => env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'),
            'chain_id' => (int) env('BSC_CHAIN_ID', 56),
            'rpc_url' => env('BSC_RPC_URL', 'https://bsc-dataseed.binance.org'),
        ]);
    }

    /**
     * Handle "Track Payment" action from member dashboard
     */
    public function trackPayment(Request $request)
    {
        $user = authUser();

        if (!$user->wallet_address) {
            $msg = 'Please bind your personal BEP-20 USDT wallet address before verifying payments.';
            return $request->wantsJson() ? response()->json(['status' => false, 'message' => $msg], 422) : back()->with('error', $msg);
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
            return $request->wantsJson()
                ? response()->json(['status' => false, 'message' => $verification['message']], 400)
                : back()->with('error', $verification['message']);
        }

        // Process slot activation & distribute income
        $result = $this->matrixIncomeService->processSlotActivation($user, $slot, $verification);

        return $request->wantsJson()
            ? response()->json([
                'status' => true,
                'message' => $result['message'],
                'slot_number' => $slot->slot_number,
                'tx_hash' => $verification['tx_hash'],
            ])
            : back()->with('success', $result['message']);
    }
}
