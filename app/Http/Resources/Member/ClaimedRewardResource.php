<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\MemberResource;
use App\Http\Resources\Member\RewardResource;

class ClaimedRewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rewardClaim = [
            'member' => $this->whenLoaded('member', function () {
                return new MemberResource($this->member);
            }),
            'reward' => $this->whenLoaded('reward', function () {
                return new RewardResource($this->reward);
            }),
            'currentPoints' => $this->current_points,
            'dateClaimed' => $this->date_claimed,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $rewardClaim;
    }
}
