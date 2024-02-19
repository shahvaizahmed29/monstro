<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $achievement = [
            'id' => $this->id,
            'programName' => $this->program->name,
            'name' => $this->name,
            'badge' => $this->badge,
            'rewardPoints' => $this->reward_points,
            'parentId' => $this->parent_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'action' => $this->whenLoaded('actions', function () {
                return ActionResource::collection($this->actions);
            }),
            'members' => $this->whenLoaded('members', function () {
                return MemberResource::collection($this->members);
            }),
        ];

        return $achievement;
    }
}
