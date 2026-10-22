<?php

namespace App\Services;

use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use Illuminate\Support\Facades\DB;
use Exception;

class TreePlacementService
{
    const MAX_CHILDREN = 3;

    /**
     * Place user into Slot 1 Working Tree upon registration
     */
    public function placeInWorkingTree(User $user, User $sponsor): SlotPosition
    {
        $slot1 = Slot::where('slot_number', 1)->firstOrFail();

        // Check if user already placed in Slot 1
        $existing = SlotPosition::where('user_id', $user->id)
            ->where('slot_id', $slot1->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $sponsor, $slot1) {
            // Find sponsor's position in Slot 1
            $sponsorPos = SlotPosition::where('user_id', $sponsor->id)
                ->where('slot_id', $slot1->id)
                ->first();

            if (!$sponsorPos) {
                // Fallback: root company
                $companyUser = User::where('member_id', 'COMPANY01')->first();
                $sponsorPos = SlotPosition::where('user_id', $companyUser->id)
                    ->where('slot_id', $slot1->id)
                    ->firstOrFail();
            }

            // Find next available parent position under sponsor using BFS
            $targetParentPosId = $this->findNextWorkingTreeParentPositionId($sponsorPos->id);
            $targetParentPos = SlotPosition::findOrFail($targetParentPosId);
            $targetParentUser = User::findOrFail($targetParentPos->user_id);

            $existingChildren = $targetParentPos->child_position_ids ?? [];
            $legPosition = count($existingChildren) + 1; // 1, 2, or 3
            $newLevel = $targetParentPos->level + 1;

            // Create new SlotPosition for new user
            $newPos = SlotPosition::create([
                'user_id' => $user->id,
                'slot_id' => $slot1->id,
                'slot_number' => 1,
                'parent_position_id' => $targetParentPos->id,
                'child_position_ids' => [],
                'leg_position' => $legPosition,
                'level' => $newLevel,
                'received_payments_count' => 0,
                'is_completed' => false,
            ]);

            // Append child position ID to parent's children array
            $existingChildren[] = $newPos->id;
            $targetParentPos->child_position_ids = array_values($existingChildren);
            $targetParentPos->save();

            // Update User model parent references
            $user->parent_id = $targetParentUser->member_id;
            if ($targetParentUser->parent_string) {
                $user->parent_string = $targetParentUser->parent_string . ',' . $user->id;
            } else {
                $user->parent_string = $targetParentUser->id . ',' . $user->id;
            }
            $user->save();

            return $newPos;
        });
    }

    /**
     * Breadth-First Search (BFS) to find first node with < 3 children under $rootPosId
     */
    private function findNextWorkingTreeParentPositionId(int $rootPosId): int
    {
        $queue = [$rootPosId];
        $visited = [];

        while (!empty($queue)) {
            $currId = array_shift($queue);

            if (isset($visited[$currId])) {
                continue;
            }
            $visited[$currId] = true;

            $pos = SlotPosition::find($currId);
            if (!$pos) continue;

            $children = $pos->child_position_ids ?? [];
            if (count($children) < self::MAX_CHILDREN) {
                return $currId;
            }

            foreach ($children as $childId) {
                if (!isset($visited[$childId])) {
                    $queue[] = $childId;
                }
            }
        }

        return $rootPosId;
    }

    /**
     * Place user into Global FCFS Autopool for Slots 2 to 12
     */
    public function placeInAutopool(User $user, Slot $slot): SlotPosition
    {
        $existing = SlotPosition::where('user_id', $user->id)
            ->where('slot_id', $slot->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $slot) {
            // Find root position of this slot (Company A)
            $rootPos = SlotPosition::where('slot_id', $slot->id)
                ->whereNull('parent_position_id')
                ->firstOrFail();

            // Find next available position in this slot via global BFS (Top-to-Bottom, Left-to-Right)
            $targetParentPosId = $this->findNextAutopoolParentPositionId($slot->id, $rootPos->id);
            $targetParentPos = SlotPosition::findOrFail($targetParentPosId);

            $existingChildren = $targetParentPos->child_position_ids ?? [];
            $legPosition = count($existingChildren) + 1;
            $newLevel = $targetParentPos->level + 1;

            $newPos = SlotPosition::create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'parent_position_id' => $targetParentPos->id,
                'child_position_ids' => [],
                'leg_position' => $legPosition,
                'level' => $newLevel,
                'received_payments_count' => 0,
                'is_completed' => false,
            ]);

            $existingChildren[] = $newPos->id;
            $targetParentPos->child_position_ids = array_values($existingChildren);
            $targetParentPos->save();

            // Update user's current slot
            if ($user->current_slot_id < $slot->slot_number) {
                $user->current_slot_id = $slot->slot_number;
                $user->save();
            }

            return $newPos;
        });
    }

    /**
     * BFS to find first node with < 3 children in global autopool for a slot
     */
    private function findNextAutopoolParentPositionId(int $slotId, int $rootPosId): int
    {
        $queue = [$rootPosId];
        $visited = [];

        while (!empty($queue)) {
            $currId = array_shift($queue);

            if (isset($visited[$currId])) {
                continue;
            }
            $visited[$currId] = true;

            $pos = SlotPosition::find($currId);
            if (!$pos) continue;

            $children = $pos->child_position_ids ?? [];
            if (count($children) < self::MAX_CHILDREN) {
                return $currId;
            }

            foreach ($children as $childId) {
                if (!isset($visited[$childId])) {
                    $queue[] = $childId;
                }
            }
        }

        return $rootPosId;
    }

    /**
     * Place user into Global FCFS Recycle Pool for a slot
     */
    public function placeInRecyclePool(User $user, Slot $slot, int $cycleNumber): RecyclePosition
    {
        $existing = RecyclePosition::where('user_id', $user->id)
            ->where('slot_id', $slot->id)
            ->where('cycle_number', $cycleNumber)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $slot, $cycleNumber) {
            $rootPos = RecyclePosition::where('slot_id', $slot->id)
                ->where('cycle_number', $cycleNumber)
                ->whereNull('parent_position_id')
                ->first();

            if (!$rootPos) {
                // If no root exists for this cycle, this user becomes the root
                return RecyclePosition::create([
                    'user_id' => $user->id,
                    'slot_id' => $slot->id,
                    'slot_number' => $slot->slot_number,
                    'cycle_number' => $cycleNumber,
                    'parent_position_id' => null,
                    'child_position_ids' => [],
                    'leg_position' => 1,
                    'level' => 1,
                    'received_payments_count' => 0,
                    'is_completed' => false,
                ]);
            }

            // Find next available slot via BFS
            $queue = [$rootPos->id];
            $visited = [];
            $targetParentId = $rootPos->id;

            while (!empty($queue)) {
                $currId = array_shift($queue);
                if (isset($visited[$currId])) continue;
                $visited[$currId] = true;

                $pos = RecyclePosition::find($currId);
                if (!$pos) continue;

                $children = $pos->child_position_ids ?? [];
                if (count($children) < self::MAX_CHILDREN) {
                    $targetParentId = $currId;
                    break;
                }

                foreach ($children as $childId) {
                    if (!isset($visited[$childId])) {
                        $queue[] = $childId;
                    }
                }
            }

            $targetParent = RecyclePosition::findOrFail($targetParentId);
            $existingChildren = $targetParent->child_position_ids ?? [];
            $legPosition = count($existingChildren) + 1;

            $newPos = RecyclePosition::create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'cycle_number' => $cycleNumber,
                'parent_position_id' => $targetParent->id,
                'child_position_ids' => [],
                'leg_position' => $legPosition,
                'level' => $targetParent->level + 1,
                'received_payments_count' => 0,
                'is_completed' => false,
            ]);

            $existingChildren[] = $newPos->id;
            $targetParent->child_position_ids = array_values($existingChildren);
            $targetParent->save();

            return $newPos;
        });
    }
}
