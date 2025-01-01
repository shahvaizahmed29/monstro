<?php

namespace App\Http\Resources\Member;

use App\Http\Resources\Member\AchievementResource;
use App\Models\MemberRewardClaim;
use App\Models\Reward;
use App\Models\RewardClaim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementRewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reward = Reward::where("achievement_id", $this->achievement->id)->first();
        if($reward) {
            $reward->remainingClaims = $reward->limit_per_member - RewardClaim::where("reward_id", $reward->id)->where("member_id", $this->member_id)->count();
        }
        
        $rewardClaim = [
            'id' => $this->id,
            'note' => $this->note,
            'dateAchieved' => $this->date_achieved,
            'progress' => $this->progress,
            'isExpired' => $this->is_expired,
            'reward' => $reward,
            'achievement' => [
                'id' => $this->achievement->id,
                'name' => $this->achievement->name,
                'badge' => $this->achievement->badge,
                'requiredPoints' => $this->achievement->required_points,
                'image' => $this->image,
            ],

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $rewardClaim;
    }
}
