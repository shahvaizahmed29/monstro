<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\LocationResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $program = [
            'id' => $this->id,
            'locationId' => $this->location_id,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'minAge' => $this->min_age,
            'maxAge' => $this->max_age,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'location' => $this->whenLoaded('location', function () {
                return new LocationResource($this->location);
            }),
            'levels' => $this->whenLoaded('levels', function () {
                return ProgramLevelResource::collection($this->levels);
            }),
        ];

        return $program;
    }
}
