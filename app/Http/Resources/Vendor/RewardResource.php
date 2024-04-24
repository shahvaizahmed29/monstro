<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reward = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'limitPerMember' => $this->limit_per_member,
            'rewardPoints' => $this->reward_points,
            'achievement' => $this->whenLoaded('achievement', function () {
                return AchievementResource::collection($this->achievement);
            }),
            'remainingTrades' => $this->whenLoaded('member_reward_claim', function () {
                return $this->limit_per_member - $this->member_reward_claim->count();
            }),
        ];

        return $reward;
    }
}
