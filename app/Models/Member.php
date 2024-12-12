<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class Member extends Model
{
    use HasFactory, SoftDeletes;

    public const ACTIVE = 1; 
    public const INACTIVE = 2;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'referral_code',
        'avatar',
        'current_points',
        'parent_id'
    ];

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'member_locations', 'member_id', 'location_id')->withPivot('stripe_customer_id');
    }    

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'member_achievements', 'member_id', 'achievement_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Member::class, 'parent_id', 'id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'member_programs', 'member_id', 'program_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->reservations()
            ->where('status', Reservation::ACTIVE)
            ->exists();
    }

    public function redeemPoints(): int
    {
        return $this->rewards()
            ->whereHas('reward', fn($query) => $query->where('type', Reward::POINTS))
            ->sum('reward.reward_points');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(MemberRewardClaim::class);
    }
}
