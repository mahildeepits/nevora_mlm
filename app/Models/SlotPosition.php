<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slot_id',
        'slot_number',
        'parent_position_id',
        'child_position_ids',
        'leg_position',
        'level',
        'received_payments_count',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'child_position_ids' => 'array',
            'leg_position' => 'integer',
            'level' => 'integer',
            'received_payments_count' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }

    public function parentPosition(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_position_id');
    }
}
