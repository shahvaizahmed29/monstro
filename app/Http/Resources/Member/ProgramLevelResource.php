<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\ProgramResource;


class ProgramLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $program_level = [
            'id' => $this->id,
            'name' => $this->name,
            'programId' => $this->program_id,
            'parentId' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function () {
                return new ProgramLevelResource($this->parent);
            }),
            'program' => $this->whenLoaded('program', function () {
                return new ProgramResource($this->program);
            })
        ];
                
        return $program_level;
    }
}
