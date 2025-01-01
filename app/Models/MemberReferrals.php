<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class MemberReferrals extends Model
{
    use HasFactory;
    protected $fillable = [
        'member_id',
        'location_id',
        'referred_member_id'
    ];

    public function location() {
      return $this->belongsTo(Location::class);
    }

    public function members() {
      return $this->belongsTo(Member::class);
    }

    /**
     * Get the member who referred (the referrer).
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Get the member who was referred.
     */
    public function referredMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'referred_member_id');
    }

    public static function boot()
    {
        parent::boot();

        // Hook into the "created" event
        static::created(function ($referral) {
            // Trigger the updateMemberAchievement logic for the referring member
            if ($referral->member_id) {
                self::updateMemberAchievement($referral->member_id, $referral->location_id);
            }
        });

        // Hook into the "deleted" event (if necessary)
        static::deleted(function ($referral) {
            if ($referral->member_id) {
                self::updateMemberAchievement($referral->member_id, $referral->location_id);
            }
        });
    }

    private static function updateMemberAchievement($memberId, $locationId)
    {
        // Define the action type constant
        $actionType = Action::where('name', Action::NO_OF_REFERRALS_ADDED)->first();

        if (!$actionType) {
            // No action found with the name NO_OF_REFERRALS_ADDED
            return;
        }

        // Find achievements associated with the action type
        $achievements = Achievement::where(["location_id" => $locationId])->whereHas('actions', function ($query) use ($actionType) {
            $query->where('action_id', $actionType->id);
        })->get();

        foreach ($achievements as $achievement) {
            $requiredCount = $achievement->actions()->where('action_id', $actionType->id)->value('count');
            $currentReferralCount = self::where('member_id', $memberId)->count();

            $progress = min(($currentReferralCount / $requiredCount) * 100, 100);

            // Check if the member already has a record for this achievement
            $memberAchievement = MemberAchievement::where('member_id', $memberId)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($memberAchievement) {
                // Update progress if record exists
                $memberAchievement->update([
                    'progress' => $progress,
                    'status' => $progress === 100 ? 'Completed' : 'In Progress',
                ]);
            } else {
                // Create a new record if it doesn't exist
                MemberAchievement::create([
                    'member_id' => $memberId,
                    'achievement_id' => $achievement->id,
                    'progress' => $progress,
                    'status' => $progress === 100 ? 'Completed' : 'In Progress',
                    'Note' => "Achievement for referrals"
                ]);
            }
        }
    }

}
