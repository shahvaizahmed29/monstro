<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProgramsResource extends JsonResource
{
    public function toArray($request)
    {
        $programs = collect($this->programs)->map(function ($program) {
            return [
                'id' => $program['id'],
                'goHighLevelLocationId' => $program['go_high_level_location_id'],
                'name' => $program['name'],
                'address' => $program['address'],
                'city' => $program['city'],
                'state' => $program['state'],
                'logoUrl' => $program['logo_url'],
                'country' => $program['country'],
                'postalCode' => $program['postal_code'],
                'website' => $program['website'],
                'firstName' => $program['first_name'],
                'lastName' => $program['last_name'],
                'email' => $program['email'],
                'phone' => $program['phone'],
                'metaData' => $program['meta_data'],
                'createdAt' => $program['created_at'],
                'updatedAt' => $program['updated_at'],
                'deletedAt' => $program['deleted_at'],
            ];
        })->toArray();
    
        return $programs;
    }
}
