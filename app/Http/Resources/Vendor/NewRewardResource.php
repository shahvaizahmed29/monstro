<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewRewardResource extends JsonResource
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
            'images' => $this->images,
            'icon' => $this->icon,
            'limitPerMember' => $this->limit_per_member,
            'requiredPoints' => $this->required_points,
            'achievement' => $this->whenLoaded('achievement', function () {
                return new AchievementResource($this->achievement);
            }),
        ];
        return $reward;
    }
}
