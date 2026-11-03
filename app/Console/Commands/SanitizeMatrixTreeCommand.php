<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlotPosition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SanitizeMatrixTreeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matrix:sanitize-tree {--dry-run : Only report unpaid positions without deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitize 3-matrix tree by purging unpaid users from slot_positions and repairing parent linkages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? 'Running in DRY-RUN mode (no DB changes)...' : 'Sanitizing matrix tree...');

        $positions = SlotPosition::with('user')->get();
        $unpaidPositions = [];

        foreach ($positions as $pos) {
            $user = $pos->user;

            // Root company or admin accounts are always preserved
            if ($user && ($user->member_id === 'COMPANY01' || $user->role_id === 1)) {
                continue;
            }

            // If user does not exist or is_paid is false
            if (!$user || !$user->is_paid) {
                $unpaidPositions[] = $pos;
            }
        }

        $count = count($unpaidPositions);
        if ($count === 0) {
            $this->info('✓ Clean state! No unpaid users found in slot_positions.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} unpaid position(s) residing in slot_positions:");

        DB::transaction(function () use ($unpaidPositions, $isDryRun) {
            foreach ($unpaidPositions as $pos) {
                $memberId = $pos->user ? $pos->user->member_id : "User #{$pos->user_id}";
                $this->line(" - Position #{$pos->id}: Slot {$pos->slot_number}, Member: {$memberId}");

                if (!$isDryRun) {
                    // 1. Remove this position from its parent's child_position_ids array
                    if ($pos->parent_position_id) {
                        $parentPos = SlotPosition::find($pos->parent_position_id);
                        if ($parentPos && is_array($parentPos->child_position_ids)) {
                            $parentPos->child_position_ids = array_values(
                                array_filter($parentPos->child_position_ids, fn($id) => (int)$id !== (int)$pos->id)
                            );
                            $parentPos->save();
                        }
                    }

                    // 2. Clear parent references on any children pointing to this unpaid position
                    if (!empty($pos->child_position_ids) && is_array($pos->child_position_ids)) {
                        SlotPosition::whereIn('id', $pos->child_position_ids)
                            ->update(['parent_position_id' => $pos->parent_position_id]);
                    }

                    // 3. Delete the unpaid slot position
                    $pos->delete();
                }
            }
        });

        if ($isDryRun) {
            $this->info("Dry run complete. {$count} unpaid position(s) identified.");
        } else {
            $this->info("✓ Successfully purged {$count} unpaid position(s). Tree linkages repaired!");
        }

        return Command::SUCCESS;
    }
}

