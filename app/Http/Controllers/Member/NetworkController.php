<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SlotPosition;
use App\Models\UnifiedTransaction;
use App\Services\TreePlacementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class NetworkController extends Controller
{
    protected TreePlacementService $treePlacementService;

    public function __construct(TreePlacementService $treePlacementService)
    {
        $this->treePlacementService = $treePlacementService;
    }

    /**
     * Dedicated Invite Member & Downline Registration Portal
     */
    public function invite(Request $request)
    {
        $user = authUser();
        $refLink = route('member.register.form', ['sponsor' => $user->member_id]);
        $directsCount = $user->directs()->count();
        $activeDirectsCount = $user->activeDirects()->count();
        $recentDirects = $user->directs()->latest()->take(5)->get();

        return view('member.network.invite', compact('user', 'refLink', 'directsCount', 'activeDirectsCount', 'recentDirects'));
    }

    /**
     * Sponsor registers a direct downline member directly from panel
     */
    public function registerDownline(Request $request)
    {
        $sponsor = authUser();

        if (!$sponsor->is_paid) {
            return back()->with('error', 'Only active/paid members are eligible to sponsor downline members.');
        }

        // Direct user to official atomic registration flow with sponsor ID locked
        return redirect()->route('member.register.form', ['sponsor' => $sponsor->member_id])
            ->with('info', 'Please complete downline registration by connecting the Web3 wallet and authorizing the $15 USDT activation fee.');
    }
    /**
     * Module 1: Interactive 3-Matrix Tree Viewer
     */
    public function tree(Request $request, ?string $memberId = null)
    {
        $loggedInUser = authUser();
        $targetUser = $loggedInUser;

        // If search requested or memberId passed
        $search = $request->get('search', $memberId);
        if ($search) {
            $found = User::where('member_id', $search)->first();
            if ($found) {
                // Security check: Only allow viewing downline nodes (or self)
                if ($found->id === $loggedInUser->id || str_contains($found->parent_string ?? '', (string) $loggedInUser->id)) {
                    $targetUser = $found;
                } else {
                    return back()->with('error', 'You can only view members within your downline hierarchy.');
                }
            } else {
                return back()->with('error', 'Member ID not found.');
            }
        }

        // Build 3-matrix tree data (Root + Level 1 (3) + Level 2 (9))
        $treeData = $this->buildTreeData($targetUser, 2);

        // Previous user for UP button
        $parentUser = null;
        if ($targetUser->id !== $loggedInUser->id && $targetUser->parent_id) {
            $parentUser = User::where('member_id', $targetUser->parent_id)->first();
        }

        return view('member.network.tree', compact('targetUser', 'treeData', 'parentUser', 'loggedInUser'));
    }

    private function buildTreeData(User $user, int $maxDepth, int $currentDepth = 0): array
    {
        $slot1Pos = SlotPosition::where('user_id', $user->id)
            ->where('slot_number', 1)
            ->first();

        $directChildren = [];
        if ($currentDepth < $maxDepth && $slot1Pos) {
            $childIds = $slot1Pos->child_position_ids ?? [];
            foreach ($childIds as $cPosId) {
                $cPos = SlotPosition::find($cPosId);
                if ($cPos) {
                    $cUser = User::find($cPos->user_id);
                    if ($cUser) {
                        $directChildren[] = $this->buildTreeData($cUser, $maxDepth, $currentDepth + 1);
                    }
                }
            }
        }

        // Fill remaining empty slots up to 3
        while (count($directChildren) < 3 && $currentDepth < $maxDepth) {
            $directChildren[] = null; // empty available slot
        }

        return [
            'id' => $user->id,
            'member_id' => $user->member_id,
            'name' => $user->name,
            'is_paid' => $user->is_paid,
            'current_slot_id' => $user->current_slot_id,
            'image' => $user->profile_image_url,
            'join_date' => $user->created_at->format('d M Y'),
            'directs_count' => $user->directs()->count(),
            'children' => $directChildren,
        ];
    }

    /**
     * Module 2: My Directs List
     */
    public function myDirects(Request $request)
    {
        $user = authUser();
        $query = $user->directs();

        if ($request->filled('status')) {
            $query->where('is_paid', $request->status === 'paid');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%$s%")
                  ->orWhere('member_id', 'LIKE', "%$s%")
                  ->orWhere('email', 'LIKE', "%$s%");
            });
        }

        $directs = $query->paginate(15)->withQueryString();

        return view('member.network.directs', compact('directs'));
    }

    /**
     * Module 3: Downline Team Hierarchy
     */
    public function teamHierarchy(Request $request)
    {
        $user = authUser();

        // Get all downlines using ancestor parent_string
        $downlineQuery = User::whereRaw("FIND_IN_SET(?, parent_string)", [$user->id])
            ->where('id', '!=', $user->id);

        if ($request->filled('status')) {
            $downlineQuery->where('is_paid', $request->status === 'paid');
        }

        if ($request->filled('slot')) {
            $downlineQuery->where('current_slot_id', $request->slot);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $downlineQuery->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%$s%")
                  ->orWhere('member_id', 'LIKE', "%$s%")
                  ->orWhere('email', 'LIKE', "%$s%");
            });
        }

        $team = $downlineQuery->latest()->paginate(20)->withQueryString();

        return view('member.network.hierarchy', compact('team'));
    }

    /**
     * Module 4: Downline Business Report
     */
    public function downlineBusiness(Request $request)
    {
        $user = authUser();
        $directs = $user->directs()->get();

        $businessReport = [];
        $totalNetworkVolume = 0;
        $totalNetworkUsers = 0;

        foreach ($directs as $direct) {
            // Find all users in this direct leg
            $legUserIds = User::whereRaw("FIND_IN_SET(?, parent_string)", [$direct->id])
                ->pluck('id')
                ->push($direct->id)
                ->unique();

            $legUsersCount = $legUserIds->count();
            $legPaidCount = User::whereIn('id', $legUserIds)->where('is_paid', true)->count();

            // Total volume generated in this leg from verified transactions
            $legVolume = (float) UnifiedTransaction::whereIn('user_id', $legUserIds)
                ->where('status', 'Completed')
                ->whereIn('category', ['Slot Income', 'Reserved for Next Slot', 'Recycle Income'])
                ->sum('amount');

            $businessReport[] = [
                'direct' => $direct,
                'total_members' => $legUsersCount,
                'paid_members' => $legPaidCount,
                'turnover' => $legVolume,
            ];

            $totalNetworkVolume += $legVolume;
            $totalNetworkUsers += $legUsersCount;
        }

        return view('member.network.business', compact('businessReport', 'totalNetworkVolume', 'totalNetworkUsers'));
    }
}
