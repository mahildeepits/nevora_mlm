<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_number',
        'name',
        'entry_fee',
        'reserved_unit_amount',
        'reserved_count',
        'company_deduction',
        'net_payout_amount',
        'max_recycles',
        'matrix_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'entry_fee' => 'decimal:2',
            'reserved_unit_amount' => 'decimal:2',
            'company_deduction' => 'decimal:2',
            'net_payout_amount' => 'decimal:2',
            'max_recycles' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(SlotPosition::class, 'slot_id');
    }

    public function recyclePositions(): HasMany
    {
        return $this->hasMany(RecyclePosition::class, 'slot_id');
    }
}
