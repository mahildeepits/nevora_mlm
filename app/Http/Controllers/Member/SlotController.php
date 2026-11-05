<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use App\Models\UnifiedTransaction;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    /**
     * Display the 12-Slot Matrix Catalog
     * Fully responsive, displaying active slots and locked slots with frosted-glass overlays.
     */
    public function index()
    {
        $user = authUser();

        // Retrieve all slots, or fallback to default 12 slots if DB unseeded
        $slots = Slot::orderBy('slot_number')->get();

        if ($slots->isEmpty()) {
            $slots = $this->getDefaultSlotsFallback();
        }

        // Attach computed metrics for each slot for the logged-in user
        $slotsData = $slots->map(function ($slot) use ($user) {
            $slotNumber = $slot->slot_number;
            $isActive = $user->is_paid && ($user->current_slot_id >= $slotNumber);

            // Compute downline team placed in this slot
            $slotPos = SlotPosition::where('user_id', $user->id)
                ->where('slot_number', $slotNumber)
                ->first();

            $teamCount = 0;
            if ($slotPos && !empty($slotPos->child_position_ids)) {
                $teamCount = count($slotPos->child_position_ids);
                foreach ($slotPos->child_position_ids as $cId) {
                    $cPos = SlotPosition::find($cId);
                    if ($cPos && !empty($cPos->child_position_ids)) {
                        $teamCount += count($cPos->child_position_ids);
                    }
                }
            }

            // Recycle / Reinvest Count
            $recycleCount = RecyclePosition::where('user_id', $user->id)
                ->where('slot_number', $slotNumber)
                ->count();

            // Total Slot Earnings
            $totalEarning = UnifiedTransaction::where('user_id', $user->id)
                ->where('slot_number', $slotNumber)
                ->where('status', 'confirmed')
                ->sum('amount');

            $multiplier = $slot->entry_fee > 0 ? round($totalEarning / $slot->entry_fee, 1) : 0;

            return [
                'model' => $slot,
                'slot_number' => $slotNumber,
                'name' => $slot->name,
                'entry_fee' => (float) $slot->entry_fee,
                'is_active' => $isActive,
                'team_count' => $teamCount,
                'recycle_count' => $recycleCount,
                'total_earning' => (float) $totalEarning,
                'multiplier' => $multiplier > 0 ? "{$multiplier}x" : "0.0x",
            ];
        });

        // User stats summary
        $totalSlotEarnings = UnifiedTransaction::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        return view('member.slots.index', compact('user', 'slotsData', 'totalSlotEarnings'));
    }

    /**
     * Display the B-TITAN Exact Slot Matrix Tree View with Payment Routing
     * Responsive across mobile with Bottom Drawer for payment inspection
     * Displays ONLY unique member_id inside geometric shapes matching B-TITAN
     */
    public function showTree(Request $request, int $slot_number, ?string $memberId = null)
    {
        $loggedInUser = authUser();
        $targetUser = $loggedInUser;

        // Support searching or drilling down into downlines
        $search = $request->get('search', $memberId);
        if ($search) {
            $found = User::where('member_id', $search)->first();
            if ($found) {
                // Security check: Only allow self, admin, or downline lineage
                $isAllowed = false;
                if ($found->id === $loggedInUser->id || $loggedInUser->id === 1) {
                    $isAllowed = true;
                } else {
                    $ancestors = array_filter(explode(',', (string) $found->parent_string));
                    $isAllowed = in_array((string) $loggedInUser->id, $ancestors);
                }

                if ($isAllowed) {
                    $targetUser = $found;
                } else {
                    return back()->with('error', 'You can only view members within your team hierarchy.');
                }
            } else {
                return back()->with('error', 'Member ID not found.');
            }
        }

        // Get slot details
        $slot = Slot::where('slot_number', $slot_number)->first();
        if (!$slot) {
            $slot = (object) [
                'id' => $slot_number,
                'slot_number' => $slot_number,
                'name' => "Slot {$slot_number}",
                'entry_fee' => $this->getDefaultEntryFee($slot_number),
            ];
        }

        // Check if slot is active for target user
        $isTargetSlotActive = $targetUser->is_paid && ($targetUser->current_slot_id >= $slot_number);

        // If logged-in user hasn't activated this slot, redirect with warning
        if ($targetUser->id === $loggedInUser->id && !$isTargetSlotActive) {
            return redirect()->route('member.slots.index')
                ->with('error', "Slot {$slot_number} is currently locked. Activate it first to view its tree structure.");
        }

        // Cycle Pagination (e.g. Cycle 1 of N)
        // Check highest recorded cycle for this user in this slot
        $maxRecycleCycle = RecyclePosition::where('user_id', $targetUser->id)
            ->where('slot_number', $slot_number)
            ->max('cycle_number');

        $totalCycles = max(1, (int) $maxRecycleCycle);
        // Default to cycle 1 (or the cycle requested in ?cycle=)
        $currentCycle = (int) $request->get('cycle', 1);
        if ($currentCycle < 1) $currentCycle = 1;
        if ($currentCycle > $totalCycles) $currentCycle = $totalCycles;

        $hasPrevCycle = $currentCycle > 1;
        $hasNextCycle = $currentCycle < $totalCycles;

        // Build exact 3-Matrix Data with Payment Attribution
        $spots = $this->buildBTitanSpots($targetUser, $slot_number, $currentCycle, $loggedInUser);

        // Parent user for "Up One Level" button
        $parentUser = null;
        if ($targetUser->id !== $loggedInUser->id && $targetUser->parent_id) {
            $parentUser = User::where('member_id', $targetUser->parent_id)->first();
        }

        // Build Breadcrumbs from loggedInUser down to targetUser
        $breadcrumbs = $this->buildBreadcrumbs($loggedInUser, $targetUser, $slot_number);

        // AJAX dynamic tree response (no full-page reload)
        if ($request->ajax() || $request->get('ajax')) {
            return response()->json([
                'success' => true,
                'slot_number' => $slot_number,
                'targetUser' => [
                    'id' => $targetUser->id,
                    'member_id' => $targetUser->member_id,
                    'name' => $targetUser->name,
                    'is_root' => ($targetUser->id === $loggedInUser->id),
                ],
                'breadcrumbs' => $breadcrumbs,
                'spots' => $spots,
                'currentCycle' => $currentCycle,
                'totalCycles' => $totalCycles,
                'hasPrevCycle' => $hasPrevCycle,
                'hasNextCycle' => $hasNextCycle,
            ]);
        }

        return view('member.slots.tree', compact(
            'slot',
            'slot_number',
            'targetUser',
            'loggedInUser',
            'parentUser',
            'breadcrumbs',
            'spots',
            'currentCycle',
            'totalCycles',
            'hasPrevCycle',
            'hasNextCycle'
        ));
    }

    /**
     * Build Breadcrumb trail from loggedInUser to targetUser
     */
    private function buildBreadcrumbs(User $loggedInUser, User $targetUser, int $slotNumber): array
    {
        if ($targetUser->id === $loggedInUser->id) {
            return [
                [
                    'member_id' => $loggedInUser->member_id,
                    'name' => $loggedInUser->name,
                    'is_root' => true,
                    'is_current' => true,
                    'url' => route('member.slots.tree', ['slot_number' => $slotNumber]),
                ]
            ];
        }

        $chain = [];
        $curr = $targetUser;
        while ($curr) {
            $chain[] = [
                'member_id' => $curr->member_id,
                'name' => $curr->name,
                'is_root' => ($curr->id === $loggedInUser->id),
                'is_current' => ($curr->id === $targetUser->id),
                'url' => route('member.slots.tree', [
                    'slot_number' => $slotNumber,
                    'memberId' => $curr->id === $loggedInUser->id ? null : $curr->member_id
                ]),
            ];
            if ($curr->id === $loggedInUser->id) break;
            $parentMemberId = $curr->parent_id;
            $curr = $parentMemberId ? User::where('member_id', $parentMemberId)->first() : null;
        }

        return array_reverse($chain);
    }

    /**
     * Calculate generational distance between ancestor and descendant
     */
    private function getGenerationDistance(User $ancestor, User $descendant): int
    {
        if ($ancestor->id === $descendant->id) return 0;

        $hops = 0;
        $curr = $descendant;
        while ($curr && $hops < 15) {
            if ($curr->id === $ancestor->id) return $hops;
            $hops++;
            $curr = $curr->parent_id ? User::where('member_id', $curr->parent_id)->first() : null;
        }
        return $hops;
    }

    /**
     * Resolve node visual shape, role, and label based on 3-matrix lineage relative to loggedInUser
     */
    private function resolveSpotRule(int $spotIndex, int $generation, int $targetLeg = 1): array
    {
        // 1. Generation 0: Logged-in user is Root
        if ($generation === 0) {
            $rules = [
                1 => ['upline_triangle', 'upline', 'Upline'],
                2 => ['upline_triangle', 'upline', 'Upline'],
                3 => ['income_square', 'your_income', 'Your Income (#1)'],

                4 => ['income_square', 'your_income', 'Your Income (#2)'],
                5 => ['upline_triangle', 'upline', 'Upline'],
                6 => ['downline_triangle', 'downline', 'Downline'],

                7 => ['upgrade_square', 'buy_level', 'Next Slot Reserve (#3)'],
                8 => ['upline_triangle', 'upline', 'Upline'],
                9 => ['downline_triangle', 'downline', 'Downline'],

                10 => ['upgrade_square', 'buy_level', 'Next Slot Reserve (#4)'],
                11 => ['upline_triangle', 'upline', 'Upline'],
                12 => ['downline_triangle', 'downline', 'Downline'],
            ];
            return $rules[$spotIndex] ?? ['income_square', 'your_income', 'Your Income'];
        }

        // 2. Generation 1: Viewing a Level 1 child of loggedInUser
        if ($generation === 1) {
            $firstIncomeRule = ($targetLeg == 1)
                ? ['income_square', 'your_income', 'Your Income (#2)']
                : (($targetLeg == 2)
                    ? ['upgrade_square', 'buy_level', 'Next Slot Reserve (#3)']
                    : ['upgrade_square', 'buy_level', 'Next Slot Reserve (#4)']);

            $rules = [
                // Level 1 of target = Level 2 of logged-in user
                1 => $firstIncomeRule,
                2 => ['upline_triangle', 'upline', 'Upline'],
                3 => ['downline_triangle', 'downline', 'Downline'],

                // Level 2 of target = Level 3 of logged-in user
                4 => ['downline_triangle', 'downline', 'Downline'],
                5 => ['upgrade_square', 'buy_level', 'Next Slot Reserve (#5)'],
                6 => ['downline_triangle', 'downline', 'Downline'],

                7 => ['downline_triangle', 'downline', 'Downline'],
                8 => ['income_square', 'your_income', 'Your Income (#6)'],
                9 => ['downline_triangle', 'downline', 'Downline'],

                10 => ['downline_triangle', 'downline', 'Downline'],
                11 => ['income_square', 'your_income', 'Your Income (#7)'],
                12 => ['downline_triangle', 'downline', 'Downline'],
            ];
            return $rules[$spotIndex] ?? ['downline_triangle', 'downline', 'Downline'];
        }

        // 3. Generation 2: Viewing deeper downlines (Level 2+)
        if ($generation === 2) {
            $rules = [
                // Level 1 of target = Level 3 of logged-in user
                1 => ['downline_triangle', 'downline', 'Downline'],
                2 => ['recycle_spiral', 'recycle', 'Recycle Payment (#13)'],
                3 => ['downline_triangle', 'downline', 'Downline'],

                // Level 2 of target = Level 4 of logged-in user (neutral/downline shapes)
                4 => ['upline_triangle', 'upline', 'Upline'],
                5 => ['income_square', 'neutral', 'Team Member'],
                6 => ['downline_triangle', 'downline', 'Downline'],

                7 => ['upline_triangle', 'upline', 'Upline'],
                8 => ['income_square', 'neutral', 'Team Member'],
                9 => ['downline_triangle', 'downline', 'Downline'],

                10 => ['upline_triangle', 'upline', 'Upline'],
                11 => ['income_square', 'neutral', 'Team Member'],
                12 => ['downline_triangle', 'downline', 'Downline'],
            ];
            return $rules[$spotIndex] ?? ['downline_triangle', 'downline', 'Downline'];
        }

        // 4. Default for deeper nodes
        $deepRules = [
            1 => ['downline_triangle', 'downline', 'Downline'],
            2 => ['income_square', 'neutral', 'Team Member'],
            3 => ['downline_triangle', 'downline', 'Downline'],

            4 => ['upline_triangle', 'upline', 'Upline'],
            5 => ['income_square', 'neutral', 'Team Member'],
            6 => ['downline_triangle', 'downline', 'Downline'],

            7 => ['upline_triangle', 'upline', 'Upline'],
            8 => ['income_square', 'neutral', 'Team Member'],
            9 => ['downline_triangle', 'downline', 'Downline'],

            10 => ['upline_triangle', 'upline', 'Upline'],
            11 => ['income_square', 'neutral', 'Team Member'],
            12 => ['downline_triangle', 'downline', 'Downline'],
        ];
        return $deepRules[$spotIndex] ?? ['downline_triangle', 'downline', 'Downline'];
    }

    /**
     * Build the 3-Matrix Structure Array
     */
    private function buildBTitanSpots(User $targetUser, int $slotNumber, int $cycle, User $loggedInUser): array
    {
        // Find position record (SlotPosition for Cycle 1, RecyclePosition for Cycle > 1)
        $posRecord = null;
        $isRecycle = ($cycle > 1);
        if ($cycle === 1) {
            $posRecord = SlotPosition::where('user_id', $targetUser->id)
                ->where('slot_number', $slotNumber)
                ->first();
            if (!$posRecord) {
                $posRecord = RecyclePosition::where('user_id', $targetUser->id)
                    ->where('slot_number', $slotNumber)
                    ->where('cycle_number', 1)
                    ->first();
                if ($posRecord) $isRecycle = true;
            }
        } else {
            $posRecord = RecyclePosition::where('user_id', $targetUser->id)
                ->where('slot_number', $slotNumber)
                ->where('cycle_number', $cycle)
                ->first();
        }

        $childIds = $posRecord ? ($posRecord->child_position_ids ?? []) : [];
        $findPos = function($id) use ($isRecycle) {
            if (!$id) return null;
            return $isRecycle ? RecyclePosition::find($id) : SlotPosition::find($id);
        };

        // Fetch Tier 1 positions (3 children under Root for 3-Matrix)
        $p1 = isset($childIds[0]) ? $findPos($childIds[0]) : null;
        $p2 = isset($childIds[1]) ? $findPos($childIds[1]) : null;
        $p3 = isset($childIds[2]) ? $findPos($childIds[2]) : null;

        // Fetch Tier 2 positions (3 children under each Tier 1 position)
        $p4 = ($p1 && isset($p1->child_position_ids[0])) ? $findPos($p1->child_position_ids[0]) : null;
        $p5 = ($p1 && isset($p1->child_position_ids[1])) ? $findPos($p1->child_position_ids[1]) : null;
        $p6 = ($p1 && isset($p1->child_position_ids[2])) ? $findPos($p1->child_position_ids[2]) : null;

        $p7 = ($p2 && isset($p2->child_position_ids[0])) ? $findPos($p2->child_position_ids[0]) : null;
        $p8 = ($p2 && isset($p2->child_position_ids[1])) ? $findPos($p2->child_position_ids[1]) : null;
        $p9 = ($p2 && isset($p2->child_position_ids[2])) ? $findPos($p2->child_position_ids[2]) : null;

        $p10 = ($p3 && isset($p3->child_position_ids[0])) ? $findPos($p3->child_position_ids[0]) : null;
        $p11 = ($p3 && isset($p3->child_position_ids[1])) ? $findPos($p3->child_position_ids[1]) : null;
        $p12 = ($p3 && isset($p3->child_position_ids[2])) ? $findPos($p3->child_position_ids[2]) : null;

        // Compute generational distance between loggedInUser and targetUser
        $generation = $this->getGenerationDistance($loggedInUser, $targetUser);
        $targetLeg = $posRecord ? ($posRecord->leg_position ?? 1) : 1;

        // Resolve rules for spots 1 to 12
        $r1 = $this->resolveSpotRule(1, $generation, $targetLeg);
        $r2 = $this->resolveSpotRule(2, $generation, $targetLeg);
        $r3 = $this->resolveSpotRule(3, $generation, $targetLeg);

        $r4 = $this->resolveSpotRule(4, $generation, $targetLeg);
        $r5 = $this->resolveSpotRule(5, $generation, $targetLeg);
        $r6 = $this->resolveSpotRule(6, $generation, $targetLeg);

        $r7 = $this->resolveSpotRule(7, $generation, $targetLeg);
        $r8 = $this->resolveSpotRule(8, $generation, $targetLeg);
        $r9 = $this->resolveSpotRule(9, $generation, $targetLeg);

        $r10 = $this->resolveSpotRule(10, $generation, $targetLeg);
        $r11 = $this->resolveSpotRule(11, $generation, $targetLeg);
        $r12 = $this->resolveSpotRule(12, $generation, $targetLeg);

        // Format each spot
        $spots = [
            'root' => [
                'member_id' => $targetUser->member_id,
                'is_root' => true,
                'is_filled' => true,
                'shape' => 'purple_pill',
                'role' => ($targetUser->id === $loggedInUser->id) ? 'you' : 'downline_root',
                'role_label' => ($targetUser->id === $loggedInUser->id) ? 'You (Root)' : "Team Root ({$targetUser->member_id})",
                'payment' => [
                    'payer_id' => $targetUser->member_id,
                    'payee_id' => $targetUser->member_id,
                    'amount' => $this->getDefaultEntryFee($slotNumber),
                    'category' => 'Root Matrix',
                    'tx_hash' => null,
                ]
            ],
            // Tier 1: 3 Legs under Root
            '1' => $this->formatSpot($p1, $slotNumber, $r1[0], $r1[1], $r1[2], $loggedInUser, $targetUser),
            '2' => $this->formatSpot($p2, $slotNumber, $r2[0], $r2[1], $r2[2], $loggedInUser, $targetUser),
            '3' => $this->formatSpot($p3, $slotNumber, $r3[0], $r3[1], $r3[2], $loggedInUser, $targetUser),

            // Tier 2: 3 Legs under Pos 1
            '4' => $this->formatSpot($p4, $slotNumber, $r4[0], $r4[1], $r4[2], $loggedInUser, $targetUser),
            '5' => $this->formatSpot($p5, $slotNumber, $r5[0], $r5[1], $r5[2], $loggedInUser, $targetUser),
            '6' => $this->formatSpot($p6, $slotNumber, $r6[0], $r6[1], $r6[2], $loggedInUser, $targetUser),

            // Tier 2: 3 Legs under Pos 2
            '7' => $this->formatSpot($p7, $slotNumber, $r7[0], $r7[1], $r7[2], $loggedInUser, $targetUser),
            '8' => $this->formatSpot($p8, $slotNumber, $r8[0], $r8[1], $r8[2], $loggedInUser, $targetUser),
            '9' => $this->formatSpot($p9, $slotNumber, $r9[0], $r9[1], $r9[2], $loggedInUser, $targetUser),

            // Tier 2: 3 Legs under Pos 3
            '10' => $this->formatSpot($p10, $slotNumber, $r10[0], $r10[1], $r10[2], $loggedInUser, $targetUser),
            '11' => $this->formatSpot($p11, $slotNumber, $r11[0], $r11[1], $r11[2], $loggedInUser, $targetUser),
            '12' => $this->formatSpot($p12, $slotNumber, $r12[0], $r12[1], $r12[2], $loggedInUser, $targetUser),

            // Spillover tags
            'tag_1' => $this->getSpilloverTag($p1, $targetUser),
            'tag_2' => $this->getSpilloverTag($p2, $targetUser),
            'tag_3' => $this->getSpilloverTag($p3, $targetUser),
        ];

        return $spots;
    }

    /**
     * Format a spot for B-TITAN geometric rendering
     */
    private function formatSpot($pos, int $slotNumber, string $shape, string $role, string $roleLabel, User $loggedInUser, User $rootUser): array
    {
        if (!$pos || !$pos->user) {
            return [
                'member_id' => '-',
                'is_filled' => false,
                'is_vacant' => true,
                'shape' => $shape,
                'role' => $role,
                'role_label' => $roleLabel,
                'payment' => null,
            ];
        }

        $user = $pos->user;

        // Query payment transaction
        $tx = UnifiedTransaction::where('from_user_id', $user->id)
            ->where('slot_number', $slotNumber)
            ->latest()
            ->first();

        $payeeUser = $tx && $tx->user ? $tx->user : null;
        $payeeMemberId = $payeeUser ? $payeeUser->member_id : 'COMPANY01';
        $payeeName = $payeeUser ? $payeeUser->name : 'NEVORA Platform';
        $isPaidToMe = ($payeeUser && $payeeUser->id === $loggedInUser->id);
        $amount = $tx ? (float) $tx->amount : $this->getDefaultEntryFee($slotNumber);
        $category = $tx ? $tx->category : 'Slot Income';
        $txHash = $tx ? $tx->tx_hash : null;
        $txDate = $tx ? $tx->created_at->format('d M Y, h:i A') : $user->created_at->format('d M Y');

        return [
            'id' => $user->id,
            'member_id' => $user->member_id,
            'is_filled' => true,
            'is_vacant' => false,
            'shape' => $shape,
            'role' => $role,
            'role_label' => $roleLabel,
            'payment' => [
                'payer_id' => $user->member_id,
                'payer_name' => $user->name,
                'payee_id' => $payeeMemberId,
                'payee_name' => $payeeName,
                'is_paid_to_me' => $isPaidToMe,
                'amount' => $amount,
                'category' => $category,
                'tx_hash' => $txHash,
                'tx_date' => $txDate,
                'status' => $tx ? $tx->status : 'confirmed',
                'bscscan_url' => $txHash ? "https://bscscan.com/tx/{$txHash}" : null,
            ]
        ];
    }

    /**
     * Resolve spillover tag (displays sponsor if different from direct parent)
     */
    private function getSpilloverTag($pos, User $rootUser): string
    {
        if (!$pos || !$pos->user) return '-';
        $u = $pos->user;
        if ($u->parent_id && $u->parent_id !== $rootUser->member_id) {
            return $u->parent_id;
        }
        return '-';
    }

    /**
     * Default slot prices fallback
     */
    private function getDefaultEntryFee(int $slotNumber): float
    {
        $fees = [
            1 => 15.00,
            2 => 45.00,
            3 => 135.00,
            4 => 378.00,
            5 => 1071.00,
            6 => 3096.00,
            7 => 9045.00,
            8 => 26640.00,
            9 => 39366.00,
            10 => 47493.00,
            11 => 55800.00,
            12 => 65610.00,
        ];

        return $fees[$slotNumber] ?? 15.00;
    }

    /**
     * In-memory collection fallback if slots table is not seeded
     */
    private function getDefaultSlotsFallback()
    {
        $names = [
            1 => 'Starter',
            2 => 'Bronze',
            3 => 'Silver',
            4 => 'Gold',
            5 => 'Platinum',
            6 => 'Diamond',
            7 => 'Crown',
            8 => 'Royal',
            9 => 'Imperial',
            10 => 'Titan',
            11 => 'Apex',
            12 => 'Infinity',
        ];

        $collection = collect();
        for ($i = 1; $i <= 12; $i++) {
            $collection->push((object) [
                'id' => $i,
                'slot_number' => $i,
                'name' => "Slot {$i} - " . ($names[$i] ?? "Level {$i}"),
                'entry_fee' => $this->getDefaultEntryFee($i),
                'matrix_type' => $i === 1 ? 'working_tree' : 'autopool',
                'is_active' => true,
            ]);
        }

        return $collection;
    }
}
