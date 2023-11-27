<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vendor = [
            'userId' => $this->user_id,
            'companyName' => $this->company_name,
            'companyEmail' => $this->company_email,
            'companWebsite' => $this->company_website,
            'companyAddress' => $this->company_address,
            'logo' => $this->logo,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'locations' => LocationResource::collection($this->locations)
        ];

        return $vendor;
    }
}
