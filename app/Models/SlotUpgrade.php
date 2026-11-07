<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotUpgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slot_position_id',
        'from_slot_number',
        'to_slot_number',
        'reserved_amount',
        'reserved_payments_count',
        'status',
        'admin_id',
        'admin_wallet',
        'user_wallet',
        'tx_hash',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'from_slot_number' => 'integer',
            'to_slot_number' => 'integer',
            'reserved_amount' => 'decimal:2',
            'reserved_payments_count' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function slotPosition(): BelongsTo
    {
        return $this->belongsTo(SlotPosition::class, 'slot_position_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function fromSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'from_slot_number', 'slot_number');
    }

    public function toSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'to_slot_number', 'slot_number');
    }
}

