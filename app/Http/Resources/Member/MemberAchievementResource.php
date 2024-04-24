<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\AchievementResource;

class MemberAchievementResource extends JsonResource
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
            'status' => $this->status,
            'note' => $this->note,
            'date_achieved' => $this->date_achieved,
            'achievement' => $this->whenLoaded('achievement', function () {
                return AchievementResource::collection($this->achievement);
            }),
        ];

        return $achievement;
    }
}
