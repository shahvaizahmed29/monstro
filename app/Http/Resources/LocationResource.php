<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'goHighLevelLocationId' => $this->go_high_level_location_id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'logoUrl' => $this->logo_url,
            'country' => $this->country,
            'postalCode' => $this->postal_code,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'metaData' => $this->meta_data,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $locations;
    }
}
