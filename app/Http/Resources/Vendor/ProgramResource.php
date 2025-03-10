<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\LocationResource;

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
            'avatar' => $this->avatar,
            'vendorId' => $this->location->vendor_id,
            'location' => $this->whenLoaded('location', function () {
                return new LocationResource($this->location);
            }),
            'programLevels' => $this->whenLoaded('programLevels', function () {
                return ProgramLevelResource::collection($this->programLevels);
            }),
            'plans' => $this->memberPlans
        ];

        return $program;
    }
}
