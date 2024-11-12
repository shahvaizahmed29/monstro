<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignedContractsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locations = [
            'id' => $this->id,
            'content' => $this->content,
            'title' => $this->contract->title,
            'signed' => $this->signed,
            'member' => $this->member,
            'plan' => $this->stripePlan,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            
        ];

        return $locations;
    }
}
