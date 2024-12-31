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
            'program' => $this->program,
            'name' => $this->name,
            'badge' => $this->badge,
            'points' => $this->points,            
            'action' => $this->actions,
            'image' => $this->image,
            'members' => $this->whenLoaded('members', function () {
                return MemberResource::collection($this->members);
            }),
        ];

        return $achievement;
    }
}
