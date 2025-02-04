<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;use Illuminate\Database\Eloquent\SoftDeletes;

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
        'parent_id',
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

    public function rewards(): HasMany
    {
        return $this->hasMany(RewardClaim::class);
    }

    /**
     * Get the referrals made by this member (as the referrer).
     */
    public function referralsMade(): HasMany
    {
        return $this->hasMany(MemberReferrals::class, 'member_id');
    }

    /**
     * Get the referrals where this member was referred (as the referred member).
     */
    public function referralsReceived(): HasMany
    {
        return $this->hasMany(MemberReferrals::class, 'referred_member_id');
    }

    public function familyMembers()
    {
        return $this->belongsToMany(Member::class, 'family_members', 'member_id', 'related_member_id')
            ->withPivot('relationship', 'is_payer')
            ->withTimestamps();
    }

    public function relatedByFamily()
    {
        return $this->belongsToMany(Member::class, 'family_members', 'related_member_id', 'member_id'
        )->withPivot('relationship', 'is_payer')
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(MemberPayment::class, 'payer_id');
    }

    public function programsPaidFor()
    {
        return $this->hasManyThrough(Program::class, MemberPayment::class, 'payer_id', 'id', 'id', 'program_id');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
      }
}
