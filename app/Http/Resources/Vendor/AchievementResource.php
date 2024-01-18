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
            'goHighLevelUserId' => $this->program->name,
            'name' => $this->name,
            'address' => $this->badge,
            'city' => $this->reward_points,
            'state' => $this->parent_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $achievement;
    }
}
