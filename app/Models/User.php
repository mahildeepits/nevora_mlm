<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'member_id',
        'name',
        'email',
        'password',
        'mobile',
        'profile_image',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'sponsor_id',
        'parent_id',
        'parent_string',
        'role_id',
        'is_paid',
        'current_slot_id',
        'wallet_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_paid' => 'boolean',
            'current_slot_id' => 'integer',
            'role_id' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role_id === 1;
    }

    /**
     * Check if this user is a system exempt account (COMPANY01, MEMBER01, ADMIN01, or admin role)
     * Exempt accounts are not restricted by the 3 direct limit and are always income eligible.
     */
    public function isExemptAccount(): bool
    {
        return in_array($this->member_id, ['COMPANY01', 'MEMBER01', 'ADMIN01']) || $this->role_id === 1;
    }

    /**
     * Check if user is eligible to receive payments/income.
     * Requires at least 2 active direct referrals (unless an exempt account).
     */
    public function isEligibleForIncome(): bool
    {
        if ($this->isExemptAccount()) {
            return true;
        }

        return $this->activeDirects()->count() >= 2;
    }

    /**
     * Check if user has reached the maximum allowed direct referrals (max 3).
     * Exempt accounts (COMPANY01, MEMBER01) have no limit.
     */
    public function hasReachedSponsorLimit(): bool
    {
        if ($this->isExemptAccount()) {
            return false;
        }

        return $this->directs()->count() >= 3;
    }

    /**
     * Formatted direct sponsor quota string (e.g. "2 / 3" or "Unlimited")
     */
    public function getDirectQuotaAttribute(): string
    {
        if ($this->isExemptAccount()) {
            return 'Unlimited';
        }

        return $this->directs()->count() . ' / 3';
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id', 'member_id');
    }

    public function placementParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'member_id');
    }

    public function directs(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_id', 'member_id')->latest();
    }

    public function activeDirects(): HasMany
    {
        return $this->directs()->where('is_paid', true);
    }

    public function slotPositions(): HasMany
    {
        return $this->hasMany(SlotPosition::class, 'user_id');
    }

    public function recyclePositions(): HasMany
    {
        return $this->hasMany(RecyclePosition::class, 'user_id');
    }

    public function unifiedTransactions(): HasMany
    {
        return $this->hasMany(UnifiedTransaction::class, 'user_id')->latest();
    }

    public function slotUpgrades(): HasMany
    {
        return $this->hasMany(SlotUpgrade::class, 'user_id')->latest();
    }

    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/profile_images/' . $this->profile_image);
        }
        return $this->is_paid
            ? asset('assets/images/user-green.png')
            : asset('assets/images/user-red.png');
    }

    public function getEarningsSummary(): array
    {
        $slotIncome = $this->unifiedTransactions()
            ->where('category', 'Slot Income')
            ->where('transaction_type', 'Credit')
            ->where('status', 'Completed')
            ->sum('amount');

        $recycleIncome = $this->unifiedTransactions()
            ->where('category', 'Recycle Income')
            ->where('transaction_type', 'Credit')
            ->where('status', 'Completed')
            ->sum('amount');

        $reservedCredits = $this->unifiedTransactions()
            ->where('category', 'Reserved for Next Slot')
            ->where('transaction_type', 'Credit')
            ->where('status', 'Completed')
            ->sum('amount');

        $reservedDebits = $this->unifiedTransactions()
            ->where('category', 'Reserved for Next Slot')
            ->where('transaction_type', 'Debit')
            ->where('status', 'Completed')
            ->sum('amount');

        $reservedAmount = max(0, (float) ($reservedCredits - $reservedDebits));
        $totalEarnings = $slotIncome + $recycleIncome;

        return [
            'total_earnings' => (float) $totalEarnings,
            'slot_income' => (float) $slotIncome,
            'recycle_income' => (float) $recycleIncome,
            'reserved_amount' => (float) $reservedAmount,
        ];
    }

    public function getDownlineUsers()
    {
        return self::whereRaw("FIND_IN_SET(?, parent_string)", [$this->id])
            ->where('id', '!=', $this->id)
            ->get();
    }
}
